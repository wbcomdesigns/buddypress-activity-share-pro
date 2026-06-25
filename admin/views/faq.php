<?php
/**
 * FAQ tab body (static, plain-language).
 *
 * Moved from Buddypress_Share_Admin::bp_share_faq_page() during the 2.3.0
 * admin UX migration.
 *
 * @package Buddypress_Share
 * @since   2.3.0
 */

defined( 'ABSPATH' ) || exit;

$bpas_faqs = array(
	array(
		'q' => __( 'How do I turn on social sharing?', 'buddypress-share' ),
		'a' => __( 'Sharing is on by default. Use the Social Networks tab to turn it on or off, and make sure at least one network is active.', 'buddypress-share' ),
	),
	array(
		'q' => __( 'Can visitors share without logging in?', 'buddypress-share' ),
		'a' => __( 'Yes. Turn on Guest sharing in the Social Networks tab so logged-out visitors can share public activity.', 'buddypress-share' ),
	),
	array(
		'q' => __( 'How do I change how the buttons look?', 'buddypress-share' ),
		'a' => __( 'Open the Display tab to pick an icon style (Circle, Rectangle, Black & white, or Bar) and set the colors.', 'buddypress-share' ),
	),
	array(
		'q' => __( 'Which networks are supported?', 'buddypress-share' ),
		'a' => __( 'Facebook, X (Twitter), LinkedIn, Pinterest, Reddit, WordPress, Pocket, Telegram, Bluesky, WhatsApp, Email, and Copy Link. Turn each one on or off individually.', 'buddypress-share' ),
	),
	array(
		'q' => __( 'Can I turn off sharing for certain activity types?', 'buddypress-share' ),
		'a' => __( 'Yes. In the Restrictions tab you can turn off resharing for blog posts, member profiles, and groups.', 'buddypress-share' ),
	),
	array(
		'q' => __( 'How does resharing work?', 'buddypress-share' ),
		'a' => __( 'When a member reshares an activity, you can show just the original activity or the full activity with nested content. Set this in the Restrictions tab.', 'buddypress-share' ),
	),
	array(
		'q' => __( 'Do sharing links open in a popup?', 'buddypress-share' ),
		'a' => __( 'By default, yes — sharing links open in a small popup. You can turn this off in the Social Networks tab.', 'buddypress-share' ),
	),
	array(
		'q' => __( 'Does this work with BuddyBoss Platform?', 'buddypress-share' ),
		'a' => __( 'Yes. The plugin works with both BuddyPress and BuddyBoss Platform automatically.', 'buddypress-share' ),
	),
);
?>
<div class="bp-share-faq-section">
	<h2 class="bpas-section-title"><?php esc_html_e( 'Frequently asked questions', 'buddypress-share' ); ?></h2>

	<?php foreach ( $bpas_faqs as $bpas_faq ) : ?>
		<div class="faq-item">
			<h3><?php echo esc_html( $bpas_faq['q'] ); ?></h3>
			<p><?php echo esc_html( $bpas_faq['a'] ); ?></p>
		</div>
	<?php endforeach; ?>

	<div class="faq-item">
		<h3><?php esc_html_e( 'Where can I get support?', 'buddypress-share' ); ?></h3>
		<p>
			<?php
			printf(
				/* translators: 1: opening support link tag, 2: closing link tag, 3: opening docs link tag, 4: closing link tag. */
				esc_html__( 'For premium support, visit our %1$ssupport portal%2$s. You can also read the %3$sdocumentation%4$s for detailed guides.', 'buddypress-share' ),
				'<a href="https://wbcomdesigns.com/support/" target="_blank" rel="noopener noreferrer">',
				'</a>',
				'<a href="https://docs.wbcomdesigns.com/buddypress-activity-share-pro/" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);
			?>
		</p>
	</div>
</div>
