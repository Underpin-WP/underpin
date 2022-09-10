<?php

namespace Underpin\Factories;


use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Helpers\Processors\Dependency_Processor;
use Underpin\Interfaces\Data_Provider;
use Underpin\Interfaces\Observer;
use Underpin\Registries\Logger;
use Underpin\Registries\Mutable_Collection;
use UnitEnum;

class Can_Broadcast implements \Underpin\Interfaces\Can_Broadcast {

	protected Mutable_Collection $observer_registry;

	public function __construct() {
		$this->observer_registry = Mutable_Collection::make( Mutable_Collection::class );
	}

	public function attach( UnitEnum $key, Observer $observer ): static {
		$key = $key->name;

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

		return $this;
	}

	public function detach( UnitEnum $key, $observer_id ): static {
		$key = $key->name;

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

	public function broadcast( string $key, ?Data_Provider $args = null ): void {
		try {
			$items = $this->observer_registry->get( $key );

			if ( false === $args || empty( $items->to_array() ) ) {
				return;
			}

			/* @var Observer $observer */
			foreach ( ( new Dependency_Processor( $this->observer_registry->get( $key ) ) )->mutate()->to_array() as $observer ) {
				$observer->update( $this, $args );
			}
		} catch ( Operation_Failed|Unknown_Registry_Item ) {
			return;
		}

	}

}