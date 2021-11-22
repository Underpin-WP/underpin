<?php

namespace Underpin\Factories;


use Underpin\Abstracts\Middleware;
use Underpin\Abstracts\Event_Type;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Include_Backtrace_Middleware extends Middleware {

	private static $filter_added = false;

	public function add_backtrace( $data, $code, $message, Event_Type $instance ) {
		if ( $instance->has_middleware( get_class( $this ) ) ) {
			$data['backtrace'] = wp_debug_backtrace_summary( null, 3, false );
		}
		return $data;
	}

	public function do_actions() {
		if ( $this->loader_item instanceof Event_Type ) {
			if ( false === self::$filter_added ) {
				add_filter( 'underpin/logger/additional_logged_data', [ $this, 'add_backtrace' ], 10, 4 );
				self::$filter_added = true;
			}
		} else {
			\Underpin\underpin()->logger()->log(
				'warning',
				'invalid_middleware_type',
				'Include_Backtrace_Middleware was ran on middleware whose loader item is not an Event_Type instance',
				[ 'loader_item' => get_class( $this->loader_item ), 'expects' => 'Event_Type' ]
			);
		}
	}

}