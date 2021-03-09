<?php
/**
 * WordPress Option Abstraction
 *
 * @since   1.0.0
 * @package Lib\Core\Abstracts
 */


namespace Underpin\Factories;

use Underpin\Abstracts\Meta_Record_Type;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Option
 * WordPress Option Class
 *
 * @since   1.0.0
 * @package Lib\Core\Abstracts
 */
class User_Meta_Type extends Meta_Record_Type {
	protected $type = 'user';
}