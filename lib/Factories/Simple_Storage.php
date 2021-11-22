<?php

namespace Underpin\Factories;


use Underpin\Traits\Instance_Setter;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Simple_Storage extends \Underpin\Abstracts\Storage {
	use Instance_Setter;

	public function __construct( array $args ) {
		$this->params = $args;
	}

}