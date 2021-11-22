<?php

namespace Underpin\Factories\Observers;


use Exception;
use Underpin\Abstracts\Observer;
use Underpin\Abstracts\Storage;
use Underpin\Abstracts\Event_Type;
use Underpin\Factories\Log_Item;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Trigger_Exception extends Observer {

	protected $id          = 'php_throw_exception';
	public $name           = 'PHP throw exception';
	public $description    = 'Throws an exception when called.';

	public function update( $instance, Storage $args ) {
		/* @var $event Event_Type */
		$event = $args->event_type;

		/* @var $item Log_Item */
		$item = $args->item;

		$message = "$event->psr_level: $item->code - $item->message";

		throw new Exception( $message );
	}

}