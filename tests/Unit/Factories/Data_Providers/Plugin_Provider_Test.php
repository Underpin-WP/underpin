<?php

namespace Underpin\Tests\Unit\Factories\Data_Providers;


use Generator;
use Underpin\Factories\Data_Providers\Plugin_Provider;
use Underpin\Tests\Test_Case;
use Underpin\Tests\Traits\With_Getter_Tests;
use Underpin\Interfaces\Base;

class Plugin_Provider_Test extends Test_Case {

	use With_Getter_Tests;

	public function provider_can_get_fields(): Generator {
		yield 'plugin' => [ 'plugin', $this->createMock( Base::class ) ];
	}

	protected function get_instance(): object {
		return new Plugin_Provider( $this->createMock( Base::class ) );
	}

}