<?php
/**
 * Single Log item instance.
 * Handles output and formatting for log item.
 *
 * @since   1.0.0
 * @package Underpin\Factories
 */


namespace Underpin\Factories;


use Underpin\Abstracts\Event_Type;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Log_Item
 *
 * @since   1.0.0
 * @package Underpin\Factories
 */
class Log_Item {

	/**
	 * Event code.
	 *
	 * @since 1.0.0
	 *
	 * @var string Event code
	 */
	public $code;

	/**
	 * Message
	 *
	 * @since 1.0.0
	 *
	 * @var string Message.
	 */
	public $message;

	/**
	 * Ref.
	 *
	 * @since 1.0.0
	 *
	 * @var mixed Reference. Usually an id or something related to this item.
	 */
	public $ref;

	/**
	 * Context.
	 *
	 * @since 1.0.0
	 *
	 * @var mixed Reference Context. Usually a slug that offers context to what the ID is.
	 */
	public $context;

	/**
	 * Event data.
	 *
	 * @since 1.0.0
	 *
	 * @var array Data.
	 */
	public $data;

	/**
	 * Event type.
	 *
	 * @since 1.0.0
	 *
	 * @var string Event code
	 */
	public $type;


	/**
	 * Event type object.
	 *
	 * @since 1.0.0
	 *
	 * @var Event_Type Event
	 */
	protected $event_type;

	/**
	 * Log Item Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type    Event log type
	 * @param string $code    The event code to use.
	 * @param string $message The message to log.
	 * @param array  $data    Arbitrary data associated with this event message.
	 */
	public function __construct( $type, $code, $message, $data = array() ) {
		if ( $type instanceof Event_Type ) {
			$this->type       = $type->type;
			$this->event_type = $type;
		} else {
			$this->type = $type;
		}
		$this->code    = $code;
		$this->message = $message;
		$this->data    = $data;

		if ( isset( $this->data['context'] ) ) {
			$this->context = $this->data['context'];
			unset( $this->data['context'] );
		}

		if ( isset( $this->data['ref'] ) ) {
			$this->ref = $this->data['ref'];
			unset( $this->data['ref'] );
		}

	}

	/**
	 * Formats the event log.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function format() {
		$additional_data = [];

		if ( ! empty( $this->ref ) ) {
			$additional_data['ref']     = $this->ref;
			$additional_data['context'] = $this->context;
		}

		if ( $this->event_type instanceof Event_Type ) {
			$additional_data['group']     = $this->event_type->group;
			$additional_data['volume']    = $this->event_type->volume;
			$additional_data['psr_level'] = $this->event_type->psr_level;
		}

		$log_message = 'Underpin ' . $this->type . ' event' . ': ' . $this->code . ' - ' . $this->message;


		$data = array_merge(
			$additional_data,
			$this->data
		);

		if ( ! empty( $data ) ) {
			$log_message .= "\n data:" . var_export( (object) $data, true );
		}

		return date( 'm/d/Y H:i:s' ) . ': ' . $log_message;
	}

	/**
	 * Converts this log item to a WP Error object.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error
	 */
	public function error() {
		return new WP_Error( $this->code, $this->message, $this->data );
	}

}