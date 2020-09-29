<?php
/**
 * Admin Page Template
 *
 * @author: Alex Standiford
 * @date  : 12/21/19
 */


use Underpin\Abstracts\Admin_Page;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $template ) || ! $template instanceof Admin_Page ) {
	return;
}

$sections = $template->get_param( 'sections', [] );
$section  = $template->get_param( 'section', '' );

if ( count( $sections ) > 1 ) {
	echo $template->get_template( 'admin-heading', [
		'section'   => $section,
		'sections'  => $sections,
		'menu_slug' => $template->get_param( 'menu_slug' ),
	] );
}

?>
<?php if ( ! empty( $section ) && isset( $sections[ $section ] ) ): ?>

	<form method="post" id="runner-dispatch">
		<h2><?= $template->get_param( 'title', '' ) ?></h2>
		<p style="max-width:700px;"><?= $template->get_param( 'description', '' ) ?></p>
		<table class="form-table">
			<tbody>

			<?= $template->section( $section )->get_template( 'admin-section' ); ?>

			</tbody>
		</table>
		<?php wp_nonce_field( $template->get_param( 'nonce_action', '' ), 'underpin_nonce' ); ?>
		<?php submit_button(); ?>
	</form>

<?php endif; ?>