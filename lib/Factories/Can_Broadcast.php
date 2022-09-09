<?php

namespace Underpin\Factories;


use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Helpers\Processors\Dependency_Processor;
use Underpin\Interfaces\Data_Provider;
use Underpin\Interfaces\Observer;
use Underpin\Registries\Logger;
use Underpin\Registries\Mutable_Collection;

class Can_Broadcast implements \Underpin\Interfaces\Can_Broadcast {

	protected Mutable_Collection $observer_registry;

	public function __construct() {
		$this->observer_registry = Mutable_Collection::make( Mutable_Collection::class );
	}

	/**
	 * @throws Operation_Failed
	 */
	public function attach( $key, Observer $observer ): void {
		try {
			$this->observer_registry->get( $key );
		} catch ( Operation_Failed ) {
			$this->observer_registry->add( $key, Mutable_Collection::make( Observer::class ) );
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
	 * @throws Operation_Failed
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
	 * @throws Operation_Failed
	 */
	public function broadcast( string $key, ?Data_Provider $args = null ): void {
		try {
			if ( false === $args || empty( $this->observer_registry->get( $key )->to_array() ) ) {
				return;
			}
		} catch ( Operation_Failed ) {
			return;
		}

		/* @var Observer $observer */
		foreach ( Dependency_Processor::prepare( $this->observer_registry->get( $key ) ) as $observer ) {
			$observer->update( $this, $args );
		}
	}

}