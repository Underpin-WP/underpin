<?php

namespace Underpin\Traits;

use Underpin\Abstracts\Storage;
use Underpin\Abstracts\Underpin;
use Underpin\Factories\Accumulator;
use Underpin\Factories\Dependency_Processor;
use Underpin\Factories\Object_Registry;
use Underpin\Factories\Observer;
use Underpin\Factories\Simple_Storage;
use function Underpin\underpin;

trait With_Static_Subject {

	protected static $observer_registry;

	private static function init_observer_registry( $key ) {

		if ( ! isset( self::$observer_registry ) ) {
			self::$observer_registry = new Object_Registry( [
				'abstraction_class' => Object_Registry::class,
			] );
		}

		if ( is_wp_error( self::$observer_registry->get( $key ) ) ) {
			self::$observer_registry->add( $key, new Object_Registry( [
				'abstraction_class' => '\Underpin\Abstracts\Observer',
				'default_factory'   => '\Underpin\Factories\Observer',
			] ) );
		}
	}

	public static function attach( $key, Observer $observer ) {
		self::init_observer_registry( $key );

		self::$observer_registry[ $key ][] = $observer;
	}

	public static function detach( $key, $observer_id ) {
		self::init_observer_registry( $key );

		foreach ( self::$observer_registry->get( $key ) as $iterator => $observer ) {
			if ( $observer->id === $observer_id ) {
				unset( self::$observer_registry[ $key ][ $iterator ] );
			}
		}

	}

	private function setup_args( $key, $args = null ) {
		self::init_observer_registry( $key );

		// Bail if there are no observers set.
		if ( empty( (array) self::$observer_registry->get( $key ) ) ) {
			return false;
		}

		if ( ! $args instanceof Storage ) {
			$args = new Simple_Storage( $args );
		}

		return $args;
	}

	protected function notify( $key, $args = null ) {
		$args = $this->setup_args( $key, $args );

		if ( false === $args ) {
			return;
		}

		/* @var Observer $observer */
		foreach ( Dependency_Processor::prepare( self::$observer_registry->get( $key ) ) as $observer ) {
			$observer->update( $this, $args );
		}
	}

	protected function filter( $key, Accumulator $accumulator ) {
		$args = $this->setup_args( $key, $accumulator );

		if ( ! $args instanceof Accumulator ) {
			return $accumulator->get_state();
		}

		/* @var Observer $observer */
		foreach ( Dependency_Processor::prepare( self::$observer_registry->get( $key ) ) as $observer ) {
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
		foreach ( array_reverse( Dependency_Processor::prepare( self::$observer_registry->get( $key ) ) ) as $observer ) {
			$observer->update( $this, $args );
		}

		return $args->get_state();
	}

}