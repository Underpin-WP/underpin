<?php
/**
 * $FILE_DESCRIPTION
 *
 * @since   $VERSION
 * @package $PACKAGE
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $template ) || ! $template instanceof Underpin\Abstracts\Batch_Task ) {
	return;
}

?>
<div class="notice notice-info batch-notice" data-batch-id="<?= $template->get_param( 'batch_id', '' ); ?>">
	<p class="message"><?= $template->get_param( 'message', '' ); ?></p>
	<div class="progress-bar">
		<div class="progress"></div>
	</div>
	<p class="status-wrap">
		<button class="button-primary"><?= $template->get_param( 'button_text', '' ); ?></button>
		<span class="status"></span>
	</p>
</div>
