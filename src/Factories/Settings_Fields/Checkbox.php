<?php
/**
 * Settings Checkbox Field.
 *
 * @since 1.0.0
 * @package Underpin\Factories\Settings_Fields
 */

namespace Underpin\Factories\Settings_Fields;

use Underpin\Abstracts\Settings_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Checkbox
 *
 * @since 1.0.0
 * @package Underpin\Factories\Settings_Fields
 */
class Checkbox extends Settings_Field {

	/**
	 * @inheritDoc
	 */
	function get_field_type() {
		return 'checkbox';
	}

	/**
	 * @inheritDoc
	 */
	function sanitize( $value ) {
		return (boolean) $value;
	}

}