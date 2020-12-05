<?php
/**
 * Registers Scripts to WordPress
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts;

use Underpin\Traits\Feature_Extension;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Script
 * Class Scripts
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Script {
	use Feature_Extension;

	/**
	 * The handle for this script.
	 *
	 * @since 1.0.0
	 * @var string the script handle.
	 */
	protected $handle;

	/**
	 * The version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $ver = false;

	/**
	 * The source url for this script.
	 *
	 * @since 1.0.0
	 * @var bool|string
	 */
	protected $src = false;

	/**
	 * The dependencies for this script.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $deps = [];

	/**
	 * If this script should be displayed in the footer.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $in_footer = false;


	/**
	 * Params to send to the script when it is enqueued.
	 *
	 * @since 1.0.0
	 *
	 * @var array Array of params.
	 */
	protected $localized_params = [];

	/**
	 * The variable name for the localized object.
	 * Defaults to the handle if not set.
	 *
	 * @since 1.0.0
	 *
	 * @var string The localized object name.
	 */
	protected $localized_var;

	/**
	 * Script constructor.
	 */
	public function __construct() {
		if ( empty( $this->localized_var ) ) {
			$this->localized_var = $this->handle;
		}
	}


	/**
	 * @inheritDoc
	 */
	public function do_actions() {
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Callback to retrieve the localized parameters for this script.
	 * If this is empty, localize does not fire.
	 *
	 * @since 1.0.0
	 * @return array list of localized params as key => value pairs.
	 */
	public function get_localized_params() {
		return $this->localized_params;
	}

	/**
	 * Returns true if the script has been enqueued. Bypasses doing it wrong check.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_enqueued() {
		return (bool) wp_scripts()->query( $this->handle, 'enqueued' );
	}


	/**
	 * Retrieves the specified localized param.
	 *
	 * @since 1.0.0
	 *
	 * @param $param
	 * @return mixed|WP_Error
	 */
	public function get_param( $param ) {
		if ( isset( $this->localized_params[ $param ] ) ) {
			return $this->localized_params[ $param ];
		}

		return new \WP_Error( 'param_not_set', 'The localized param ' . $param . ' is not set.' );
	}

	/**
	 * Adds a param to localize to this script.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   The key for the localized value.
	 * @param string $value The value
	 * @return true|WP_Error True if successful, \WP_Error if param was added too late.
	 */
	public function set_param( $key, $value ) {

		// If the script is already enqueued, return an error.
		if ( $this->is_enqueued() ) {
			return underpin()->logger()->log_as_error(
				'error',
				'param_set_too_late',
				'The localized param ' . $key . ' was set after the script was already enqueued.',
				[ 'handle' => $this->handle, 'key' => $key, 'value' => $value ]
			);
		}

		$this->localized_params[ $key ] = $value;

		return true;
	}

	/**
	 * Removes a localized param.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key to remove.
	 * @return true|WP_Error True if successful, \WP_Error if param was added too late.
	 */
	public function remove_param( $key ) {

		// If the script is already enqueued, return an error.
		if ( wp_script_is( $this->handle ) ) {
			return underpin()->logger()->log_as_error(
				'error',
				'param_removed_too_late',
				'The localized param ' . $key . ' attempted to be removed after the script was already enqueued.',
				[ 'handle' => $this->handle, 'key' => $key ]
			);
		}

		if ( isset( $this->localized_params[ $key ] ) ) {
			unset( $this->localized_params[ $key ] );
		}

		return true;
	}

	/**
	 * Localizes the script, if there are any arguments to pass.
	 *
	 * @since 1.0.0
	 */
	public function localize() {
		$localized_params = $this->get_localized_params();

		// If we actually have localized params, localize and enqueue.
		if ( ! empty( $localized_params ) ) {
			$localized = wp_localize_script( $this->handle, $this->localized_var, $localized_params );

			if ( false === $localized ) {
				underpin()->logger()->log(
					'error',
					'script_was_not_localized',
					'The script ' . $this->handle . ' failed to localize. That is all I know, unfortunately.',
					[ 'handle' => $this->handle, 'params' => $localized_params ]
				);
			} else {
				underpin()->logger()->log(
					'notice',
					'script_was_localized',
					'The script ' . $this->handle . ' localized successfully.',
					[ 'handle' => $this->handle, 'params' => $localized_params ]
				);
			}
		}
	}

	/**
	 * Registers this script.
	 * In-general, this should automatically run based on the contexts provided in the class.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$registered = wp_register_script( $this->handle, $this->src, $this->deps, $this->ver, $this->in_footer );

		if ( false === $registered ) {
			underpin()->logger()->log(
				'error',
				'script_was_not_registered',
				'The script ' . $this->handle . ' failed to register. That is all I know, unfortunately.',
				['ref' => $this->handle]
			);
		} else {
			underpin()->logger()->log(
				'notice',
				'script_was_registered',
				'The script ' . $this->handle . ' registered successfully.',
				['ref' => $this->handle]
			);
		}

	}

	/**
	 * Enqueues the script, and auto-localizes values if necessary.
	 *
	 * @since 1.0.0
	 */
	public function enqueue() {
		$this->localize();
		wp_enqueue_script( $this->handle );

		// Confirm it was enqueued.
		if ( wp_script_is( $this->handle, 'enqueued' ) ) {
			underpin()->logger()->log(
				'notice',
				'script_was_enqueued',
				'The script ' . $this->handle . ' has been enqueued.',
				['ref' => $this->handle]
			);
		} else {
			underpin()->logger()->log(
				'error',
				'script_failed_to_enqueue',
				'The script ' . $this->handle . ' failed to enqueue.',
				['ref' => $this->handle]
			);
		}

	}
	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new WP_error( 'batch_task_param_not_set', 'The batch task key ' . $key . ' could not be found.' );
		}
	}

}