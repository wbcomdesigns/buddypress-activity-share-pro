<?php
/**
 * First-run onboarding (full-width, no sidebar).
 *
 * Shown once on a fresh install (activation redirects with onboarding=1).
 * Both CTAs call the bpas_complete_onboarding AJAX handler which sets the
 * bpas_onboarding_complete site option to '1', then land on Overview.
 * Existing installs never see this: the activator presets the flag to '1'
 * on upgrade.
 *
 * @var string $page_url admin.php?page=buddypress-share.
 *
 * @package Buddypress_Share
 * @since   2.3.0
 */

defined( 'ABSPATH' ) || exit;

$bpas_steps = array(
	array(
		'icon'  => 'dashicons-share-alt2',
		'title' => __( 'Pick your networks', 'buddypress-share' ),
		'desc'  => __( 'Choose which social networks members can share to.', 'buddypress-share' ),
		'tab'   => 'networks',
	),
	array(
		'icon'  => 'dashicons-art',
		'title' => __( 'Style the buttons', 'buddypress-share' ),
		'desc'  => __( 'Set the icon style and colors to match your site.', 'buddypress-share' ),
		'tab'   => 'display',
	),
	array(
		'icon'  => 'dashicons-admin-settings',
		'title' => __( 'Set restrictions', 'buddypress-share' ),
		'desc'  => __( 'Decide what can be reshared and how it appears.', 'buddypress-share' ),
		'tab'   => 'restrictions',
	),
);
?>
<div class="wrap bpas-admin">
	<div class="bpas-onboarding">
		<div class="bpas-onboarding__hero">
			<span class="dashicons dashicons-share" aria-hidden="true"></span>
			<h1 class="bpas-onboarding__title"><?php esc_html_e( 'Welcome to Activity Share', 'buddypress-share' ); ?></h1>
			<p class="bpas-onboarding__sub"><?php esc_html_e( 'Let members share activities to social networks and reshare within your community. Here are three quick steps to get started.', 'buddypress-share' ); ?></p>
		</div>

		<div class="bpas-onboarding__steps">
			<?php
			$bpas_n = 1;
			foreach ( $bpas_steps as $bpas_step ) :
				?>
				<a class="bpas-onboarding__step" href="<?php echo esc_url( $page_url . '&tab=' . $bpas_step['tab'] ); ?>" data-bpas-onboarding-go>
					<span class="bpas-onboarding__num"><?php echo esc_html( number_format_i18n( $bpas_n ) ); ?></span>
					<span class="bpas-onboarding__step-body">
						<span class="bpas-onboarding__step-title">
							<span class="dashicons <?php echo esc_attr( $bpas_step['icon'] ); ?>" aria-hidden="true"></span>
							<?php echo esc_html( $bpas_step['title'] ); ?>
						</span>
						<span class="bpas-onboarding__step-desc"><?php echo esc_html( $bpas_step['desc'] ); ?></span>
					</span>
				</a>
				<?php
				++$bpas_n;
			endforeach;
			?>
		</div>

		<div class="bpas-onboarding__actions">
			<button type="button" class="bpas-btn bpas-btn-primary" id="bpas-onboarding-start">
				<?php esc_html_e( 'Get started', 'buddypress-share' ); ?>
			</button>
			<button type="button" class="bpas-btn bpas-btn-secondary" id="bpas-onboarding-skip">
				<?php esc_html_e( 'Skip for now', 'buddypress-share' ); ?>
			</button>
		</div>
	</div>
</div>
