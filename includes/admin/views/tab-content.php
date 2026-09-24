<?php
/**
 * Content types tab: which post types get share buttons, and where.
 *
 * @package BPAS_Pro
 * @var array $selected post_type => placement.
 * @var array $types    Public post type objects.
 */

defined( 'ABSPATH' ) || exit;

$bpas_pro_placements = array(
	'below'    => __( 'Below the content', 'buddypress-activity-share-pro' ),
	'above'    => __( 'Above the content', 'buddypress-activity-share-pro' ),
	'floating' => __( 'Floating button', 'buddypress-activity-share-pro' ),
);
?>
<form class="bpas-admin__form" data-bpas-pro-form="content">
	<section class="bpas-card">
		<header class="bpas-card__head">
			<h2 class="bpas-card__title"><?php esc_html_e( 'Content types', 'buddypress-activity-share-pro' ); ?></h2>
			<p class="bpas-card__desc"><?php esc_html_e( 'Add the share menu to single posts, pages, products, courses and other content. You can also place it anywhere with the Share buttons block or the [bpas_share] shortcode.', 'buddypress-activity-share-pro' ); ?></p>
		</header>
		<ul class="bpas-pro-types">
			<?php foreach ( $types as $bpas_pro_type ) : ?>
				<?php $bpas_pro_on = isset( $selected[ $bpas_pro_type->name ] ); ?>
				<li class="bpas-pro-types__row">
					<label class="bpas-pro-types__label">
						<input type="checkbox" name="type_<?php echo esc_attr( $bpas_pro_type->name ); ?>" value="<?php echo esc_attr( $bpas_pro_type->name ); ?>" data-bpas-type <?php checked( $bpas_pro_on ); ?> />
						<?php echo esc_html( $bpas_pro_type->labels->name ); ?>
					</label>
					<label class="screen-reader-text" for="bpas-place-<?php echo esc_attr( $bpas_pro_type->name ); ?>">
						<?php
						/* translators: %s: post type name. */
						echo esc_html( sprintf( __( 'Placement for %s', 'buddypress-activity-share-pro' ), $bpas_pro_type->labels->name ) );
						?>
					</label>
					<select id="bpas-place-<?php echo esc_attr( $bpas_pro_type->name ); ?>" data-bpas-placement="<?php echo esc_attr( $bpas_pro_type->name ); ?>">
						<?php foreach ( $bpas_pro_placements as $bpas_pro_value => $bpas_pro_label ) : ?>
							<option value="<?php echo esc_attr( $bpas_pro_value ); ?>" <?php selected( $selected[ $bpas_pro_type->name ] ?? 'below', $bpas_pro_value ); ?>><?php echo esc_html( $bpas_pro_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
	<p class="bpas-admin__actions"><button type="submit" class="button button-primary"><?php esc_html_e( 'Save changes', 'buddypress-activity-share-pro' ); ?></button></p>
</form>
