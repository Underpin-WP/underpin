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

if ( ! isset( $template ) || ! $template instanceof Underpin\Abstracts\Settings_Field ) {
	return;
}

$name          = $template->get_param( 'name', '' );
$description   = $template->get_param( 'description', '' );
$wrapper_class = $template->get_field_param( 'wrapper_class' );

if ( is_wp_error( $wrapper_class ) ) {
	$wrapper_class = false;
}

$has_description = ! empty( $description );
$field_params    = array_merge( [ 'has_description' => $has_description ], $template->get_params() );
?>

<tr<?= false !== $wrapper_class ? ' class="' . $wrapper_class . '"' : '' ?>>
	<th scope="row">

		<label for="<?= $name ?>">
			<?= $template->get_param( 'label', '' ) ?>
		</label>

	</th>

	<td>
		<?= $template->get_template( 'input', $field_params ) ?>
		<?php if ( $has_description ): ?>
			<p class="description" id="<?= $name ?>_description">
				<?= $description ?>
			</p>
		<?php endif; ?>

	</td>
</tr>
