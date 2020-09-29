<?php
/**
 * Renders widget fields
 *
 * @author: Alex Standiford
 * @date  : 2019-10-28
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $template ) || ! $template instanceof \Underpin\Abstracts\Widget ) {
	return;
}

?>
<div id="<?= $template->get_param( 'id', '' ); ?>" class="underpin_widget">
	<p><?= $template->get_param( 'description', '' ); ?></p>
	<?php foreach ( $template->get_param( 'fields', [] ) as $field ): ?>
		<?= $field instanceof \Underpin\Abstracts\Settings_Field ? $field->place() : ''; ?>
	<?php endforeach; ?>
</div>
