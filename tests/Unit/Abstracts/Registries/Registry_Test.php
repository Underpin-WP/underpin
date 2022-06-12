<?php

namespace Underpin\Tests\Unit\Abstracts\Registries;

use Generator;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Exceptions\Unknown_Registry_Item;
use Underpin\Abstracts\Registries\Registry;
use Underpin\Helpers\Processors\List_Filter;
use Underpin\Tests\Helpers;
use Underpin\Tests\Test_Case;

class Registry_Test extends Test_Case {

	/**
	 * Covers \Underpin\Factories\Registry::_add
	 *
	 * @return void
	 */
	public function test_can__add(): void {
		$class = $this->getMockForAbstractClass( Registry::class );

		Helpers::call_inaccessible_method( $class, '_add', 'foo', 'bar' );

		$this->assertSame( [ 'foo' => 'bar' ], $class->to_array() );
	}

	/**
	 * Covers \Underpin\Factories\Registry::add
	 *
	 * @dataProvider provider_can_add
	 *
	 * @param $valid
	 *
	 * @return void
	 * @throws Invalid_Registry_Item
	 */
	public function test_can_add( $valid ): void {
		$mock = $this->getMockForAbstractClass(
			originalClassName      : Registry::class,
			callOriginalConstructor: false,
			mockedMethods          : [ 'validate_item', '_add' ]
		);

		$assertion = $mock->expects( $this->once() )->method( 'validate_item' );

		if ( $valid instanceof \Throwable ) {
			$assertion->willThrowException( $valid );
			$this->expectException( $valid::class );
		} else {
			$assertion->willReturn( $valid );
		}

		$mock->expects( $this->exactly( true === $valid ? 1 : 0 ) )->method( '_add' )->with( 'key', 'value' );

		$result = $mock->add( 'key', 'value' );
		$this->assertSame( $mock, $result );
	}

	/* @see test_can_add */
	public function provider_can_add(): Generator {
		yield 'Stores data in the array when valid' => [ true ];
		yield 'Does not store data when invalid' => [ false ];
		yield 'Does not store data when exception is thrown' => [ new Invalid_Registry_Item( 'foo', 9001 ) ];
	}

	/**
	 * Covers \Underpin\Factories\Registry::get
	 *
	 * @param bool $exists
	 *
	 * @return void
	 * @dataProvider provider_can_get
	 */
	public function test_get_throws_exceptions_when_item_cannot_be_found( bool $exists ): void {
		$mock = $this->getMockForAbstractClass( originalClassName: Registry::class, mockedMethods: [ 'is_registered' ] );

		$mock->expects( $this->once() )->method( 'is_registered' )->willReturn( $exists );

		Helpers::set_protected_property( $mock, 'storage', [ 'foo' => 'bar' ] );

		if ( ! $exists ) {
			$this->expectException( Unknown_Registry_Item::class );
		}

		$mock->get( 'foo' );
	}

	public function provider_can_get(): Generator {
		yield 'item is set' => [ true ];
		yield 'item is not set' => [ false ];
	}

	/**
	 * @throws Unknown_Registry_Item
	 */
	public function test_can_get() {
		$mock = $this->getMockForAbstractClass( originalClassName: Registry::class, mockedMethods: [ 'is_registered' ] );
		$mock->expects( $this->once() )->method( 'is_registered' )->willReturn( true );
		Helpers::set_protected_property( $mock, 'storage', [ 'foo' => 'bar', 'bar' => 'baz' ] );

		$this->assertSame( $mock->get( 'foo' ), 'bar' );
	}

	/**
	 * Covers \Underpin\Factories\Registry::get
	 *
	 * @dataProvider provider_can_add
	 *
	 * @return void
	 */
	public function test_get_throws_exceptions_when_key_is_not_set(): void {
		$mock = $this->getMockForAbstractClass( originalClassName: Registry::class );

		Helpers::set_protected_property( $mock, 'storage', [ 'foo' => 'bar' ] );

		$this->expectException( Unknown_Registry_Item::class );

		$mock->get( 'baz' );
	}

	/**
	 * @param array $registry_items
	 * @param bool  $expected
	 *
	 * @return void
	 * @dataProvider provider_is_registered
	 */
	public function test_is_registered( array $registry_items, bool $expected ): void {
		$mock = $this->getMockForAbstractClass( Registry::class );

		Helpers::set_protected_property( $mock, 'storage', $registry_items );

		$this->assertSame( $expected, Helpers::call_inaccessible_method( $mock, 'is_registered', 'foo' ) );
	}

	/* @see test_is_registered */
	public function provider_is_registered(): Generator {
		yield 'registered' => [ [ 'foo' => 'bar', 'bar' => 'baz' ], true ];
		yield 'not registered' => [ [ 'boo' => 'bar', 'bar' => 'foo' ], false ];
	}

	/**
	 * @covers \Underpin\Abstracts\Registries\Registry::query
	 */
	public function test_can_query() {
		$mock = $this->getMockForAbstractClass( Registry::class );
		Helpers::set_protected_property( $mock, 'storage', [ 'foo' => 'bar' ] );

		$this->assertEquals( new List_Filter( [ 'foo' => 'bar' ] ), $mock->query() );
	}

}