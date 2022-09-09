<?php

namespace Underpin\Tests\Unit\Abstracts\Registries;

use Generator;
use Mockery;
use Underpin\Abstracts\Registries\Object_Registry;
use Underpin\Exceptions\Invalid_Registry_Item;
use Underpin\Factories\Log_Item;
use Underpin\Helpers\Object_Helper;
use Underpin\Interfaces\Feature_Extension;
use Underpin\Interfaces\With_Middleware;
use Underpin\Loaders\Underpin;
use Underpin\Tests\Helpers;
use Underpin\Tests\Test_Case;
use Underpin\WordPress\Abstracts\Registry;

class Object_Registry_Test extends Test_Case {

	/**
	 * @covers \Underpin\Abstracts\Registries\Object_Registry::_add
	 */
	public function test__add_converts_item_to_class() {
		$class    = $this->createPartialMock( Object_Registry::class, [ 'get_class' ] );
		$expected = (object) [ 'bar' => 'baz' ];
		Helpers::set_protected_property( $class, 'default_factory', 'foo' );

		$class->expects( $this->once() )->method( 'get_class' )->willReturn( $expected );

		Helpers::call_inaccessible_method( $class, '_add', 'foo', 'bar' );

		$this->assertSame( [ 'foo' => $expected ], $class->to_array() );
	}

	/**
	 * @return void
	 * @covers \Underpin\Abstracts\Registries\Object_Registry::add
	 * @throws \Underpin\Exceptions\Invalid_Registry_Item
	 * @throws \Underpin\Exceptions\Operation_Failed
	 */
	public function test_can_add(): void {
		$class = $this->createPartialMock( Object_Registry::class, [ '_add', 'validate_item', 'init_object' ] );
		$key   = 'key';
		$value = 'value';
		$class->expects( $this->once() )->method( '_add' )->with( $key, $value );
		$class->expects( $this->once() )->method( 'validate_item' )->willReturn( true );
		$class->expects( $this->once() )->method( 'init_object' );

		$this->assertSame( $class, $class->add( $key, $value ) );
	}

	/**
	 * @covers       \Underpin\Abstracts\Registries\Object_Registry::init_object
	 *
	 * @dataProvider provider_can_init_object
	 */
	public function test_can_init_object( bool $with_middleware, bool $feature_extension ) {
		$class = Mockery::mock( Object_Registry::class );

		$mocked_interfaces = [];
		if ( $with_middleware ) $mocked_interfaces[] = With_Middleware::class;
		if ( $feature_extension ) $mocked_interfaces[] = Feature_Extension::class;

		$instance = Mockery::mock( ...$mocked_interfaces )->makePartial();

		$class->expects( 'get' )->with( 'foo' )->andReturn( $instance );

		$instance->shouldReceive( 'do_middleware_actions' )->times( (int) $with_middleware );
		$instance->shouldReceive( 'do_actions' )->times( (int) $feature_extension );

		Helpers::call_inaccessible_method( $class, 'init_object', 'foo' );
	}

	/* @see test_can_init_object */
	public function provider_can_init_object(): Generator {
		yield 'with middleware trait' => [ true, false ];
		yield 'basic class' => [ false, false ];
		yield 'with feature extension' => [ false, true ];
		yield 'with trait and extension' => [ true, true ];
	}

	/**
	 * @covers \Underpin\Abstracts\Registries\Object_Registry::get_class
	 */
	public function test_get_class() {
		$this->assertEquals(
			Object_Helper::make_class( Underpin::class ),
			Helpers::call_inaccessible_method( $this->createMock( Object_Registry::class ), 'get_class', Underpin::class )
		);
	}


	/**
	 * @dataProvider provider_can_validate_item
	 */
	public function test_can_validate_item( bool $valid, string $key, mixed $value ) {
		$instance = $this->getMockForAbstractClass( Object_Registry::class );
		Helpers::set_protected_property( $instance, 'storage', [ 'foo' => 'bar' ] );
		Helpers::set_protected_property( $instance, 'abstraction_class', Registry::class );

		if ( ! $valid ) {
			$this->expectException( Invalid_Registry_Item::class );
		}

		$result = Helpers::call_inaccessible_method( $instance, 'validate_item', $key, $value );

		if ( $valid ) {
			$this->assertSame( true, $result );
		}
	}

	/* @see test_can_validate_item */
	public function provider_can_validate_item(): Generator {
		yield 'validation fails when item key is already set' => [ false, 'foo', Registry::class ];
		yield 'validation passes when item is instance of abstraction class' => [ true, 'bar', new \Underpin\Factories\Registry( fn () => false ) ];
		yield 'validation passes when item is subclass of abstraction class' => [ true, 'bar', Object_Registry::class ];
		yield 'validation passes when item is the same as the abstraction class' => [ true, 'bar', Registry::class ];
		yield 'item fails when item is not an instance of abstraction class' => [ false, 'bar', Log_Item::class ];
		yield 'item fails when item is null' => [ false, 'bar', null ];
	}

}