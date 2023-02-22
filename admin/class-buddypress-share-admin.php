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
		// if ( 'wb-plugins_page_buddypress-share' !== $hook ) {
		// return;
		// }
		if ( ! wp_style_is( 'font-awesome', 'enqueued' ) ) {
			wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), $this->version, 'all' );
		}
		wp_enqueue_style( 'wp-color-picker' );
		if ( ! isset( $_GET['page'] ) ) {
			return;
		}
		if ( isset( $_GET['page'] ) && 'wbcom-support-page' == $_GET['page'] || 'wb-plugins_page_buddypress-share' === $hook || 'wbcomplugins' === $_GET['page'] || 'wbcom-plugins-page' === $_GET['page'] || 'buddypress-share' === $_GET['page'] ) {

			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/buddypress-share-admin.css', array(), $this->version, 'all' );

		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		if ( 'wb-plugins_page_buddypress-share' !== $hook ) {
			return;
		}
		wp_enqueue_script( 'wp-color-picker' );
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

	public function wbcom_hide_all_admin_notices_from_setting_page() {
		$wbcom_pages_array  = array( 'wbcomplugins', 'wbcom-plugins-page', 'wbcom-support-page', 'buddypress-share' );
		$wbcom_setting_page = filter_input( INPUT_GET, 'page' ) ? filter_input( INPUT_GET, 'page' ) : '';

		if ( in_array( $wbcom_setting_page, $wbcom_pages_array, true ) ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
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
			<div class="wbcom-bb-plugins-offer-wrapper">
					<div id="wb_admin_logo">
						<a href="https://wbcomdesigns.com/downloads/buddypress-community-bundle/" target="_blank">
							<img src="<?php echo esc_url( BP_ACTIVITY_SHARE_PLUGIN_URL ) . 'admin/wbcom/assets/imgs/wbcom-offer-notice.png'; ?>">
						</a>
					</div>
				</div>
			<div class="wbcom-wrap">
				<div class="bpss-header">
					<div class="wbcom_admin_header-wrapper">
						<div id="wb_admin_plugin_name">
							<?php esc_html_e( 'BuddyPress Activity Share Pro Settings', 'buddypress-share' ); ?>
							<span><?php printf( __( 'Version %s', 'buddypress-share' ), BP_ACTIVITY_SHARE_PLUGIN_VERSION ); ?></span>
						</div>
						<?php echo do_shortcode( '[wbcom_admin_setting_header]' ); ?>
					</div>
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
			'bpas_reshare_settings' => esc_html__( 'Share Settings', 'buddypress-share' ),
			'bpas_icon_color_settings' => esc_html__( 'Icon Color Settings', 'buddypress-share' ),
		);
		$tab_html  = '<div class="wbcom-tabs-section"><div class="nav-tab-wrapper"><div class="wb-responsive-menu"><span>' . esc_html( 'Menu' ) . '</span><input class="wb-toggle-btn" type="checkbox" id="wb-toggle-btn"><label class="wb-toggle-icon" for="wb-toggle-btn"><span class="wb-icon-bars"></span></label></div><ul>';
		foreach ( $bpas_tabs as $bpas_tab => $bpas_name ) {
			$class     = ( $bpas_tab === $current ) ? 'nav-tab-active' : '';
			$tab_html .= '<li class="' . esc_attr( $bpas_tab ) . '"><a class="nav-tab ' . esc_attr( $class ) . '" href="admin.php?page=buddypress-share&tab=' . $bpas_tab . '">' . esc_html( $bpas_name ) . '</a></li>';
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
					$new_value        = array(
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
		echo '<div class="title-bp-share-view">';
		esc_html_e( 'Default is set to open window in popup. If this option is disabled then services open in new tab instead popup.  ', 'buddypress-share' );
		echo '</div>';
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

		if ( isset( $_POST['bp_reshare_settings'] ) && ! defined( 'DOING_AJAX' ) ) {
			update_site_option( 'bp_reshare_settings', $_POST['bp_reshare_settings'] );
			wp_redirect( $_POST['_wp_http_referer'] );
			exit();
		}

		if ( isset( $_POST['social_services_selector'] ) && ! defined( 'DOING_AJAX' ) ) {
			update_site_option( 'bp_share_services_enable', ( isset( $_POST['bp_share_services_enable'] ) ) ? $_POST['bp_share_services_enable'] : '' );
			wp_redirect( $_POST['_wp_http_referer'] );
			exit();
		}
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
			case 'bpas_reshare_settings':
				$this->bpas_reshare_setting_section();
				break;
			case 'bpas_icon_color_settings':
				$this->bpas_icon_color_setting_section();
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
		$bp_share_services_enable = get_site_option( 'bp_share_services_enable' );
		?>
		<div class="wbcom-tab-content">
			<div class="wbcom-wrapper-admin">
				<div class="wbcom-admin-title-section">
					<h3><?php esc_html_e( 'General Services', 'buddypress-share' ); ?></h3>
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
									<p class="description"><?php esc_html_e( 'Enable this option to show share button in activity page more options.', 'buddypress-share' ); ?></p>
								</div>
								<div id="bp_share_chb" class="wbcom-settings-section-options">
									<input type="checkbox" name="bp_share_services_enable" id="bp_share_services_enable" value="1" <?php checked( '1', $bp_share_services_enable ); ?>/>
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
											$get_social_value = get_site_option( 'bp_share_pro_services' );
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
											<?php } if ( empty( $get_social_value['E-mail'] ) ) { ?>
											<li class="socialicon icon_Gmail" name="icon_gmail"><?php esc_html_e( 'E-mail', 'buddypress-share' ); ?></li>
											<?php } if ( empty( $get_social_value['Whatsapp'] ) ) { ?>
											<li class="socialicon icon_WhatAapp" name="icon_whatsapp"><?php esc_html_e( 'Whatsapp', 'buddypress-share' ); ?></li>
										<?php } ?>
									</ul>
									<ul id="drag_icon_ul">
										<h3><?php esc_html_e( 'Enable', 'buddypress-share' ); ?></h3>
										<?php
										$get_social_value = get_site_option( 'bp_share_pro_services' );
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
										<?php } if ( ! empty( $get_social_value['E-mail'] ) ) { ?>
											<li class="socialicon icon_Gmail" name="icon_gmail"><?php esc_html_e( 'E-mail', 'buddypress-share' ); ?></li>
										<?php } if ( ! empty( $get_social_value['Whatsapp'] ) ) { ?>
											<li class="socialicon icon_WhatsApp" name="icon_whatsapp"><?php esc_html_e( 'Whatsapp', 'buddypress-share' ); ?></li>
										<?php } ?>
									</ul>
								</section>
							</div>
						</div>

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
							<input type="submit" class="button button-primary bp_share_option_save" value="<?php esc_html_e( 'Save Changes', 'buddypress-share' ); ?>" />
						</p>
					</form>

					<?php do_action( 'bp_share_add_services_options', $arg1 = '', $arg2 = '' ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	public function bpas_icon_color_setting_section() {
		if ( file_exists( BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/bp-icon-color-page.php' ) ) {
			require_once BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/bp-icon-color-page.php';
		}
	}

	/**
	 * This function is for that save social icon values in database
	 **/
	public function wss_social_icons() {
		$nonce = ! empty( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bp_share_nonce' ) ) {
			$error = new WP_Error( '001', 'Sorry, your nonce did not verify.', 'Some information' );
			wp_send_json_error( $error );
		}
		$success                = isset( $_POST['term_name'] ) ? sanitize_text_field( wp_unslash( $_POST['term_name'] ) ) : '';
		$icon_value             = array();
		$wss_admin_icon_setting = get_site_option( 'bp_share_pro_services' );
		if ( empty( $wss_admin_icon_setting ) ) {
			$icon_value[ $success ] = $success;
			$update_drag_value      = update_site_option( 'bp_share_pro_services', $icon_value );
		} else {
			$new_icon_value[ $success ] = $success;
			$merge                      = array_merge( $wss_admin_icon_setting, $new_icon_value );
			$update_drag_value          = update_site_option( 'bp_share_pro_services', $merge );
		}
		if ( $update_drag_value ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * This function is for that remove social icon when drag social icon in disable section
	 **/
	public function wss_social_remove_icons() {
		$nonce = ! empty( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bp_share_nonce' ) ) {
			$error = new WP_Error( '001', 'Sorry, your nonce did not verify.', 'Some information' );
			wp_send_json_error( $error );
		}
		$success_icon_val = isset( $_POST['icon_name'] ) ? sanitize_text_field( wp_unslash( $_POST['icon_name'] ) ) : '';

		$icon_value_array      = array();
		$wss_admin_icon_remove = get_site_option( 'bp_share_pro_services' );
		foreach ( $wss_admin_icon_remove as $key => $value ) {
			if ( $key === $success_icon_val ) {
				unset( $wss_admin_icon_remove[ $key ] );
				$update_drag_value = update_site_option( 'bp_share_pro_services', $wss_admin_icon_remove );
			}
		}
		if ( $update_drag_value ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * reshare settig template
	 *
	 * @since    1.0.0
	 */

	public function bpas_reshare_setting_section() {
		$bp_reshare_settings                           = get_site_option( 'bp_reshare_settings' );
		$bp_reshare_settings['reshare_share_activity'] = isset( $bp_reshare_settings['reshare_share_activity'] ) ? $bp_reshare_settings['reshare_share_activity'] : 'parent';
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
									<label><?php esc_html_e( 'Button Background Color', 'buddypress-share' ); ?></label>
								</div>
								<div class="wbcom-settings-section-options">
									<input class="regular-btn bp-reshare-color-picker" type="text" name="bp_reshare_settings[btn_bg_color]" value="<?php echo ( isset( $bp_reshare_settings['btn_bg_color'] ) ) ? $bp_reshare_settings['btn_bg_color'] : ''; ?>" />
								</div>
							</div>
							<div class="wbcom-settings-section-wrap">
								<div class="wbcom-settings-section-options-heading">
									<label><?php esc_html_e( 'Button Text Color', 'buddypress-share' ); ?></label>
								</div>
								<div class="wbcom-settings-section-options">
									<input class="regular-btn bp-reshare-color-picker" type="text" name="bp_reshare_settings[btn_text_color]" value="<?php echo ( isset( $bp_reshare_settings['btn_text_color'] ) ) ? $bp_reshare_settings['btn_text_color'] : ''; ?>" />
								</div>
							</div>
							<div class="wbcom-settings-section-wrap">
								<div class="wbcom-settings-section-options-heading">
									<label><?php esc_html_e( 'Button Hover Background Color', 'buddypress-share' ); ?></label>
								</div>
								<div class="wbcom-settings-section-options">
									<input class="regular-btn bp-reshare-color-picker" type="text" name="bp_reshare_settings[btn_hover_bg_color]" value="<?php echo ( isset( $bp_reshare_settings['btn_hover_bg_color'] ) ) ? $bp_reshare_settings['btn_hover_bg_color'] : ''; ?>" />
								</div>
							</div>
							<div class="wbcom-settings-section-wrap">
								<div class="wbcom-settings-section-options-heading">
									<label><?php esc_html_e( 'Button Hover Text Color', 'buddypress-share' ); ?></label>
								</div>
								<div class="wbcom-settings-section-options">
									<input class="regular-btn bp-reshare-color-picker" type="text" name="bp_reshare_settings[btn_hover_text_color]" value="<?php echo ( isset( $bp_reshare_settings['btn_hover_text_color'] ) ) ? $bp_reshare_settings['btn_hover_text_color'] : ''; ?>" />
								</div>
							</div>
							<div class="wbcom-settings-section-wrap">
								<div class="wbcom-settings-section-options-heading">
									<label><?php esc_html_e( 'Disable Post Share Activity', 'buddypress-share' ); ?></label>
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
												<input type="radio" name="bp_reshare_settings[reshare_share_activity]" value="parent" <?php checked( 'parent', $bp_reshare_settings['reshare_share_activity'] ); ?> />&nbsp;<?php esc_html_e( 'Parent', 'buddypress-share' ); ?>
											</label>
										</li>
										<li>
											<label>
												<input type="radio" name="bp_reshare_settings[reshare_share_activity]" value="child" <?php checked( 'child', $bp_reshare_settings['reshare_share_activity'] ); ?> />&nbsp;<?php esc_html_e( 'Child', 'buddypress-share' ); ?>
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
								<input type="submit" class="button button-primary"  value="<?php esc_html_e( 'Save Changes', 'buddypress-share' ); ?>" />
							</p>
						</div>
					</form>
				</div>
			</div>			
		</div>
		<?php
	}

	public function bpas_pro_icon_color_register_setting() {
		register_setting( 'bpas_icon_color_settings', 'bpas_icon_color_settings' );
	}

}
