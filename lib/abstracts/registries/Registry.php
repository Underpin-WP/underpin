<?php
/**
 * Registry Class.
 * This is used any time a set of identical things are stored.
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts\Registries;

use ArrayIterator;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Registry.
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Registry extends ArrayIterator {

	/**
	 * A human-readable description of this event type.
	 * This is used in debug logs to make it easier to understand why this exists.
	 *
	 * @var string
	 */
	public $description = '';

	/**
	 * A human-readable name for this event type.
	 * This is used in debug logs to make it easier to understand what this is.
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * Set to true to force this registry to skip logging.
	 *
	 * @since 1.3.1
	 *
	 * @var bool
	 */
	protected $skip_logging = false;

	/**
	 * Registry constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->set_default_items();
	}

	/**
	 * Sets the default items for the registry.
	 */
	abstract protected function set_default_items();

	/**
	 * Validates an item. This runs just before adding items to the registry.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   The key to validate.
	 * @param mixed  $value The value to validate.
	 * @return true|WP_Error true if the item is valid, WP_Error otherwise.
	 */
	abstract protected function validate_item( $key, $value );

	protected function _add( $key, $value ) {
		return $this[ $key ] = $value;
	}

	/**
	 * Adds an item to the registry
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   The key to validate.
	 * @param mixed  $value The value to validate.
	 *
	 * @return true|WP_Error true if the item is valid, WP_Error otherwise.
	 */
	public function add( $key, $value ) {
		$valid = $this->validate_item( $key, $value );

		if ( true === $valid ) {
			$this->_add( $key, $value );

			if ( false === $this->skip_logging && ! is_wp_error( underpin()->logger() ) ) {
				underpin()->logger()->log(
					'debug',
					'valid_item_added',
					'A valid registry item was registered.',
					[
						'key'   => $key,
						'value' => $value,
						'class' => get_called_class(),
					]
				);
			}
		} else {
			if ( false === $this->skip_logging && ! is_wp_error( underpin()->logger() ) ) {
				underpin()->logger()->log(
					'warning',
					'invalid_event',
					'An item called ' . $key . ' could not be registered.',
					array( 'key' => $key, 'value' => $value )
				);
			}
		}

		return $valid;
	}

	/**
	 * Retrieves a registered item.
	 *
	 * @param string $key The identifier for the item.
	 * @return mixed the item value.
	 */
	public function get( $key ) {
		if ( isset( $this[ $key ] ) ) {
			return $this[$key];
		} else {
			$error = new WP_Error(
				'key_not_set',
				'Specified key is not set.',
				[
					'key'           => $key,
					'name'          => $this->name,
					'description'   => $this->description,
					'registry_type' => get_called_class(),
				]
			);

			if ( 'logger' !== $key && ! is_wp_error( underpin()->logger() ) && underpin()->is_debug_mode_enabled() ) {
				underpin()->logger()->log_wp_error( 'notice', $error );
			}

			return $error;
		}
	}

}