<?php
/**
 * License updater handler
 */
class BP_ACTIVITY_SHARE_PLUGIN_License_Updater {
    
    public function __construct() {
        add_action( 'init', array( $this, 'init_updater' ), 0 );
    }
    
    /**
     * Initialize the updater
     */
    public function init_updater() {
        // Don't run in admin ajax or doing cron
        if ( wp_doing_ajax() || wp_doing_cron() ) {
            return;
        }
        
        // Get license key
        $license_key = get_option( 'bp_activity_share_plugin_license_key' );
        
        if ( empty( $license_key ) ) {
            return;
        }
        
        // Include the updater wrapper
        if ( ! class_exists( 'BP_ACTIVITY_SHARE_PLUGIN_EDD_Updater_Wrapper' ) ) {
            require_once dirname( __FILE__ ) . '/class-buddypress-share-edd-updater-wrapper.php';
        }
        
        // Initialize the updater
        new BP_ACTIVITY_SHARE_PLUGIN_EDD_Updater_Wrapper( $license_key );
    }
}