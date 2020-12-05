<?php
/**
 *
 *
 * @since
 * @package
 */


namespace Underpin\Debug_Bar_Items;


use Underpin\Abstracts\Debug_Bar_Section;
use Underpin\Abstracts\Underpin;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Registered_Items
 *
 *
 * @since
 * @package
 */
class Registered_Items extends Debug_Bar_Section {

	public $id = 'registered-items';
	public $title = 'Registered Items';
	public $subtitle = "Here's what items were registered during this session.";

	public function get_items() {
		return Underpin::export();
	}
}