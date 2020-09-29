<?php
/**
 * Erasers
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Eraser;
use Underpin\Abstracts\Registries\Loader_Registry;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Erasers
 * Registry for Erasers
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */

class Erasers extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Abstracts\Eraser';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
//		$this->add( 'key','namespaced_class' );
	}

	/**
	 * @param string $key
	 * @return Eraser|WP_Error Script Resulting shortcode class, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}
}