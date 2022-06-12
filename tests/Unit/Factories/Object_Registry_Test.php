<?php

namespace Underpin\Tests\Unit\Factories;


use Closure;
use Generator;
use Underpin\Factories\Registry;
use Underpin\Tests\Helpers;
use Underpin\Tests\Test_Case;

class Object_Registry_Test extends Test_Case {

	/**
	 * @dataProvider provider_can_validate_item
	 */
	public function test_can_validate_item( Closure $callback, bool $expected ) {
		$instance = new Registry( $callback );

		$result = Helpers::call_inaccessible_method( $instance, 'validate_item', 'foo', 'bar' );

		$this->assertSame( $expected, $result );
	}

	/* @see test_can_validate_item */
	public function provider_can_validate_item(): Generator {
		yield 'non_boolean_callback' => [ fn () => 'invalid', false ];
		yield 'callback_true' => [ fn () => true, true ];
		yield 'callback_false' => [ fn () => false, false ];
		yield 'uses_args' => [ fn ( $key, $value ) => $key === 'foo' && $value === 'bar', true ];
	}

}