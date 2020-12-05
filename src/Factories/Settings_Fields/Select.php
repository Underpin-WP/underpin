<?php
/**
 * Settings Select Field
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
 * Class Select
 *
 * @since 1.0.0
 * @package Underpin\Factories\Settings_Fields
 */
class Select extends Settings_Field {

	/**
	 * @inheritDoc
	 */
	function get_field_type() {
		return 'select';
	}

	/**
	 * @inheritDoc
	 */
	function sanitize( $value ) {
		return (string) $value;
	}
}