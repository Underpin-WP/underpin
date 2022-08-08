<?php
/**
 * Single Log item instance.
 * Handles output and formatting for log item.
 *
 * @since   1.0.0
 * @package Underpin\Factories
 */


namespace Underpin\Factories;


/**
 * Class Log_Item
 *
 * @since   1.0.0
 * @package Underpin\Factories
 */
class Log_Item implements \Underpin\Interfaces\Log_Item {

	/**
	 * Event type.
	 *
	 * @since 1.0.0
	 *
	 * @var string Event code
	 */
	public readonly string $type;


	/**
	 * Event type object.
	 *
	 * @since 1.0.0
	 *
	 * @var Event_Type|null Event
	 */
	protected ?Event_Type $event_type = null;

	/**
	 * Log Item Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string          $code    The event code to use.
	 * @param string          $message The message to log.
	 * @param string          $context
	 * @param int|string|null $ref
	 * @param array           $data    Arbitrary data associated with this event message.
	 */
	public function __construct(
		/**
		 * Event code.
		 *
		 * @since 1.0.0
		 *
		 * @var string Event code
		 */
		public readonly string          $code,
		/**
		 * Message
		 *
		 * @since 1.0.0
		 *
		 * @var string Message.
		 */
		public readonly string          $message,

		/**
		 * Context.
		 *
		 * @since 1.0.0
		 *
		 * @var mixed Reference Context. Usually a slug that offers context to what the ID is.
		 */
		public readonly string          $context = '',

		/**
		 * Ref.
		 *
		 * @since 1.0.0
		 *
		 * @var mixed Reference. Usually an id or something related to this item.
		 */
		public readonly int|string|null $ref = null,
		public readonly array           $data = array()
	) {
	}

	public function set_type( Event_Type $type ): static {
		$this->event_type = $type;

		return $this;
	}

	/**
	 * Formats the event log.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function __toString(): string {
		$additional_data = [];

		if ( ! empty( $this->ref ) ) {
			$additional_data['ref']     = $this->ref;
			$additional_data['context'] = $this->context;
		}

		if ( $this->event_type instanceof Event_Type ) {
			$additional_data['group']     = $this->event_type->get_group();
			$additional_data['volume']    = $this->event_type->get_volume();
			$additional_data['psr_level'] = $this->event_type->get_psr_level();
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

}