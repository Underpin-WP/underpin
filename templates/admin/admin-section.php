<?php
/**
 * Admin Section Template
 *
 * @author: Alex Standiford
 * @date  : 12/21/19
 */

use Underpin\Abstracts\Admin_Section;
use Underpin\Abstracts\Settings_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $template ) || ! $template instanceof Admin_Section ) {
	return;
}

if ( is_wp_error( $template->get_fields() ) ) {
	return;
}

?>
	<tr>
		<td colspan="2">
			<h3><?= $template->name ?></h3>
			<hr>
		</td>
	</tr>
<?php
foreach ( $template->get_fields() as $field ) {
	if ( $field instanceof Settings_Field ) {
		echo $field->place( true );
	}
}
?>