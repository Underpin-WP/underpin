<?php
/**
 * Widgets
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Registries\Loader_Registry;
use WP_Error;
use WP_Widget;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Widgets
 * Registry for Cron Jobs
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */
class Widgets extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Abstracts\Widget';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
		//$this->add( 'key','namespaced_class' );
	}

	/**
	 * @inheritDoc
	 */
	public function add( $key, $value ) {
		$valid = parent::add( $key, $value );

		if ( true === $valid ) {
			add_action( 'widgets_init', function() use ( $value ) {
				register_widget( $value );
				underpin()->logger()->log(
					'notice',
					'widget_registered_successfully',
					'The widget ' . $value . ' Was successfully registered.'
				);
			} );
		}

		return $valid;
	}

	/**
	 * @param string $key
	 * @return WP_Widget|WP_Error Script Resulting WP_Widget class, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

}