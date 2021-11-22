<?php
/**
 * Feature Extension Trait.
 *
 * @since   1.0.0
 */

namespace Underpin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Feature_Extension {


	/**
	 * A human-readable description of this feature.
	 * This is used in debug logs to make it easier to understand why this exists.
	 *
	 * @var string
	 */
	public $description = '';

	/**
	 * A human-readable name for this feature.
	 * This is used in debug logs to make it easier to understand what this is.
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * Callback to do the actions to register whatever this class is intended to extend.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	abstract public function do_actions();
}