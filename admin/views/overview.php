<?php
/**
 * Overview tab body — at-a-glance stats + current config + quick actions.
 *
 * Stats are read with COUNT(*) aggregates against the indexed
 * {prefix}bp_share_post_tracking table (idx_post_shares / idx_user_shares /
 * idx_date_shares) and cached in a transient so the page does not query
 * cold on every load. No unbounded SELECT over share rows. Empty + error
 * states are handled. Big-site readiness: §11 of the plan.
 *
 * @var string $page_url admin.php?page=buddypress-share.
 *
 * @package Buddypress_Share
 * @since   2.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cached share stats. Counts only; cached 5 minutes (invalidated whenever
 * the public cache is cleared via bp_share_clear_public_cache).
 *
 * @return array{total:int,today:int,error:bool}
 */
$bpas_get_overview_stats = static function () {
	$cached = get_transient( 'bpas_overview_stats' );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;
	$stats = array(
		'total' => 0,
		'today' => 0,
		'error' => false,
	);

	$table = $wpdb->prefix . 'bp_share_post_tracking';
	// Confirm the table exists before counting (fresh installs may not have it yet).
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	if ( $exists === $table ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$stats['total'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
		$stats['today'] = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM `{$table}` WHERE shared_at >= %s", gmdate( 'Y-m-d 00:00:00' ) )
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	set_transient( 'bpas_overview_stats', $stats, 5 * MINUTE_IN_SECONDS );
	return $stats;
};

$bpas_stats = $bpas_get_overview_stats();

$bpas_enabled_services = get_site_option( 'bp_share_services', array() );
if ( ! is_array( $bpas_enabled_services ) ) {
	$bpas_enabled_services = array();
}
$bpas_active_count  = count( $bpas_enabled_services );
$bpas_sharing_on    = (int) get_site_option( 'bp_share_services_enable', 1 ) === 1;
$bpas_guest_on      = (int) get_site_option( 'bp_share_services_logout_enable', 1 ) === 1;
$bpas_icon_settings = get_option( 'bpas_icon_color_settings', array() );
$bpas_icon_style    = isset( $bpas_icon_settings['icon_style'] ) ? $bpas_icon_settings['icon_style'] : 'circle';
?>
<h2 class="bpas-section-title"><?php esc_html_e( 'Overview', 'buddypress-share' ); ?></h2>
<p class="bpas-section-intro"><?php esc_html_e( 'A quick look at your sharing activity and current setup.', 'buddypress-share' ); ?></p>

<?php if ( $bpas_stats['error'] ) : ?>
	<div class="bpas-notice bpas-notice--info">
		<span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
		<?php esc_html_e( 'Share statistics are temporarily unavailable. Please try again later.', 'buddypress-share' ); ?>
	</div>
<?php endif; ?>

<div class="bpas-stats-grid">
	<div class="bpas-stat">
		<p class="bpas-stat__label"><?php esc_html_e( 'Total post shares', 'buddypress-share' ); ?></p>
		<p class="bpas-stat__value"><?php echo esc_html( number_format_i18n( $bpas_stats['total'] ) ); ?></p>
		<p class="bpas-stat__trend"><?php esc_html_e( 'All time', 'buddypress-share' ); ?></p>
	</div>
	<div class="bpas-stat">
		<p class="bpas-stat__label"><?php esc_html_e( 'Shares today', 'buddypress-share' ); ?></p>
		<p class="bpas-stat__value"><?php echo esc_html( number_format_i18n( $bpas_stats['today'] ) ); ?></p>
		<p class="bpas-stat__trend"><?php esc_html_e( 'Since midnight', 'buddypress-share' ); ?></p>
	</div>
	<div class="bpas-stat">
		<p class="bpas-stat__label"><?php esc_html_e( 'Active networks', 'buddypress-share' ); ?></p>
		<p class="bpas-stat__value"><?php echo esc_html( number_format_i18n( $bpas_active_count ) ); ?></p>
		<p class="bpas-stat__trend">
			<a href="<?php echo esc_url( $page_url . '&tab=networks' ); ?>"><?php esc_html_e( 'Manage networks', 'buddypress-share' ); ?></a>
		</p>
	</div>
</div>

<div class="bpas-card">
	<div class="bpas-card__head">
		<p class="bpas-card__title"><?php esc_html_e( 'Current setup', 'buddypress-share' ); ?></p>
	</div>
	<div class="bpas-card__body">
		<div class="bp-share-checkbox-group">
			<div class="bp-share-checkbox-item" style="cursor:default;">
				<span class="checkbox-label">
					<span class="checkbox-icon dashicons <?php echo esc_attr( $bpas_sharing_on ? 'dashicons-yes-alt' : 'dashicons-marker' ); ?>" aria-hidden="true"></span>
					<?php echo $bpas_sharing_on ? esc_html__( 'Social sharing is on', 'buddypress-share' ) : esc_html__( 'Social sharing is off', 'buddypress-share' ); ?>
				</span>
			</div>
			<div class="bp-share-checkbox-item" style="cursor:default;">
				<span class="checkbox-label">
					<span class="checkbox-icon dashicons <?php echo esc_attr( $bpas_guest_on ? 'dashicons-yes-alt' : 'dashicons-marker' ); ?>" aria-hidden="true"></span>
					<?php echo $bpas_guest_on ? esc_html__( 'Guests can share', 'buddypress-share' ) : esc_html__( 'Guest sharing is off', 'buddypress-share' ); ?>
				</span>
			</div>
			<div class="bp-share-checkbox-item" style="cursor:default;">
				<span class="checkbox-label">
					<span class="checkbox-icon dashicons dashicons-art" aria-hidden="true"></span>
					<?php
					/* translators: %s: current icon style name. */
					echo esc_html( sprintf( __( 'Button style: %s', 'buddypress-share' ), ucfirst( $bpas_icon_style ) ) );
					?>
				</span>
			</div>
		</div>
	</div>
</div>

<div class="bpas-card">
	<div class="bpas-card__head">
		<p class="bpas-card__title"><?php esc_html_e( 'Quick actions', 'buddypress-share' ); ?></p>
	</div>
	<div class="bpas-card__body">
		<div class="bpas-quick-actions">
			<a href="<?php echo esc_url( $page_url . '&tab=networks' ); ?>" class="bpas-btn bpas-btn-primary">
				<span class="dashicons dashicons-share-alt2" aria-hidden="true"></span>
				<?php esc_html_e( 'Choose networks', 'buddypress-share' ); ?>
			</a>
			<a href="<?php echo esc_url( $page_url . '&tab=display' ); ?>" class="bpas-btn bpas-btn-secondary">
				<span class="dashicons dashicons-art" aria-hidden="true"></span>
				<?php esc_html_e( 'Customize buttons', 'buddypress-share' ); ?>
			</a>
			<a href="<?php echo esc_url( $page_url . '&onboarding=1' ); ?>" class="bpas-btn bpas-btn-secondary">
				<span class="dashicons dashicons-welcome-learn-more" aria-hidden="true"></span>
				<?php esc_html_e( 'Setup guide', 'buddypress-share' ); ?>
			</a>
		</div>
	</div>
</div>
