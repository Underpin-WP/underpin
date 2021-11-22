<?php

namespace Underpin\Factories\Observers;


use Exception;
use Underpin\Abstracts\Observer;
use Underpin\Abstracts\Storage;
use Underpin_Logger\Factories\Log_Item;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Trigger_Notice extends Observer {

	protected $id          = 'php_trigger_notice';
	public    $name        = 'PHP Trigger Notice';
	public    $description = 'Triggers a notice when called.';

	public function __construct( $id, $level = E_USER_NOTICE ) {
		$this->level = $level;
		parent::__construct( $id );
	}

	public function update( $instance, Storage $args ) {
		/* @var $item Log_Item */
		$item = $args->item;

		$message = "$item->code - $item->message";

		trigger_error( $message, $this->level );
	}

}