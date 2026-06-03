<?php
/**
 * BPAS Admin Panel: menu, enqueue, shell + view routing.
 *
 * Replaces the legacy Wbcom wrapper chrome. Follows the
 * wp-plugin-development skill Part 6 card-panel pattern and the
 * references/wbcom-wrapper-migration.md playbook (Parts 5, 6, 15).
 *
 * NOTE: This class owns the admin *chrome* only. All persistence,
 * sanitization, and the two service AJAX handlers stay on
 * Buddypress_Share_Admin (the data contract — playbook Part 3).
 *
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/admin
 * @since      2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Bpas_Admin_Panel
 *
 * @since 2.3.0
 */
class Bpas_Admin_Panel {

	/**
	 * Canonical menu slug for the single admin page.
	 *
	 * Clean slug under the wbcomplugins hub (owner directive 2026-06-03:
	 * no legacy wbcom-buddypress-share alias).
	 */
	const MENU_SLUG = 'buddypress-share';

	/**
	 * Legacy admin object (sanitizers + service AJAX live there).
	 *
	 * @var Buddypress_Share_Admin
	 */
	private $legacy;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Constructor.
	 *
	 * @param Buddypress_Share_Admin $legacy  Legacy admin (data contract).
	 * @param string                 $version Plugin version.
	 */
	public function __construct( $legacy, $version ) {
		$this->legacy  = $legacy;
		$this->version = $version;
	}

	/**
	 * Sidebar tab registry. One admin page, sidebar-routed views.
	 *
	 * @return array<string, array{label:string, icon:string, group:string}>
	 */
	public static function get_tabs() {
		$tabs = array(
			'overview'     => array(
				'label' => __( 'Overview', 'buddypress-share' ),
				'icon'  => 'dashicons-chart-bar',
				'group' => 'main',
			),
			'networks'     => array(
				'label' => __( 'Social Networks', 'buddypress-share' ),
				'icon'  => 'dashicons-share-alt2',
				'group' => 'settings',
			),
			'display'      => array(
				'label' => __( 'Display', 'buddypress-share' ),
				'icon'  => 'dashicons-art',
				'group' => 'settings',
			),
			'restrictions' => array(
				'label' => __( 'Restrictions', 'buddypress-share' ),
				'icon'  => 'dashicons-admin-settings',
				'group' => 'settings',
			),
			'post-types'   => array(
				'label' => __( 'Post Type Sharing', 'buddypress-share' ),
				'icon'  => 'dashicons-admin-post',
				'group' => 'settings',
			),
			'faq'          => array(
				'label' => __( 'FAQ', 'buddypress-share' ),
				'icon'  => 'dashicons-editor-help',
				'group' => 'resources',
			),
		);

		/**
		 * Filter the admin sidebar tabs.
		 *
		 * @since 2.3.0
		 * @param array $tabs Tab registry keyed by slug.
		 */
		return apply_filters( 'bpas_admin_tabs', $tabs );
	}

	/**
	 * Map a sidebar tab slug to its view file basename.
	 *
	 * @return array<string,string>
	 */
	private function view_map() {
		return array(
			'overview'     => 'overview',
			'networks'     => 'settings-networks',
			'display'      => 'settings-display',
			'restrictions' => 'settings-restrictions',
			'post-types'   => 'settings-post-types',
			'faq'          => 'faq',
			'onboarding'   => 'onboarding',
		);
	}

	/**
	 * Bootstrap hooks.
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_menu', array( $this, 'takeover_hub_landing' ), 999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'in_admin_header', array( $this, 'suppress_foreign_notices' ), 1 );
	}

	/**
	 * Attach as a single submenu under the shared WB Plugins hub.
	 */
	public function add_menu() {
		$cap = 'manage_options';

		if ( empty( $GLOBALS['admin_page_hooks']['wbcomplugins'] ) ) {
			add_menu_page(
				esc_html__( 'WB Plugins', 'buddypress-share' ),
				esc_html__( 'WB Plugins', 'buddypress-share' ),
				$cap,
				'wbcomplugins',
				array( $this, 'render_hub' ),
				'dashicons-lightbulb',
				59
			);
		}

		add_submenu_page(
			'wbcomplugins',
			esc_html__( 'BuddyPress Activity Share', 'buddypress-share' ),
			esc_html__( 'Activity Share', 'buddypress-share' ),
			$cap,
			self::MENU_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Reclaim the shared hub's landing render so our clean card grid
	 * wins over any legacy wrapper dashboard. Playbook Part 15.
	 */
	public function takeover_hub_landing() {
		global $admin_page_hooks;
		if ( empty( $admin_page_hooks['wbcomplugins'] ) ) {
			return;
		}
		remove_all_actions( 'toplevel_page_wbcomplugins' );
		add_action( 'toplevel_page_wbcomplugins', array( $this, 'render_hub' ) );
	}

	/**
	 * True if the current screen is our admin page OR the shared hub
	 * landing (our callback owns the hub render, so our CSS/JS loads there).
	 *
	 * @param WP_Screen $screen Current screen.
	 * @return bool
	 */
	private function is_our_screen( $screen ) {
		if ( empty( $screen->id ) ) {
			return false;
		}
		return (bool) preg_match( '/_page_' . preg_quote( self::MENU_SLUG, '/' ) . '$/', $screen->id )
			|| 'toplevel_page_wbcomplugins' === $screen->id;
	}

	/**
	 * Enqueue admin assets only on our screen + the hub landing.
	 *
	 * @param string $hook_suffix Current admin page hook (unused; we
	 *                            inspect the screen object instead).
	 */
	public function enqueue_assets( $hook_suffix ) {
		unset( $hook_suffix );
		$screen = get_current_screen();
		if ( ! $screen || ! $this->is_our_screen( $screen ) ) {
			return;
		}

		$plugin_url = BP_ACTIVITY_SHARE_PLUGIN_URL;

		// jQuery UI for the services drag-drop on the Networks tab.
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );

		// WordPress color picker for the Display tab.
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Dashicons (icon set — Dashicons, not Lucide, per migration note).
		wp_enqueue_style( 'dashicons' );

		// Main admin stylesheet (auto .min + RTL).
		bp_share_enqueue_style(
			'buddypress-share-admin',
			$plugin_url . 'admin/css/buddypress-share-admin',
			array(),
			$this->version,
			'all'
		);

		// Main admin script (auto .min).
		bp_share_enqueue_script(
			'buddypress-share-admin',
			$plugin_url . 'admin/js/buddypress-share-admin',
			array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ),
			$this->version,
			true
		);

		wp_localize_script(
			'buddypress-share-admin',
			'bp_share_admin_vars',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'bp_share_admin_nonce' ),
				'strings'  => array(
					'loading' => __( 'Loading…', 'buddypress-share' ),
					'saving'  => __( 'Saving…', 'buddypress-share' ),
					'saved'   => __( 'Settings saved.', 'buddypress-share' ),
					'error'   => __( 'Something went wrong. Please try again.', 'buddypress-share' ),
				),
			)
		);

		// Generic toast/confirm helper localization (used by admin JS).
		wp_localize_script(
			'buddypress-share-admin',
			'bpasAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'saved'           => __( 'Settings saved.', 'buddypress-share' ),
					'saveFailed'      => __( 'Could not save. Please try again.', 'buddypress-share' ),
					'confirmDanger'   => __( 'Are you sure? This cannot be undone.', 'buddypress-share' ),
					'confirmContinue' => __( 'Continue', 'buddypress-share' ),
					'confirmCancel'   => __( 'Cancel', 'buddypress-share' ),
				),
			)
		);
	}

	/**
	 * Suppress 3rd-party admin notices on our screen.
	 */
	public function suppress_foreign_notices() {
		$screen = get_current_screen();
		if ( ! $screen || ! $this->is_our_screen( $screen ) ) {
			return;
		}
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );
	}

	/**
	 * Render the shared WB Plugins hub landing page.
	 */
	public function render_hub() {
		$view = BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/views/hub.php';
		if ( file_exists( $view ) ) {
			include $view;
		}
	}

	/**
	 * Render the single admin page. Routes to the active tab/view.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'buddypress-share' ) );
		}

		$bpas_tabs = self::get_tabs();
		$tab_slugs = array_keys( $bpas_tabs );

		// Legacy ?section= values keep old links working.
		$legacy_section_map = array(
			''             => 'networks',
			'general'      => 'networks',
			'services'     => 'networks',
			'display'      => 'display',
			'icons'        => 'display',
			'restrictions' => 'restrictions',
			'sharing'      => 'restrictions',
			'post-types'   => 'post-types',
			'faq'          => 'faq',
		);

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$onboarding = isset( $_GET['onboarding'] ) ? sanitize_key( wp_unslash( $_GET['onboarding'] ) ) : '';
		$requested  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		$section    = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// First-run onboarding takes over the whole page (no sidebar).
		if ( '1' === $onboarding && '1' !== (string) get_site_option( 'bpas_onboarding_complete', '0' ) ) {
			$this->render_onboarding();
			return;
		}

		$active = '';
		if ( $requested && isset( $bpas_tabs[ $requested ] ) ) {
			$active = $requested;
		} elseif ( '' !== $section && isset( $legacy_section_map[ $section ] ) ) {
			$active = $legacy_section_map[ $section ];
		}
		if ( ! $active ) {
			$active = $tab_slugs[0];
		}

		$view_map = $this->view_map();
		$view     = isset( $view_map[ $active ] ) ? $view_map[ $active ] : 'overview';

		// Vars consumed by shell.php (template-variable contract — playbook 12.2).
		$page_url          = admin_url( 'admin.php?page=' . self::MENU_SLUG );
		$in_settings_group = isset( $bpas_tabs[ $active ]['group'] ) && 'settings' === $bpas_tabs[ $active ]['group'];
		$legacy_admin      = $this->legacy;
		$plugin_version    = $this->version;
		$view_path         = BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/views/' . $view . '.php';
		$shell             = BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/views/shell.php';

		include $shell;
	}

	/**
	 * Render the full-width onboarding view.
	 */
	private function render_onboarding() {
		$page_url       = admin_url( 'admin.php?page=' . self::MENU_SLUG );
		$plugin_version = $this->version;
		$view           = BP_ACTIVITY_SHARE_PLUGIN_PATH . 'admin/views/onboarding.php';
		if ( file_exists( $view ) ) {
			include $view;
		}
	}

	/**
	 * AJAX handler that records first-run onboarding completion.
	 *
	 * Additive only: writes the new bpas_onboarding_complete site option.
	 */
	public function complete_onboarding() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bp_share_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'buddypress-share' ) ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'buddypress-share' ) ) );
		}

		update_site_option( 'bpas_onboarding_complete', '1' );

		wp_send_json_success(
			array(
				'redirect' => admin_url( 'admin.php?page=' . self::MENU_SLUG ),
			)
		);
	}
}
