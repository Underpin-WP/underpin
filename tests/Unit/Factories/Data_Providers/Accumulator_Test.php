<?php

namespace Underpin\Tests\Unit\Factories\Data_Providers;


use Generator;
use Underpin\Exceptions\Invalid_Callback;
use Underpin\Factories\Data_Providers\Accumulator;
use Underpin\Tests\Helpers;
use Underpin\Tests\Test_Case;
use Underpin\Tests\Traits\With_Getter_Tests;

class Accumulator_Test extends Test_Case {

	use With_Getter_Tests;

	public function provider_can_get_fields(): Generator {
		yield 'state' => [ 'state', [ 'foo' ] ];
	}

	protected function get_instance(): object {
		return new Accumulator();
	}

	/**
	 * @throws Invalid_Callback
	 */
	public function test_can_reset() {
		$instance = new Accumulator( default: [ 'foo' ] );

		$instance->reset();

		$this->assertSame( [ 'foo' ], Helpers::get_protected_property( $instance, 'state' )->getValue( $instance ) );
	}

	/**
	 * @throws Invalid_Callback
	 */
	public function test_can_construct() {
		$instance = new Accumulator( default: [ 'test-default' ] );

		$default = Helpers::get_protected_property( $instance, 'default' )->getValue( $instance );

		$this->assertSame( $default, [ 'test-default' ] );
	}

	/**
	 * @dataProvider provider_can_set_valid_callback
	 * @throws Invalid_Callback
	 */
	public function test_can_set_valid_callback( $callback, $expected ) {
		$instance = new Accumulator( valid_callback: $callback );
		$this->assertEquals( $expected, Helpers::get_protected_property( $instance, 'valid_callback' )->getValue( $instance ) );
	}

	/* @see test_can_set_valid_callback */
	public function provider_can_set_valid_callback(): Generator {
		yield 'unset callback' => [ null, fn () => true ];
		yield 'closure' => [ fn () => 'neat', fn () => 'neat' ];
	}

}