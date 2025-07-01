<?php
/**
 * License Updater class for BuddyPress Activity Share Pro
 * Handles EDD Software Licensing integration
 */
class BP_Share_License_Updater {
    private static $instance = null;
    private $updater_wrapper;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Don't initialize updater in constructor to avoid class loading issues
        add_action( 'init', array( $this, 'init_updater' ), 15 );
    }
    
    /**
     * Initialize EDD Software Licensing updater
     */
    public function init_updater() {
        // To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
        $doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
        if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
            return;
        }

        // Check if required classes exist before trying to use them
        if ( ! class_exists( 'BP_Share_EDD_Updater_Wrapper' ) ) {
            error_log( 'BP Share: BP_Share_EDD_Updater_Wrapper class not found' );
            return;
        }

        // retrieve our license key from the DB
        $license_key = trim( get_option( 'bp_share_license_key' ) );

        // setup the updater through our wrapper
        $this->updater_wrapper = new BP_Share_EDD_Updater_Wrapper(
            BP_ACTIVITY_SHARE_STORE_URL,
            BP_ACTIVITY_SHARE_PLUGIN_PATH . 'buddypress-share.php',
            array(
                'version'   => BP_ACTIVITY_SHARE_PLUGIN_VERSION,
                'license'   => $license_key,
                'item_id'   => BP_ACTIVITY_SHARE_ITEM_ID,
                'item_name' => BP_ACTIVITY_SHARE_ITEM_NAME,
                'author'    => 'Wbcom Designs',
                'beta'      => false,
            )
        );
    }
    
    /**
     * Get updater instance
     */
    public function get_updater() {
        if ( $this->updater_wrapper ) {
            return $this->updater_wrapper->get_updater();
        }
        return null;
    }
    
    /**
     * Check if updates are available
     */
    public function has_update() {
        $updater = $this->get_updater();
        if ( ! $updater ) {
            return false;
        }
        
        $update_cache = get_site_transient( 'update_plugins' );
        
        if ( ! isset( $update_cache->response[ BP_ACTIVITY_SHARE_PLUGIN_BASENAME ] ) ) {
            return false;
        }
        
        $plugin_data = $update_cache->response[ BP_ACTIVITY_SHARE_PLUGIN_BASENAME ];
        
        return version_compare( BP_ACTIVITY_SHARE_PLUGIN_VERSION, $plugin_data->new_version, '<' );
    }
    
    /**
     * Get update information
     */
    public function get_update_info() {
        if ( ! $this->has_update() ) {
            return false;
        }
        
        $update_cache = get_site_transient( 'update_plugins' );
        return $update_cache->response[ BP_ACTIVITY_SHARE_PLUGIN_BASENAME ];
    }
}