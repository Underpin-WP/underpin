<?php
/**
 * Debug bar wrapper template.
 *
 * @since   1.0.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $template ) || ! $template instanceof Underpin\Utilities\Debug_Bar ) {
	return;
}
$sections = $template->get_param( 'sections', [] );

?>
<div id="debug-bar-wrap">
	<span class="debug-bar-close">X</span>
	<?= $template->get_template( 'section-menu', [ 'sections' => $sections ] ) ?>

	<?php foreach ( $sections as $id => $section ): ?>
		<?= $template->get_template( 'section', ['active' => $id === 0, 'section' => $section ] ) ?>
	<?php endforeach; ?>
</div>