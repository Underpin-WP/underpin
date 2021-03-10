<?php
/**
 * WordPress Option Abstraction
 *
 * @since   1.0.0
 * @package Lib\Core\Abstracts
 */


namespace Underpin\Factories;

use Underpin\Abstracts\Meta_Record_Type;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Option
 * WordPress Option Class
 *
 * @since   1.0.0
 * @package Lib\Core\Abstracts
 */
class Post_Meta_Type extends Meta_Record_Type {

	protected $type = 'post';

	/**
	 * @inheritDoc
	 */
	public function update( $object_id, $value, $prev_value = '' ) {
		return update_post_meta( $object_id, $value, $prev_value );
	}

	/**
	 * @inheritDoc
	 */
	public function delete( $object_id, $value = '' ) {
		return delete_post_meta( $object_id, $this->key, $value );
	}

	/**
	 * @inheritDoc
	 */
	public function add( $object_id, $unique = false ) {
		return add_post_meta( $object_id, $this->key, $this->default_value, $unique );
	}

}