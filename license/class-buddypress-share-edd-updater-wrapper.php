<?php
/**
 * EDD Software Licensing Updater Wrapper
 */
class BP_ACTIVITY_SHARE_PLUGIN_EDD_Updater_Wrapper {
    
    private $license_key;
    
    public function __construct( $license_key ) {
        $this->license_key = $license_key;
        $this->init();
    }
    
    private function init() {
        // Include EDD updater if not already loaded
        if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
            require_once BP_ACTIVITY_SHARE_PLUGIN_PLUGIN_DIR . 'license/EDD_SL_Plugin_Updater.php';
        }
        
        // Setup the updater
        $updater = new EDD_SL_Plugin_Updater(
            BP_ACTIVITY_SHARE_PLUGIN_STORE_URL,
            BP_ACTIVITY_SHARE_PLUGIN_PLUGIN_FILE,
            array(
                'version'   => BP_ACTIVITY_SHARE_PLUGIN_VERSION,
                'license'   => $this->license_key,
                'item_id'   => BP_ACTIVITY_SHARE_PLUGIN_ITEM_ID,
                'item_name' => BP_ACTIVITY_SHARE_PLUGIN_ITEM_NAME,
                'author'    => 'WBCom Designs',
                'url'       => home_url(),
            )
        );
    }
}