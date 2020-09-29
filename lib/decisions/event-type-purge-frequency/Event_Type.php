<?php
/**
 * Event Type Frequency Decision
 *
 * @since   1.0.0
 * @package Underpin\Decisions\Event_Type_Purge_Frequency
 */


namespace Underpin\Decisions\Event_Type_Purge_Frequency;


use Underpin\Abstracts\Decision;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Event_Type
 * Class Event Type Frequency
 *
 * @since   1.0.0
 * @package Underpin\Decisions\Event_Type_Purge_Frequency
 */
class Event_Type extends Decision {

	public $id = 'event_type';
	public $name = 'Event Type Frequency.';
	public $description = 'Default. Frequency Specified in Event Type';
	public $priority = 1000;

	public function is_valid( $params = [] ) {
		return true;
	}

	public function valid_actions( $params = [] ) {
		return $params['event_type']->purge_frequency;
	}
}