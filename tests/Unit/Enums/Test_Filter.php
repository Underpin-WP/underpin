<?php

namespace Underpin\Tests\Unit\Enums;


namespace Underpin\Tests\Unit\Enums;

use Generator;
use Underpin\Enums\Filter;
use Underpin\Tests\Test_Case;

class Test_Filter extends Test_Case {

	/**
	 * @dataProvider provider_can_get_field
	 * @covers       Filter::field
	 *
	 * @param Filter $enum
	 * @param        $expected
	 *
	 * @return void
	 */
	public function test_can_get_field( Filter $enum, $expected ) {
		$this->assertSame( $enum->field( 'foo' ), $expected );
	}

	public function provider_can_get_field(): Generator {
		yield 'not_in' => [ Filter::not_in, 'foo__not_in' ];
		yield 'in' => [ Filter::in, 'foo__in' ];
		yield 'and' => [ Filter::and, 'foo__and' ];
		yield 'equals' => [ Filter::equals, 'foo__equals' ];
	}

	/**
	 * @dataProvider provider_can_get_key
	 * @covers       Filter::key
	 *
	 * @param Filter $enum
	 * @param        $expected
	 *
	 * @return void
	 */
	public function test_can_get_key( Filter $enum, $expected ): void {
		$this->assertSame( $enum->key(), $expected );
	}

	public function provider_can_get_key(): Generator {
		yield 'not_in' => [ Filter::not_in, 'filter_enum_key__not_in' ];
		yield 'in' => [ Filter::in, 'filter_enum_key__in' ];
		yield 'and' => [ Filter::and, 'filter_enum_key__and' ];
		yield 'equals' => [ Filter::equals, 'filter_enum_key__equals' ];
	}

}