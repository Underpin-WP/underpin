<?php
/**
 * Renders Admin Notices
 *
 * @since   1.1.0
 * @package Underpin\Templates
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $template ) || ! $template instanceof Underpin\Abstracts\Admin_Notice ) {
	return;
}


?>
<div class="<?= $template->get_param('classes','') ?>" id="<?= $template->get_param('id') ?>" >
	<p class="message"><?= $template->get_param( 'message', '' ); ?></p>
</div>