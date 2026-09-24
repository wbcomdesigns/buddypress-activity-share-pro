<?php
/**
 * Features tab: one toggle per Pro feature.
 *
 * @package BPAS_Pro
 * @var array $features Current feature flags.
 * @var array $rows     key => [label, description].
 */

defined( 'ABSPATH' ) || exit;
?>
<form class="bpas-admin__form" data-bpas-pro-form="features">
	<section class="bpas-card">
		<header class="bpas-card__head">
			<h2 class="bpas-card__title"><?php esc_html_e( 'Features', 'buddypress-activity-share-pro' ); ?></h2>
			<p class="bpas-card__desc"><?php esc_html_e( 'Turn Activity Share Pro features on or off.', 'buddypress-activity-share-pro' ); ?></p>
		</header>
		<div class="bpas-fields">
			<?php foreach ( $rows as $bpas_pro_key => $bpas_pro_row ) : ?>
				<label class="bpas-switch">
					<input type="checkbox" name="<?php echo esc_attr( $bpas_pro_key ); ?>" value="1" <?php checked( ! empty( $features[ $bpas_pro_key ] ) ); ?> />
					<span class="bpas-switch__text">
						<strong><?php echo esc_html( $bpas_pro_row[0] ); ?></strong>
						<span><?php echo esc_html( $bpas_pro_row[1] ); ?></span>
					</span>
				</label>
			<?php endforeach; ?>
		</div>
		<p class="bpas-card__note"><?php esc_html_e( 'Share rewards turn on by themselves when WB Gamification, GamiPress or myCRED is active; set the points in that plugin.', 'buddypress-activity-share-pro' ); ?></p>
	</section>
	<p class="bpas-admin__actions"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save changes', 'buddypress-activity-share-pro' ); ?></button></p>
</form>
