<?php
/**
 * Loader Registry.
 * This is used any time a set of extended classes are registered, and instantiated once.
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts\Registries;

use Underpin\Abstracts\Feature_Extension;
use Underpin\Abstracts\Underpin;
use Underpin\Traits\With_Parent;
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
abstract class Loader_Registry extends Registry {

	use With_Parent;

	/**
	 * The abstraction class name.
	 * This is used to validate that the items in this service locator are extended
	 * from the correct abstraction.
	 *
	 * @since 1.0.0
	 * @var string The name of the abstract class this service locator uses.
	 */
	protected $abstraction_class = '';

	/**
	 * The default factory name.
	 * When generating a new instance without specifying a class, this factory will be used by default.
	 *
	 * @since 1.2.0
	 * @var string The name of the abstract class this service locator uses.
	 */
	protected $default_factory = '';

	/**
	 * Loader_Registry constructor.
	 *
	 */
	public function __construct( $parent_id = false ) {
		if ( false !== $parent_id ) {
			$this->parent_id = $parent_id;
		}
		parent::__construct( $this->get_registry_id() );
	}

	/**
	 * Gets the service locator ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string The registry ID for this service locator.
	 */
	protected function get_registry_id() {
		$class = explode( '\\', $this->abstraction_class );

		return strtolower( array_pop( $class ) );
	}

	/**
	 * @inheritDoc
	 */
	public function add( $key, $value ) {
		$valid = $this->validate_item( $key, $value );
		if ( true === $valid ) {
			$this[ $key ] = Underpin::make_class( $value, $this->default_factory );
		} else {
			$this[ $key ] = $valid;
		}

		// If this implements registry actions, go ahead and start those up, too.
		if ( self::has_trait( 'Underpin\Traits\Feature_Extension', $this->get( $key ) ) ) {
			$this->get( $key )->do_actions();

			if ( ! $this instanceof \Underpin_Logger\Loaders\Logger && ! is_wp_error( underpin()->logger() ) ) {
				underpin()->logger()->log(
					'debug',
					'loader_actions_ran',
					'The actions for the ' . $this->registry_id . ' item called ' . $key . ' ran.',
					[ 'ref' => $this->registry_id, 'key' => $key, 'value' => $value ]
				);
			}
		}

		if ( ! is_wp_error( $valid ) ) {
			do_action( 'underpin/loader_registered', $key, $value, get_called_class(), $this->parent_id );
		}

		return $valid;
	}

	/**
	 * Checks to see if the class, or any of its parents, uses the specified trait.
	 *
	 * @since 1.0.0
	 *
	 * @param string              $trait The trait to check for
	 * @param object|string|false $class The class to check.
	 * @return bool true if the class uses the specified trait, otherwise false.
	 */
	public static function has_trait( $trait, $class ) {

		if ( false === $class ) {
			return false;
		}

		$traits = class_uses( $class );

		if ( in_array( $trait, $traits ) ) {
			return true;
		}

		while ( get_parent_class( $class ) ) {
			$class = get_parent_class( $class );

			$has_trait = self::has_trait( $trait, $class );

			if ( true === $has_trait ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function validate_item( $key, $value ) {

		if ( is_array( $value ) ) {
			$value = $value['class'] ?? $this->default_factory;
		}

		if ( $value === $this->abstraction_class || is_subclass_of( $value, $this->abstraction_class ) || $value instanceof $this->abstraction_class ) {
			return true;
		}

		return underpin()->logger()->log_as_error(
			'error',
			'invalid_service_type',
			'The specified item could not be instantiated. Invalid instance type',
			[ 'ref' => $key, 'value' => $value, 'expects_type' => $this->abstraction_class ]
		);
	}

	/**
	 * Determines if a registry item passes the arguments.
	 *
	 * @since 1.3.0
	 *
	 * @param string $item_key Registry item key
	 * @param array  $args     List of arguments
	 *
	 * @return object|\WP_Error The instance, if it matches the filters. Otherwise WP_Error.
	 */
	protected function filter_item( string $item_key, array $args ) {
		$item = $this->get( $item_key );

		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$valid = true;

		foreach ( $args as $key => $arg ) {
			// Process the argument key
			$processed = explode( '__', $key );

			// Set the field type to the first item in the array.
			$field = $processed[0];

			// If there was some specificity after a __, use it.
			$type = count( $processed ) > 1 ? $processed[1] : 'in';

			// Bail early if this field is not in this object.
			if ( ! property_exists( $item, $field ) ) {
				continue;
			}

			$object_field = $item->$field;

			// Convert argument to an array. This allows us to always use array functions for checking.
			if ( ! is_array( $arg ) ) {
				$arg = array( $arg );
			}


			// Convert field to array. This allows us to always use array functions to check.
			if ( ! is_array( $object_field ) ) {
				$object_field = array( $object_field );
			}

			// Run the intersection.
			$fields = array_intersect( $arg, $object_field );

			// Check based on type.
			switch ( $type ) {
				case 'not_in':
					$valid = empty( $fields );
					break;
				case 'and':
					$valid = count( $fields ) === count( $arg );
					break;
				default:
					$valid = ! empty( $fields );
					break;
			}

			if ( false === $valid ) {
				break;
			}
		}

		if ( true === $valid ) {
			return $item;
		}

		return new \WP_Error( 'item_failed_filter', 'The specified item failed the filter', [
			'item' => $item,
			'args' => $args,
		] );
	}

	/**
	 * Pre-filters the list of items.
	 *
	 * @since 1.3.0
	 *
	 * @param array $args Arguments to pre-filter by.
	 *
	 * @return int[]|string[]
	 */
	protected function pre_filter_items( array $args = [] ) {

		// Filter out items, if loader keys are specified
		if ( isset( $args['loader_key__in'] ) ) {
			$items = array_intersect( array_keys( (array) $this ), $args['loader_key__in'] );
			unset( $args['loader_key__in'] );
		} else {
			$items = array_keys( (array) $this );
		}

		return $items;
	}

	/**
	 * Finds the first loader item that matches the provided arguments.
	 *
	 * @since 1.3.0
	 *
	 * @param array $args List of filter arguments
	 *
	 * @return object|\WP_Error loader item if found, otherwise WP_Error.
	 */
	public function find( array $args = [] ) {
		foreach ( $this->pre_filter_items( $args ) as $item_key ) {
			$item = $this->filter_item( $item_key, $args );

			if ( ! is_wp_error( $item ) ) {
				return $item;
			}
		}

		return new \WP_Error( 'item_not_found', 'No item matching the arguments could be found', [
			'args' => $args,
		] );
	}

	/**
	 * Queries a loader registry.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Filtered items no-longer preserve keys by default. Include "preserve_keys" argument in array if you
	 *              want to preserve keys.
	 *
	 * @param array $args
	 *
	 * @return object[] Array of registry items.
	 */
	public function filter( array $args = [] ): array {
		$results = [];

		// Filter out items, if loader keys are specified
		foreach ( $this->pre_filter_items( $args ) as $item_key ) {
			$item = $this->filter_item( $item_key, $args );

			if ( ! is_wp_error( $item ) ) {
				if ( isset( $args['preserve_keys'] ) && true === $args['preserve_keys'] ) {
					$results[ $item_key ] = $item;
				} else {
					$results[] = $item;
				}
			}
		}

		return $results;
	}
}