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

if ( ! isset( $template ) || ! $template instanceof Underpin\Factories\Settings_Fields\Number ) {
	return;
}

$name = $template->get_param( 'name', '' );
?>

<input
	name="<?= $name ?>"
	type="number"
	id="<?= $template->get_id() ?>"

	<?php if ( $template->get_param( 'has_description' ) ): ?>
		aria-describedby="<?= $name ?>_description"
	<?php endif; ?>

	value="<?= $template->get_field_value(); ?>"
	class="<?= $template->get_param( 'class', 'regular-text' ) ?>"
	<?= $template->attributes( [
		'max',
		'min',
		'placeholder',
		'readonly',
		'step',
	] ) ?>
>
