<?php
/**
 * Analytics tab body — surfaces the share data the plugin already collects.
 *
 * The plugin tracks two independent streams that were never shown to the admin
 * before 2.3.0 (P1-1):
 *
 *  - Post-type shares — every share is a row in `{prefix}bp_share_post_tracking`.
 *    Read here via BP_Share_Post_Type_Tracker::get_overall_stats(), which runs
 *    indexed COUNT(*) / GROUP BY aggregates (idx_post_shares / idx_user_shares /
 *    idx_date_shares) — no unbounded SELECT over share rows.
 *  - Activity reshares — counted in the `share_count` activity meta. A bounded,
 *    top-N query (LIMIT 10) over the BP activity-meta table, cached.
 *
 * All output is cached in a 5-minute transient keyed by the date range, handles
 * empty / error states, and never queries cold on every load. Big-site
 * readiness: COUNT/GROUP BY + LIMIT only.
 *
 * @var string $page_url admin.php?page=buddypress-share.
 *
 * @package Buddypress_Share
 * @since   2.3.0
 */

defined( 'ABSPATH' ) || exit;

// --- Date range filter (read-only GET; no nonce needed for a filter) -------.
$bpas_ranges = array(
	'7'   => __( 'Last 7 days', 'buddypress-share' ),
	'30'  => __( 'Last 30 days', 'buddypress-share' ),
	'90'  => __( 'Last 90 days', 'buddypress-share' ),
	'365' => __( 'Last 12 months', 'buddypress-share' ),
);

$bpas_range = isset( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : '30'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if ( ! array_key_exists( $bpas_range, $bpas_ranges ) ) {
	$bpas_range = '30';
}
$bpas_days      = (int) $bpas_range;
$bpas_date_from = gmdate( 'Y-m-d', strtotime( '-' . $bpas_days . ' days' ) );
$bpas_date_to   = gmdate( 'Y-m-d' );

/**
 * Activity reshare stats (top reshared activities + total). Cached.
 *
 * @param int $days Range in days (unused in the query; reshare meta has no
 *                  date column, so this reports all-time totals — labelled as
 *                  such in the UI).
 * @return array{total:int,top:array,error:bool}
 */
$bpas_get_activity_stats = static function () {
	$cached = get_transient( 'bpas_analytics_activity' );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;
	$out = array(
		'total' => 0,
		'top'   => array(),
		'error' => false,
	);

	if ( ! function_exists( 'buddypress' ) || ! bp_is_active( 'activity' ) ) {
		set_transient( 'bpas_analytics_activity', $out, 5 * MINUTE_IN_SECONDS );
		return $out;
	}

	$meta_table = buddypress()->activity->table_name_meta;

	// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	// Sum of all share_count values = total activity reshares. Indexed on
	// meta_key via idx_bp_share_count (created in the activator).
	$out['total'] = (int) $wpdb->get_var(
		$wpdb->prepare( "SELECT COALESCE(SUM(meta_value),0) FROM `{$meta_table}` WHERE meta_key = %s", 'share_count' )
	);

	// Top 10 most-reshared activities. Bounded LIMIT, ordered by the indexed
	// numeric meta value.
	$out['top'] = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT activity_id, CAST(meta_value AS UNSIGNED) AS shares
			 FROM `{$meta_table}`
			 WHERE meta_key = %s AND CAST(meta_value AS UNSIGNED) > 0
			 ORDER BY shares DESC
			 LIMIT 10",
			'share_count'
		),
		ARRAY_A
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	if ( ! is_array( $out['top'] ) ) {
		$out['top'] = array();
	}

	set_transient( 'bpas_analytics_activity', $out, 5 * MINUTE_IN_SECONDS );
	return $out;
};

// --- Gather data -----------------------------------------------------------.
$bpas_post_stats = array();
if ( class_exists( 'BP_Share_Post_Type_Tracker' ) ) {
	$bpas_cache_key  = 'bpas_analytics_post_' . $bpas_range;
	$bpas_post_stats = get_transient( $bpas_cache_key );
	if ( false === $bpas_post_stats || ! is_array( $bpas_post_stats ) ) {
		$bpas_post_stats = BP_Share_Post_Type_Tracker::get_instance()->get_overall_stats(
			array(
				'date_from' => $bpas_date_from,
				'date_to'   => $bpas_date_to,
			)
		);
		if ( ! is_array( $bpas_post_stats ) ) {
			$bpas_post_stats = array();
		}
		set_transient( $bpas_cache_key, $bpas_post_stats, 5 * MINUTE_IN_SECONDS );
	}
}

$bpas_total_shares = isset( $bpas_post_stats['total_shares'] ) ? (int) $bpas_post_stats['total_shares'] : 0;
$bpas_unique_posts = isset( $bpas_post_stats['unique_posts'] ) ? (int) $bpas_post_stats['unique_posts'] : 0;
$bpas_unique_users = isset( $bpas_post_stats['unique_users'] ) ? (int) $bpas_post_stats['unique_users'] : 0;
$bpas_services     = isset( $bpas_post_stats['services'] ) && is_array( $bpas_post_stats['services'] ) ? $bpas_post_stats['services'] : array();
$bpas_top_posts    = isset( $bpas_post_stats['top_posts'] ) && is_array( $bpas_post_stats['top_posts'] ) ? $bpas_post_stats['top_posts'] : array();

$bpas_activity = $bpas_get_activity_stats();

// Largest service count, for the proportional bars.
$bpas_service_max = 0;
foreach ( $bpas_services as $bpas_svc ) {
	$bpas_service_max = max( $bpas_service_max, (int) $bpas_svc['count'] );
}
?>
<h2 class="bpas-section-title"><?php esc_html_e( 'Analytics', 'buddypress-share' ); ?></h2>
<p class="bpas-section-intro"><?php esc_html_e( 'Share activity collected by the plugin. Post-type figures respect the selected date range; activity reshares are all-time.', 'buddypress-share' ); ?></p>

<form method="get" class="bpas-analytics-filter">
	<input type="hidden" name="page" value="buddypress-share" />
	<input type="hidden" name="tab" value="analytics" />
	<label for="bpas-range" class="bp-share-field__label"><?php esc_html_e( 'Date range', 'buddypress-share' ); ?></label>
	<select name="range" id="bpas-range" onchange="this.form.submit()">
		<?php foreach ( $bpas_ranges as $bpas_range_key => $bpas_range_label ) : ?>
			<option value="<?php echo esc_attr( $bpas_range_key ); ?>" <?php selected( $bpas_range, $bpas_range_key ); ?>>
				<?php echo esc_html( $bpas_range_label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<noscript><button type="submit" class="button"><?php esc_html_e( 'Apply', 'buddypress-share' ); ?></button></noscript>
</form>

<div class="bpas-stats-grid">
	<div class="bpas-stat">
		<p class="bpas-stat__label"><?php esc_html_e( 'Post shares', 'buddypress-share' ); ?></p>
		<p class="bpas-stat__value"><?php echo esc_html( number_format_i18n( $bpas_total_shares ) ); ?></p>
		<p class="bpas-stat__trend"><?php echo esc_html( $bpas_ranges[ $bpas_range ] ); ?></p>
	</div>
	<div class="bpas-stat">
		<p class="bpas-stat__label"><?php esc_html_e( 'Posts shared', 'buddypress-share' ); ?></p>
		<p class="bpas-stat__value"><?php echo esc_html( number_format_i18n( $bpas_unique_posts ) ); ?></p>
		<p class="bpas-stat__trend"><?php esc_html_e( 'Distinct posts', 'buddypress-share' ); ?></p>
	</div>
	<div class="bpas-stat">
		<p class="bpas-stat__label"><?php esc_html_e( 'Members sharing', 'buddypress-share' ); ?></p>
		<p class="bpas-stat__value"><?php echo esc_html( number_format_i18n( $bpas_unique_users ) ); ?></p>
		<p class="bpas-stat__trend"><?php esc_html_e( 'Distinct sharers', 'buddypress-share' ); ?></p>
	</div>
	<div class="bpas-stat">
		<p class="bpas-stat__label"><?php esc_html_e( 'Activity reshares', 'buddypress-share' ); ?></p>
		<p class="bpas-stat__value"><?php echo esc_html( number_format_i18n( $bpas_activity['total'] ) ); ?></p>
		<p class="bpas-stat__trend"><?php esc_html_e( 'All time', 'buddypress-share' ); ?></p>
	</div>
</div>

<div class="bpas-card">
	<div class="bpas-card__head">
		<p class="bpas-card__title"><?php esc_html_e( 'Shares by network', 'buddypress-share' ); ?></p>
	</div>
	<div class="bpas-card__body">
		<?php if ( empty( $bpas_services ) ) : ?>
			<div class="bpas-empty-state">
				<span class="bpas-empty-state__icon" aria-hidden="true"><span class="dashicons dashicons-chart-bar"></span></span>
				<p class="bpas-empty-state__title"><?php esc_html_e( 'No post shares yet', 'buddypress-share' ); ?></p>
				<p class="bpas-empty-state__desc"><?php esc_html_e( 'Network breakdowns appear here once members start sharing posts.', 'buddypress-share' ); ?></p>
			</div>
		<?php else : ?>
			<ul class="bpas-bar-list">
				<?php foreach ( $bpas_services as $bpas_svc ) : ?>
					<?php
					$bpas_svc_count = (int) $bpas_svc['count'];
					$bpas_svc_name  = ucfirst( (string) $bpas_svc['service'] );
					$bpas_svc_pct   = $bpas_service_max > 0 ? round( ( $bpas_svc_count / $bpas_service_max ) * 100 ) : 0;
					?>
					<li class="bpas-bar-list__row">
						<span class="bpas-bar-list__label"><?php echo esc_html( $bpas_svc_name ); ?></span>
						<span class="bpas-bar-list__track">
							<span class="bpas-bar-list__fill" style="width:<?php echo esc_attr( $bpas_svc_pct ); ?>%"></span>
						</span>
						<span class="bpas-bar-list__value"><?php echo esc_html( number_format_i18n( $bpas_svc_count ) ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</div>

<div class="bpas-card">
	<div class="bpas-card__head">
		<p class="bpas-card__title"><?php esc_html_e( 'Most-shared posts', 'buddypress-share' ); ?></p>
	</div>
	<div class="bpas-card__body">
		<?php if ( empty( $bpas_top_posts ) ) : ?>
			<div class="bpas-empty-state">
				<span class="bpas-empty-state__icon" aria-hidden="true"><span class="dashicons dashicons-admin-post"></span></span>
				<p class="bpas-empty-state__title"><?php esc_html_e( 'No shared posts in this range', 'buddypress-share' ); ?></p>
				<p class="bpas-empty-state__desc"><?php esc_html_e( 'Try a wider date range, or check back after members share some posts.', 'buddypress-share' ); ?></p>
			</div>
		<?php else : ?>
			<table class="bpas-data-table widefat striped">
				<caption class="screen-reader-text"><?php esc_html_e( 'Most-shared posts in the selected range', 'buddypress-share' ); ?></caption>
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Post', 'buddypress-share' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Type', 'buddypress-share' ); ?></th>
						<th scope="col" class="bpas-data-table__num"><?php esc_html_e( 'Shares', 'buddypress-share' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $bpas_top_posts as $bpas_row ) : ?>
						<?php
						$bpas_pid   = isset( $bpas_row['post_id'] ) ? (int) $bpas_row['post_id'] : 0;
						$bpas_title = $bpas_pid ? get_the_title( $bpas_pid ) : '';
						if ( '' === $bpas_title ) {
							/* translators: %d: post ID. */
							$bpas_title = sprintf( __( 'Post #%d', 'buddypress-share' ), $bpas_pid );
						}
						$bpas_edit = $bpas_pid ? get_edit_post_link( $bpas_pid ) : '';
						?>
						<tr>
							<td>
								<?php if ( $bpas_edit ) : ?>
									<a href="<?php echo esc_url( $bpas_edit ); ?>"><?php echo esc_html( $bpas_title ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $bpas_title ); ?>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( isset( $bpas_row['post_type'] ) ? $bpas_row['post_type'] : '' ); ?></td>
							<td class="bpas-data-table__num"><?php echo esc_html( number_format_i18n( isset( $bpas_row['share_count'] ) ? (int) $bpas_row['share_count'] : 0 ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>

<div class="bpas-card">
	<div class="bpas-card__head">
		<p class="bpas-card__title"><?php esc_html_e( 'Most-reshared activities', 'buddypress-share' ); ?></p>
	</div>
	<div class="bpas-card__body">
		<?php if ( empty( $bpas_activity['top'] ) ) : ?>
			<div class="bpas-empty-state">
				<span class="bpas-empty-state__icon" aria-hidden="true"><span class="dashicons dashicons-share"></span></span>
				<p class="bpas-empty-state__title"><?php esc_html_e( 'No reshared activities yet', 'buddypress-share' ); ?></p>
				<p class="bpas-empty-state__desc"><?php esc_html_e( 'Activity reshare counts appear here as members reshare each other&#8217;s posts.', 'buddypress-share' ); ?></p>
			</div>
		<?php else : ?>
			<table class="bpas-data-table widefat striped">
				<caption class="screen-reader-text"><?php esc_html_e( 'Most-reshared activities (all time)', 'buddypress-share' ); ?></caption>
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Activity', 'buddypress-share' ); ?></th>
						<th scope="col" class="bpas-data-table__num"><?php esc_html_e( 'Reshares', 'buddypress-share' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $bpas_activity['top'] as $bpas_act ) : ?>
						<?php
						$bpas_aid  = isset( $bpas_act['activity_id'] ) ? (int) $bpas_act['activity_id'] : 0;
						$bpas_link = ( $bpas_aid && function_exists( 'bp_activity_get_permalink' ) ) ? bp_activity_get_permalink( $bpas_aid ) : '';
						/* translators: %d: activity ID. */
						$bpas_label = sprintf( __( 'Activity #%d', 'buddypress-share' ), $bpas_aid );
						?>
						<tr>
							<td>
								<?php if ( $bpas_link ) : ?>
									<a href="<?php echo esc_url( $bpas_link ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $bpas_label ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $bpas_label ); ?>
								<?php endif; ?>
							</td>
							<td class="bpas-data-table__num"><?php echo esc_html( number_format_i18n( isset( $bpas_act['shares'] ) ? (int) $bpas_act['shares'] : 0 ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>
