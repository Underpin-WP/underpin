<?php
/**
 * Loader Registry.
 * This is used any time a set of extended classes are registered, and instantiated once.
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts\Registries;

use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Log_Item;
use Underpin\Helpers\Object_Helper;
use Underpin\Interfaces\Feature_Extension;
use Underpin\Interfaces\With_Middleware;
use Underpin\Loaders\Logger;
use Underpin\WordPress\Abstracts\Registry;

/**
 * Class Registry.
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Object_Registry extends Registry {

	/**
	 * The abstraction class name.
	 * This is used to validate that the items in this service locator are extended
	 * from the correct abstraction.
	 *
	 * @since 1.0.0
	 * @var string The name of the abstract class this service locator uses.
	 */
	protected string $abstraction_class = '';

	/**
	 * The default factory name.
	 * When generating a new instance without specifying a class, this factory will be used by default.
	 *
	 * @since 1.2.0
	 * @var string The name of the abstract class this service locator uses.
	 */
	protected string $default_factory = '';

	/**
	 * @inheritDoc
	 * @since 1.3.0 Middleware support added.
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
	 * @inheritDoc
	 * @throws Unknown_Registry_Item
	 */
	public function add( string $key, mixed $value ): static {
		parent::add( $key, $value );
		$this->init_object( $key );

		return $this;
	}

	/**
	 * Does the actions needed to set up a newly added registered object.
	 *
	 * @param string $key The key ot setup
	 *
	 * @return void
	 * @throws Unknown_Registry_Item
	 */
	public function init_object( string $key ): void {
		$item = $this->get( $key );

		// If this implements middleware actions, do those things too.
		if ( $item instanceof With_Middleware ) {
			$item->do_middleware_actions();

			Logger::log(
				'debug',
				new Log_Item(
					code   : 'middleware_actions_ran',
					message: 'The middleware actions for a registry item ran.',
					ref    : $key
				)
			);
		}

		// If this implements registry actions, go ahead and start those up, too.
		if ( $item instanceof Feature_Extension ) {
			$item->do_actions();

			Logger::log(
				'debug',
				new Log_Item(
					code   : 'loader_actions_ran',
					message: 'The actions for a registry item ran.',
					ref    : $key
				)
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function validate_item( $key, $value ): bool {

		if ( is_array( $value ) ) {
			$value = $value['class'] ?? $this->default_factory;
		}

		if ( $this->is_registered( $key ) ) {
			throw new Invalid_Registry_Item( "The specified key is already registered.", 0, 'error', null, null );
		}

		if ( $value === $this->abstraction_class || is_subclass_of( $value, $this->abstraction_class ) || $value instanceof $this->abstraction_class ) {
			return true;
		}

		throw new Invalid_Registry_Item( 'The specified item could not be instantiated. Invalid instance type', 0, 'error', null, null );
	}

}