<?php
/**
 * EDD Software Licensing Updater Wrapper for BuddyPress Activity Share Pro
 * 
 * This wrapper class handles text domain issues with the EDD updater
 * and provides a clean interface for our plugin.
 *
 * @package BuddyPress_Share
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load the EDD updater class
 */
require_once BP_ACTIVITY_SHARE_PLUGIN_PATH . 'license/EDD_SL_Plugin_Updater.php';

/**
 * Wrapper class for EDD_SL_Plugin_Updater
 */
class BP_Share_EDD_Updater_Wrapper {
    
    /**
     * The EDD updater instance
     *
     * @var EDD_SL_Plugin_Updater
     */
    private $updater;
    
    /**
     * Constructor
     *
     * @param string $api_url     The URL pointing to the custom API endpoint.
     * @param string $plugin_file Path to the plugin file.
     * @param array  $api_data    Optional data to send with API calls.
     */
    public function __construct( $api_url, $plugin_file, $api_data = null ) {
        // Initialize the EDD updater
        $this->updater = new EDD_SL_Plugin_Updater( $api_url, $plugin_file, $api_data );
        
        // Add filters to handle text domain issues
        $this->add_text_domain_filters();
    }
    
    /**
     * Add filters to handle text domain translation
     */
    private function add_text_domain_filters() {
        // Filter the update messages to use our text domain
        add_filter( 'gettext', array( $this, 'filter_edd_strings' ), 10, 3 );
        add_filter( 'gettext_with_context', array( $this, 'filter_edd_strings_with_context' ), 10, 4 );
    }
    
    /**
     * Filter EDD strings to use our text domain
     *
     * @param string $translated  The translated text.
     * @param string $text        The text to translate.
     * @param string $domain      The text domain.
     * @return string
     */
    public function filter_edd_strings( $translated, $text, $domain ) {
        // Only filter EDD domain strings in our admin pages
        if ( 'easy-digital-downloads' !== $domain ) {
            return $translated;
        }
        
        // Check if we're on our plugin pages
        if ( ! $this->is_our_admin_page() ) {
            return $translated;
        }
        
        // List of strings we want to translate with our domain
        $strings_to_translate = array(
            'There is a new version of %1$s available.' => __( 'There is a new version of %1$s available.', 'buddypress-share' ),
            'Contact your network administrator to install the update.' => __( 'Contact your network administrator to install the update.', 'buddypress-share' ),
            'View version %2$s details' => __( 'View version %2$s details', 'buddypress-share' ),
            'Update now.' => __( 'Update now.', 'buddypress-share' ),
            'You do not have permission to install plugin updates' => __( 'You do not have permission to install plugin updates', 'buddypress-share' ),
            'Error' => __( 'Error', 'buddypress-share' ),
        );
        
        if ( isset( $strings_to_translate[ $text ] ) ) {
            return $strings_to_translate[ $text ];
        }
        
        return $translated;
    }
    
    /**
     * Filter EDD strings with context to use our text domain
     *
     * @param string $translated  The translated text.
     * @param string $text        The text to translate.
     * @param string $context     The context for the translation.
     * @param string $domain      The text domain.
     * @return string
     */
    public function filter_edd_strings_with_context( $translated, $text, $context, $domain ) {
        if ( 'easy-digital-downloads' !== $domain ) {
            return $translated;
        }
        
        if ( ! $this->is_our_admin_page() ) {
            return $translated;
        }
        
        // Handle any contextual translations if needed
        return $translated;
    }
    
    /**
     * Check if we're on our plugin's admin page
     *
     * @return bool
     */
    private function is_our_admin_page() {
        if ( ! is_admin() ) {
            return false;
        }
        
        // Check if we're on our settings page
        $screen = get_current_screen();
        if ( $screen && strpos( $screen->id, 'buddypress-share' ) !== false ) {
            return true;
        }
        
        // Check if we're on plugins page (for update notices)
        if ( $screen && $screen->id === 'plugins' ) {
            return true;
        }
        
        // Check if we're on updates page
        if ( $screen && $screen->id === 'update-core' ) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get the updater instance
     *
     * @return EDD_SL_Plugin_Updater
     */
    public function get_updater() {
        return $this->updater;
    }
}