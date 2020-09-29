<?php
/**
 * Settings Text Field
 *
 * @since   1.0.0
 * @package Underpin\Factories\Settings_Fields
 */


namespace Underpin\Factories\Settings_Fields;

use Underpin\Abstracts\Settings_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Text
 *
 * @since   1.0.0
 * @package Underpin\Factories\Settings_Fields
 */
class Email extends Settings_Field {

	/**
	 * @inheritDoc
	 */
	function get_field_type() {
		return 'email';
	}

	/**
	 * @inheritDoc
	 */
	function sanitize( $value ) {

		$multiple = $this->get_field_param( 'multiple' );

		// If the email has the "multiple" property, sanitize each email.
		if ( ! is_wp_error( $multiple ) && true === $multiple ) {
			$emails = explode( ',', $value );

			foreach ( $emails as $key => $email ) {
				$email = trim( $email );
				if ( ! is_email( $email ) ) {
					unset( $emails[ $key ] );
				}
			}

			$value = implode( ',', $emails );
		} else {

			if ( ! is_email( $value ) ) {
				$value = '';
			}
		}

		return (string) $value;
	}
}