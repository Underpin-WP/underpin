<?php

namespace Underpin\Factories;


use Underpin\Exceptions\Operation_Failed;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Interfaces\Can_Broadcast;
use Underpin\Interfaces\Data_Provider;
use Underpin\Registries\Immutable_Collection;
use Underpin\Registries\Logger;
use Underpin\Registries\Mutable_Collection;
use Underpin\Registries\Mutable_Collection_With_Remove;

class Broadcaster implements Can_Broadcast {

	protected Mutable_Collection $observer_registry;

	public function __construct() {
		$this->observer_registry = Mutable_Collection::make( Mutable_Collection::class );
	}

	/**
	 * @param string   $key
	 * @param callable $observer
	 *
	 * @return $this
	 * @throws Operation_Failed
	 * @throws Unknown_Registry_Item
	 */
	public function attach( string $key, callable $observer ): static {
		try {
			$this->observer_registry->get( $key );
		} catch ( Operation_Failed ) {
			$this->observer_registry->add( $key, new Registry( fn ( $item ) => is_callable( $item ) ) );
		}

		$id = count( $this->observer_registry->to_array() );
		$this->observer_registry->get( $key )->add( $id, $observer );

		Logger::log(
			'info',
			new Log_Item(
				code   : 'event_attached',
				message: 'Event attached',
				context: 'registry_key',
				ref    : $key,
				data   : [
					'subject' => get_called_class(),
					'id'      => $id,
				]
			)
		);

		return $this;
	}

	/**
	 * @throws Operation_Failed
	 */
	public function detach( string $key, $observer_id ): static {
		try {
			/* @var Mutable_Collection_With_Remove $item */
			$item = $this->observer_registry->get( $key );
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
						ref    : $key,
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

	public function broadcast( string $key, ?Data_Provider $args = null ): void {
		try {
			/* @var Immutable_Collection $item */
			$item = $this->observer_registry->get( $key );
			if ( false === $args || empty( $item->to_array() ) ) {
				return;
			}
			/* @var callable $observer */
			foreach ( $item->to_array() as $observer ) {
				$observer( $args );
			}
		} catch ( Operation_Failed|Unknown_Registry_Item ) {
			return;
		}

	}

}