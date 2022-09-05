<?php

namespace Underpin\Factories;


use ReflectionException;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Helpers\Processors\Dependency_Processor;
use Underpin\Interfaces\Data_Provider;
use Underpin\Interfaces\Observer;
use Underpin\Registries\Logger;
use Underpin\Registries\Object_Registry;
use UnitEnum;

class Broadcaster implements \Underpin\Interfaces\Broadcaster {

	protected Object_Registry $observer_registry;

	public function __construct() {
		$this->observer_registry = new Object_Registry( Object_Registry::class );
	}

	/**
	 * @throws Invalid_Registry_Item
	 * @throws Unknown_Registry_Item|ReflectionException
	 */
	public function attach( UnitEnum $key, Observer $observer ): static {
		try {
			$this->observer_registry->get( $key->value );
		} catch ( Unknown_Registry_Item ) {
			$this->observer_registry->add( $key->value, new Object_Registry( Observer::class ) );
		}

		$this->observer_registry->get( $key->value )->add( $observer->get_id(), $observer );

		Logger::log(
			'info',
			new Log_Item(
				code   : 'event_attached',
				message: 'Event attached',
				context: 'registry_key',
				ref    : $key->value,
				data   : [
					'subject' => get_called_class(),
					'id'      => $observer->get_id(),
				]
			)
		);

		return $this;
	}

	/**
	 * @throws Unknown_Registry_Item
	 */
	public function detach( UnitEnum $key, $observer_id ): static {
		foreach ( $this->observer_registry->get( $key ) as $iterator => $observer ) {
			if ( $observer->id === $observer_id ) {
				Logger::log(
					'info',
					new Log_Item(
						code   : 'event_detached',
						message: 'Event detached',
						context: 'registry_key',
						ref    : $key,
						data   : [
							'subject'     => get_called_class(),
							'name'        => $observer->name,
							'description' => $observer->description,
						]
					)
				);
				unset( $this->observer_registry[ $key ][ $iterator ] );
			}
		}

		return $this;
	}

	/**
	 * @throws Invalid_Registry_Item
	 * @throws Unknown_Registry_Item
	 */
	public function broadcast( UnitEnum $key, ?Data_Provider $args = null ): void {
		try {
			if ( false === $args || empty( $this->observer_registry->get( $key->name )->to_array() ) ) {
				return;
			}
		} catch ( Unknown_Registry_Item ) {
			return;
		}

		/* @var Observer $observer */
		foreach ( Dependency_Processor::prepare( $this->observer_registry->get( $key->name ) ) as $observer ) {
			$observer->update( $this, $args );
		}
	}

}