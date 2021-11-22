<?php
/**
 * Template Loader Trait
 * Handles template loading and template inheritance.
 *
 * @since   1.0.0
 * @package Underpin\Traits
 */

namespace Underpin\Traits;

use Underpin\Abstracts\Underpin;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Underpin-specific Template Trait.
 * Creates templates based off of the location of Underpin.
 *
 * @since   1.0.0
 * @package underpin\traits
 */
trait Underpin_Templates {
	use Templates;

	protected function get_template_root_path() {
		return underpin()->template_dir();
	}

	protected function get_override_dir() {
		return 'underpin/';
	}

}