<?php
/**
 * Text Field Template
 *
 * @author: Alex Standiford
 * @date  : 12/21/19
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $template ) || ! $template instanceof Underpin\Factories\Settings_Fields\Radio ) {
	return;
}

$name        = $template->get_param( 'name', '' );
$description = $template->get_param( 'description', '' );

?>

<input
	<?php checked( $template->get_field_value() ); ?>

	name="<?= $name ?>"
	type="radio"
	id="<?= $template->get_id() ?>"
	value="<?= $template->get_param('value') ?>"
	class="<?= $template->get_param( 'class', 'regular-text' ) ?>"

	<?php if ( $template->get_param( 'has_description' ) ): ?>
		aria-describedby="<?= $name ?>_description"
	<?php endif; ?>
	>
</input>
<label for="<?= $template->get_id() ?>">
	<?= $template->get_param('label') ?>
</label>