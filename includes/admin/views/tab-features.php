<?php
/**
 * Features tab: one toggle per Pro feature.
 *
 * @package BPAS_Pro
 * @var array  $features  Current feature flags.
 * @var array  $rows      key => [label, description].
 * @var string $send_note Shown when Send to a friend is unavailable.
 * @var array  $rewards_by Names of the active points plugins (WB Gamification, GamiPress, myCRED).
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
			<div class="bpas-switch bpas-switch--status">
				<span class="dashicons <?php echo $rewards_by ? 'dashicons-yes-alt bpas-switch__state--on' : 'dashicons-minus'; ?>" aria-hidden="true"></span>
				<span class="bpas-switch__text">
					<strong><?php esc_html_e( 'Share rewards', 'buddypress-activity-share-pro' ); ?></strong>
					<span>
						<?php
						echo esc_html(
							$rewards_by
								/* translators: %s: points plugin names, for example "WB Gamification" or "GamiPress, myCRED". */
								? sprintf( __( 'On - points set in %s', 'buddypress-activity-share-pro' ), implode( ', ', $rewards_by ) )
								: __( 'Off - activate a points plugin (WB Gamification, GamiPress or myCRED).', 'buddypress-activity-share-pro' )
						);
						?>
					</span>
				</span>
			</div>
		</div>
		<?php if ( $send_note ) : ?>
			<p class="bpas-card__note"><?php echo esc_html( $send_note ); ?></p>
		<?php endif; ?>
		<p class="bpas-card__note"><?php esc_html_e( 'Share rewards turn on by themselves when WB Gamification, GamiPress or myCRED is active; set the points in that plugin.', 'buddypress-activity-share-pro' ); ?></p>
	</section>
	<p class="bpas-admin__actions"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save changes', 'buddypress-activity-share-pro' ); ?></button></p>
</form>
