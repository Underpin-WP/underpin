<?php
/**
 *
 *
 * @since
 * @package
 */


namespace Underpin\Utilities;


use Underpin\Abstracts\Admin_Bar_Menu;
use Underpin\Traits\Underpin_Templates;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Debug_Bar
 *
 *
 * @since
 * @package
 */
class Debug_Bar extends Admin_Bar_Menu {
	use Underpin_Templates;

	/**
	 * @inheritDoc
	 */
	public $description = 'This registers the actual debug bar button to the wp admin bar.';

	public $name = "Debug Bar";

	public function __construct() {
		parent::__construct( 'underpin_debugger', [
			'parent' => 'top-secondary',
			'title'  => 'Underpin Events',
			'href'   => '#',
			'meta'   => [
				'onclick' => '',
			],
		] );
	}

	public function do_actions() {
		parent::do_actions();
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ], 11 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 11 );
		add_action( 'shutdown', [ $this, 'render_callback' ] );
	}

	/**
	 * Loads in the debug bar script
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {
		if ( ! underpin()->is_debug_mode_enabled() ) {
			return;
		}

		underpin()->scripts()->enqueue( 'debug' );
		underpin()->styles()->enqueue( 'debug' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_templates() {
		return [
			'wrapper'         => [
				'override_visibility' => 'private',
			],
			'section'         => [
				'override_visibility' => 'private',
			],
			'console'         => [
				'override_visibility' => 'private',
			],
			'tabs'    => [
				'override_visibility' => 'private',
			],
			'section-menu'    => [
				'override_visibility' => 'private',
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function get_template_group() {
		return 'debug-bar';
	}

	/**
	 * Renders the actual debug bar.
	 */
	public function render_callback() {
		if ( ! underpin()->is_debug_mode_enabled() ) {
			return;
		}

		echo $this->get_template( 'wrapper', [
			'sections' => (array) underpin()->debug_bar_sections(),
		] );
	}
}