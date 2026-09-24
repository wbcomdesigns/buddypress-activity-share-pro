<?php
/**
 * License tab (EDD SL SDK). The licence enables updates only - every feature works without it.
 *
 * @package BPAS_Pro
 * @var \EasyDigitalDownloads\Updater\Licensing\License|null $license SDK licence object.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="bpas-card">
	<header class="bpas-card__head">
		<h2 class="bpas-card__title"><?php esc_html_e( 'License', 'buddypress-activity-share-pro' ); ?></h2>
		<p class="bpas-card__desc"><?php esc_html_e( 'Activate your license key to get updates. All features work with or without it.', 'buddypress-activity-share-pro' ); ?></p>
	</header>
	<?php if ( ! $license ) : ?>
		<p><?php esc_html_e( 'Automatic updates are unavailable because the update library is missing from this copy of the plugin. Please download it again from your Wbcom Designs account.', 'buddypress-activity-share-pro' ); ?></p>
	<?php else : ?>
		<div class="bpas-pro-license">
			<label for="edd_sl_sdk[<?php echo esc_attr( (string) BPAS_PRO_EDD_ITEM_ID ); ?>]"><strong><?php esc_html_e( 'License key', 'buddypress-activity-share-pro' ); ?></strong></label>
			<div class="edd-sl-sdk__license-control">
				<input type="password" autocomplete="off" spellcheck="false" class="edd-sl-sdk__license--input regular-text"
					id="edd_sl_sdk[<?php echo esc_attr( (string) BPAS_PRO_EDD_ITEM_ID ); ?>]"
					name="<?php echo esc_attr( $license->get_key_option_name() ); ?>"
					value="<?php echo esc_attr( $license->get_license_key() ); ?>"
					data-item="<?php echo esc_attr( (string) BPAS_PRO_EDD_ITEM_ID ); ?>"
					data-key="<?php echo esc_attr( $license->get_key_option_name() ); ?>"
					data-slug="buddypress-activity-share-pro"
					placeholder="<?php esc_attr_e( 'Paste your license key', 'buddypress-activity-share-pro' ); ?>" />
				<?php $license->get_actions( true ); ?>
			</div>
			<?php $license->get_license_status_message(); ?>
			<p class="description">
				<?php
				printf(
					/* translators: %s: link to the customer's account. */
					esc_html__( 'Find your key in your %s.', 'buddypress-activity-share-pro' ),
					'<a href="https://wbcomdesigns.com/profile/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Wbcom Designs account', 'buddypress-activity-share-pro' ) . '</a>'
				);
				?>
			</p>
		</div>
	<?php endif; ?>
</section>
