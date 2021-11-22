<?php
/**
 * Event Type Factory
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Factories;


use Underpin\Traits\Instance_Setter;
use Underpin\Abstracts\Event_Type;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Event_Type_Instance
 * Handles creating custom event types
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
class Event_Type_Instance extends Event_Type {
	use Instance_Setter;

	public function __construct( $args = [] ) {
		// Override default params.
		$this->set_values($args);

		parent::__construct();
	}

}