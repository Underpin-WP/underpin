<?php

namespace Underpin\Traits;

use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Factories\Data_Providers\Accumulator;
use Underpin\Factories\Log_Item;
use Underpin\Factories\Object_Registry;
use Underpin\Helpers\Processors\Dependency_Processor;
use Underpin\Interfaces\Data_Provider;
use Underpin\Interfaces\Observer;
use Underpin\Loaders\Logger;


trait With_Subject {

	protected Object_Registry $observer_registry;

	/**
	 * @throws Invalid_Registry_Item
	 * @throws Unknown_Registry_Item
	 */
	private function init_observer_registry( $key ): void {

		if ( ! isset( $this->observer_registry ) ) {
			$this->observer_registry = new Object_Registry( Object_Registry::class );
		}

		try {
			$this->observer_registry->get( $key );
		} catch ( Unknown_Registry_Item ) {
			$this->observer_registry->add( $key, new Object_Registry( Observer::class ) );
		}
	}

	/**
	 * @throws Invalid_Registry_Item
	 * @throws Unknown_Registry_Item
	 */
	public function attach( $key, Observer $observer ): void {
		$this->init_observer_registry( $key );

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
	 * @throws Invalid_Registry_Item
	 * @throws Unknown_Registry_Item
	 */
	public function detach( $key, $observer_id ): void {
		$this->init_observer_registry( $key );

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
	private function setup_args( $key, ?Data_Provider $args ): Data_Provider|bool {
		self::init_observer_registry( $key );

		// Bail if there are no observers set.
		if ( empty( $this->observer_registry->get( $key )->to_array() ) ) {
			return false;
		}


		return $args;
	}

	/**
	 * @throws Invalid_Registry_Item
	 * @throws Unknown_Registry_Item
	 */
	protected function notify( string $key, ?Data_Provider $args = null ): void {
		$args = $this->setup_args( $key, $args );

		if ( false === $args ) {
			return;
		}

		/* @var Observer $observer */
		foreach ( Dependency_Processor::prepare( $this->observer_registry->get( $key ) ) as $observer ) {
			$observer->update( $this, $args );
		}
	}

	/**
	 * @throws Invalid_Registry_Item
	 * @throws Unknown_Registry_Item
	 */
	protected function apply_filters( $key, Accumulator $accumulator ): mixed {
		$args = $this->setup_args( $key, $accumulator );

		if ( ! $args instanceof Accumulator ) {
			return $accumulator->get_state();
		}

		/* @var Observer $observer */
		foreach ( Dependency_Processor::prepare( $this->observer_registry->get( $key ) ) as $observer ) {
			$observer->update( $this, $args );
		}

		return $args->get_state();
	}

}