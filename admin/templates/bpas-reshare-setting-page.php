<?php

$bp_reshare_settings          = get_site_option( 'bp_reshare_settings' );
$bp_reshare_settings_activity = isset( $bp_reshare_settings['reshare_share_activity'] ) ? $bp_reshare_settings['reshare_share_activity'] : 'parent';
?>
<div class="wbcom-tab-content">
	<div class="wbcom-wrapper-admin">
		<div class="wbcom-admin-title-section">
			<h3><?php esc_html_e( 'Share Settings', 'buddypress-share' ); ?></h3>
		</div>
		<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
			<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" id="bp_share_form">
				<?php wp_nonce_field( 'update-options' ); ?>
				<div class="form-table">
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label><?php esc_html_e( 'Disable Post Share Activity', 'buddypress-share' ); ?></label>
							<p class="description"><?php esc_html_e( 'You can turn on or off the blog post sharing feature as per your preference.', 'buddypress-share' ); ?></p>
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
							<label><?php esc_html_e( 'Reshare share Activity', 'buddypress-share' ); ?></label>
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
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options">
							<code>[bp_activity_post_reshare]</code>
							<?php esc_html_e( 'Use this shortcode in which post type you want to reshare in Activity.', 'buddypress-share' ); ?>
							<br/><br/>
							<code>
							add_filter('bp_activity_reshare_post_type', 'function_name' );
							function function_name( $post_type ) {
								$post_type[] = 'custom post type slug';
								return $post_type;
							}
							</code>
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
