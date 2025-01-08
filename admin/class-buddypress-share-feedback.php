<?php
/**
 * Plugin review class.
 * Prompts users to give a review of the plugin on WordPress.org after a period of usage.
 *
 * Heavily based on code by Rhys Wynne
 * https://winwar.co.uk/2014/10/ask-wordpress-plugin-reviews-week/
 *
 * @package Buddypress_Share
 */

if ( ! class_exists( 'BP_Share_Feedback' ) ) :

	/**
	 * The feedback.
	 */
	class BP_Share_Feedback {

		/**
		 * Slug.
		 *
		 * @var string $slug
		 */
		private $slug;

		/**
		 * Name.
		 *
		 * @var string $name
		 */
		private $name;

		/**
		 * Time limit.
		 *
		 * @var string $time_limit
		 */
		private $time_limit;

		/**
		 * No Bug Option.
		 *
		 * @var string $nobug_option
		 */
		public $nobug_option;

		/**
		 * Activation Date Option.
		 *
		 * @var string $date_option
		 */
		public $date_option;

		/**
		 * Class constructor.
		 *
		 * @param string $args Arguments.
		 */
		public function __construct( $args ) {
			$this->slug       = $args['slug'];
			$this->name       = $args['name'];
			$this->time_limit = isset( $args['time_limit'] ) ? $args['time_limit'] : WEEK_IN_SECONDS;

			$this->date_option  = $this->slug . '_activation_date';
			$this->nobug_option = $this->slug . '_no_bug';

			// Initialize the hooks.
			$this->init_hooks();
		}

		/**
		 * Initialize action hooks.
		 */
		private function init_hooks() {
			add_action( 'admin_init', array( $this, 'check_installation_date' ) );
			add_action( 'admin_init', array( $this, 'set_no_bug' ), 5 );
		}

		/**
		 * Seconds to words.
		 *
		 * @param string $seconds Seconds in time.
		 */
		public function seconds_to_words( $seconds ) {
			// Define the time units and their corresponding values in seconds.
			$units = array(
				'year'   => YEAR_IN_SECONDS,
				'week'   => WEEK_IN_SECONDS,
				'day'    => DAY_IN_SECONDS,
				'hour'   => HOUR_IN_SECONDS,
				'minute' => MINUTE_IN_SECONDS,
				'second' => 1,
			);

			// Iterate over the units and return the first matching value.
			foreach ( $units as $name => $divisor ) {
				$value = intval( $seconds / $divisor );
				if ( $value > 0 ) {
					return sprintf( _n( '%d ' . $name, '%d ' . $name . 's', $value, 'buddypress-share' ), $value ); // phpcs:ignore
				}
			}

			// Return 'zero seconds' if no time unit applies.
			return __( 'zero seconds', 'buddypress-share' );
		}

		/**
		 * Check date on admin initiation and add to admin notice if it was more than the time limit.
		 */
		public function check_installation_date() {
			// Retrieve the installation date option once.
			$install_date = get_site_option( $this->date_option );

			// Set the installation date if it doesn't exist.
			if ( false === $install_date ) {
				$install_date = time();
				add_site_option( $this->date_option, $install_date );
			}

			// Retrieve the nobug option once.
			$nobug_option = get_site_option( $this->nobug_option );

			// Check if the nobug option is not set and if the time since installation exceeds the limit.
			if ( ! $nobug_option && ( time() - $install_date ) > $this->time_limit ) {
				add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
			}
		}

		/**
		 * Display the admin notice.
		 */
		public function display_admin_notice() {
			$screen = get_current_screen();
			if ( isset( $screen->base ) && 'plugins' === $screen->base ) {
				$no_bug_url         = esc_url( wp_nonce_url( admin_url( '?' . $this->nobug_option . '=true' ), 'buddypress-share-feedback-nonce' ) );
				$time_since_install = $this->seconds_to_words( time() - get_site_option( $this->date_option ) );

				$rtl_css = is_rtl() ? '-rtl' : '';

				if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
					$css_extension = '.css';
				} else {
					$css_extension = '.min.css';
				}

				// Enqueue the external stylesheet.
				wp_enqueue_style( 'buddypress-share-admin-notice', BP_ACTIVITY_SHARE_PLUGIN_URL . 'admin/css' . $rtl_css . '/buddypress-share-admin-notice' . $css_extension, array(), BP_ACTIVITY_SHARE_PLUGIN_VERSION );
				?>
				<div class="notice updated buddypress-share-notice">
					<div class="buddypress-share-notice-inner">
						<div class="buddypress-share-notice-icon">
							<img src="<?php echo esc_url( BP_ACTIVITY_SHARE_PLUGIN_URL . 'admin/images/bp_social_share.png' ); ?>" alt="<?php esc_attr_e( 'BuddyPress Activity Social Share', 'buddypress-share' ); ?>" />
						</div>
						<div class="buddypress-share-notice-content">
							<h3><?php esc_html_e( 'Are you enjoying BuddyPress Activity Social Share?', 'buddypress-share' ); ?></h3>
							<p>
								<?php esc_html_e( 'We hope you\'re enjoying the plugin ! Could you please leave us a review ? ', 'buddypress-share' ); ?>
							</p>
						</div>
						<div class="buddypress-share-install-now">
							<a href="<?php echo esc_url( 'https : // wordpress.org/support/plugin/bp-activity-social-share/reviews/' ); ?>" class="button button-primary" target="_blank">
								<?php esc_html_e( 'Leave a Review', 'buddypress-share' ); ?>
							</a>
							<a href="<?php echo esc_url( $no_bug_url ); ?>" class="no-thanks">
								<?php esc_html_e( 'No thanks', 'buddypress-share' ); ?>
							</a>
						</div>
					</div>
				</div>
				<?php
			}
		}

		/**
		 * Set the plugin to no longer bug users if user asks not to be.
		 */
		public function set_no_bug() {
			// Bail out if nonce is not set, verification fails, or user doesn't have proper capabilities.
			if (
				! isset( $_GET['_wpnonce'] ) ||
				! wp_verify_nonce( sanitize_text_field( $_GET['_wpnonce'] ), 'buddypress-share-feedback-nonce' ) ||
				! current_user_can( 'manage_options' ) ||
				! isset( $_GET[ $this->nobug_option ] )
			) {
				return;
			}

			// Add site option if all checks pass.
			add_site_option( sanitize_key( $this->nobug_option ), true );
		}
	}
endif;

/*
* Instantiate the BP_Share_Feedback class.
*/
new BP_Share_Feedback(
	array(
		'slug'       => 'bp_social_share',
		'name'       => __( 'BuddyPress Activity Social Share', 'buddypress-share' ),
		'time_limit' => WEEK_IN_SECONDS,
	)
);
