<?php
/**
 * Feature Extension Trait.
 *
 */

namespace Underpin\Interfaces;

interface Feature_Extension {

	/**
	 * Callback to do the actions to register whatever this class is intended to extend.
	 *
	 *
	 * @return void
	 */
	function do_actions(): void;
}