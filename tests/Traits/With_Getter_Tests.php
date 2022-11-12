<?php

namespace Underpin\Tests\Traits;


use Generator;
use Underpin\Tests\Helpers;

trait With_Getter_Tests {

	/**
	 * @dataProvider provider_can_get_fields
	 */
	public function test_can_get_fields( string $key, mixed $value ): void {
		$instance = $this->get_instance();

		Helpers::set_protected_property( $instance, $key, $value );
		$this->assert_field( $value, call_user_func( [ $instance, "get_$key" ] ) );
	}

	abstract public function provider_can_get_fields(): Generator;

	abstract protected function get_instance(): object;

	protected function assert_field( mixed $expected, mixed $value ): void {
		if ( method_exists( $this, 'assertSame' ) ) {
			$this->assertSame( $expected, $value );
		}
	}

}