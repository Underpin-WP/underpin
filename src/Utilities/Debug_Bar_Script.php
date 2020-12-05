<?php
/**
 *
 *
 * @since
 * @package
 */


namespace Underpin\Utilities;

use Underpin\Abstracts\Script;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Debug_Bar
 *
 *
 * @since
 * @package
 */
class Debug_Bar_Script extends Script {

	protected $handle = 'underpin_debug';

	protected $deps = [ 'jquery' ];

	protected $in_footer = true;

	public $description = 'Script that handles the debug bar interface.';

	public $name = "Debug Bar Script";

	public function __construct() {
		$this->src = underpin()->js_url() . 'debug.min.js';
		parent::__construct();
	}

}