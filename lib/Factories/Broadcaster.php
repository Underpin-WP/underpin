<?php

namespace Underpin\Factories;


use ReflectionException;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Helpers\Processors\Dependency_Processor;
use Underpin\Interfaces\Data_Provider;
use Underpin\Interfaces\Observer;
use Underpin\Registries\Logger;
use Underpin\Registries\Mutable_Collection;
use Underpin\Registries\Mutable_Collection_With_Remove;
use UnitEnum;

class Broadcaster implements \Underpin\Interfaces\Broadcaster {

	protected Mutable_Collection $observer_registry;

	public function __construct() {
		$this->observer_registry = Mutable_Collection::make( Mutable_Collection::class );
	}

	/**
	 * @param UnitEnum $key
	 * @param Observer $observer
	 *
	 * @return $this
	 * @throws Operation_Failed
	 * @throws Unknown_Registry_Item
	 */
	public function attach( UnitEnum $key, Observer $observer ): static {
		try {
			$this->observer_registry->get( $key->value );
		} catch ( Operation_Failed ) {
			$this->observer_registry->add( $key->value, Mutable_Collection_With_Remove::make( Observer::class ) );
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
	 * @throws Operation_Failed
	 */
	public function detach( UnitEnum $key, $observer_id ): static {
		try {
			/* @var Mutable_Collection_With_Remove $item */
			$item = $this->observer_registry->get( $key->name );
		} catch ( Unknown_Registry_Item $e ) {
			return $this;
		}

		foreach ( $item as $iterator => $observer ) {
			if ( $observer->id === $observer_id ) {
				Logger::log(
					'info',
					new Log_Item(
						code   : 'event_detached',
						message: 'Event detached',
						context: 'registry_key',
						ref    : $key->name,
						data   : [
							'subject'     => get_called_class(),
							'name'        => $observer->name,
							'description' => $observer->description,
						]
					)
				);
				$item->remove( $iterator );
			}
		}

		return $this;
	}

	public function broadcast( UnitEnum $key, ?Data_Provider $args = null ): void {
		try {
			$item = $this->observer_registry->get( $key->name );
			if ( false === $args || empty( $item->to_array() ) ) {
				return;
			}
			/* @var Observer $observer */
			foreach ( ( new Dependency_Processor( $item ) )->mutate()->to_array() as $observer ) {
				$observer->update( $this, $args );
			}
		} catch ( Operation_Failed|Unknown_Registry_Item ) {
			return;
		}

	}

}