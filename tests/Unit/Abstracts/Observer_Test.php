<?php

namespace Underpin\Tests\Unit\Abstracts;


use Generator;
use Underpin\Abstracts\Observer;
use Underpin\Tests\Helpers;
use Underpin\Tests\Test_Case;
use Underpin\Tests\Traits\With_Getter_Tests;

class Observer_Test extends Test_Case {

	use With_Getter_Tests;

	protected function get_instance(): object {
		return $this->getMockForAbstractClass( originalClassName: Observer::class, callOriginalConstructor: false );
	}

	public function provider_can_get_fields(): Generator {
		yield 'id' => [ 'id', 'foo' ];
		yield 'priority' => [ 'priority', 20 ];
		yield 'dependencies' => [ 'dependencies', [ 'this', 'that' ] ];
	}

	/**
	 * @covers \Underpin\Abstracts\Observer::add_dependency
	 */
	public function test_can_add_dependency(): void {
		$instance     = $this->get_instance()->add_dependency( "bar" );
		$dependencies = Helpers::get_protected_property( $instance, 'dependencies' );

		$this->assertSame( [ 'bar' ], $dependencies->getValue( $instance ) );
	}

	/**
	 * @covers \Underpin\Abstracts\Observer::remove_dependency
	 */
	public function test_can_remove_dependency(): void {
		$instance = $this->get_instance();
		Helpers::set_protected_property( $instance, 'dependencies', [ 'bar', 'foo' ] );

		$instance->remove_dependency( 'foo' );

		$this->assertSame( [ 'bar' ], Helpers::get_protected_property( $instance, 'dependencies' )->getValue( $instance ) );
	}

}