<?php
/**
 * Analytics tab (rendered by bpas-pro-admin.js from GET bpas/v1/analytics).
 *
 * @package BPAS_Pro
 * @var bool $tracking Share analytics feature on.
 */

defined( 'ABSPATH' ) || exit;
?>
<?php if ( ! $tracking ) : ?>
	<div class="bpas-card bpas-card--pro">
		<h2 class="bpas-card__title"><?php esc_html_e( 'Clicks and visits are not being counted', 'buddypress-activity-share-pro' ); ?></h2>
		<p class="bpas-card__desc"><?php esc_html_e( 'Reposts and sends are always counted. Turn on Share analytics on the Features tab to also count clicks on share buttons and visits from shared links.', 'buddypress-activity-share-pro' ); ?></p>
	</div>
<?php endif; ?>

<section class="bpas-card" data-bpas-analytics>
	<header class="bpas-card__head bpas-pro-analytics__head">
		<h2 class="bpas-card__title"><?php esc_html_e( 'Share analytics', 'buddypress-activity-share-pro' ); ?></h2>
		<div class="bpas-pro-analytics__filters">
			<label for="bpas-days" class="screen-reader-text"><?php esc_html_e( 'Period', 'buddypress-activity-share-pro' ); ?></label>
			<select id="bpas-days" data-bpas-days>
				<option value="7"><?php esc_html_e( 'Last 7 days', 'buddypress-activity-share-pro' ); ?></option>
				<option value="30" selected><?php esc_html_e( 'Last 30 days', 'buddypress-activity-share-pro' ); ?></option>
				<option value="90"><?php esc_html_e( 'Last 90 days', 'buddypress-activity-share-pro' ); ?></option>
			</select>
			<label for="bpas-object-type" class="screen-reader-text"><?php esc_html_e( 'Content', 'buddypress-activity-share-pro' ); ?></label>
			<select id="bpas-object-type" data-bpas-object-type>
				<option value=""><?php esc_html_e( 'All content', 'buddypress-activity-share-pro' ); ?></option>
				<option value="activity"><?php echo esc_html( _x( 'Activity', 'content type', 'buddypress-activity-share-pro' ) ); ?></option>
				<option value="post"><?php esc_html_e( 'Posts and pages', 'buddypress-activity-share-pro' ); ?></option>
			</select>
			<button type="button" class="button" data-bpas-csv><?php esc_html_e( 'Export CSV', 'buddypress-activity-share-pro' ); ?></button>
		</div>
	</header>

	<ul class="bpas-pro-stats">
		<li><span class="bpas-pro-stats__value" data-bpas-stat="share">-</span><span class="bpas-pro-stats__label"><?php esc_html_e( 'Shares to other sites', 'buddypress-activity-share-pro' ); ?></span></li>
		<li><span class="bpas-pro-stats__value" data-bpas-stat="repost">-</span><span class="bpas-pro-stats__label"><?php esc_html_e( 'Reposts', 'buddypress-activity-share-pro' ); ?></span></li>
		<li><span class="bpas-pro-stats__value" data-bpas-stat="send">-</span><span class="bpas-pro-stats__label"><?php esc_html_e( 'Sent to friends', 'buddypress-activity-share-pro' ); ?></span></li>
		<li><span class="bpas-pro-stats__value" data-bpas-stat="visit">-</span><span class="bpas-pro-stats__label"><?php esc_html_e( 'Visits from shared links', 'buddypress-activity-share-pro' ); ?></span></li>
	</ul>

	<h3 class="bpas-pro-analytics__subtitle"><?php esc_html_e( 'By network', 'buddypress-activity-share-pro' ); ?></h3>
	<ul class="bpas-pro-bars" data-bpas-networks></ul>

	<h3 class="bpas-pro-analytics__subtitle"><?php esc_html_e( 'Most shared', 'buddypress-activity-share-pro' ); ?></h3>
	<div class="bpas-pro-table-wrap">
		<table class="widefat striped bpas-pro-table" data-bpas-top>
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Item', 'buddypress-activity-share-pro' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Type', 'buddypress-activity-share-pro' ); ?></th>
					<th scope="col" class="num"><?php echo esc_html( _x( 'Shares', 'number of shares', 'buddypress-activity-share-pro' ) ); ?></th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
	<nav class="bpas-pro-pager" aria-label="<?php esc_attr_e( 'Most shared pages', 'buddypress-activity-share-pro' ); ?>" data-bpas-pager></nav>
	<p class="bpas-pro-analytics__status" role="status" aria-live="polite" data-bpas-analytics-status></p>
</section>
