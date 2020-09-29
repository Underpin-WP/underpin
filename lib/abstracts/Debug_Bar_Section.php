<?php
/**
 * Debug Bar Section
 *
 * @since   1.0.0
 * @package Underpin\Factories
 */

namespace Underpin\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Debug_Bar_Section
 * Class Debug Bar Section
 *
 * @since   1.0.0
 * @package Underpin\Factories
 */
abstract class Debug_Bar_Section {

	/**
	 * Subtitle to display with this section.
	 *
	 * @var string
	 */
	public $subtitle;

	/**
	 * Title to display with this section.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Unique identifier for this section.
	 *
	 * @var string
	 */
	public $id;

	abstract public function get_items();
}