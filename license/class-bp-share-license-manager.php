<?php
/**
 * License Manager class for BuddyPress Activity Share Pro
 */
class BP_Share_License_Manager {
    private static $instance = null;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // License AJAX handlers
        add_action( 'wp_ajax_bp_share_activate_license', array( $this, 'ajax_activate_license' ) );
        add_action( 'wp_ajax_bp_share_deactivate_license', array( $this, 'ajax_deactivate_license' ) );
        add_action( 'wp_ajax_bp_share_check_license', array( $this, 'ajax_check_license' ) );
        add_action( 'wp_ajax_bp_share_save_license_key', array( $this, 'ajax_save_license_key' ) );
        
        // Handle form submissions
        add_action( 'admin_init', array( $this, 'handle_license_actions' ) );
    }
    
    /**
     * Get license status information
     */
    public function get_license_status() {
        $license_key = get_option( 'bp_share_license_key' );
        $license_status = get_option( 'bp_share_license_status' );
        $license_data = get_option( 'bp_share_license_data' );
        
        return array(
            'key' => $license_key,
            'status' => $license_status,
            'data' => $license_data,
            'is_valid' => $license_status === 'valid',
            'has_key' => ! empty( $license_key )
        );
    }
    
    /**
     * Get status display HTML
     */
    public function get_status_display( $license_status ) {
        switch ( $license_status['status'] ) {
            case 'valid':
                return '<span class="bp-share-status-success">✓ Active</span>';
            case 'expired':
                return '<span class="bp-share-status-warning">⚠ Expired</span>';
            case 'invalid':
                return '<span class="bp-share-status-error">✗ Invalid</span>';
            default:
                return '<span class="bp-share-status-inactive">- Not activated</span>';
        }
    }
    
    /**
     * Render license tab content
     */
    public function render_license_tab() {
        $license_status = $this->get_license_status();
        ?>
        <div class="bp-share-license-section">
            <div class="bp-share-license-info">
                <h3><?php _e( 'Plugin License', 'buddypress-share' ); ?></h3>
                <p><?php _e( 'Enter your license key to receive automatic updates and premium support.', 'buddypress-share' ); ?></p>
            </div>
            
            <form method="post" id="bp-share-license-form">
                <?php wp_nonce_field( 'bp_share_license_nonce', 'bp_share_license_nonce' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="bp_share_license_key"><?php _e( 'License Key', 'buddypress-share' ); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="bp_share_license_key" 
                                   name="bp_share_license_key" 
                                   value="<?php echo esc_attr( $license_status['key'] ); ?>" 
                                   class="regular-text" 
                                   placeholder="<?php esc_attr_e( 'Enter your license key', 'buddypress-share' ); ?>" />
                            <p class="description">
                                <?php _e( 'Enter the license key you received when purchasing the plugin.', 'buddypress-share' ); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <?php _e( 'License Status', 'buddypress-share' ); ?>
                        </th>
                        <td>
                            <div id="license-status">
                                <?php echo $this->render_license_status_display( $license_status ); ?>
                            </div>
                        </td>
                    </tr>
                </table>
                
                <div class="bp-share-license-actions">
                    <?php if ( $license_status['status'] === 'valid' ) : ?>
                        <button type="button" id="deactivate-license" class="button button-secondary">
                            <?php _e( 'Deactivate License', 'buddypress-share' ); ?>
                        </button>
                    <?php else : ?>
                        <button type="button" id="activate-license" class="button button-primary">
                            <?php _e( 'Activate License', 'buddypress-share' ); ?>
                        </button>
                    <?php endif; ?>
                    
                    <button type="button" id="check-license" class="button">
                        <?php _e( 'Check License', 'buddypress-share' ); ?>
                    </button>
                    
                    <button type="submit" class="button">
                        <?php _e( 'Save License Key', 'buddypress-share' ); ?>
                    </button>
                </div>
                
                <div id="license-message" class="bp-share-license-message"></div>
            </form>
            
            <!-- License Information -->
            <div class="bp-share-license-info-box">
                <h4><?php _e( 'License Benefits', 'buddypress-share' ); ?></h4>
                <ul>
                    <li><span class="dashicons dashicons-update"></span> <?php _e( 'Automatic plugin updates', 'buddypress-share' ); ?></li>
                    <li><span class="dashicons dashicons-sos"></span> <?php _e( 'Premium support', 'buddypress-share' ); ?></li>
                    <li><span class="dashicons dashicons-shield"></span> <?php _e( 'Security updates', 'buddypress-share' ); ?></li>
                    <li><span class="dashicons dashicons-star-filled"></span> <?php _e( 'New features and improvements', 'buddypress-share' ); ?></li>
                </ul>
                
                <p>
                    <a href="<?php echo esc_url( BP_ACTIVITY_SHARE_STORE_URL ); ?>" target="_blank" class="button button-secondary">
                        <?php _e( 'Get License Key', 'buddypress-share' ); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render license status display
     */
    private function render_license_status_display( $license_status ) {
        ob_start();
        
        if ( $license_status['status'] === 'valid' ) : ?>
            <span class="bp-share-license-status bp-share-license-valid">
                <span class="dashicons dashicons-yes-alt"></span>
                <?php _e( 'Active', 'buddypress-share' ); ?>
            </span>
            
            <?php if ( $license_status['data'] && isset( $license_status['data']->expires ) ) : ?>
                <p class="description">
                    <?php 
                    if ( $license_status['data']->expires === 'lifetime' ) {
                        _e( 'License never expires', 'buddypress-share' );
                    } else {
                        printf( 
                            __( 'License expires: %s', 'buddypress-share' ), 
                            date_i18n( get_option( 'date_format' ), strtotime( $license_status['data']->expires ) )
                        );
                    }
                    ?>
                </p>
            <?php endif; ?>
            
        <?php elseif ( $license_status['status'] === 'expired' ) : ?>
            <span class="bp-share-license-status bp-share-license-expired">
                <span class="dashicons dashicons-warning"></span>
                <?php _e( 'Expired', 'buddypress-share' ); ?>
            </span>
            <p class="description">
                <?php _e( 'Your license has expired. Please renew to continue receiving updates.', 'buddypress-share' ); ?>
            </p>
            
        <?php elseif ( $license_status['status'] === 'invalid' || $license_status['status'] === 'site_inactive' ) : ?>
            <span class="bp-share-license-status bp-share-license-invalid">
                <span class="dashicons dashicons-dismiss"></span>
                <?php _e( 'Invalid', 'buddypress-share' ); ?>
            </span>
            <p class="description">
                <?php _e( 'This license is not valid for this site. Please check your license key.', 'buddypress-share' ); ?>
            </p>
            
        <?php else : ?>
            <span class="bp-share-license-status bp-share-license-inactive">
                <span class="dashicons dashicons-minus"></span>
                <?php _e( 'Inactive', 'buddypress-share' ); ?>
            </span>
            <p class="description">
                <?php _e( 'Please enter and activate your license key.', 'buddypress-share' ); ?>
            </p>
        <?php endif;
        
        return ob_get_clean();
    }
    
    /**
     * Handle license actions from form submissions
     */
    public function handle_license_actions() {
        // Listen for our activate button to be clicked
        if ( isset( $_POST['bp_share_license_activate'] ) ) {
            $this->activate_license();
        }
        
        // Listen for our deactivate button to be clicked
        if ( isset( $_POST['bp_share_license_deactivate'] ) ) {
            $this->deactivate_license();
        }
        
        // Handle license key save
        if ( isset( $_POST['bp_share_license_key'] ) && isset( $_POST['bp_share_license_nonce'] ) ) {
            if ( wp_verify_nonce( $_POST['bp_share_license_nonce'], 'bp_share_license_nonce' ) ) {
                $license_key = sanitize_text_field( $_POST['bp_share_license_key'] );
                $old_license = get_option( 'bp_share_license_key' );
                
                if ( $old_license && $old_license !== $license_key ) {
                    delete_option( 'bp_share_license_status' );
                    delete_option( 'bp_share_license_data' );
                }
                
                update_option( 'bp_share_license_key', $license_key );
            }
        }
    }
    
    /**
     * AJAX handler for saving license key
     */
    public function ajax_save_license_key() {
        check_ajax_referer( 'bp_share_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'buddypress-share' ) ) );
        }
        
        $license_key = sanitize_text_field( $_POST['bp_share_license_key'] ?? '' );
        $old_license = get_option( 'bp_share_license_key' );
        
        if ( $old_license && $old_license !== $license_key ) {
            delete_option( 'bp_share_license_status' );
            delete_option( 'bp_share_license_data' );
        }
        
        update_option( 'bp_share_license_key', $license_key );
        
        wp_send_json_success( array( 'message' => __( 'License key saved successfully', 'buddypress-share' ) ) );
    }
    
    /**
     * AJAX activate license
     */
    public function ajax_activate_license() {
        check_ajax_referer( 'bp_share_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'buddypress-share' ) ) );
        }
        
        $license = trim( $_POST['license_key'] ?? '' );
        
        if ( empty( $license ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a license key', 'buddypress-share' ) ) );
        }
        
        $result = $this->activate_license_api( $license );
        
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }
        
        wp_send_json_success( array( 'message' => __( 'License activated successfully!', 'buddypress-share' ) ) );
    }
    
    /**
     * AJAX deactivate license
     */
    public function ajax_deactivate_license() {
        check_ajax_referer( 'bp_share_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'buddypress-share' ) ) );
        }
        
        $result = $this->deactivate_license_api();
        
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }
        
        wp_send_json_success( array( 'message' => __( 'License deactivated successfully!', 'buddypress-share' ) ) );
    }
    
    /**
     * AJAX check license
     */
    public function ajax_check_license() {
        check_ajax_referer( 'bp_share_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'buddypress-share' ) ) );
        }
        
        $license = trim( get_option( 'bp_share_license_key' ) );
        
        if ( empty( $license ) ) {
            wp_send_json_error( array( 'message' => __( 'No license key found', 'buddypress-share' ) ) );
        }
        
        $result = $this->check_license_api( $license );
        
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }
        
        $license_data = $result;
        
        if ( 'valid' === $license_data->license ) {
            wp_send_json_success( array( 'message' => __( 'License is valid!', 'buddypress-share' ) ) );
        } else {
            wp_send_json_success( array( 'message' => __( 'License is not valid.', 'buddypress-share' ) ) );
        }
    }
    
    /**
     * Activate license via API
     */
    private function activate_license_api( $license ) {
        // data to send in our API request
        $api_params = array(
            'edd_action' => 'activate_license',
            'license'    => $license,
            'item_id'    => BP_ACTIVITY_SHARE_ITEM_ID,
            'item_name'  => rawurlencode( BP_ACTIVITY_SHARE_ITEM_NAME ),
            'url'        => home_url(),
            'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
        );
        
        // Call the custom API.
        $response = wp_remote_post(
            BP_ACTIVITY_SHARE_STORE_URL,
            array(
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => $api_params,
            )
        );
        
        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            $message = is_wp_error( $response ) ? $response->get_error_message() : __( 'An error occurred, please try again.', 'buddypress-share' );
            return new WP_Error( 'api_error', $message );
        }
        
        $license_data = json_decode( wp_remote_retrieve_body( $response ) );
        
        if ( false === $license_data->success ) {
            switch ( $license_data->error ) {
                case 'expired':
                    $message = sprintf(
                        __( 'Your license key expired on %s.', 'buddypress-share' ),
                        date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                    );
                    break;
                case 'disabled':
                case 'revoked':
                    $message = __( 'Your license key has been disabled.', 'buddypress-share' );
                    break;
                case 'missing':
                    $message = __( 'Invalid license.', 'buddypress-share' );
                    break;
                case 'invalid':
                case 'site_inactive':
                    $message = __( 'Your license is not active for this URL.', 'buddypress-share' );
                    break;
                case 'item_name_mismatch':
                    $message = sprintf( __( 'This appears to be an invalid license key for %s.', 'buddypress-share' ), BP_ACTIVITY_SHARE_ITEM_NAME );
                    break;
                case 'no_activations_left':
                    $message = __( 'Your license key has reached its activation limit.', 'buddypress-share' );
                    break;
                default:
                    $message = __( 'An error occurred, please try again.', 'buddypress-share' );
                    break;
            }
            return new WP_Error( 'license_error', $message );
        }
        
        // $license_data->license will be either "valid" or "invalid"
        if ( 'valid' === $license_data->license ) {
            update_option( 'bp_share_license_key', $license );
            update_option( 'bp_share_license_status', $license_data->license );
            update_option( 'bp_share_license_data', $license_data );
            return true;
        } else {
            return new WP_Error( 'license_invalid', __( 'License activation failed.', 'buddypress-share' ) );
        }
    }
    
    /**
     * Deactivate license via API
     */
    private function deactivate_license_api() {
        $license = trim( get_option( 'bp_share_license_key' ) );
        
        // data to send in our API request
        $api_params = array(
            'edd_action' => 'deactivate_license',
            'license'    => $license,
            'item_id'    => BP_ACTIVITY_SHARE_ITEM_ID,
            'item_name'  => rawurlencode( BP_ACTIVITY_SHARE_ITEM_NAME ),
            'url'        => home_url(),
            'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
        );
        
        // Call the custom API.
        $response = wp_remote_post(
            BP_ACTIVITY_SHARE_STORE_URL,
            array(
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => $api_params,
            )
        );
        
        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            $message = is_wp_error( $response ) ? $response->get_error_message() : __( 'An error occurred, please try again.', 'buddypress-share' );
            return new WP_Error( 'api_error', $message );
        }
        
        // decode the license data
        $license_data = json_decode( wp_remote_retrieve_body( $response ) );
        
        // $license_data->license will be either "deactivated" or "failed"
        if ( 'deactivated' === $license_data->license ) {
            delete_option( 'bp_share_license_status' );
            delete_option( 'bp_share_license_data' );
            return true;
        } else {
            return new WP_Error( 'license_error', __( 'License deactivation failed.', 'buddypress-share' ) );
        }
    }
    
    /**
     * Check license via API
     */
    private function check_license_api( $license ) {
        $api_params = array(
            'edd_action' => 'check_license',
            'license'    => $license,
            'item_id'    => BP_ACTIVITY_SHARE_ITEM_ID,
            'item_name'  => rawurlencode( BP_ACTIVITY_SHARE_ITEM_NAME ),
            'url'        => home_url(),
            'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
        );
        
        // Call the custom API.
        $response = wp_remote_post(
            BP_ACTIVITY_SHARE_STORE_URL,
            array(
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => $api_params,
            )
        );
        
        if ( is_wp_error( $response ) ) {
            return $response;
        }
        
        $license_data = json_decode( wp_remote_retrieve_body( $response ) );
        
        update_option( 'bp_share_license_status', $license_data->license );
        update_option( 'bp_share_license_data', $license_data );
        
        return $license_data;
    }
    
    /**
     * Activate license (form submission)
     */
    private function activate_license() {
        // Run a quick security check
        if ( ! check_admin_referer( 'bp_share_license_nonce', 'bp_share_license_nonce' ) ) {
            return;
        }
        
        // Retrieve the license from the database
        $license = trim( get_option( 'bp_share_license_key' ) );
        if ( ! $license ) {
            $license = ! empty( $_POST['bp_share_license_key'] ) ? sanitize_text_field( $_POST['bp_share_license_key'] ) : '';
        }
        if ( ! $license ) {
            return;
        }
        
        $result = $this->activate_license_api( $license );
        
        if ( is_wp_error( $result ) ) {
            $redirect = add_query_arg(
                array(
                    'page'          => 'buddypress-share',
                    'tab'           => 'license',
                    'sl_activation' => 'false',
                    'message'       => rawurlencode( $result->get_error_message() ),
                ),
                admin_url( 'options-general.php' )
            );
            
            wp_safe_redirect( $redirect );
            exit();
        }
        
        wp_safe_redirect( admin_url( 'options-general.php?page=buddypress-share&tab=license&sl_activation=true' ) );
        exit();
    }
    
    /**
     * Deactivate license (form submission)
     */
    private function deactivate_license() {
        // Run a quick security check
        if ( ! check_admin_referer( 'bp_share_license_nonce', 'bp_share_license_nonce' ) ) {
            return;
        }
        
        $result = $this->deactivate_license_api();
        
        if ( is_wp_error( $result ) ) {
            $redirect = add_query_arg(
                array(
                    'page'          => 'buddypress-share',
                    'tab'           => 'license',
                    'sl_activation' => 'false',
                    'message'       => rawurlencode( $result->get_error_message() ),
                ),
                admin_url( 'options-general.php' )
            );
            
            wp_safe_redirect( $redirect );
            exit();
        }
        
        wp_safe_redirect( admin_url( 'options-general.php?page=buddypress-share&tab=license&sl_deactivation=true' ) );
        exit();
    }
}