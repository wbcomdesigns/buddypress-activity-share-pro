<?php
/**
 * Fired during plugin activation
 *
 * @link       http://wbcomdesigns.com
 * @since      1.0.0
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/includes
 */

/**
 * Fired during plugin activation.
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Buddypress_Share
 * @subpackage Buddypress_Share/includes
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Buddypress_Share_Activator {
	/**
	 * Short Description. (use period)
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if ( get_site_option( 'bp_share_services' ) == false ) {
			$bp_share_pro_icon_default = array(
				'Facebook' => 'Facebook',
				'Twitter'  => 'Twitter',
				'Linkedin' => 'Linkedin',
				'Whatsapp' => 'Whatsapp',
			);
			update_site_option( 'bp_share_services', $bp_share_pro_icon_default );
		}
		if ( get_site_option( 'bp_share_all_services_disable' ) == false ) {
			update_site_option( 'bp_share_all_services_disable', 'enable' );
		}
		if ( get_site_option( 'bp_share_services_enable' ) == false ) {
			update_site_option( 'bp_share_services_enable', 1 );
		}
		if ( get_site_option( 'bpas_icon_color_settings' ) == false ) {
			$bpas_icon_color_settings = array(
				'icon_style' => 'circle',
			);
			update_site_option( 'bpas_icon_color_settings', $bpas_icon_color_settings );
		}
	}
}
