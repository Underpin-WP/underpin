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

$name            = $template->get_param( 'name', '' );
$description     = $template->get_param( 'description', '' );
$has_description = ! empty( $description );
$field_params    = array_merge( [ 'has_description' => $has_description ], $template->get_params() );
?>

<tr>
	<th scope="row">
		<p>
			<?= $description ?>
		</p>
	</th>
	<td>
		<?php foreach ( $template->get_param( 'choices', [] ) as $value => $params ) : ?>
			<?= $template->get_template( 'input', array_merge( $field_params, $params ) ) ?>
		<?php endforeach; ?>
	</td>
</tr>
