<?php
/**
 * Script for batch tasks
 *
 * @since   1.0.0
 * @package Underpin\Utilitites
 */


namespace Underpin\Utilities;


use Underpin\Abstracts\Underpin;
use Underpin\Abstracts\Script;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Batch_Script
 * Script for batch tasks
 *
 * @since   1.0.0
 * @package Underpin\Utilitites
 */
class Batch_Script extends Script {

	protected $handle = 'underpin_batch';

	protected $deps = [ 'jquery' ];

	public $description = 'Script that handles batch tasks.';

	public $name = "Batch Task Runner Script";

	protected $in_footer = true;

	public function __construct() {
		$this->src = underpin()->js_url() . 'batch.min.js';
		$this->ver = underpin()->version();
		$this->set_param( 'ajaxUrl', admin_url( 'admin-ajax.php' ) );
		parent::__construct();
	}

}