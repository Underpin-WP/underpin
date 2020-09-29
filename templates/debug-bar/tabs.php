<?php
/**
 * Debug bar event tab listing
 *
 * @since   1.0.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $template ) || ! $template instanceof Underpin\Utilities\Debug_Bar ) {
	return;
}

// Bail early if we dont have any events to display.
if ( empty( $template->get_param( 'items' ) ) ) {
	return;
}

$item_keys = array_keys( $template->get_param( 'items', [] ) );

?>
<nav id="underpin-debug-bar-tabs" class="nav-tab-wrapper">
	<?php foreach ( $template->get_param( 'items', [] ) as $key => $item ): ?>
		<?php if ( ! empty( $item ) ): ?>
			<?php
			if ( is_array( $item ) ) {
				$item_count = count( $item );
				$tab        = "$key ($item_count)";
			} else {
				$tab = $key;
			}
			?>
			<a class="nav-tab<?= $item_keys[0] === $key ? ' nav-tab-active' : '' ?>" href="#" data-event="<?= $key ?>">
				<?= $tab ?>
			</a>
		<?php endif; ?>
	<?php endforeach; ?>
</nav>