<?php
/**
 * Loader Registry.
 * This is used any time a set of extended classes are registered, and instantiated once.
 *
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts\Registries;

use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Helpers\Object_Helper;
use Underpin\Helpers\Processors\Registry_Query;
use Underpin\Interfaces\Queryable;

/**
 * Class Registry.
 *
 * @package Underpin\Abstracts
 */
abstract class Object_Registry extends Registry implements Queryable {

	/**
	 * The abstraction class name.
	 * This is used to validate that the items in this service locator are extended
	 * from the correct abstraction.
	 *
	 * @var string The name of the abstract class this service locator uses.
	 */
	protected string $abstraction_class = '';

	/**
	 * The default factory name.
	 * When generating a new instance without specifying a class, this factory will be used by default.
	 *
	 * @var string The name of the abstract class this service locator uses.
	 */
	protected string $default_factory = '';

	/**
	 * @inheritDoc
	 */
	protected function _add( $key, $value ): void {
		parent::_add( $key, $this->get_class( $value ) );
	}

	/**
	 * @param $value
	 *
	 * @return object The created class
	 */
	protected function get_class( $value ): object {
		return Object_Helper::make_class( $value, $this->default_factory );
	}

	/**
	 * Queries this registry
	 *
	 * @return Registry_Query
	 */
	public function query(): Registry_Query {
		return new Registry_Query( $this );
	}

	/**
	 * @inheritDoc
	 */
	public function validate_item( $key, $value ): bool {

		if ( is_array( $value ) ) {
			$value = $value['class'] ?? $this->default_factory;
		}

		if ( $this->is_registered( $key ) ) {
			throw new Invalid_Registry_Item( "The specified key is already registered.", 403, 'error', null, null );
		}

		if ( $value === $this->abstraction_class || is_subclass_of( $value, $this->abstraction_class ) || $value instanceof $this->abstraction_class ) {
			return true;
		}

		throw new Invalid_Registry_Item( 'The specified item could not be instantiated. Invalid instance type', 403, 'error', null, null );
	}

}