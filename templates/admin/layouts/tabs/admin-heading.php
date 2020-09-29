<?php
/**
 * Admin Heading Template
 * Default template to render an admin page.
 *
 * @since 1.0.0
 */


use Underpin\Abstracts\Admin_Page;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $template ) || ! $template instanceof Admin_Page ) {
	return;
}

$current = $template->get_param( 'section', '' );
?>
<nav class="nav-tab-wrapper">
	<?php foreach ( $template->get_param( 'sections' ) as $id => $section ): ?>
		<a class="nav-tab<?= $current === $id ? ' nav-tab-active' : '' ?>" href="<?= $template->get_section_url( $id ) ?>"><?= $template->section( $id )->name; ?></a>
	<?php endforeach; ?>
</nav>