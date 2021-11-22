<?php

namespace Underpin\Factories;


use Underpin\Abstracts\Middleware;
use Underpin\Abstracts\Event_Type;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Basic_Logger_Middleware extends Middleware {

	public function do_actions() {
		if ( $this->loader_item instanceof Event_Type ) {
			$this->loader_item->writers->add( 'basic_logger', '\Underpin\Factories\Basic_Logger' );
		} else {
			\Underpin\underpin()->logger()->log(
				'warning',
				'invalid_middleware_type',
				'Use_Basic_Logger was ran on middleware whose loader item is not an Event_Type instance',
				[ 'loader_item' => get_class( $this->loader_item ), 'expects' => 'Event_Type' ]
			);
		}
	}

}