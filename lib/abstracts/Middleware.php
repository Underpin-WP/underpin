<?php
/**
 * A single instance of Middleware
 *
 * @since   1.3.0
 * @package Underpin\Abstracts
 */
namespace Underpin\Abstracts;


use Underpin\Factories\Loader_Registry_Item;
use Underpin\Traits\Feature_Extension;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Middleware
 *
 * @since   1.3.0
 * @package Underpin\Abstracts
 */
abstract class Middleware {

	use Feature_Extension;

	/**
	 * Loader Item
	 *
	 * @var Loader_Registry_Item The loader item
	 */
	protected $loader_item;

	/**
	 * The priority in which this middleware should be ran.
	 *
	 * @since 1.3.0
	 * @var int
	 */
	protected $priority = 10;

	/**
	 * Sets the middleware's loader.
	 *
	 * @since 1.3.0
	 *
	 * @param Loader_Registry_Item|string|array $loader_item The loader item to set
	 */
	public function set_loader( $loader_item ) {
		$this->loader_item = $loader_item;
	}

	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new \WP_Error( 'middleware_param_not_set', 'The middleware key ' . $key . ' could not be found.' );
		}
	}
}