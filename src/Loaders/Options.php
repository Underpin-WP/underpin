<?php
/**
 * Options
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Registries\Loader_Registry;
use Underpin\Factories\Option;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Options
 * Registry for Cron Jobs
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */
class Options extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Factories\Option';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
		$this->add( 'debug_mode_enabled', [
			'class' => 'Underpin\Factories\Option',
			'args'  => [
				'underpin_debug_mode_enabled',
				'Determines if Debug Mode is on',
				'Debug Mode',
				false,
			],
		] );
	}

	/**
	 * @param string $key
	 * @return Option|WP_Error Script Resulting block class, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

	/**
	 * Retrieves a single option value from an array of options.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key        The Options key to use.
	 * @param string $option_key The array key from the option.
	 * @return mixed|WP_Error The value if set, otherwise WP_Error.
	 */
	public function pluck( $key, $option_key ) {
		$option = $this->get( $key );

		if ( is_wp_error( $option ) ) {
			return $option;
		}

		return $option->pluck( $option_key );
	}
}