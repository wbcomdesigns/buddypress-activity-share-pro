<?php
/**
 * Display settings tab body (icon style + colors).
 *
 * Moved verbatim from Buddypress_Share_Admin::bp_share_display_settings_page()
 * during the 2.3.0 admin UX migration.
 *
 * CONTRACT — split-scope sentinel (rationale §12.9): bpas_icon_color_settings
 * is READ with get_option here but WRITTEN with update_site_option elsewhere.
 * This is preserved BYTE-FOR-BYTE. Do NOT reconcile the scope in this UX
 * release — a scope change is a data-behavior change for a separate release.
 *
 * @package Buddypress_Share
 * @since   2.3.0
 */

defined( 'ABSPATH' ) || exit;

// Read with get_option (split-scope sentinel — preserved verbatim).
$bpas_icon_color_settings = get_option( 'bpas_icon_color_settings', array() );
$current_style            = isset( $bpas_icon_color_settings['icon_style'] ) ? $bpas_icon_color_settings['icon_style'] : 'circle';

$icon_styles = array(
	'circle'     => __( 'Circle', 'buddypress-share' ),
	'rec'        => __( 'Rectangle', 'buddypress-share' ),
	'blackwhite' => __( 'Black & white', 'buddypress-share' ),
	'baricon'    => __( 'Bar', 'buddypress-share' ),
);
?>

<form method="post" action="options.php">
	<?php
	settings_fields( 'bpas_icon_color_settings' );
	do_settings_sections( 'bpas_icon_color_settings' );
	?>

	<h2 class="bpas-section-title"><?php esc_html_e( 'Button appearance', 'buddypress-share' ); ?></h2>
	<p class="bpas-section-intro"><?php esc_html_e( 'Choose how the share buttons look on your site.', 'buddypress-share' ); ?></p>

	<div class="bp-share-settings-grid">
		<div class="bp-share-settings-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'Icon style', 'buddypress-share' ); ?></h3>
			</div>
			<div class="card-body">
				<div class="bp-share-style-selector">
					<?php foreach ( $icon_styles as $style_key => $style_name ) : ?>
						<label class="style-option <?php echo esc_attr( $current_style === $style_key ? 'selected' : '' ); ?>">
							<input type="radio"
								name="bpas_icon_color_settings[icon_style]"
								id="icon_style_<?php echo esc_attr( $style_key ); ?>"
								value="<?php echo esc_attr( $style_key ); ?>"
								<?php checked( $style_key, $current_style ); ?> />
							<span class="style-preview <?php echo esc_attr( $style_key ); ?>"></span>
							<span class="style-name"><?php echo esc_html( $style_name ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<div class="bp-share-settings-card color-settings-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'Colors', 'buddypress-share' ); ?></h3>
			</div>
			<div class="card-body">
				<div class="color-setting-group">
					<label for="bg_color"><?php esc_html_e( 'Background color', 'buddypress-share' ); ?></label>
					<div class="color-input-wrapper">
						<input type="text"
							name="bpas_icon_color_settings[bg_color]"
							id="bg_color"
							value="<?php echo esc_attr( $bpas_icon_color_settings['bg_color'] ?? '#667eea' ); ?>"
							class="bp-share-color-picker" />
						<p class="description"><?php esc_html_e( 'Main background color for the share buttons.', 'buddypress-share' ); ?></p>
					</div>
				</div>

				<div class="color-setting-group">
					<label for="text_color"><?php esc_html_e( 'Icon color', 'buddypress-share' ); ?></label>
					<div class="color-input-wrapper">
						<input type="text"
							name="bpas_icon_color_settings[text_color]"
							id="text_color"
							value="<?php echo esc_attr( $bpas_icon_color_settings['text_color'] ?? '#ffffff' ); ?>"
							class="bp-share-color-picker" />
						<p class="description"><?php esc_html_e( 'Color of the icons and text.', 'buddypress-share' ); ?></p>
					</div>
				</div>

				<div class="color-setting-group">
					<label for="hover_color"><?php esc_html_e( 'Hover color', 'buddypress-share' ); ?></label>
					<div class="color-input-wrapper">
						<input type="text"
							name="bpas_icon_color_settings[hover_color]"
							id="hover_color"
							value="<?php echo esc_attr( $bpas_icon_color_settings['hover_color'] ?? '#5a6fd8' ); ?>"
							class="bp-share-color-picker" />
						<p class="description"><?php esc_html_e( 'Color shown when a visitor hovers over a button.', 'buddypress-share' ); ?></p>
					</div>
				</div>

				<div class="color-setting-group">
					<label for="border_color"><?php esc_html_e( 'Border color', 'buddypress-share' ); ?></label>
					<div class="color-input-wrapper">
						<input type="text"
							name="bpas_icon_color_settings[border_color]"
							id="border_color"
							value="<?php echo esc_attr( $bpas_icon_color_settings['border_color'] ?? '' ); ?>"
							class="bp-share-color-picker" />
						<p class="description"><?php esc_html_e( 'Outline color around the share buttons and dropdown. Leave empty to use the default border.', 'buddypress-share' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php submit_button( __( 'Save settings', 'buddypress-share' ), 'primary', 'submit', true, array( 'class' => 'button button-primary' ) ); ?>
</form>
