<?php
/**
 * Widget Abstraction
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts;


use Underpin\Traits\Feature_Extension;
use Underpin\Traits\Underpin_Templates;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Widget
 * Abstraction layer for WP Widgets
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Widget extends \WP_Widget {
	use Underpin_Templates;

	public $description = '';

	/**
	 * Widget constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->widget_options['description'] = $this->description;
		parent::__construct( $this->id_base, $this->name, $this->widget_options );
	}

	/**
	 * @inheritDoc
	 */
	public function widget( $args, $instance ) {
		underpin()->logger()->log(
			'error',
			'widget_not_set',
			'The widget ' . $this->name . ' must extend extend the widget method.'
		);

		parent::widget( $args, $instance );
	}

	/**
	 * Retrieves a set of fields, set to the provided values.
	 * This method expects an array of Field objects.
	 *
	 * @since 1.0.0
	 *
	 * @param $instance
	 * @return array|\WP_Error Array of field classes, or WP_Error
	 */
	abstract public function get_fields( $instance );

	/**
	 * @inheritDoc
	 */
	public function form( $instance ) {
		$fields = $this->get_fields( $instance );
		// If something went wrong, log and return.
		if ( is_wp_error( $fields ) ) {
			underpin()->logger()->log_wp_error(
				'error',
				$fields
			);

			return;
		}

		// If everything went okay, output the form.
		echo $this->get_template( 'form', [
			'fields'      => $fields,
			'description' => $this->description,
			'id'          => $this->id,
		] );
	}

	/**
	 * @inheritDoc
	 */
	public function update( $new, $old ) {
		$fields = $this->get_fields( $old );

		// If something went wrong, log and return.
		if ( is_wp_error( $fields ) ) {
			underpin()->logger()->log_wp_error(
				'error',
				$fields
			);

			return $fields;
		}

		$errors   = new \WP_Error();
		$instance = [];
		foreach ( $fields as $field ) {

			// First, make sure it's a valid field
			if ( ! $field instanceof Settings_Field ) {
				$errors->add(
					'underpin_widget_not_settings_field',
					'The provided field is not an instance of Settings_Field',
					[ 'field' => $field ]
				);
			} elseif ( isset( $new[ $field->get_setting_key() ] ) ) {
				$new_value                             = $new[ $field->get_setting_key() ];
				$updated                               = $field->update_value( $new_value );
				$instance[ $field->get_setting_key() ] = $updated;
			}
		}

		if ( $errors->has_errors() ) {
			underpin()->logger()->log(
				'error',
				'failed_to_save_widget_settings',
				'Failed to save widget settings',
				[ 'ref' => $this->id_base, 'errors' => $errors ]
			);

			$instance = false;
		}

		return $instance;
	}


	/**
	 * @inheritDoc
	 **/
	protected function get_templates() {
		return [
			'form' => [
				'override_visibility' => 'private',
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function get_template_group() {
		return 'widget';
	}
}