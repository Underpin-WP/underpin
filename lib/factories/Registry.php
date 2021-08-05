<?php

namespace Underpin\Factories;


use Underpin\Traits\Instance_Setter;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Registry extends \Underpin\Abstracts\Registries\Registry {

	use Instance_Setter;

	protected $default_items = [];

	protected $validate_callback;

	public function __construct( $args ) {
		$registry_id = $args['registry_id'];
		$this->set_values( $args );
		parent::__construct( $registry_id );
	}

	protected function set_default_items() {
		foreach ( $this->default_items as $key => $value ) {
			$this->add( $key, $value );
		}

		unset( $this->default_items );
	}

	protected function validate_item( $key, $value ) {
		return $this->set_callable( $this->validate_callback, $key, $value );
	}

}