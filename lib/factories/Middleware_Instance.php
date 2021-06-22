<?php
/**
 * A single instance of Middleware
 *
 * @since   1.3.0
 * @package Underpin\Factories
 */
namespace Underpin\Factories;


use Underpin\Abstracts\Middleware;
use Underpin\Traits\Instance_Setter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Middleware
 *
 * @since   1.3.0
 * @package Underpin\Factories
 */
class Middleware_Instance extends Middleware {

	use Instance_Setter;

	/**
	 * Callback to fire for this middleware.
	 *
	 * @since 1.3.0
	 *
	 * @var callable|callable-string The action
	 */
	protected $do_actions_callback;

	/**
	 * Middleware_Instance constructor.
	 *
	 * @since 1.3.0
	 *
	 * @param array $args Values to set in this class.
	 */
	public function __construct( array $args ) {
		$this->set_values( $args );
	}

	/**
	 * @inheritdoc
	 */
	public function do_actions() {
		return $this->set_callable( $this->do_actions_callback, $this->loader_item );
	}

}