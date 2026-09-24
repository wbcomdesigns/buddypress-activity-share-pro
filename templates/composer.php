<?php
/**
 * Repost / send / log-in dialog.
 *
 * Override at {theme}/buddypress-activity-share-pro/composer.php is not supported on purpose:
 * the JS depends on these data attributes.
 *
 * @package BPAS_Pro
 * @var array $view logged_in, login_url, register_url, groups_on.
 */

defined( 'ABSPATH' ) || exit;
?>
<dialog class="bpas-pro-dialog" data-bpas-dialog aria-labelledby="bpas-pro-dialog-title">
	<form method="dialog" class="bpas-pro-dialog__box" data-bpas-dialog-form novalidate>
		<header class="bpas-pro-dialog__head">
			<h2 id="bpas-pro-dialog-title" class="bpas-pro-dialog__title" data-bpas-dialog-title><?php echo esc_html( _x( 'Repost', 'verb', 'buddypress-activity-share-pro' ) ); ?></h2>
			<button type="button" class="bpas-pro-dialog__close" data-bpas-dialog-close>
				<?php echo bpas_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG. ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Close', 'buddypress-activity-share-pro' ); ?></span>
			</button>
		</header>

		<?php if ( ! $view['logged_in'] ) : ?>
			<div class="bpas-pro-dialog__body" data-bpas-mode="login">
				<p><?php esc_html_e( 'Log in or join the community to repost and send posts to friends.', 'buddypress-activity-share-pro' ); ?></p>
				<p class="bpas-pro-dialog__actions">
					<a class="bpas-pro-btn bpas-pro-btn--primary" href="<?php echo esc_url( $view['login_url'] ); ?>"><?php esc_html_e( 'Log in', 'buddypress-activity-share-pro' ); ?></a>
					<?php if ( $view['register_url'] ) : ?>
						<a class="bpas-pro-btn" href="<?php echo esc_url( $view['register_url'] ); ?>"><?php echo esc_html( _x( 'Join', 'create an account', 'buddypress-activity-share-pro' ) ); ?></a>
					<?php endif; ?>
				</p>
			</div>
		<?php else : ?>
			<div class="bpas-pro-dialog__body">
				<?php if ( $view['groups_on'] ) : // No groups = only one place to post, so no question to ask. ?>
					<fieldset class="bpas-pro-field" data-bpas-field="destination">
						<legend><?php esc_html_e( 'Where to post', 'buddypress-activity-share-pro' ); ?></legend>
						<label class="bpas-pro-choice" data-bpas-dest="profile"><input type="radio" name="destination" value="profile" checked /> <?php esc_html_e( 'My profile', 'buddypress-activity-share-pro' ); ?></label>
						<label class="bpas-pro-choice" data-bpas-dest="group"><input type="radio" name="destination" value="group" /> <?php esc_html_e( 'A group', 'buddypress-activity-share-pro' ); ?></label>
					</fieldset>
				<?php endif; ?>

				<div class="bpas-pro-field" data-bpas-field="group" hidden>
					<label for="bpas-pro-group"><?php esc_html_e( 'Group', 'buddypress-activity-share-pro' ); ?></label>
					<input type="search" id="bpas-pro-group-search" class="bpas-pro-input" data-bpas-group-search placeholder="<?php esc_attr_e( 'Search your groups', 'buddypress-activity-share-pro' ); ?>" hidden />
					<select id="bpas-pro-group" class="bpas-pro-input" name="group_id" data-bpas-group-select></select>
					<p class="bpas-pro-field__empty" data-bpas-group-empty hidden><?php esc_html_e( 'You have not joined any groups yet.', 'buddypress-activity-share-pro' ); ?></p>
				</div>

				<div class="bpas-pro-field" data-bpas-field="friend" hidden>
					<label for="bpas-pro-friend"><?php esc_html_e( 'Friend', 'buddypress-activity-share-pro' ); ?></label>
					<input type="search" id="bpas-pro-friend" class="bpas-pro-input" data-bpas-friend-search autocomplete="off" placeholder="<?php esc_attr_e( 'Type a name', 'buddypress-activity-share-pro' ); ?>" aria-describedby="bpas-pro-friend-hint" />
					<p id="bpas-pro-friend-hint" class="bpas-pro-field__hint"><?php esc_html_e( 'Only your friends are shown.', 'buddypress-activity-share-pro' ); ?></p>
					<ul class="bpas-pro-options" role="listbox" data-bpas-friend-list aria-label="<?php esc_attr_e( 'Friends', 'buddypress-activity-share-pro' ); ?>"></ul>
				</div>

				<div class="bpas-pro-field">
					<label for="bpas-pro-comment" data-bpas-comment-label><?php esc_html_e( 'Add a comment (optional)', 'buddypress-activity-share-pro' ); ?></label>
					<textarea id="bpas-pro-comment" class="bpas-pro-input bp-suggestions" name="comment" rows="3" maxlength="5000"></textarea>
				</div>

				<p class="bpas-pro-dialog__status" role="status" aria-live="polite" data-bpas-dialog-status></p>

				<p class="bpas-pro-dialog__actions">
					<button type="button" class="bpas-pro-btn" data-bpas-dialog-close><?php esc_html_e( 'Cancel', 'buddypress-activity-share-pro' ); ?></button>
					<button type="submit" class="bpas-pro-btn bpas-pro-btn--primary" data-bpas-dialog-submit><?php echo esc_html( _x( 'Repost', 'verb', 'buddypress-activity-share-pro' ) ); ?></button>
				</p>
			</div>
		<?php endif; ?>
	</form>
</dialog>
<div class="bpas-pro-toast" role="status" aria-live="polite" data-bpas-toast hidden></div>
