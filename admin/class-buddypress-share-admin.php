<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/admin
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Buddypress_Share_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		if ( 'wb-plugins_page_buddypress-share' !== $hook ) {
			return;
		}
		if ( ! wp_style_is( 'font-awesome', 'enqueued' ) ) {
			wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), $this->version, 'all' );
		}
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/buddypress-share-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		wp_enqueue_script( 'jquery-ui-sortable' );
		if ( 'wb-plugins_page_buddypress-share' !== $hook ) {
			return;
		}
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/buddypress-share-admin.js', array( 'jquery' ), $this->version, true );
		wp_localize_script(
			$this->plugin_name,
			'my_ajax_object',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'bp_share_nonce' ),
			)
		);
	}

	/**
	 * Build the admin options page.
	 *
	 * @access public
	 * @author  Wbcom Designs
	 * @since    1.0.0
	 */
	public function bp_share_plugin_options() {
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'bpas_welcome';
		// admin check
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'buddypress-share' ) );
		}
		?>
			<div class="wrap">
							<hr class="wp-header-end">
							<div class="wbcom-wrap">
				<div class="bpss-header">
				<?php echo do_shortcode( '[wbcom_admin_setting_header]' ); ?>
					<h1 class="wbcom-plugin-heading">
					<?php esc_html_e( 'BuddyPress Activity Social Share Settings', 'buddypress-share' ); ?>
					</h1>
				</div>
				<div class="wbcom-admin-settings-page">
				<?php
				settings_errors();
				$this->bpas_plugin_settings_tabs( $tab );
				settings_fields( $tab );
				do_settings_sections( $tab );
				?>
				</div>
							</div>
			</div>
			<?php
	}

	/**
	 * Tab listing
	 *
	 * @param current $current the current tab.
	 * @since    1.0.0
	 */
	public function bpas_plugin_settings_tabs( $current ) {
		$bpas_tabs = array(
			'bpas_welcome'          => esc_html__( 'Welcome', 'buddypress-share' ),
			'bpas_general_settings' => esc_html__( 'General Settings', 'buddypress-share' ),
		);
		$tab_html  = '<div class="wbcom-tabs-section"><div class="nav-tab-wrapper"><div class="wb-responsive-menu"><span>' . esc_html( 'Menu' ) . '</span><input class="wb-toggle-btn" type="checkbox" id="wb-toggle-btn"><label class="wb-toggle-icon" for="wb-toggle-btn"><span class="wb-icon-bars"></span></label></div><ul>';
		foreach ( $bpas_tabs as $bpas_tab => $bpas_name ) {
			$class     = ( $bpas_tab === $current ) ? 'nav-tab-active' : '';
			$tab_html .= '<li><a class="nav-tab ' . esc_attr( $class ) . '" href="admin.php?page=buddypress-share&tab=' . $bpas_tab . '">' . esc_html( $bpas_name ) . '</a></li>';
		}
		$tab_html .= '</div></ul></div>';
		echo $tab_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$this->bpas_include_admin_setting_tabs( $current );
	}

	/**
	 * Display already inserted services.
	 *
	 * @access public
	 * @author  Wbcom Designs
	 * @since    1.0.0
	 */
	public function bp_share_insert_services_ajax() {
		$service_name        = isset( $_POST['service_name'] ) ? sanitize_text_field( wp_unslash( $_POST['service_name'] ) ) : '';
		$service_faw         = isset( $_POST['service_faw'] ) ? sanitize_text_field( wp_unslash( $_POST['service_faw'] ) ) : '';
		$service_key         = $service_value = isset( $_POST['service_value'] ) ? sanitize_text_field( wp_unslash( $_POST['service_value'] ) ) : '';
		$service_description = isset( $_POST['service_description'] ) ? sanitize_text_field( wp_unslash( $_POST['service_description'] ) ) : '';
		$option_name         = 'bp_share_services';
		if ( ! empty( $_POST ) && check_admin_referer( 'bp_share_nonce', 'nonce' ) ) {
			if ( get_site_option( $option_name ) !== false ) {
				$services = get_site_option( $option_name );
				if ( empty( $services ) ) {
					$new_service = array(
						"$service_value" => array(
							"chb_$service_value"  => 1,
							'service_name'        => "$service_name",
							'service_icon'        => "$service_faw",
							'service_description' => "$service_description",
						),
					);
					update_site_option( $option_name, $new_service );
				} else {
					$new_value = array(
						"chb_$service_value"  => 1,
						'service_name'        => "$service_name",
						'service_icon'        => "$service_faw",
						'service_description' => "$service_description",
					);
					$bp_copy_activity = $services['bp_copy_activity'];
					unset( $services['bp_copy_activity'] );
					$services[ $service_value ]   = $new_value;
					$services['bp_copy_activity'] = $bp_copy_activity;
					update_site_option( $option_name, $services );
				}
			} else {
				$new_service = array(
					"$service_value" => array(
						"chb_$service_value"  => 1,
						'service_name'        => "$service_name",
						'service_icon'        => "$service_faw",
						'service_description' => "$service_description",
					),
				);
				// The option hasn't been added yet. We'll add it with $autoload set to 'no'.
				$deprecated = null;
				$autoload   = 'no';
				update_site_option( $option_name, $new_service, $deprecated, $autoload );
			}
		}
		die();
	}

	/**
	 * Intialize setting to show share in popup or new page.
	 *
	 * @access public
	 * @author  Wbcom Designs
	 * @since    1.0.0
	 */
	public function bp_share_checkbox_open_services_render() {
		$extra_options = get_site_option( 'bp_share_services_extra' );
		?>
		<input type='checkbox' name='bp_share_services_open'
		<?php
		if ( isset( $extra_options['bp_share_services_open'] ) && $extra_options['bp_share_services_open'] === 1 ) {
			echo 'checked="checked"'; }
		?>
		value='1'>
		<?php
	}


	/**
	 * Intialize bp_share_settings_section_callback.
	 *
	 * @access public
	 * @author  Wbcom Designs
	 * @since    1.0.0
	 */
	public function bp_share_settings_section_callback() {
		echo '<div class="bp_share_settings_section_callback_class">';
		esc_html_e( 'Default is set to open window in popup. If this option is disabled then services open in new tab instead popup.  ', 'buddypress-share' );
	}

	/**
	 * bp_share_chb_services_ajax.
	 *
	 * @access public
	 * @author   Wbcom Designs
	 * @since    1.0.0
	 */
	public function bp_share_chb_services_ajax() {
		if ( ! empty( $_POST ) && check_admin_referer( 'bp_share_nonce', 'nonce' ) ) {

			$option_name      = 'bp_share_services';
			$active_services  = isset( $_POST['active_chb_array'] ) ? wp_unslash( $_POST['active_chb_array'] ) : array();
			$extras_options   = isset( $_POST['active_chb_extras'] ) ? wp_unslash( $_POST['active_chb_extras'] ) : array();
			$extra_option_new = array();

			if ( ! empty( $extras_options ) ) {
				if ( in_array( 'bp_share_services_open', $extras_options ) ) {
					$extra_option_new['bp_share_services_open'] = 1;
				}
			} else {
				$extra_option_new['bp_share_services_open'] = 0;
			}
			update_site_option( 'bp_share_services_extra', $extra_option_new );
			$services = get_site_option( 'bp_share_services' );
			if ( ! empty( $services ) ) {
				if ( ! empty( $active_services ) ) {
					foreach ( $services as $service_key => $value ) {
						if ( in_array( 'chb_' . $service_key, $active_services ) ) {
							$services[ $service_key ][ 'chb_' . $service_key ] = 1;
							update_site_option( $option_name, $services );
						} else {
							$services[ $service_key ][ 'chb_' . $service_key ] = 0;
							update_site_option( $option_name, $services );
						}
					}
					update_site_option( 'bp_share_all_services_disable', 'enable' );
				} else {
					foreach ( $services as $service_key => $value ) {
						$services[ $service_key ][ 'chb_' . $service_key ] = 0;
						update_site_option( $option_name, $services );
					}
					update_site_option( 'bp_share_all_services_disable', 'disable' );
				}
			}
		}
		die();
	}

	/**
	 * bp_share_delete_user_services_ajax.
	 *
	 * @access public
	 * @author   Wbcom Designs
	 * @since    1.0.0
	 */
	public function bp_share_delete_user_services_ajax() {
		$option_name   = 'bp_share_services';
		$service_array = filter_var_array( $_POST['service_array'], FILTER_SANITIZE_STRING );
		$services      = get_site_option( $option_name );
		if ( ! empty( $service_array ) ) {
			foreach ( $service_array as $service_array_key => $service_array_value ) {
				foreach ( $services as $service_key => $value ) {
					if ( $service_key == $service_array_value ) {
						unset( $services[ $service_key ] );
						update_site_option( $option_name, $services );
					}
				}
			}
		}
		die();
	}

	/**
	 * bp_share_add_options.
	 *
	 * @access public
	 * @author   Wbcom Designs
	 * @since    1.0.0
	 */
	public function bp_share_add_options( $activity_url, $activity_title ) {
		$services = apply_filters( 'bp_share_add_services', $services = array(), $activity_url = '', $activity_title = '' );
		if ( ! empty( $services ) ) {
			$options_key = array();
			foreach ( $services as $key => $value ) {
				$options_key[ 'bp_share_' . strtolower( $key ) ] = $key;
			}
		}
		if ( isset( $options_key ) && $options_key != '' ) {
			?>
			<script>
				var customOptions = '<?php echo json_encode( $options_key ); ?>';
				var optionObj = jQuery.parseJSON(customOptions);
				var select = document.getElementById("social_services_selector_id");
				for (index in optionObj) {
					select.options[select.options.length] = new Option(optionObj[index], index);
				}
			</script>
			<?php
		} else {
			$services = get_site_option( 'bp_share_services' );
			if ( ! empty( $services ) ) {
				$services_options_key = array();
				foreach ( $services as $key => $value ) {
					$services_options_key[] = $key;
				}
			}
			if ( ! empty( $services_options_key ) ) {
				?>
				<script>
					var selected = [];
					jQuery("#social_services_selector_id option").each(function ()
					{
						if (jQuery(this).val() != '') {
							selected.push(jQuery(this).val());
						}
					});
					var all_options = '<?php echo json_encode( $services_options_key ); ?>';
					var all_options = jQuery.parseJSON(all_options);
					var difference = [];

					jQuery.grep(all_options, function (el) {
						if (jQuery.inArray(el, selected) == -1)
							difference.push(el);
					});
					if (difference.length != 0) {
						for (option in difference) {
							jQuery('#tr_' + difference[option]).remove();
						}
						var data = {
							'action': 'bp_share_delete_user_services_ajax',
							'service_array': difference,
						};
						// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
						jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function (response) {
							//                            console.log(response);
						});
					}
				</script>
									<?php
			}
		}
	}

	/**
	 * Bp_share_user_added_services.
	 *
	 * @access public
	 * @author   Wbcom Designs
	 * @since    1.0.0
	 */
	public function bp_share_user_added_services( $services, $activity_url, $activity_title ) {
		$user_services = apply_filters( 'bp_share_add_services', $services, $activity_url, $activity_title );
		$service       = get_site_option( 'bp_share_services' );
		if ( ! empty( $user_services ) ) {
			$options_values = array();
			foreach ( $user_services as $key => $value ) {
				$options_values[ 'bp_share_' . strtolower( $key ) ] = $value;
			}
			if ( ! empty( $service ) ) {
				foreach ( $options_values as $options_key => $options_value ) {
					foreach ( $service as $key => $value ) {
						if ( isset( $key ) && $key == $options_key && $value[ 'chb_' . $key ] == 1 ) {
							echo '<a target="blank" class="bp-share" href="' . esc_url( $options_value ) . '" rel="' . esc_attr( $options_key ) . '"><span class="fa-stack fa-lg"><i class="' . esc_attr( $value['service_icon'] ) . '"></i></span></a>';
						}
					}
				}
			}
		}
	}

	/**
	 * Ajax Call when delete any inserted services.
	 *
	 * @access public
	 * @author   Wbcom Designs
	 * @since    1.0.0
	 */
	public function bp_share_delete_services_ajax() {
		if ( ! empty( $_POST ) && check_admin_referer( 'bp_share_nonce', 'nonce' ) ) {
			$option_name  = 'bp_share_services';
			$service_name = isset( $_POST['service_name'] ) ? wp_unslash( $_POST['service_name'] ) : array();
			$services     = get_site_option( $option_name );
			if ( ! empty( $services ) ) {
				foreach ( $services as $service_key => $value ) {
					if ( $service_key == $service_name ) {
						unset( $services[ $service_key ] );
						update_site_option( $option_name, $services );
						echo esc_html( $service_key );
					}
				}
			}
		}
		die();
	}

	/**
	 * Intialize plugin admin settings.
	 *
	 * @access public
	 * @author  Wbcom Designs
	 * @since    1.0.0
	 */
	public function bp_share_settings_init() {
		register_setting( 'bp_share_services_extra', 'bp_share_services_extra' );
		add_settings_section(
			'bp_share_extra_options',
			esc_html__( 'Extra Options', 'buddypress-share' ),
			array( $this, 'bp_share_settings_section_callback' ),
			'bp_share_services_extra'
		);
		add_settings_field(
			'bp_share_services_open',
			esc_html__( 'Open as popup window', 'buddypress-share' ),
			array( $this, 'bp_share_checkbox_open_services_render' ),
			'bp_share_services_extra',
			'bp_share_extra_options'
		);
	}

	/**
	 * Function for add plugin menu.
	 *
	 * @access public
	 * @author  Wbcom Designs
	 * @since    1.0.0
	 */
	public function bp_share_plugin_menu() {
		if ( empty( $GLOBALS['admin_page_hooks']['wbcomplugins'] ) ) {
			add_menu_page( esc_html__( 'WB Plugins', 'buddypress-share' ), esc_html__( 'WB Plugins', 'buddypress-share' ), 'manage_options', 'wbcomplugins', array( $this, 'bp_share_plugin_options' ), 'dashicons-lightbulb', 59 );
			add_submenu_page( 'wbcomplugins', esc_html__( 'General', 'buddypress-share' ), esc_html__( 'General', 'buddypress-share' ), 'manage_options', 'wbcomplugins' );
		}
		add_submenu_page( 'wbcomplugins', esc_html__( 'BuddyPress Share', 'buddypress-share' ), esc_html__( 'BuddyPress Share', 'buddypress-share' ), 'manage_options', $this->plugin_name, array( $this, 'bp_share_plugin_options' ) );
	}

	/**
	 * Sort social share links in admin
	 *
	 * @since    1.0.0
	 */
	public function bp_share_sort_social_links_ajax() {
		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'bp_share_nonce' ) ) {
			exit();
		} else {
			if ( ! isset( $_POST['sorted_data'] ) ) {
				exit;
			}
			$sorts        = wp_unslash( $_POST['sorted_data'] );
			$services     = get_site_option( 'bp_share_services' );
			$count        = 0;
			$new_settings = array();
			foreach ( $services as $key => $service ) {
				foreach ( $sorts as $srt ) {
					if ( 'chb_bp_copy_activity' !== $srt['key'] ) {
						if ( (int) $count === (int) $srt['newIndex'] ) {
							$setting_key                  = str_replace( 'chb_', '', $srt['key'] );
							$new_settings[ $setting_key ] = $services[ $setting_key ];
						}
					}
				}
				$count++;
			}
			$new_settings['bp_copy_activity'] = $services['bp_copy_activity'];
			update_site_option( 'bp_share_services', $new_settings );
		}
		exit();
	}

	/**
	 * Tab listing
	 *
	 * @param bpas_tab $bpas_tab the current tab.
	 * @since    1.0.0
	 */
	public function bpas_include_admin_setting_tabs( $bpas_tab ) {
		$bpas_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'bpas_welcome';
		switch ( $bpas_tab ) {
			case 'bpas_welcome':
				$this->bpas_welcome_section();
				break;
			case 'bpas_general_settings':
				$this->bpas_general_setting_section();
				break;
			default:
				$this->bpas_welcome_section();
				break;
		}
	}

	/**
	 * welcome template
	 *
	 * @since    1.0.0
	 */
	public function bpas_welcome_section() {

		if ( file_exists( BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/bp-welcome-page.php' ) ) {
			require_once BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/bp-welcome-page.php';
		}
	}

	/**
	 * Social service settig template
	 *
	 * @since    1.0.0
	 */
	public function bpas_general_setting_section() {
		?>
		<div class="wbcom-tab-content">
			<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" id="bp_share_form">
			<?php wp_nonce_field( 'update-options' ); ?>
					<h3><?php esc_html_e( 'Add Social Services', 'buddypress-share' ); ?></h3>
					<table cellspacing="0" class="add_share_services widefat plugins">
						<thead>
							<tr>
								<th class="manage-column column-cb check-column" id="cb" scope="col">&nbsp;</th>
								<th class="manage-column column-name" id="name" scope="col" style="width: 190px;"><?php esc_html_e( 'Component', 'buddypress-share' ); ?></th>
								<th class="manage-column column-select_services" id="select_services" scope="col"><?php esc_html_e( 'Select Service', 'buddypress-share' ); ?></th>
							</tr>
						</thead>
						<tbody id="the-list">
							<tr>
								<td scope="row"></td>
								<td class="plugin-title">
									<strong><?php esc_html_e( 'Social Sites', 'buddypress-share' ); ?></strong><span class="bp_share_req">*</span></td>
								<td class="column-description desc">
									<div class="plugin-description">
										<select name="social_services_selector" id="social_services_selector_id" class="social_services_selector">
											<option value="">-<?php esc_html_e( 'select', 'buddypress-share' ); ?>-</option>
											<option value="bp_share_facebook"><?php esc_html_e( 'Facebook', 'buddypress-share' ); ?></option>
											<option value="bp_share_twitter"><?php esc_html_e( 'Twitter', 'buddypress-share' ); ?></option>
											<option value="bp_share_pinterest"><?php esc_html_e( 'Pinterest', 'buddypress-share' ); ?></option>
											<option value="bp_share_linkedin"><?php esc_html_e( 'Linkedin', 'buddypress-share' ); ?></option>
											<option value="bp_share_reddit"><?php esc_html_e( 'Reddit', 'buddypress-share' ); ?></option>
											<option value="bp_share_wordpress"><?php esc_html_e( 'WordPress', 'buddypress-share' ); ?></option>
											<option value="bp_share_pocket"><?php esc_html_e( 'Pocket', 'buddypress-share' ); ?></option>
											<option value="bp_share_email"><?php esc_html_e( 'Email', 'buddypress-share' ); ?></option>
											<option value="bp_share_whatsapp"><?php esc_html_e( 'Whatsapp', 'buddypress-share' ); ?></option>
											<option value="bp_copy_activity"><?php esc_html_e( 'Copy', 'buddypress-share' ); ?></option>
										</select>
									</div>
									<p class="error_service error_service_selector"><?php esc_html_e( 'This field is required!', 'buddypress-share' ); ?></p>
								</td>
							</tr>
							<tr>
								<td scope="row"></td>
								<td class="plugin-title">
									<strong><?php esc_html_e( 'Font Awesome Icon Class', 'buddypress-share' ); ?></strong><span class="bp_share_req">*</span></td>
								<td class="column-faw-icon desc">
									<div class="plugin-faw-icon">
										<input class="faw_class_input" name="faw_class_input" type="text">
										<p class="error_service error_service_faw-icon"><?php esc_html_e( 'This field is required!', 'buddypress-share' ); ?></p>
									</div>
								</td>
							</tr>
							<tr>
								<td scope="row"></td>
								<td class="plugin-title">
									<strong><?php esc_html_e( 'Description', 'buddypress-share' ); ?></strong><span class="bp_share_req">*</span></td>
								<td class="column-description desc">
									<div class="plugin-description">
										<textarea name="bp_share_description" class="bp_share_description"></textarea>
										<p class="error_service error_service_description"><?php esc_html_e( 'This field is required!', 'buddypress-share' ); ?></p>
									</div>
								</td>
							</tr>
							<tr>
								<td scope="row"></td>
								<td class="plugin-title">
								</td>
								<td class="add_services_btn_td">
									<input type="button" class="add_services_btn" name="add_services_btn" value="<?php esc_html_e( 'Add Services', 'buddypress-share' ); ?>">
									<p class="spint_action"><i class="fa fa-cog fa-spin fa-3x fa-fw"></i></p>
								</td>
							</tr>
						</tbody>
					</table><!--END: add_share_services table-->
					<br/>
					<table cellspacing="0" class="widefat plugins">
						<thead>
							<tr>
								<th class="manage-column column-cb check-column" id="cb" scope="col">&nbsp;</th>
								<th class="manage-column column-name" id="name" scope="col"><?php esc_html_e( 'Social Sites', 'buddypress-share' ); ?></th>
								<th class="manage-column column-description" id="description" scope="col"><?php esc_html_e( 'Description', 'buddypress-share' ); ?></th>
								<th class="manage-column column-services-action" id="services-action" scope="col"><?php esc_html_e( 'Action', 'buddypress-share' ); ?></th>
							</tr>
						</thead>
						<tbody id="the-list" class="bp_share_social_list">
						<?php
						$social_options = get_site_option( 'bp_share_services' );
						if ( ! empty( $social_options ) ) {
							$count = 0;
							foreach ( $social_options as $service_key => $social_option ) {

								?>
									<tr class="bp-share-services-row" id="<?php echo 'tr_' . esc_attr( $service_key ); ?>" data-pos-index="<?php echo esc_attr( $count ); ?>" data-key="<?php echo 'chb_' . esc_attr( $service_key ); ?>">
										<td scope="row" id="bp_share_chb" class="bp-share-td">
											<input type="checkbox" name="<?php echo 'chb_' . esc_attr( $service_key ); ?>" value="1" <?php	echo ( 1 === $social_options[ $service_key ][ 'chb_' . $service_key ] ) ? 'checked="checked"' : ''; ?>
											/>
										</td>
										<td class="bp-share-title bp-share-td">
											<strong><i class="<?php echo esc_attr( $social_option['service_icon'] ); ?> fa-lg"></i> <?php echo esc_html( $social_option['service_name'] ); ?></strong>
											<div class="row-actions-visible"></div>
										</td>
										<td class="bp-share-column-description desc bp-share-td">
											<div class="plugin-description">
												<p><?php echo esc_html( $social_option['service_description'] ); ?></p>
											</div>
											<div class="active second plugin-version-author-uri">
											</div>
										</td>
									<?php if ( 'bp_copy_activity' !== $service_key ) : ?>
										<td class="service_delete bp-share-td"><p class="service_delete_icon" data-bind="<?php echo esc_attr( $service_key ); ?>"><i class="fa fa-window-close"></i></p></td>
									<?php endif; ?>
									</tr>
									<?php
									$count++;
							}
						}
						?>
						</tbody>
					</table><!--END:social options table-->
					<div class="bp-share-services-extra">
							<?php
							do_settings_sections( 'bp_share_services_extra' );
							echo '</div>';
							?>
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
					<input type="submit" class="button-primary bp_share_option_save" value="<?php esc_html_e( 'Save Changes', 'buddypress-share' ); ?>" />
				</p>
			</form>
				<?php do_action( 'bp_share_add_services_options', $arg1 = '', $arg2 = '' ); ?>
		</div>
		<?php
	}

}
