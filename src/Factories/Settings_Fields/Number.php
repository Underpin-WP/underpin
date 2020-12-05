<?php
/**
 * Settings Text Field
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
 * Class Number
 *
 * @since 1.0.0
 * @package Underpin\Factories\Settings_Fields
 */
class Number extends Settings_Field {

	/**
	 * @inheritDoc
	 */
	function get_field_type() {
		return 'number';
	}

	/**
	 * @inheritDoc
	 */
	function sanitize( $value ) {

		// If we have set the step value of the field so that it cannot be a decimal, cast as an int.
		if ( 1 === $this->get_field_param( 'step' ) ) {
			$value = (int) $value;
		} else {
			$value = (float) $value;
		}

		$min = $this->get_field_param( 'min' );
		$max = $this->get_field_param( 'max' );

		if ( ! is_wp_error( $min ) && $min > $value ) {
			$value = (int) $min;
		}

		if ( ! is_wp_error( $max ) && $max <= $value ) {
			$value = (int) $max;
		}

		return $value;
	}
}