<?php

namespace Underpin\Tests;

class Helpers {

	public static function call_inaccessible_method( $object, $method_name, ...$args ) {
		$reflection = new \ReflectionClass( get_class( $object ) );
		$method     = $reflection->getMethod( $method_name );
		$method->setAccessible( true );

		return $method->invokeArgs( $object, $args );
	}

	public static function get_protected_property($object, $property) {
		$reflection = new \ReflectionClass($object);
		$reflection_property = $reflection->getProperty($property);

		return $reflection_property;
	}

	public static function set_protected_property($object, $property, $value) {
		$reflection_property = self::get_protected_property($object,$property);
		$reflection_property->setAccessible(true);

		$reflection_property->setValue($object, $value);
	}
}