<?php
/**
 * Admin Page abstraction
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts;

use Underpin\Loaders\Logger;
use Underpin\Traits\Feature_Extension;
use Underpin\Traits\Underpin_Templates;
use WP_Error;
use function Underpin\Underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Page
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Admin_Page extends Admin_Sub_Menu {
	use Underpin_Templates;
	use Feature_Extension;

	/**
	 * List of sections.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $sections = [];

	/**
	 * The nonce action used to validate when interfacing with this page.
	 *
	 * @since 1.0.0
	 *
	 * @var string the nonce action.
	 */
	protected $nonce_action;

	/**
	 * Determines how this settings page will be laid out.
	 * This can be set to "single" or "tabbed".
	 * If it's set to "single", all sections will be put on a single settings page.
	 * If it is set to "tabbed", each section will be placed in its own tab.
	 *
	 * @since 1.0.0
	 *
	 * @var string The layout type. Either "single", or "tabs"
	 */
	protected $layout = 'single';

	/**
	 * Admin_Page constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args List of arguments used to create this menu page.
	 */
	public function __construct() {
		parent::__construct();
		$this->nonce_action = $this->menu_slug . '_nonce';
	}

	/**
	 * @inheritDoc
	 */
	public function do_actions() {
		parent::do_actions();
		add_action( 'admin_init', [ $this, 'handle_update_request' ], 99 );
	}

	/**
	 * Retrieves the specified section.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The section to retrieve. If left blank, this will automatically retrieve from GET.
	 * @return Admin_Section|WP_Error
	 */
	public function section( $key = '' ) {

		if ( '' === $key ) {
			$key = $this->get_current_section_key();
		}

		if ( isset( $this->sections[ $key ] ) ) {
			if ( is_string( $this->sections[ $key ] ) ) {
				$this->sections[ $key ] = new $this->sections[$key];
			}

			if ( $this->sections[ $key ] instanceof Admin_Section ) {
				return $this->sections[ $key ];
			}
		}

		return Underpin()->logger()->log_as_error(
			'error',
			'no_admin_section_found',
			'No valid section could be found',
			[ 'sections' => $this->sections, 'admin_page' => $this->parent_menu ]
		);
	}

	/**
	 * Validates a submitted request
	 *
	 * @since 1.1.0
	 *
	 * @return bool|WP_Error
	 */
	public function validate_request() {
		$errors = new \WP_Error();

		// If this is not an admin page, bail
		if ( ! is_admin() ) {
			$errors->add(
				'update_request_settings_not_admin',
				__( 'An update request attempted to run outside of the admin area.' ),
				[ 'page' => $_SERVER['REQUEST_URI'] ]
			);
		}

		// If this is not the correct settings page, bail
		if ( ! $this->is_admin_page() ) {
			$errors->add(
				'update_request_settings_invalid_settings_page',
				__( 'An update request attempted to run outside of the specified settings settings page.' ),
				[ 'actual_page' => isset( $_GET['page'] ) ? $_GET['page'] : '', 'expected_page' => $this->menu_slug ]
			);
		}

		// If we don't have a nonce, bail.
		if ( ! isset( $_POST['underpin_nonce'] ) ) {
			$errors->add(
				'update_request_settings_no_nonce',
				__( 'An update request attempted to run without a nonce.' )
			);
		}

		// If the current user can't edit these options, bail
		if ( true !== current_user_can( $this->capability ) ) {
			$errors->add(
				'update_request_settings_invalid_permissions',
				__( 'An update request attempted to run without the privileges.' ),
				[ 'user' => get_current_user_id() ]
			);
		}

		// If the nonce is invalid, bail
		if ( isset( $_POST['underpin_nonce'] ) && 1 !== wp_verify_nonce( $_POST['underpin_nonce'], $this->nonce_action ) ) {
			$errors->add(
				'update_request_settings_invalid_nonce',
				__( 'An update requested attempted to run with an invalid nonce.' )
			);
		}

		return $errors->has_errors() ? $errors : true;
	}

	/**
	 * Callback to handle update requests from the options screen.
	 *
	 * @since 1.0.0
	 *
	 * @return true|WP_Error True if saved successfully, otherwise WP_Error.
	 */
	public function handle_update_request() {
		$page_valid    = $this->validate_request();
		$section_valid = $this->section()->validate_request();
		$errors        = Underpin()->logger()->gather_errors( $page_valid, $section_valid );


		if ( $errors->has_errors() ) {
			return $errors;
		}

		if ( 'single' === $this->layout ) {
			$errors = new \WP_Error();

			foreach ( array_keys( $this->sections ) as $section ) {
				Logger::extract( $errors, $this->section( $section )->save() );
			}

			if ( $errors->has_errors() ) {
				return $errors;
			} else {
				return true;
			}
		} else {
			return $this->section()->save();
		}
	}


	/**
	 * Retrieves the URL of the current section.
	 *
	 * @since 1.0.0
	 *
	 * @param string $section The name of the section.
	 * @return string a URL of the specified section of this settings page.
	 */
	public function get_section_url( $section ) {

		$url = get_admin_url( null, 'admin.php' );
		$url .= '?page=' . $this->menu_slug;

		if ( isset( $this->sections[ $section ] ) ) {
			$url .= '&section=' . $section;
		}

		return $url;
	}

	/**
	 * Retrieves the name of the current section.
	 *
	 * @since 1.0.0
	 *
	 * @return string The current section name, or an empty string.
	 */
	public function get_current_section_key() {
		if ( isset( $_GET['section'] ) && isset( $this->sections[ $_GET['section'] ] ) ) {
			return $_GET['section'];
		}

		$section_names = array_keys( $this->sections );

		if ( ! empty( $section_names ) ) {
			return $section_names[0];
		}

		return '';
	}

	/**
	 * Callback function to render the actual settings content.
	 *
	 * @since 1.0.0
	 */
	public function render_callback() {

		$template = $this->get_template( 'admin', [
			'title'        => $this->page_title,
			'section'      => $this->get_current_section_key(),
			'sections'     => $this->sections,
			'menu_slug'    => $this->menu_slug,
			'nonce_action' => $this->nonce_action,
		] );
		if ( ! is_wp_error( $template ) ) {
			echo $template;
		}
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
			'admin'         => [
				'override_visibility' => 'private',
			],
			'admin-heading' => [
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
		return 'admin/layouts/' . $this->layout;
	}

	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new WP_error( 'batch_task_param_not_set', 'The batch task key ' . $key . ' could not be found.' );
		}
	}

}