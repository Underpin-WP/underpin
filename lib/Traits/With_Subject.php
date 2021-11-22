<?php

namespace Underpin\Traits;

use Underpin\Abstracts\Observer;
use Underpin\Abstracts\Storage;
use Underpin\Factories\Accumulator;
use Underpin\Factories\Dependency_Processor;
use Underpin\Factories\Object_Registry;
use Underpin\Factories\Simple_Storage;
use Underpin\Loaders\Logger;
use function Underpin\underpin;

trait With_Subject {

	protected $observer_registry;

	private function init_observer_registry( $key ) {

		if ( ! isset( $this->observer_registry ) ) {
			$this->observer_registry = new Object_Registry( [
				'abstraction_class' => Object_Registry::class,
			] );
		}

		if ( is_wp_error( $this->observer_registry->get( $key ) ) ) {
			$this->observer_registry->add( $key, new Object_Registry( [
				'abstraction_class' => '\Underpin\Abstracts\Observer',
				'default_factory'   => '\Underpin\Factories\Observer',
			] ) );
		}
	}

	public function attach( $key, Observer $observer ) {
		$this->init_observer_registry( $key );

		$this->observer_registry[ $key ][] = $observer;

			Logger::log(
				'info',
				'event_attached',
				'Event attached',
				[
					'subject'     => get_called_class(),
					'key'         => $key,
					'name'        => $observer->name,
					'description' => $observer->description,
				]
			);
	}

	public function detach( $key, $observer_id ) {
		$this->init_observer_registry( $key );

		foreach ( $this->observer_registry->get( $key ) as $iterator => $observer ) {
			if ( $observer->id === $observer_id ) {
					Logger::log(
						'info',
						'event_detached',
						'Event detached',
						[
							'subject'     => get_called_class(),
							'key'         => $key,
							'name'        => $observer->name,
							'description' => $observer->description,
						]
					);
				unset( $this->observer_registry[ $key ][ $iterator ] );
			}
		}

	}

	private function setup_args( $key, $args = null ) {
		self::init_observer_registry( $key );

		// Bail if there are no observers set.
		if ( empty( (array) $this->observer_registry->get( $key ) ) ) {
			return false;
		}


		if ( ! $args instanceof Storage ) {
			$args = new Simple_Storage( $args );
		}

		return $args;
	}

	protected function notify( $key, $args = [] ) {
		$args = $this->setup_args( $key, $args );

		if ( false === $args ) {
			return;
		}

		/* @var Observer $observer */
		foreach ( Dependency_Processor::prepare( $this->observer_registry->get( $key ) ) as $observer ) {
			$observer->update( $this, $args );
		}
	}

	protected function apply_filters( $key, Accumulator $accumulator ) {
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

	protected function decide( $key, Accumulator $accumulator ) {
		$args = $this->setup_args( $key, $accumulator );

		if ( ! $args instanceof Accumulator ) {
			return $accumulator->get_state();
		}

		/* @var Observer $observer */
		foreach ( array_reverse( Dependency_Processor::prepare( $this->observer_registry->get( $key ) )) as $observer ) {
			var_dump($observer->get_id());
			$observer->update( $this, $args );
		}

		return $args->get_state();
	}

}