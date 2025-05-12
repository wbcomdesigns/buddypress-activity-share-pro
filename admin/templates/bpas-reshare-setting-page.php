<?php

$bp_reshare_settings          = get_site_option( 'bp_reshare_settings' );
$bp_reshare_settings_activity = isset( $bp_reshare_settings['reshare_share_activity'] ) ? $bp_reshare_settings['reshare_share_activity'] : 'parent';

$bp_reshare_settings_save_notice   = "display:none";

if( isset( $_GET['settings-updated'] ) && ( 'true' == $_GET['settings-updated'] ) ){ //phpcs:ignore
	$bp_reshare_settings_save_notice = '';
}

?>
<div class="wbcom-tab-content">
	<div class="wbcom-wrapper-admin">
		<div class="wbcom-admin-title-section">
			<h3><?php esc_html_e( 'Share Settings', 'buddypress-share' ); ?></h3>
		</div>
		<div class="bpas-save-option-message" style=<?php echo esc_attr( $bp_reshare_settings_save_notice ); ?>>
			<p><strong><?php esc_html_e( 'Settings saved successfully.', 'buddypress-share' ); ?></strong></p>
			<button type="button" class="notice-dismiss"></button>
		</div>	
		<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
			<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" id="bp_reshare_form">
				<?php wp_nonce_field( 'update-options' ); ?>
				<div class="form-table">
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label><?php esc_html_e( 'Disable Post Share Activity', 'buddypress-share' ); ?></label>
							<p class="description"><?php esc_html_e( 'Enable or disable the feature for sharing blog posts according to your preference.', 'buddypress-share' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
						<input class="regular-btn " type="checkbox" name="bp_reshare_settings[disable_post_reshare_activity]" value="1" 
						<?php
						if ( isset( $bp_reshare_settings['disable_post_reshare_activity'] ) && $bp_reshare_settings['disable_post_reshare_activity'] == 1 ) :
							?>
							checked <?php endif; ?> />
						</div>
					</div>
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label><?php esc_html_e( 'Disable My Profile Sharing', 'buddypress-share' ); ?></label>
							<p class="description"><?php esc_html_e( 'Enable or disable the feature for sharing your profile according to your preference.', 'buddypress-share' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
						<input class="regular-btn " type="checkbox" name="bp_reshare_settings[disable_my_profile_reshare_activity]" value="1" 
						<?php
						if ( isset( $bp_reshare_settings['disable_my_profile_reshare_activity'] ) && $bp_reshare_settings['disable_my_profile_reshare_activity'] == 1 ) :
							?>
							checked <?php endif; ?> />
						</div>
					</div>
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label><?php esc_html_e( 'Disable Message Sharing', 'buddypress-share' ); ?></label>
							<p class="description"><?php esc_html_e( 'Enable or disable the feature for sharing messages according to your preference.', 'buddypress-share' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
						<input class="regular-btn " type="checkbox" name="bp_reshare_settings[disable_message_reshare_activity]" value="1" 
						<?php
						if ( isset( $bp_reshare_settings['disable_message_reshare_activity'] ) && $bp_reshare_settings['disable_message_reshare_activity'] == 1 ) :
							?>
							checked <?php endif; ?> />
						</div>
					</div>
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label><?php esc_html_e( 'Disable Group Sharing', 'buddypress-share' ); ?></label>
							<p class="description"><?php esc_html_e( 'Enable or disable the feature for sharing groups according to your preference.', 'buddypress-share' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
						<input class="regular-btn " type="checkbox" name="bp_reshare_settings[disable_group_reshare_activity]" value="1" 
						<?php
						if ( isset( $bp_reshare_settings['disable_group_reshare_activity'] ) && $bp_reshare_settings['disable_group_reshare_activity'] == 1 ) :
							?>
							checked <?php endif; ?> />
						</div>
					</div>
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label><?php esc_html_e( 'Disable Friends Sharing', 'buddypress-share' ); ?></label>
							<p class="description"><?php esc_html_e( 'Enable or disable the feature for sharing friends according to your preference.', 'buddypress-share' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
						<input class="regular-btn " type="checkbox" name="bp_reshare_settings[disable_friends_reshare_activity]" value="1" 
						<?php
						if ( isset( $bp_reshare_settings['disable_friends_reshare_activity'] ) && $bp_reshare_settings['disable_friends_reshare_activity'] == 1 ) :
							?>
							checked <?php endif; ?> />
						</div>
					</div>
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label><?php esc_html_e( 'Reshare Activity', 'buddypress-share' ); ?></label>
						</div>
						<div class="wbcom-settings-section-options">
							<ul>
								<li>
									<label>
										<input type="radio" name="bp_reshare_settings[reshare_share_activity]" value="parent" <?php checked( 'parent', $bp_reshare_settings_activity ); ?> />&nbsp;<?php esc_html_e( 'Parent', 'buddypress-share' ); ?>
									</label>
								</li>
								<li>
									<label>
										<input type="radio" name="bp_reshare_settings[reshare_share_activity]" value="child" <?php checked( 'child', $bp_reshare_settings_activity ); ?> />&nbsp;<?php esc_html_e( 'Child', 'buddypress-share' ); ?>
									</label>
								</li>									
							</ul>	
						</div>
				</div>
				<p class="submit">
					<input type="submit" class="button button-primary" name="bpas_submit_reshare_options"  value="<?php esc_html_e( 'Save Changes', 'buddypress-share' ); ?>" />
				</p>
				</div>
			</form>
		</div>
	</div>			
</div>
