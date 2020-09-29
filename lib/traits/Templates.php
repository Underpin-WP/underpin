<?php
/**
 * Template Loader Trait
 * Handles template loading and template inheritance.
 *
 * @since   1.0.0
 * @package Underpin\Traits
 */

namespace Underpin\Traits;

use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait Templates
 *
 * @since   1.0.0
 * @package underpin\traits
 */
trait Templates {

	/**
	 * Params
	 *
	 * @since 1.0.0
	 *
	 * @var array of parameter value arrays keyed by their parameter names.
	 */
	private $params = [];

	/**
	 * Visibility types
	 *
	 * @since 1.0.0
	 *
	 * @var array of valid visibility types.
	 */
	private $visibility_types = [ 'theme', 'plugin', 'public', 'private' ];

	/**
	 * Depth
	 *
	 * @since 1.0.0
	 *
	 * @var int The current depth of this instance
	 */
	private $depth = 0;

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
	public abstract function get_templates();

	/**
	 * Fetches the template group name. This determines the sub-directory for the templates.
	 *
	 * @since 1.0.0
	 *
	 * @return string The template group name
	 */
	protected abstract function get_template_group();

	/**
	 * Retrieves the template group's path. This determines where templates will be searched for within this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return string The full path to the template root directory.
	 */
	protected abstract function get_template_root_path();

	/**
	 * Gets the default template argument for the class.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of default template args.
	 */
	protected function get_template_arg_defaults() {
		return [
			'override_visibility' => 'private',
			'visible_in_api'      => false,
		];
	}

	/**
	 * Gets the specified template, if it is valid.
	 *
	 * @since 1.0.0
	 *
	 * @param $template_name string The template name to get.
	 * @param $params        array of param values that can be used in the template via get_param().
	 * @return string|WP_Error The template contents, or a WP_Error explaining why the template failed to load.
	 */
	public function get_template( $template_name, array $params = [] ) {

		if ( $this->is_valid_template( $template_name ) ) {

			if ( $this->template_file_exists( $template_name ) ) {

				$template = $this->include_template( $template_name, $params );
			} else {
				$template_path = $this->get_template_path( $template_name );
				$template      = underpin()->logger()->log_as_error(
					'error',
					'template_file_does_not_exist',
					__( "Template $template_name was not loaded because the file located at $template_path does not exist.", 'underpin' )
				);

				/**
				 * Fires just after the template loader determines that the template file does not exist.
				 */
				do_action( 'underpin/templates/invalid_template_file_doesnt_exist', $template_name, $params, $template_path, $template );
			}
		} else {
			$class    = __CLASS__;
			$template = underpin()->logger()->log_as_error(
				'error',
				'underpin_invalid_template',
				__( "Template $template_name was not loaded because it is not in the list of use-able templates for $class", 'underpin' )
			);

			/**
			 * Fires just after the template loader determines that the template is not in the current class template schema.
			 */
			do_action( 'underpin/templates/invalid_template_not_in_schema', $template_name, $params, $template );
		}

		return $template;
	}

	/**
	 * Gets the current template depth.
	 *
	 * The template depth goes up each time a template is loaded within the base template. This is used internally to
	 * determine which params should be loaded-in, but it can also be useful when recursively loading in a template.
	 *
	 * @since 1.0.0
	 *
	 * @return int The current template depth.
	 */
	public function get_depth() {
		return $this->depth;
	}

	/**
	 * Get the value of the specified param, if it exists.
	 *
	 * Params are passed into a template via the params argument of get_template.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $param   The param to load.
	 * @param mixed $default (optional) The default value of the param, if it does not exist.
	 * @return mixed The parameter value, if it exists. Otherwise, this will use the default value.
	 */
	public function get_param( $param, $default = false ) {
		if ( isset( $this->params[ $this->depth ] ) && isset( $this->params[ $this->depth ][ $param ] ) ) {
			$param = $this->params[ $this->depth ][ $param ];
		} else {
			$param = $default;
		}

		return $param;
	}

	/**
	 * Retrieves all of the params for the current template.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of params for the current template
	 */
	public function get_params() {
		if ( isset( $this->params[ $this->depth ] ) ) {
			return $this->params[ $this->depth ];
		}

		return [];
	}

	/**
	 * Gets the template directory based on the template group.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_template_directory() {
		$template_group     = $this->get_template_group();
		$template_directory = trailingslashit( $this->get_template_root_path() ) . $template_group;

		return $template_directory;
	}

	/**
	 * Gets the visibility of the current template
	 *
	 * @since 1.0.0
	 *
	 * @param string $template_name The template to use.
	 * @return string The template visibility.
	 */
	private function get_template_visibility( $template_name ) {
		$visibility = $this->get_template_arg( $template_name, 'override_visibility' );

		// If the override visibility is invalid, grab the default.
		if ( ! in_array( $visibility, $this->visibility_types ) ) {
			$defaults   = $this->get_template_arg_defaults();
			$visibility = $defaults['override_visibility'];
		}

		return $visibility;
	}

	/**
	 * Gets the template path, given the file name.
	 *
	 * @since 1.0.0
	 *
	 * @param $template_name string the template name to include.
	 * @return string The complete template path.
	 */
	protected function get_template_path( $template_name ) {
		return trailingslashit( $this->get_template_directory() ) . $template_name . '.php';
	}

	/**
	 * Locates the template that should be loaded.
	 *
	 * If a template is defined as public, the template can be overridden inside a theme, or child theme.
	 * This function checks the child theme, then the parent theme, and finally the plugin fallback.
	 *
	 * @since 1.0.0
	 *
	 * @param $template_name string The template name to locate.
	 * @return string The path to the located template.
	 */
	protected function locate_template( $template_name ) {
		$template_visibility = $this->get_template_visibility( $template_name );
		// Bail early if the template is private.
		if ( 'private' === $template_visibility ) {
			return $this->get_template_path( $template_name );
		}

		$override_dir       = 'underpin-templates/';
		$template_group     = trailingslashit( $this->get_template_group() );
		$override_path      = $override_dir . $template_group;
		$override_path      = apply_filters( "underpin/templates/template_directory", $override_path, $template_name, $template_group, $template_visibility );
		$override_file_path = trailingslashit( $override_path ) . $template_name . '.php';

		// Check to see if we have a template override from another plugin
		if ( 'plugins' === $template_visibility || 'public' === $template_visibility ) {


			// If another plugin has an override, use it.
			if ( file_exists( $override_file_path ) ) {
				$template = $override_file_path;
			}

			// Make sure someone isn't trying to force-load from the theme.
			if ( false !== strpos( $override_path, get_stylesheet_directory() ) || false !== strpos( $override_path, get_template_directory() ) ) {
				unset( $template );
			}
		}

		// Check to see if we have a theme override.
		if ( 'themes' === $template_visibility || 'public' === $template_visibility ) {

			// If the active child theme has an override, use it.
			if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $override_file_path ) ) {
				$template = trailingslashit( get_stylesheet_directory() ) . $override_file_path;

				// If the active parent theme has an override, use it.
			} elseif ( file_exists( trailingslashit( get_template_directory() ) . $override_file_path ) ) {
				$template = trailingslashit( get_template_directory() ) . $override_file_path;
			}
		}

		// If we didn't get an override, load the default.
		if ( ! isset( $template ) ) {
			$template = $this->get_template_path( $template_name );
		}

		return $template;
	}

	/**
	 * Gets a single argument from a template configuration. Falls back to the default value
	 *
	 * @since 1.0.0
	 *
	 * @param string $template_name The template name to get the argument from.
	 * @param string $arg           The argument to fetch.
	 * @return WP_Error|mixed A WP Error object if something went wrong, the argument for the current value otherwise.
	 */
	private function get_template_arg( $template_name, $arg ) {
		if ( isset( $this->get_template_arg_defaults()[ $arg ] ) ) {
			if ( $this->is_valid_template( $template_name ) ) {
				$templates = $this->get_templates();

				$result = isset( $templates[$template_name][ $arg ] ) ? $templates[$template_name][ $arg ] : $this->get_template_arg_defaults()[ $arg ];
			} else {
				$result = underpin()->logger()->log_as_error(
					'error',
					'underpin_get_arg_invalid_template',
					__( "Template $template_name argument $arg was not fetched because $template_name is not a valid template." ,'underpin' )
				);
			}
		} else {
			$result = underpin()->logger()->log_as_error(
				'error',
				'underpin_get_arg_invalid_argument',
				__( "Template $template_name argument $arg was not fetched because $arg is not a valid template argument." ,'underpin' )
			);
		}

		return $result;
	}

	/**
	 * Checks to see if the current template is defined.
	 *
	 * @since 1.0.0
	 *
	 * @param $template_name string The template name to check.
	 * @return bool True if the template is valid, false otherwise.
	 */
	public function is_valid_template( $template_name ) {
		$valid_templates = array_keys( $this->get_templates() );

		return in_array( $template_name, $valid_templates );
	}

	/**
	 * Checks to see if the template file exists.
	 *
	 * @since 1.0.0
	 *
	 * @param $template_name string The template name to check.
	 * @return bool True if the template file exists, false otherwise.
	 */
	public function template_file_exists( $template_name ) {
		return file_exists( $this->get_template_path( $template_name ) );
	}

	/**
	 * Updates current depth and params, gets the template contents.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template_name The template name.
	 * @param array  $params        The params to use in the template.
	 * @return false|string The template contents if the file exists, false otherwise.
	 */
	private function include_template( $template_name, $params ) {

		$this->depth++;
		if ( 'private' !== $this->get_template_arg( $template_name, 'override_visibility' ) ) {
			$this->params[ $this->depth ] = apply_filters( "underpin/templates/template_params", $params, $template_name, $this->get_template_path( $template_name ), $this->depth );
		} else {
			$this->params[ $this->depth ] = $params;
		}

		if ( 'private' !== $this->get_template_visibility( $template_name ) ) {
			/**
			 * Fires just before the template output buffer begins.
			 */
			do_action( 'underpin/templates/before_template_buffer', $template_name, $this->depth, $this->params[ $this->depth ], $this->get_template_path( $template_name ) );
		}

		ob_start();

		if ( 'private' !== $this->get_template_visibility( $template_name ) ) {
			/**
			 * Fires inside of the output buffer, just before the template is rendered.
			 *
			 * Note that this only fires when the provided template is not private.
			 */
			do_action( 'underpin/templates/before_template', $template_name, $this->depth, $this->params[ $this->depth ], $this->get_template_path( $template_name ) );
		}

		underpin_include_file_with_scope( $this->locate_template( $template_name ), [
			'template' => $this,
		] );

		if ( 'private' !== $this->get_template_visibility( $template_name ) ) {

			/**
			 * Fires inside of the output buffer, just after the template is rendered.
			 *
			 * Note that this only fires when the provided template is not private.
			 */
			do_action( 'underpin/templates/after_template', $template_name, $this->depth, $this->params[ $this->depth ], $this->get_template_path( $template_name ) );
		}

		$result = ob_get_clean();

		if ( 'private' !== $this->get_template_visibility( $template_name ) ) {

			/**
			 * Fires outside of the output buffer, just after the template is rendered.
			 *
			 * Note that this only fires when the provided template is not private.
			 */
			do_action( 'underpin/templates/after_template_buffer', $template_name, $this->depth, $this->params[ $this->depth ], $this->get_template_path( $template_name ) );
		}

		unset( $this->params[ $this->depth ] );
		$this->depth--;

		return $result;
	}
}

/**
 * Includes a file and passes the specified scope items as local scope.
 *
 * @since 1.0.0
 *
 * @param $file  string The file to include
 * @param $scope array The scope items keyed by their variable name.
 * @return bool True if include was successful, false otherwise.
 */
function underpin_include_file_with_scope( $file, $scope ) {
	if ( file_exists( $file ) ) {
		extract( $scope );
		include $file;

		return true;
	}

	return false;
}