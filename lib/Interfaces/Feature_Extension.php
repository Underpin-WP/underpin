<?php
/**
 * Feature Extension Trait.
 *
 * @since   1.0.0
 */

namespace Underpin\Interfaces;

interface Feature_Extension {

	/**
	 * Callback to do the actions to register whatever this class is intended to extend.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function do_actions(): void;
}