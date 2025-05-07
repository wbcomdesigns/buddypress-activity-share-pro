<?php
$bpas_icon_color_settings = get_option( 'bpas_icon_color_settings' );

$bp_share_icon_settings_save_notice = "display:none";
if( isset( $_GET['settings-updated'] ) && ( 'true' == $_GET['settings-updated'] ) ){
	$bp_share_icon_settings_save_notice = '';
}

?>
<div class="wbcom-tab-content">
	<div class="wbcom-admin-title-section">
		<h3><?php esc_html_e( 'Icon Settings', 'buddypress-share' ); ?></h3>
	</div>
	<div class="wbcom-admin-option-wrap wb-activity-share-color-icons wbcom-admin-option-wrap-view">
		<div class="bpas-save-option-message" style=<?php echo esc_attr( $bp_share_icon_settings_save_notice ); ?>>
			<p><strong><?php esc_html_e( 'Settings saved successfully.', 'buddypress-share' ); ?></strong></p>
			<button type="button" class="notice-dismiss"></button>
		</div>	
		<div class="option-not-save-message"></div>
		<form method="post" action="options.php">
			<?php
				settings_fields( 'bpas_icon_color_settings' );
				do_settings_sections( 'bpas_icon_color_settings' );
			?>
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="wbcom-social-share">
						<?php esc_html_e( 'Sharing Icon Style', 'buddypress-share' ); ?> 
					</label>
				</div>
				<div class="wbcom-settings-section-options">
					<div class="wbcom-social-icon-style"> 
						<div class="wbcom-settings-section-options">
							<label>
								<input type="radio" class="bpas-social-radio-btn" name="bpas_icon_color_settings[icon_style]" value="circle"<?php ( isset( $bpas_icon_color_settings['icon_style'] ) ) ? checked( $bpas_icon_color_settings['icon_style'], 'circle' ) : ''; ?>>
								<img src="<?php echo esc_attr( BP_ACTIVITY_SHARE_PLUGIN_URL ) . 'admin/images/style_01.jpg'; ?>">
							</label>
							<label>
								<input type="radio" class="bpas-social-radio-btn" name="bpas_icon_color_settings[icon_style]" value="rec"<?php ( isset( $bpas_icon_color_settings['icon_style'] ) ) ? checked( $bpas_icon_color_settings['icon_style'], 'rec' ) : ''; ?>>
								<img src="<?php echo esc_attr( BP_ACTIVITY_SHARE_PLUGIN_URL ) . 'admin/images/style_02.jpg'; ?>">
							</label>
							<label>
								<input type="radio" class="bpas-social-radio-btn" name="bpas_icon_color_settings[icon_style]" value="blackwhite"<?php echo ( isset( $bpas_icon_color_settings['icon_style'] ) ) ? checked( $bpas_icon_color_settings['icon_style'], 'blackwhite' ) : ''; ?>>
								<img src="<?php echo esc_attr( BP_ACTIVITY_SHARE_PLUGIN_URL ) . 'admin/images/style_03.jpg'; ?>">
							</label>
							<label>
								<input type="radio" class="bpas-social-radio-btn" name="bpas_icon_color_settings[icon_style]" value="baricon"<?php echo ( isset( $bpas_icon_color_settings['icon_style'] ) ) ? checked( $bpas_icon_color_settings['icon_style'], 'baricon' ) : ''; ?>>
								<img src="<?php echo esc_attr( BP_ACTIVITY_SHARE_PLUGIN_URL ) . 'admin/images/style_04.jpg'; ?>'?>">
							</label>
							<label>
								<input type="radio" class="bpas-social-radio-btn" name="bpas_icon_color_settings[icon_style]" value="benzene"<?php echo ( isset( $bpas_icon_color_settings['icon_style'] ) ) ? checked( $bpas_icon_color_settings['icon_style'], 'benzene' ) : ''; ?>>
								<img style="display:none;" src="<?php echo esc_attr( BP_ACTIVITY_SHARE_PLUGIN_URL ) . 'admin/images/style_05.jpg'; ?>">
							</label>
						</div>
					</div>
				</div>	
				<?php submit_button(); ?>			
		</form>
	</div>
</div>
