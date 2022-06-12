<?php

namespace Underpin\Tests\Unit\Factories\Observers;


use Generator;
use Underpin\Abstracts\Registries\Object_Registry;
use Underpin\Factories\Data_Providers\Plugin_Builder_Provider;
use Underpin\Factories\Observers\Loader;
use Underpin\Interfaces\Plugin_Builder;
use Underpin\Tests\Test_Case;

class Loader_Test extends Test_Case {

	public function test_can_update() {
		$loader      = new Loader( 'test', ['foo' => 'bar'] );
		$provider    = $this->createPartialMock( Plugin_Builder_Provider::class, [ 'get_builder' ] );
		$builder     = $this->getMockForAbstractClass( Plugin_Builder::class );
		$loaders     = $this->createPartialMock( Object_Registry::class, [ 'add' ] );

		$loaders->expects($this->once())->method('add')->with('test', ['foo' => 'bar']);

		$builder->expects( $this->once() )->method( 'loaders' )->willReturn($loaders);
		$provider->expects( $this->once() )->method( 'get_builder' )->willReturn( $builder );

		$loader->update( new class {

		}, $provider );
	}

}