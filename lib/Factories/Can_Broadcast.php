<?php

namespace Underpin\Factories;


use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Helpers\Processors\Dependency_Processor;
use Underpin\Interfaces\Data_Provider;
use Underpin\Interfaces\Observer;
use Underpin\Loaders\Logger;

class Can_Broadcast implements \Underpin\Interfaces\Can_Broadcast {

	protected Object_Registry $observer_registry;

	public function __construct() {
		$this->observer_registry = new Object_Registry( Object_Registry::class );
	}

	/**
	 * @throws Invalid_Registry_Item
	 * @throws Unknown_Registry_Item
	 */
	public function attach( $key, Observer $observer ): void {
		try {
			$this->observer_registry->get( $key );
		} catch ( Unknown_Registry_Item ) {
			$this->observer_registry->add( $key, new Object_Registry( Observer::class ) );
		}

		$this->observer_registry[ $key ][] = $observer;

		Logger::log(
			'info',
			new Log_Item(
				code   : 'event_attached',
				message: 'Event attached',
				context: 'registry_key',
				ref    : $key,
				data   : [
					'subject' => get_called_class(),
					'id'      => $observer->get_id(),
				]
			)
		);
	}

	/**
	 * @throws Unknown_Registry_Item
	 */
	public function detach( $key, $observer_id ): void {
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

	}

	/**
	 * @throws Invalid_Registry_Item
	 * @throws Unknown_Registry_Item
	 */
	public function broadcast( string $key, ?Data_Provider $args = null ): void {
		try {
			if ( false === $args || empty( $this->observer_registry->get( $key )->to_array() ) ) {
				return;
			}
		} catch ( Unknown_Registry_Item ) {
			return;
		}

		/* @var Observer $observer */
		foreach ( Dependency_Processor::prepare( $this->observer_registry->get( $key ) ) as $observer ) {
			$observer->update( $this, $args );
		}
	}

}