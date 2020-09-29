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
	 * Unique identifier for this registry.
	 *
	 * @since 1.0.0
	 * @var string A unique identifier for this registry.
	 */
	protected $registry_id;

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
	 * Registry constructor.
	 *
	 * @param string $registry_id The registry ID.
	 */
	public function __construct( $registry_id ) {
		$this->registry_id = (string) $registry_id;
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

	/**
	 * Adds an item to the registry
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   The key to validate.
	 * @param mixed  $value The value to validate.
	 * @return true|WP_Error true if the item is valid, WP_Error otherwise.
	 */
	public function add( $key, $value ) {
		$valid = $this->validate_item( $key, $value );

		if ( true === $valid ) {
			$this[ $key ] = $value;

			/**
			 * Fires action after an item is added to the registry.
			 *
			 * @since 1.0.0
			 * @param string $registry_id Unique registry ID in which this item was added.
			 * @param string $key         The key that was added to the registry.
			 * @param mixed  $value       The value of the item that was added.
			 */
			do_action( 'underpin/registry/after_added_item', $this->registry_id, $key, $value );

			underpin()->logger()->log(
				'notice',
				'valid_event_added',
				'A valid item for the ' . $this->registry_id . ' registry called ' . $key . ' was registered.',
				[ 'ref' => $this->registry_id, 'key' => $key, 'value' => $value ]
			);
		} else {
			underpin()->logger()->log(
				'warning',
				'invalid_event',
				'An item for the ' . $this->registry_id . ' registry called ' . $key . ' could not be registered.',
				array( 'key' => $key, 'value' => $value, 'ref' => $this->registry_id )
			);
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
			$error = new WP_Error( 'key_not_set', 'Specified key is not set.', [ 'key' => $key ] );

			if ( underpin()->is_debug_mode_enabled() ) {
				underpin()->logger()->log_wp_error( 'warning', $error );
			}

			return $error;
		}
	}

}