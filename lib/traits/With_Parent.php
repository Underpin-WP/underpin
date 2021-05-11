<?php

namespace Underpin\Traits;

use Underpin\Abstracts\Underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait With_Parent {

	protected $parent_id;

	public function parent() {
		return Underpin::get_by_id( $this->parent_id );
	}

}