<?php
/**
 * WB Plugins hub — landing dashboard at ?page=wbcomplugins.
 *
 * Lists every Wbcom plugin that has registered a submenu under the
 * shared wbcomplugins parent. Peer plugins appear automatically.
 * Legacy wrapper helper pages are filtered out.
 * See references/wbcom-wrapper-migration.md Part 15.
 *
 * @package Buddypress_Share
 * @since   2.3.0
 */

defined( 'ABSPATH' ) || exit;

$submenu_entries = isset( $GLOBALS['submenu']['wbcomplugins'] ) && is_array( $GLOBALS['submenu']['wbcomplugins'] )
	? $GLOBALS['submenu']['wbcomplugins']
	: array();

$wrapper_helper_slugs = apply_filters(
	'wbcom_hub_wrapper_helper_slugs',
	array(
		'wbcom-plugins-page',
		'wbcom-themes-page',
		'wbcom-support-page',
		'wbcom-license-page',
	)
);

$bpas_hub_plugins = array();
foreach ( $submenu_entries as $entry ) {
	$slug = isset( $entry[2] ) ? (string) $entry[2] : '';
	if ( '' === $slug || 'wbcomplugins' === $slug ) {
		continue;
	}
	if ( in_array( $slug, $wrapper_helper_slugs, true ) ) {
		continue;
	}
	$bpas_hub_plugins[] = array(
		'slug'       => $slug,
		'menu_title' => isset( $entry[0] ) ? wp_strip_all_tags( (string) $entry[0] ) : $slug,
		'page_title' => isset( $entry[3] ) ? wp_strip_all_tags( (string) $entry[3] ) : '',
		'url'        => admin_url( 'admin.php?page=' . rawurlencode( $slug ) ),
	);
}

$plugin_count = count( $bpas_hub_plugins );
?>
<div class="wrap bpas-admin">
	<header class="bpas-page-header">
		<div class="bpas-page-header__title">
			<span class="dashicons dashicons-lightbulb" aria-hidden="true"></span>
			<div>
				<h1><?php esc_html_e( 'WB Plugins', 'buddypress-share' ); ?></h1>
				<p class="bpas-page-header__subtitle">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: active Wbcom plugin count */
							_n(
								'%d Wbcom plugin active on this site.',
								'%d Wbcom plugins active on this site.',
								$plugin_count,
								'buddypress-share'
							),
							$plugin_count
						)
					);
					?>
				</p>
			</div>
		</div>
	</header>

	<?php if ( 0 === $plugin_count ) : ?>
		<div class="bpas-empty-state">
			<span class="bpas-empty-state__icon" aria-hidden="true">
				<span class="dashicons dashicons-lightbulb"></span>
			</span>
			<p class="bpas-empty-state__title"><?php esc_html_e( 'No Wbcom plugins attached to this hub yet', 'buddypress-share' ); ?></p>
			<p class="bpas-empty-state__desc">
				<?php esc_html_e( 'Activate one or more Wbcom plugins and they will appear here automatically.', 'buddypress-share' ); ?>
			</p>
		</div>
	<?php else : ?>
		<div class="bpas-hub-grid">
			<?php foreach ( $bpas_hub_plugins as $bpas_p ) : ?>
				<a href="<?php echo esc_url( $bpas_p['url'] ); ?>" class="bpas-hub-card">
					<span class="bpas-hub-card__icon" aria-hidden="true">
						<span class="dashicons dashicons-admin-plugins"></span>
					</span>
					<span class="bpas-hub-card__title"><?php echo esc_html( $bpas_p['menu_title'] ); ?></span>
					<?php if ( ! empty( $bpas_p['page_title'] ) && $bpas_p['page_title'] !== $bpas_p['menu_title'] ) : ?>
						<span class="bpas-hub-card__subtitle"><?php echo esc_html( $bpas_p['page_title'] ); ?></span>
					<?php endif; ?>
					<span class="bpas-hub-card__cta">
						<?php esc_html_e( 'Open settings', 'buddypress-share' ); ?>
						<span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="bpas-card" style="margin-top: 20px;">
		<div class="bpas-card__head">
			<p class="bpas-card__title"><?php esc_html_e( 'About WB Plugins', 'buddypress-share' ); ?></p>
		</div>
		<div class="bpas-card__body">
			<p style="margin: 0 0 8px;">
				<?php esc_html_e( 'This hub is the single entry point for every Wbcom Designs plugin installed on your site. Each plugin keeps its own settings and data.', 'buddypress-share' ); ?>
			</p>
			<p style="margin: 0;">
				<a href="https://wbcomdesigns.com/" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Visit wbcomdesigns.com for more plugins and themes →', 'buddypress-share' ); ?>
				</a>
			</p>
		</div>
	</div>
</div>
