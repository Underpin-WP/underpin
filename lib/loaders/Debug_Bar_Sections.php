<?php
/**
 * Debug Bar Sections
 *
 * @since
 * @package
 */


namespace Underpin\Loaders;


use Underpin\Abstracts\Underpin;
use Underpin\Abstracts\Registries\Loader_Registry;
use Underpin\Traits\Feature_Extension;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Debug_Bar_Sections
 *
 *
 * @since
 * @package
 */
class Debug_Bar_Sections extends Loader_Registry {

	protected $abstraction_class = 'Underpin\Abstracts\Debug_Bar_Section';

	protected function set_default_items() {
		$this->add( 'logged-events', 'Underpin\Debug_Bar_Items\Logged_Events' );
		$this->add( 'registered-items', 'Underpin\Debug_Bar_Items\Registered_Items' );
	}

}