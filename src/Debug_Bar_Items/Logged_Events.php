<?php
/**
 *
 *
 * @since
 * @package
 */


namespace Underpin\Debug_Bar_Items;


use Underpin\Abstracts\Debug_Bar_Section;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Logged_Events
 *
 *
 * @since
 * @package
 */
class Logged_Events extends Debug_Bar_Section {

	public $id = 'logged-events';
	public $title = 'Logged Events';
	public $subtitle = "Here's what was logged during this session";

	public function get_items() {
		return underpin()->logger()->get_request_events();
	}
}