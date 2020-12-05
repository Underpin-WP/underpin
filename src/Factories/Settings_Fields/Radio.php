<?php
/**
 * Settings Radio Field
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
 * Class Radio
 *
 * @since 1.0.0
 * @package Underpin\Factories\Settings_Fields
 */
class Radio extends Settings_Field {

	/**
	 * @inheritDoc
	 */
	function get_field_type() {
		return 'radio';
	}

	/**
	 * @inheritDoc
	 */
	function sanitize( $value ) {
		return (string) $value;
	}
}