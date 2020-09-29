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

if ( ! isset( $template ) || ! $template instanceof Underpin\Factories\Settings_Fields\Checkbox ) {
	return;
}

$name = $template->get_param( 'name', '' );
?>

<input
	<?php checked(  $template->get_field_value() ); ?>

		name="<?= $name ?>"
		type="checkbox"
		id="<?= $template->get_id() ?>"
		class="<?= $template->get_param( 'class', 'regular-text' ) ?>"

	<?php if ( $template->get_param( 'has_description' ) ): ?>
		aria-describedby="<?= $name ?>_description"
	<?php endif; ?>
	<?= $template->attributes( [ 'readonly' ] ) ?>
>
