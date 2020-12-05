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
	 * Loader_Registry constructor.
	 *
	 */
	public function __construct() {
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
			if ( is_string( $value ) ) {
				$this[ $key ]             = new $value;
				$this->class_list[ $key ] = $value;
			} elseif ( is_array( $value ) ) {
				$class                    = $value['class'];
				$args                     = isset( $value['args'] ) ? $value['args'] : [];
				$this[ $key ]             = new $class( ...$args );
				$this->class_list[ $key ] = $value['class'];
			} else {
				$this[ $key ]             = $value;
				$this->class_list[ $key ] = get_class( $this[ $key ] );
			}
		} else{
			$this[ $key ] = $valid;
		}

		// If this implements registry actions, go ahead and start those up, too.
		if ( self::has_trait( 'Underpin\Traits\Feature_Extension', $this->get( $key ) ) ) {
			$this->get( $key )->do_actions();

			underpin()->logger()->log(
				'notice',
				'loader_actions_ran',
				'The actions for the ' . $this->registry_id . ' item called ' . $key . ' ran.',
				[ 'ref' => $this->registry_id, 'key' => $key, 'value' => $value ]
			);
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
			$value = isset( $value['class'] ) ? $value['class'] : '';
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
	 * Retrieves the key from the provided instance.
	 *
	 * @since 1.2.3
	 *
	 * @param $instance
	 * @return \WP_Error
	 */
	public function get_key( $instance ) {
		$class = get_class( $instance );

		$items = array_flip( $this->class_list );

		if ( isset( $items[ $class ] ) ) {
			return $items[ $class ];
		}

		return new \WP_Error( 'key_not_found', 'The key for the provided class could not be found', [
			'class' => $class,
		] );

	}

	/**
	 * Queries a loader registry.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @return array
	 */
	public function filter( $args = [] ) {
		$results = [];

		// Filter out items, if loader keys are specified
		if ( isset( $args['loader_key__in'] ) ) {
			$items = array_intersect( array_keys( (array) $this ), $args['loader_key__in'] );
			unset( $args['loader_key__in'] );
		} else {
			$items = array_keys( (array) $this );
		}

		foreach ( $items as $item_key ) {
			$item = $this->get( $item_key );

			if ( ! is_wp_error( $item ) ) {
				$valid = true;

				foreach ( $args as $key => $arg ) {
					// Process the argument key
					$processed = explode( '__', $key );

					// Set the field type to the first item in the array.
					$field = $processed[0];

					// If there was some specificity after a __, use it.
					$type = count( $processed ) > 1 ? $processed[1] : 'in';

					// Bail early if this field is not in this object.
					if ( ! isset( $item->$field ) ) {
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
					$results[ $item_key ] = $item;
				}
			}
		}

		return $results;
	}
}