<?php
/**
 * Admin page shell: page header, sidebar nav, body slot.
 *
 * Receives from Bpas_Admin_Panel::render_page():
 *
 * @var array                  $bpas_tabs         Tab registry keyed by slug.
 * @var string                 $active            Active tab slug.
 * @var string                 $page_url          admin.php?page=buddypress-share.
 * @var string                 $view              View slug.
 * @var string                 $view_path         Absolute path to the partial.
 * @var bool                   $in_settings_group True when active tab is a Settings tab.
 * @var Buddypress_Share_Admin $legacy_admin      Legacy admin (renders field bodies).
 * @var string                 $plugin_version    Plugin version string.
 *
 * Each settings view owns its own <form> + settings_fields() because the
 * plugin persists across several Settings API groups (general, reshare,
 * icon). The shell only provides the chrome + slot.
 *
 * @package Buddypress_Share
 * @since   2.3.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap bpas-admin">

	<header class="bpas-page-header">
		<div class="bpas-page-header__title">
			<span class="dashicons dashicons-share" aria-hidden="true"></span>
			<div>
				<h1><?php esc_html_e( 'BuddyPress Activity Share', 'buddypress-share' ); ?></h1>
				<p class="bpas-page-header__subtitle"><?php esc_html_e( 'Let members share activities to social networks and reshare within your community.', 'buddypress-share' ); ?></p>
			</div>
		</div>
		<div class="bpas-page-header__actions">
			<?php if ( $plugin_version ) : ?>
				<span class="bpas-version-pill">v<?php echo esc_html( $plugin_version ); ?></span>
			<?php endif; ?>
		</div>
	</header>

	<?php
	/*
	 * Marker so core's common.js re-parents .notice banners below the
	 * whole header instead of splitting the title from its subtitle.
	 */
	?>
	<hr class="wp-header-end">

	<div class="bpas-settings-layout">

		<aside class="bpas-settings-sidebar">
			<div class="bpas-settings-sidebar-brand">
				<span class="bpas-settings-brand-icon" aria-hidden="true">
					<span class="dashicons dashicons-share"></span>
				</span>
				<div class="bpas-settings-brand-text">
					<p class="bpas-settings-brand-name"><?php esc_html_e( 'Activity Share', 'buddypress-share' ); ?></p>
					<p class="bpas-settings-brand-sub"><?php esc_html_e( 'Plugin', 'buddypress-share' ); ?></p>
				</div>
			</div>
			<nav class="bpas-settings-sidebar-nav" aria-label="<?php esc_attr_e( 'Activity Share navigation', 'buddypress-share' ); ?>">
				<?php
				$printed_groups = array();
				$group_labels   = array(
					'settings'  => esc_html__( 'Settings', 'buddypress-share' ),
					'resources' => esc_html__( 'Resources', 'buddypress-share' ),
				);
				foreach ( $bpas_tabs as $slug => $bpas_tab ) {
					$group = isset( $bpas_tab['group'] ) ? $bpas_tab['group'] : 'main';
					if ( 'main' !== $group && ! in_array( $group, $printed_groups, true ) ) {
						echo '<div class="bpas-snav-divider" role="separator"></div>';
						if ( isset( $group_labels[ $group ] ) ) {
							echo '<p class="bpas-snav-section-label">' . esc_html( $group_labels[ $group ] ) . '</p>';
						}
						$printed_groups[] = $group;
					}
					$classes  = 'bpas-snav-link';
					$classes .= $active === $slug ? ' bpas-snav-link--active' : '';
					echo '<a href="' . esc_url( $page_url . '&tab=' . $slug ) . '" class="' . esc_attr( $classes ) . '">';
					echo '<span class="dashicons ' . esc_attr( $bpas_tab['icon'] ) . '" aria-hidden="true"></span>';
					echo esc_html( $bpas_tab['label'] );
					echo '</a>';
				}
				?>

				<div class="bpas-snav-divider" role="separator"></div>
				<a href="<?php echo esc_url( $page_url . '&onboarding=1' ); ?>" class="bpas-snav-link">
					<span class="dashicons dashicons-welcome-learn-more" aria-hidden="true"></span>
					<?php esc_html_e( 'Setup guide', 'buddypress-share' ); ?>
				</a>
				<a href="https://docs.wbcomdesigns.com/buddypress-activity-share-pro/" class="bpas-snav-link" target="_blank" rel="noopener noreferrer">
					<span class="dashicons dashicons-book" aria-hidden="true"></span>
					<?php esc_html_e( 'Documentation', 'buddypress-share' ); ?>
					<span class="dashicons dashicons-external bpas-snav-link__ext" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'buddypress-share' ); ?></span>
				</a>
			</nav>
		</aside>

		<div class="bpas-settings-main">
			<?php
			/*
			 * Render settings notices inside the content column so the
			 * banner aligns with the panel chrome.
			 */
			settings_errors();

			if ( file_exists( $view_path ) ) {
				include $view_path;
			} else {
				echo '<div class="bpas-empty-state"><p class="bpas-empty-state__title">';
				esc_html_e( 'This section is unavailable.', 'buddypress-share' );
				echo '</p></div>';
			}
			?>
		</div>

	</div>
</div>
