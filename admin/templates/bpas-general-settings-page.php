<?php
$bp_share_services_enable        = get_site_option( 'bp_share_services_enable' );
$bp_share_services_logout_enable = get_site_option( 'bp_share_services_logout_enable' );
$extra_options                   = get_site_option( 'bp_share_services_extra' );
?>
<div class="wbcom-tab-content">
	<div class="wbcom-wrapper-admin">
		<div class="wbcom-admin-title-section wbcom-flex">
			<h3 class="wbcom-welcome-title"><?php esc_html_e( 'General Settings', 'buddypress-share' ); ?></h3>
			<a href="<?php echo esc_url( 'https://docs.wbcomdesigns.com/doc_category/buddypress-activity-social-share/' ); ?>" class="wbcom-docslink" target="_blank"><?php esc_html_e( 'Documentation', 'buddypress-share' ); ?></a>
		</div>

		<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
			<div class="save-option-message"></div>
			<div class="option-not-save-message"></div>
			<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" id="bp_share_form">
				<?php wp_nonce_field( 'update-options' ); ?>
				<div class="form-table buddypress-profanity-admin-table">
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="bp_share_services_enable"><strong><?php esc_html_e( 'Enable Social Share', 'buddypress-share' ); ?></strong></label>
							<p class="description"><?php esc_html_e( 'Enable this option to show share button in activity page.', 'buddypress-share' ); ?></p>
						</div>
						<div id="bp_share_chb" class="wbcom-settings-section-options">
							<input type="checkbox" name="bp_share_services_enable" id="bp_share_services_enable" value="1" <?php checked( '1', $bp_share_services_enable ); ?>/>
						</div>
					</div>

					<div id="social_share_logout_wrap" class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="bp_share_services_logout_enable"><strong><?php esc_html_e( 'Social Share in Logout Mode', 'buddypress-share' ); ?></strong></label>
							<p class="description"><?php esc_html_e( 'Enable this option to display social share icons when the user is logged out.', 'buddypress-share' ); ?></p>
						</div>
						<div id="bp_share_chb" class="wbcom-settings-section-options">
							<input type="checkbox" name="bp_share_services_logout_enable" id="bp_share_services_logout_enable" value="1" <?php checked( '1', $bp_share_services_logout_enable ); ?>/>
						</div>
					</div>
				</div>

				<div class="wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label for="wbcom-social-share">
							<?php esc_html_e( 'Enable Sharing Sites', 'buddypress-share' ); ?>
						</label>
					</div>

					<div class="wbcom-settings-section-options">
						<section class="social_icon_section">
							<ul id="drag_social_icon">
								<h3><?php esc_html_e( 'Disable', 'buddypress-share' ); ?></h3>
								<?php
									$get_social_value = get_site_option( 'bp_share_services' );
								if ( empty( $get_social_value['Facebook'] ) ) {
									?>
									<li class="socialicon icon_Facebook" name="icon_facebook"><?php esc_html_e( 'Facebook', 'buddypress-share' ); ?></li>
									<?php } if ( empty( $get_social_value['Twitter'] ) ) { ?>
									<li class="socialicon icon_Twitter" name="icon_gmail"><?php esc_html_e( 'Twitter', 'buddypress-share' ); ?></li>
									<?php }  if ( empty( $get_social_value['Pinterest'] ) ) { ?>
									<li class="socialicon icon_Pinterest" name="icon_Pinterest"><?php esc_html_e( 'Pinterest', 'buddypress-share' ); ?></li>
									<?php } if ( empty( $get_social_value['Linkedin'] ) ) { ?>
										<li class="socialicon icon_LinkedIn" name="icon_linkedin"><?php esc_html_e( 'Linkedin', 'buddypress-share' ); ?></li>
									<?php }  if ( empty( $get_social_value['Reddit'] ) ) { ?>
										<li class="socialicon icon_Reddit" name="icon_reddit"><?php esc_html_e( 'Reddit', 'buddypress-share' ); ?></li>
									<?php } if ( empty( $get_social_value['WordPress'] ) ) { ?>
										<li class="socialicon icon_WordPress" name="icon_wordpress"><?php esc_html_e( 'WordPress', 'buddypress-share' ); ?></li>
									<?php } if ( empty( $get_social_value['Pocket'] ) ) { ?>
										<li class="socialicon icon_Pocket" name="icon_pocket"><?php esc_html_e( 'Pocket', 'buddypress-share' ); ?></li>
									<?php } if ( empty( $get_social_value['Telegram'] ) ) { ?>
										<li class="socialicon icon_Telegram" name="icon_telegram"><?php esc_html_e( 'Telegram', 'buddypress-share' ); ?></li>
									<?php } if ( empty( $get_social_value['Bluesky'] ) ) { ?>
										<li class="socialicon icon_Bluesky" name="icon_bluesky"><?php esc_html_e( 'Bluesky', 'buddypress-share' ); ?></li>
									<?php } if ( empty( $get_social_value['E-mail'] ) ) { ?>
									<li class="socialicon icon_Gmail" name="icon_gmail"><?php esc_html_e( 'E-mail', 'buddypress-share' ); ?></li>
									<?php } if ( empty( $get_social_value['Whatsapp'] ) ) { ?>
									<li class="socialicon icon_WhatAapp" name="icon_whatsapp"><?php esc_html_e( 'WhatsApp', 'buddypress-share' ); ?></li>
								<?php } ?>
							</ul>
							<ul id="drag_icon_ul">
								<h3><?php esc_html_e( 'Enable', 'buddypress-share' ); ?></h3>
								<?php
								$get_social_value = get_site_option( 'bp_share_services' );
								if ( ! empty( $get_social_value['Facebook'] ) ) {
									?>
									<li class="socialicon icon_Facebook" name="icon_facebook"><?php esc_html_e( 'Facebook', 'buddypress-share' ); ?></li>
								<?php } if ( ! empty( $get_social_value['Twitter'] ) ) { ?>
									<li class="socialicon icon_Twitter" name="icon_twitter"><?php esc_html_e( 'Twitter', 'buddypress-share' ); ?></li>
								<?php } if ( ! empty( $get_social_value['Pinterest'] ) ) { ?>
									<li class="socialicon icon_Pinterest" name="icon_Pinterest"><?php esc_html_e( 'Pinterest', 'buddypress-share' ); ?></li>
								<?php } if ( ! empty( $get_social_value['Linkedin'] ) ) { ?>
									<li class="socialicon icon_LinkedIn" name="icon_linkedin"><?php esc_html_e( 'Linkedin', 'buddypress-share' ); ?></li>
								<?php }  if ( ! empty( $get_social_value['Reddit'] ) ) { ?>
									<li class="socialicon icon_Reddit" name="icon_reddit"><?php esc_html_e( 'Reddit', 'buddypress-share' ); ?></li>
								<?php } if ( ! empty( $get_social_value['WordPress'] ) ) { ?>
									<li class="socialicon icon_WordPress" name="icon_wordpress"><?php esc_html_e( 'WordPress', 'buddypress-share' ); ?></li>
								<?php } if ( ! empty( $get_social_value['Pocket'] ) ) { ?>
									<li class="socialicon icon_Pocket" name="icon_pocket"><?php esc_html_e( 'Pocket', 'buddypress-share' ); ?></li>
								<?php } if ( ! empty( $get_social_value['Telegram'] ) ) { ?>
									<li class="socialicon icon_Telegram" name="icon_telegram"><?php esc_html_e( 'Telegram', 'buddypress-share' ); ?></li>
								<?php } if ( ! empty( $get_social_value['Bluesky'] ) ) { ?>
									<li class="socialicon icon_Bluesky" name="icon_bluesky"><?php esc_html_e( 'Bluesky', 'buddypress-share' ); ?></li>
								<?php } if ( ! empty( $get_social_value['E-mail'] ) ) { ?>
									<li class="socialicon icon_Gmail" name="icon_gmail"><?php esc_html_e( 'E-mail', 'buddypress-share' ); ?></li>
								<?php } if ( ! empty( $get_social_value['Whatsapp'] ) ) { ?>
									<li class="socialicon icon_WhatsApp" name="icon_whatsapp"><?php esc_html_e( 'WhatsApp', 'buddypress-share' ); ?></li>
								<?php } ?>
							</ul>
						</section>
					</div>
				</div>
				<div class="wbcom-settings-section-wrap form-table">
					<div class="wbcom-settings-section-options-heading">
						<label>
							<?php esc_html_e( 'Open as a popup window', 'buddypress-share' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Default is set to open windows in a popup. If this option is disabled, services will open in a new tab instead of a popup.', 'buddypress-share' ); ?></p>
					</div>

					<div class="wbcom-settings-section-options">
						<input type="checkbox" name="bp_share_services_open" id="bpas-popup-share"  <?php echo ( isset( $extra_options['bp_share_services_open'] ) && $extra_options['bp_share_services_open'] === 'on' ) ? 'checked="checked"' : ''; ?> >
					</div>
				</div>
				<!--save the settings-->
				<input type="hidden" name="action" value="update" />
					<?php
					$social_options = get_site_option( 'bp_share_services' );
					if ( ! empty( $social_options ) ) {
						$social_key_string = '';
						foreach ( $social_options as $service_key => $social_option ) {
							if ( count( $social_options ) != 1 ) {
								$social_key_string .= $service_key . ',';
							} else {
								$social_key_string = $service_key;
							}
						}
						if ( count( $social_options ) != 1 ) {
							$social_key_string = rtrim( $social_key_string, ', ' );
						}
						?>
					<input type="hidden" name="page_options" value="<?php echo esc_attr( $social_key_string ); ?>" />
						<?php
					}
					?>
				
				<p class="submit">
					<input type="submit" name="bpas_submit_general_options" class="button button-primary bp_share_option_save" value="<?php esc_html_e( 'Save Changes', 'buddypress-share' ); ?>" />
				</p>
			</form>

			<?php do_action( 'bp_share_add_services_options' ); ?>
		</div>
	</div>
</div>
