<?php


namespace Underpin\Factories;


use Underpin\Abstracts\Storage;
use Underpin\Traits\Instance_Setter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Observer extends \Underpin\Abstracts\Observer {

	use Instance_Setter;

	protected $update;

	public function __construct( $id, $args ) {
		$this->set_values( $args );
		parent::__construct( $id );
	}

	public function update( $instance, Storage $args ) {
		$this->set_callable( $this->update, $instance, $args );
	}

}