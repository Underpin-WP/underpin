<?php
/**
 * Role Abstraction
 *
 * @since   1.1.1
 * @package Underpin\Abstracts
 */

namespace Underpin\Abstracts;

use Underpin\Traits\Feature_Extension;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Role {
	use Feature_Extension;

	/**
	 * id
	 * String that identifies this role.
	 *
	 * @var string Role
	 */
	protected $id = '';

	/**
	 * capabilities
	 * List of capabilities keyed by the capability name, e.g. array( 'edit_posts' => true, 'delete_posts' => false ).
	 *
	 * @var array
	 */
	protected $capabilities = array();

	/**
	 * @inheritDoc
	 */
	public function do_actions() {
		// Add the role.
		add_role( $this->id, $this->name, $this->capabilities );
	}

}