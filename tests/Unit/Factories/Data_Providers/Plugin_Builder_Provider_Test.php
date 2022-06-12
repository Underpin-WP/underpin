<?php

namespace Underpin\Tests\Unit\Factories\Data_Providers;


use Generator;
use Underpin\Factories\Data_Providers\Plugin_Builder_Provider;
use Underpin\Factories\Plugin_Builder;
use Underpin\Tests\Test_Case;
use Underpin\Tests\Traits\With_Getter_Tests;

class Plugin_Builder_Provider_Test extends Test_Case {
	use With_Getter_Tests;

	public function provider_can_get_fields(): Generator {
		yield 'builder' => [ 'builder', $this->createMock( Plugin_Builder::class ) ];
	}

	protected function get_instance(): object {
		return new Plugin_Builder_Provider( $this->createMock( Plugin_Builder::class ) );
	}

}