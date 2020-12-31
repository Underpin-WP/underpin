<?php

namespace Underpin\Abstracts;

use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


abstract class Extension extends Underpin {

	private static $instance;

	protected $minimum_underpin_version = '1.1.0';

	/**
	 * Checks if the PHP version meets the minimum requirements.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the minimum requirements are met, false otherwise.
	 */
	public function supports_underpin_version() {
		return version_compare( underpin()->version(), $this->minimum_underpin_version, '>=' );
	}

	/**
	 * Checks if all minimum requirements are met.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the minimum requirements are met, false otherwise.
	 */
	public function plugin_is_supported() {
		return $this->supports_wp_version() && $this->supports_php_version() && $this->supports_underpin_version();
	}

	/**
	 * Fires up the plugin.
	 *
	 * @since        1.0.0
	 *
	 * @return self
	 * @noinspection PhpUndefinedMethodInspection
	 */
	public function get( $file = '' ) {
		if ( isset( $this->file ) ) {
			$file = $this->file;
		}

		if ( ! self::$instance instanceof $this ) {
			$this->_setup_params( $file );

			// First, check to make sure the minimum requirements are met.
			if ( $this->plugin_is_supported() ) {
				self::$instance = $this;

				// Setup the plugin, if requirements were met.
				self::$instance->setup();

			} else {
				// Run unsupported actions if requirements are not met.
				$this->unsupported_actions();
			}
		}

		return self::$instance;
	}

}