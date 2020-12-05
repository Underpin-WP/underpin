<?php
/**
 *
 *
 * @since
 * @package
 */


namespace Underpin\Abstracts;

use Underpin\Loaders\Logger;
use Underpin\Traits\Underpin_Templates;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Section
 *
 * @since 1.0.0
 *
 * @since
 * @package
 */
abstract class Admin_Section {
	use Underpin_Templates;

	/**
	 * The section ID
	 *
	 * @var string
	 */
	protected $id = '';

	public $name = '';

	public $type = '';

	/**
	 * List of fields that were successfully saved in this request.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $saved_fields = [];

	/**
	 * The nonce action used to validate when interfacing with this page.
	 *
	 * @since 1.0.0
	 *
	 * @var string the nonce action.
	 */
	protected $nonce_action;

	/**
	 * The key to use when updating options.
	 *
	 * @since 1.0.0
	 *
	 * @var string The options key
	 */
	protected $options_key = false;

	/**
	 * Section fields
	 * Created using fields() method.
	 *
	 * @since 1.0.0
	 *
	 * @var
	 */
	private $fields;

	/**
	 * Determines if this request is valid for saving.
	 * Generally, this is passed down from the admin page.
	 *
	 * @var
	 */
	private $valid_request;

	/**
	 * Admin_Page constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args List of arguments used to create this menu page.
	 */
	public function __construct() {
		$this->options_key = false === $this->options_key ? $this->id . '_settings' : $this->options_key;
	}

	/**
	 * Retrieves the fields for this section.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error
	 */
	public function get_fields() {
		if ( ! isset( $this->fields ) ) {
			$this->fields = $this->fields();
		}

		return $this->fields;
	}


	abstract protected function fields();

	/**
	 * Updates a settings field.
	 *
	 * @since 1.1.0
	 *
	 * @param Settings_Field $field
	 * @return mixed|WP_Error
	 */
	public function update_field( Settings_Field $field ) {
		// Get the field name.
		$field_name = $field->get_field_param( 'name' );

		// If the field type is a checkbox, update value based on if the field was included.
		if ( 'checkbox' === $field->get_field_type() ) {
			$checked = isset( $_POST[ $field_name ] );
			$updated = $field->update_value( $checked );

			// Otherwise, Update the value if the field is provided.
		} elseif ( isset( $_POST[ $field_name ] ) && $field->sanitize( $_POST[ $field_name ] ) !== $field->value ) {
			$updated = $field->update_value( $_POST[ $field_name ] );
		}

		if ( ! isset( $updated ) ) {
			return new WP_Error(
				'field_not_changed',
				'The field was not updated because the value is the same as the current field value',
				[
					'field_name' => $field_name,
					'value'      => $_POST[ $field_name ],
				]
			);
		}

		return $updated;
	}

	/**
	 * Saves a single field to the database.
	 *
	 * @since 1.0.0
	 *
	 * @param Settings_Field $field The field to save.
	 * @return true|WP_Error true if the field saved, WP_Error otherwise.
	 */
	public function save_field( Settings_Field $field ) {
		$options_key = $this->options_key;
		$updated     = $this->update_field( $field );

		// Bail early if this field was already set.
		if ( is_wp_error( $updated ) ) {
			Underpin()->logger()->log_wp_error( 'notice', $updated );

			return $updated;
		}

		// Update the option.
		$updated = Underpin()->options()->get( $options_key )->update( $updated, $field->get_setting_key() );

		if ( true !== $updated ) {
			$updated = underpin()->logger()->log_as_error(
				'error',
				'update_request_settings_failed_to_update',
				'A setting failed to update.',
				[ 'setting' => $options_key, 'updated_return' => $updated ]
			);
		} else {
			underpin()->logger()->log(
				'notice',
				'update_request_settings_succeeded_to_update',
				'A setting updated successfully.',
				[ 'setting' => $options_key ]
			);
		}

		return $updated;
	}

	/**
	 * Validates this request.
	 *
	 * @since 1.0.0
	 *
	 * @return true|WP_Error True if request is validated, otherwise WP_Error containing what went wrong.
	 */
	public function validate_request() {
		$errors = new \WP_Error();

		foreach ( $this->get_fields() as $field ) {
			if ( ! $field instanceof Settings_Field ) {
				$errors->add(
					'field_invalid',
					'The provided field is not an instance of a settings field',
					[ 'field' => $field ]
				);
			}
		}

		return $errors->has_errors() ? $errors : true;
	}

	/**
	 * Action to save all fields.
	 *
	 * @since 1.0.0
	 *
	 * @return true|WP_Error True if all fields were saved, WP_Error containing errors if not.
	 */
	public function save() {
		$errors = new WP_Error;

		foreach ( $this->get_fields() as $field ) {

			$saved = $this->save_field( $field );

			if ( is_wp_error( $saved ) || ! $field instanceof Settings_Field ) {
				if ( 'field_not_changed' !== $saved->get_error_code() ) {
					Logger::extract( $errors, $saved );
				}
			} else {
				$this->saved_fields[ $field->get_field_param( 'name' ) ] = $field;
			}
		}

		if ( $errors->has_errors() ) {
			underpin()->logger()->log(
				'error',
				'failed_to_save_settings',
				'some settings failed to save',
				[ 'errors' => $errors ]
			);
		}

		return $errors->has_errors() ? true : $errors;
	}

	/**
	 * Fetches the valid templates and their visibility.
	 *
	 * override_visibility can be either "theme", "plugin", "public" or "private".
	 *  theme   - sets the template to only be override-able by a parent, or child theme.
	 *  plugin  - sets the template to only be override-able by another plugin.
	 *  public  - sets the template to be override-able anywhere.
	 *  private - sets the template to be non override-able.
	 *
	 * @since 1.0.0
	 *
	 * @return array of template properties keyed by the template name
	 */
	public function get_templates() {
		return [
			'admin-section' => [
				'override_visibility' => 'private',
			],
		];
	}

	/**
	 * Fetches the template group name.
	 *
	 * @since 1.0.0
	 *
	 * @return string The template group name
	 */
	protected function get_template_group() {
		return 'admin';
	}
	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new WP_error( 'batch_task_param_not_set', 'The batch task key ' . $key . ' could not be found.' );
		}
	}

}