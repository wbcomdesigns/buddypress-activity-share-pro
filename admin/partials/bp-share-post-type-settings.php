<?php
/**
 * Post Type Sharing Settings Page
 *
 * @package BuddyPress_Share_Pro
 * @since 2.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get settings instance
$settings = BP_Share_Post_Type_Settings::get_instance();
$current_settings = $settings->get_settings();
$available_services = $settings->get_available_services();

// Use the same validation logic as the settings class
$post_types = $settings->get_valid_post_types();
?>

<div class="bp-share-post-type-settings">
	<h2><?php esc_html_e( 'Post Type Sharing Settings', 'buddypress-share' ); ?></h2>
	
	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=wbcom-buddypress-share&section=post-types' ) ); ?>" id="bp-share-post-type-form">
		<?php wp_nonce_field( 'bp_share_post_type_settings', 'bp_share_nonce' ); ?>
		<input type="hidden" name="action" value="save_post_type_settings" />
		
		<!-- Post Type Configuration -->
		<div class="bp-share-settings-section">
			<h3><?php esc_html_e( 'Enable Sharing for Post Types', 'buddypress-share' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Select which post types should have social sharing functionality. Sharing is disabled by default for all post types.', 'buddypress-share' ); ?>
			</p>
			<p class="description" style="margin-top: 10px;">
				<strong><?php esc_html_e( 'Note:', 'buddypress-share' ); ?></strong> 
				<?php esc_html_e( 'Only public post types with a user interface are shown. Internal post types (like Elementor templates, reusable blocks, etc.) are automatically excluded.', 'buddypress-share' ); ?>
			</p>
			
			<div class="bp-share-post-types-list">
				<?php foreach ( $post_types as $post_type ) : 
					$is_enabled = in_array( $post_type->name, $current_settings['enabled_post_types'], true );
					$services = $settings->get_services_for_post_type( $post_type->name );
					?>
					<div class="bp-share-post-type-item" data-post-type="<?php echo esc_attr( $post_type->name ); ?>">
						<div class="post-type-header">
							<label class="post-type-toggle">
								<input type="checkbox" 
								       name="bp_share_settings[enabled_post_types][]" 
								       value="<?php echo esc_attr( $post_type->name ); ?>"
								       class="post-type-checkbox"
								       <?php checked( $is_enabled ); ?>>
								<span class="post-type-label">
									<strong><?php echo esc_html( $post_type->label ); ?></strong>
									<code><?php echo esc_html( $post_type->name ); ?></code>
								</span>
							</label>
							
							<?php if ( $is_enabled ) : ?>
								<button type="button" class="button-link configure-services">
									<?php esc_html_e( 'Configure Services', 'buddypress-share' ); ?>
									<span class="dashicons dashicons-arrow-down-alt2"></span>
								</button>
							<?php endif; ?>
						</div>
						
						<?php if ( $is_enabled ) : ?>
							<div class="post-type-services" style="display: none;">
								<h4><?php esc_html_e( 'Select Services for', 'buddypress-share' ); ?> <?php echo esc_html( $post_type->label ); ?></h4>
								<div class="services-grid">
									<?php foreach ( $available_services as $service_key => $service ) : 
										$is_service_enabled = in_array( $service_key, $services, true );
										?>
										<label class="service-item">
											<input type="checkbox" 
											       name="bp_share_settings[post_type_services][<?php echo esc_attr( $post_type->name ); ?>][]" 
											       value="<?php echo esc_attr( $service_key ); ?>"
											       <?php checked( $is_service_enabled ); ?>>
											<span class="service-icon">
												<i class="<?php echo esc_attr( $service['icon'] ); ?>"></i>
											</span>
											<span class="service-name"><?php echo esc_html( $service['name'] ); ?></span>
										</label>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		
		<!-- Display Settings -->
		<div class="bp-share-settings-section">
			<h3><?php esc_html_e( 'Display Settings', 'buddypress-share' ); ?></h3>
			
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="display_position"><?php esc_html_e( 'Position', 'buddypress-share' ); ?></label>
					</th>
					<td>
						<select name="bp_share_settings[display_position]" id="display_position">
							<option value="right" <?php selected( $current_settings['display_position'], 'right' ); ?>>
								<?php esc_html_e( 'Right Side', 'buddypress-share' ); ?>
							</option>
							<option value="left" <?php selected( $current_settings['display_position'], 'left' ); ?>>
								<?php esc_html_e( 'Left Side', 'buddypress-share' ); ?>
							</option>
						</select>
						<p class="description">
							<?php esc_html_e( 'Choose which side of the screen the floating share widget should appear.', 'buddypress-share' ); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="display_style"><?php esc_html_e( 'Style', 'buddypress-share' ); ?></label>
					</th>
					<td>
						<select name="bp_share_settings[display_style]" id="display_style">
							<option value="floating" <?php selected( $current_settings['display_style'], 'floating' ); ?>>
								<?php esc_html_e( 'Floating', 'buddypress-share' ); ?>
							</option>
							<option value="inline" <?php selected( $current_settings['display_style'], 'inline' ); ?>>
								<?php esc_html_e( 'Inline (After Content)', 'buddypress-share' ); ?>
							</option>
						</select>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="mobile_behavior"><?php esc_html_e( 'Mobile Behavior', 'buddypress-share' ); ?></label>
					</th>
					<td>
						<select name="bp_share_settings[mobile_behavior]" id="mobile_behavior">
							<option value="bottom" <?php selected( $current_settings['mobile_behavior'], 'bottom' ); ?>>
								<?php esc_html_e( 'Bottom Bar', 'buddypress-share' ); ?>
							</option>
							<option value="hidden" <?php selected( $current_settings['mobile_behavior'], 'hidden' ); ?>>
								<?php esc_html_e( 'Hidden', 'buddypress-share' ); ?>
							</option>
							<option value="same" <?php selected( $current_settings['mobile_behavior'], 'same' ); ?>>
								<?php esc_html_e( 'Same as Desktop', 'buddypress-share' ); ?>
							</option>
						</select>
						<p class="description">
							<?php esc_html_e( 'How the share widget should behave on mobile devices.', 'buddypress-share' ); ?>
						</p>
					</td>
				</tr>
			</table>
		</div>
		
		<!-- Default Services -->
		<div class="bp-share-settings-section">
			<h3><?php esc_html_e( 'Default Services', 'buddypress-share' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Select which services should be enabled by default when you enable sharing for a new post type.', 'buddypress-share' ); ?>
			</p>
			
			<div class="default-services-grid">
				<?php foreach ( $available_services as $service_key => $service ) : 
					$is_default = in_array( $service_key, $current_settings['default_services'], true );
					?>
					<label class="service-default-item">
						<input type="checkbox" 
						       name="bp_share_settings[default_services][]" 
						       value="<?php echo esc_attr( $service_key ); ?>"
						       <?php checked( $is_default ); ?>>
						<span class="service-icon">
							<i class="<?php echo esc_attr( $service['icon'] ); ?>"></i>
						</span>
						<span class="service-name"><?php echo esc_html( $service['name'] ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
		
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'buddypress-share' ); ?>">
		</p>
	</form>
</div>

<style>
/* Post Type Settings Styles */
.bp-share-settings-section {
	background: #fff;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
	padding: 20px;
	margin-bottom: 20px;
}

.bp-share-settings-section h3 {
	margin-top: 0;
	margin-bottom: 15px;
	font-size: 1.3em;
}

.bp-share-post-types-list {
	margin-top: 20px;
}

.bp-share-post-type-item {
	border: 1px solid #e0e0e0;
	border-radius: 4px;
	margin-bottom: 15px;
	background: #f9f9f9;
}

.post-type-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 15px;
	background: #fff;
	border-radius: 4px 4px 0 0;
}

.post-type-toggle {
	display: flex;
	align-items: center;
	cursor: pointer;
}

.post-type-toggle input[type="checkbox"] {
	margin-right: 10px;
}

.post-type-label code {
	font-size: 0.85em;
	background: #f0f0f0;
	padding: 2px 6px;
	border-radius: 3px;
	margin-left: 10px;
}

.configure-services {
	color: #0073aa;
	text-decoration: none;
	display: flex;
	align-items: center;
	gap: 5px;
}

.configure-services:hover {
	color: #00a0d2;
}

.post-type-services {
	padding: 20px;
	background: #f5f5f5;
	border-top: 1px solid #e0e0e0;
}

.post-type-services h4 {
	margin-top: 0;
	margin-bottom: 15px;
}

.services-grid,
.default-services-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
	gap: 15px;
}

.service-item,
.service-default-item {
	display: flex;
	align-items: center;
	background: #fff;
	padding: 10px;
	border: 1px solid #ddd;
	border-radius: 4px;
	cursor: pointer;
	transition: all 0.2s;
}

.service-item:hover,
.service-default-item:hover {
	border-color: #0073aa;
	box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.service-item input[type="checkbox"],
.service-default-item input[type="checkbox"] {
	margin-right: 8px;
}

.service-icon {
	width: 20px;
	text-align: center;
	margin-right: 8px;
}

.service-icon i {
	font-size: 16px;
}

.service-icon i.fa-bluesky,
.service-icon i.fas.fa-bluesky {
    content: "";
    background-image: url("<?php echo plugins_url('../admin/images/bluesky-fill.svg', dirname(__FILE__)); ?>");
    background-size: 17px;
    background-repeat: no-repeat;
    background-position: center;
    display: inline-block;
    width: 20px;
    height: 20px;
    speak: none;
}

.service-icon i.fa-bluesky::before,
.service-icon i.fas.fa-bluesky::before {
    content: "";
    display: none;
}

/* Service Colors */
.service-item .fa-facebook-f,
.service-default-item .fa-facebook-f { color: #1877f2; }
.service-item .fa-twitter,
.service-default-item .fa-twitter { color: #1da1f2; }
.service-item .fa-linkedin-in,
.service-default-item .fa-linkedin-in { color: #0077b5; }
.service-item .fa-whatsapp,
.service-default-item .fa-whatsapp { color: #25d366; }
.service-item .fa-telegram-plane,
.service-default-item .fa-telegram-plane { color: #0088cc; }
.service-item .fa-pinterest-p,
.service-default-item .fa-pinterest-p { color: #bd081c; }
.service-item .fa-reddit-alien,
.service-default-item .fa-reddit-alien { color: #ff4500; }
.service-item .fa-wordpress,
.service-default-item .fa-wordpress { color: #21759b; }
.service-item .fa-get-pocket,
.service-default-item .fa-get-pocket { color: #ef4056; }
.service-item .fa-bluesky,
.service-default-item .fa-bluesky { color: #00a8e8; }
.service-item .fa-envelope,
.service-default-item .fa-envelope { color: #667eea; }
.service-item .fa-print,
.service-default-item .fa-print { color: #666; }
.service-item .fa-link,
.service-default-item .fa-link { color: #667eea; }
</style>

<script>
jQuery(document).ready(function($) {
	// Toggle service configuration
	$(document).on('click', '.configure-services', function(e) {
		e.preventDefault();
		const $services = $(this).closest('.bp-share-post-type-item').find('.post-type-services');
		const $icon = $(this).find('.dashicons');
		
		$services.slideToggle(300);
		$icon.toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
	});
	
	// Handle post type checkbox change
	$('.post-type-checkbox').on('change', function() {
		const $item = $(this).closest('.bp-share-post-type-item');
		const $header = $item.find('.post-type-header');
		
		if ($(this).is(':checked')) {
			// Add configure button if not exists
			if (!$header.find('.configure-services').length) {
				$header.append(`
					<button type="button" class="button-link configure-services">
						<?php esc_html_e( 'Configure Services', 'buddypress-share' ); ?>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</button>
				`);
			}
			
			// Add services section if not exists
			if (!$item.find('.post-type-services').length) {
				const postType = $item.data('post-type');
				// Clone default services
				const $defaultServices = $('.default-services-grid').clone();
				
				// Update input names
				$defaultServices.find('input').each(function() {
					const value = $(this).val();
					$(this).attr('name', `bp_share_settings[post_type_services][${postType}][]`);
				});
				
				const $servicesSection = $(`
					<div class="post-type-services" style="display: none;">
						<h4><?php esc_html_e( 'Select Services', 'buddypress-share' ); ?></h4>
						<div class="services-grid">${$defaultServices.html()}</div>
					</div>
				`);
				
				$item.append($servicesSection);
			}
		} else {
			// Remove configure button and services
			$header.find('.configure-services').remove();
			$item.find('.post-type-services').slideUp(300, function() {
				$(this).remove();
			});
		}
	});
});
</script>