<?php
/**
 * Exporters
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Exporter;
use Underpin\Abstracts\Registries\Loader_Registry;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Exporters
 * Registry for Exporters
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */

class Exporters extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Abstracts\Exporter';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
//		$this->add( 'key','namespaced_class' );
	}

	/**
	 * @param string $key
	 * @return Exporter|WP_Error Script Resulting shortcode class, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}
}