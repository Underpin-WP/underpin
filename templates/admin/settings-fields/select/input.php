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

if ( ! isset( $template ) || ! $template instanceof Underpin\Factories\Settings_Fields\Select ) {
	return;
}

$name        = $template->get_param( 'name', '' );
$description = $template->get_param( 'description', '' );
?>

<select
		name="<?= $name ?>"
		id="<?= $template->get_id() ?>"

	<?php if ( $template->get_param( 'has_template', false ) ): ?>
		aria-describedby="<?= $name ?>_description"
	<?php endif; ?>

		class="<?= $template->get_param( 'class', 'regular-text' ) ?>"
		<?= $template->attributes([
			'autocomplete',
			'autofocus',
			'disabled',
			'form',
			'multiple',
			'name',
			'required',
			'size'
		]); ?>
>

	<?php foreach ( $template->get_param( 'choices', [] ) as $value => $text ) : ?>
		<option
						value="<?= $value ?>"
			<?php selected( $value, $template->get_field_value() ); ?>>
			<?= $text ?>
		</option>
	<?php endforeach; ?>

</select>