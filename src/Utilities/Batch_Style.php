<?php
/**
 *
 *
 * @since
 * @package
 */


namespace Underpin\Utilities;


use Underpin\Abstracts\Style;
use Underpin\Abstracts\Underpin;
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
class Batch_Style extends Style {

	protected $handle = 'underpin_batch';

	public $description = 'Styles for batch tasks.';

	public $name = "Batch Task Runner Styles";

	public function __construct() {
		$this->src = underpin()->css_url() . 'batchStyle.min.css';
	}

}