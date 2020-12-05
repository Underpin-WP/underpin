<?php
/**
 * Creates a single admin notice
 *
 * @since   1.0.0
 * @package Underpin\Abstracts\
 */


namespace Underpin\Abstracts;


use Flare_WP\Traits\Flare_WP_Templates;
use Underpin\Traits\Feature_Extension;
use Underpin\Traits\Underpin_Templates;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Notice
 * Class Notice
 *
 * @since   1.0.0
 * @package Underpin\Abstracts\
 */
abstract class Admin_Notice {
	use Feature_Extension;
	use Underpin_Templates;

	public $type = 'info';

	public $is_dismissible = false;

	public $wrapper_classes = [];

	public $id;

	public $message = '';

	/**
	 * Returns true when this admin notice should display.
	 *
	 * @since 1.0.0
	 *
	 * @return true|\WP_Error True if it should display, otherwise WP_Error.
	 */
	abstract public function should_display();

	/**
	 * @inheritDoc
	 */
	public function do_actions() {
		add_action( 'admin_notices', [ $this, 'render_callback' ] );
	}

	public function render_callback() {

		$should_display = $this->should_display();

		if ( is_wp_error( $should_display ) ) {
			underpin()->logger()->log_as_error(
				'notice',
				'underpin_notice_did_not_display',
				'A notice did not display.',
				[ 'reason' => $should_display ]
			);

			return;
		}

		$notice = $this->render_notice();

		if ( is_wp_error( $notice ) ) {
			underpin()->logger()->log_wp_error( 'warning', $notice );

			return;
		}

		echo $notice;
	}

	/**
	 * Retrieves the notice.
	 *
	 * @since 1.0.0
	 *
	 * @return string|\WP_Error
	 */
	public function render_notice() {
		return $this->get_template( 'notice', [
			'id'      => false === $this->id ? false : $this->id,
			'classes' => implode( ' ', $this->get_classes() ),
			'message' => $this->message,
		] );
	}

	/**
	 * Retrieves a list of classes for this admin notice.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_classes() {

		$type = in_array( $this->type, [ 'error', 'warning', 'success', 'info' ] ) ? $this->type : 'info';

		$classes = [
			'notice',
			'notice-' . $type,
		];

		if ( true === $this->is_dismissible ) {
			$classes[] = 'is-dismissible';
		}

		if ( is_array( $this->wrapper_classes ) ) {
			$classes = array_merge( $classes, $this->wrapper_classes );
		}

		return $classes;
	}

	/**
	 * @inheritDoc
	 */
	public function get_templates() {
		return [
			'notice' => [
				'override_visibility' => 'private',
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function get_template_group() {
		return 'admin-notice';
	}
}