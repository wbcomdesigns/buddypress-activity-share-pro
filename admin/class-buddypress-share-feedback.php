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
			if ( ! get_site_option( $this->nobug_option ) || false === get_site_option( $this->nobug_option ) ) {
				add_site_option( $this->date_option, time() );

				// Retrieve the activation date.
				$install_date = get_site_option( $this->date_option );

				// If difference between install date and now is greater than time limit, then display notice.
				if ( ( time() - $install_date ) > $this->time_limit ) {
					add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
				}
			}
		}

		/**
		 * Display the admin notice.
		 */
		public function display_admin_notice() {
			$screen = get_current_screen();

			if ( isset( $screen->base ) && 'plugins' === $screen->base ) {
				$no_bug_url = wp_nonce_url( admin_url( '?' . $this->nobug_option . '=true' ), 'buddypress-share-feedback-nounce' );
				$time       = $this->seconds_to_words( time() - get_site_option( $this->date_option ) );
				?>

<style>
.notice.buddypress-share-notice {
	border-left-color: #008ec2 !important;
	padding: 20px;
}

.rtl .notice.buddypress-share-notice {
	border-right-color: #008ec2 !important;
}

.notice.notice.buddypress-share-notice .buddypress-share-notice-inner {
	display: table;
	width: 100%;
}

.notice.buddypress-share-notice .buddypress-share-notice-inner .buddypress-share-notice-icon,
.notice.buddypress-share-notice .buddypress-share-notice-inner .buddypress-share-notice-content,
.notice.buddypress-share-notice .buddypress-share-notice-inner .buddypress-share-install-now {
	display: table-cell;
	vertical-align: middle;
}

.notice.buddypress-share-notice .buddypress-share-notice-icon {
	color: #509ed2;
	font-size: 50px;
	width: 60px;
}

.notice.buddypress-share-notice .buddypress-share-notice-icon img {
	width: 64px;
}

.notice.buddypress-share-notice .buddypress-share-notice-content {
	padding: 0 40px 0 20px;
}

.notice.buddypress-share-notice p {
	padding: 0;
	margin: 0;
}

.notice.buddypress-share-notice h3 {
	margin: 0 0 5px;
}

.notice.buddypress-share-notice .buddypress-share-install-now {
	text-align: center;
}

.notice.buddypress-share-notice .buddypress-share-install-now .buddypress-share-install-button {
	padding: 6px 50px;
	height: auto;
	line-height: 20px;
}

.notice.buddypress-share-notice a.no-thanks {
	display: block;
	margin-top: 10px;
	color: #72777c;
	text-decoration: none;
}

.notice.buddypress-share-notice a.no-thanks:hover {
	color: #444;
}

@media (max-width: 767px) {

	.notice.notice.buddypress-share-notice .buddypress-share-notice-inner {
		display: block;
	}

	.notice.buddypress-share-notice {
		padding: 20px !important;
	}

	.notice.buddypress-share-noticee .buddypress-share-notice-inner {
		display: block;
	}

	.notice.buddypress-share-notice .buddypress-share-notice-inner .buddypress-share-notice-content {
		display: block;
		padding: 0;
	}

	.notice.buddypress-share-notice .buddypress-share-notice-inner .buddypress-share-notice-icon {
		display: none;
	}

	.notice.buddypress-share-notice .buddypress-share-notice-inner .buddypress-share-install-now {
		margin-top: 20px;
		display: block;
		text-align: left;
	}

	.notice.buddypress-share-notice .buddypress-share-notice-inner .no-thanks {
		display: inline-block;
		margin-left: 15px;
	}
}
</style>
			<div class="notice updated buddypress-share-notice">
				<div class="buddypress-share-notice-inner">
					<div class="buddypress-share-notice-icon">
						<img src="<?php echo esc_url( BP_ACTIVITY_SHARE_PLUGIN_URL . 'admin/images/bp_social_share.png' ); ?>" alt="<?php echo esc_attr__( 'BuddyPress Activity Social Share', 'buddypress-share' ); ?>" />
					</div>
					<div class="buddypress-share-notice-content">
						<h3><?php echo esc_html__( 'Are you enjoying BuddyPress Activity Social Share?', 'buddypress-share' ); ?></h3>
						<p>
							<?php /* translators: 1. Name, 2. Time */ ?>
							<?php printf( esc_html__( 'We hope you\'re enjoying %1$s! Could you please do us a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'buddypress-share' ), esc_html( $this->name ) ); ?>
						</p>
					</div>
					<div class="buddypress-share-install-now">
						<?php printf( '<a href="%1$s" class="button button-primary buddypress-share-install-button" target="_blank">%2$s</a>', esc_url( 'https://wordpress.org/support/plugin/bp-activity-social-share/reviews/' ), esc_html__( 'Leave a Review', 'buddypress-share' ) ); ?>
						<a href="<?php echo esc_url( $no_bug_url ); ?>" class="no-thanks"><?php echo esc_html__( 'No thanks / I already have', 'buddypress-share' ); ?></a>
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

			// Bail out if not on correct page.
			if ( ! isset( $_GET['_wpnonce'] ) || ( ! wp_verify_nonce( $_GET['_wpnonce'], 'buddypress-share-feedback-nounce' ) || ! is_admin() || ! isset( $_GET[ $this->nobug_option ] ) || ! current_user_can( 'manage_options' ) ) ) {
				return;
			}

			add_site_option( $this->nobug_option, true );
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
