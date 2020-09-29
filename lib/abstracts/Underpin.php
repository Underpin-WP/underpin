<?php
/**
 * Plugin Underpin
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts;

use Exception;
use Underpin\Abstracts\Registries\Loader_Registry;
use Underpin\Loaders;
use WP_Error;
use function Underpin\underpin;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Underpin
 * Underpin Class
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Underpin {

	/**
	 * Houses all of the singleton classes used in this plugin.
	 * Not intended to be manipulated directly.
	 *
	 * @since 1.0.0
	 * @var array Array of class instance.
	 */
	private $class_registry = [];

	/**
	 * Base class instances for everything that uses this bootstrap.
	 *
	 * @since 1.0.0
	 * @var Underpin|null The one true instance of the Underpin
	 */
	protected static $instances = [];

	/**
	 * The namespace for loaders. Used for loader autoloading.
	 *
	 * @since 1.0.0
	 *
	 * @var string Complete namespace for all loaders.
	 */
	protected $root_namespace = "Underpin";

	protected $minimum_php_version;
	protected $minimum_wp_version;
	protected $version;
	protected $url;
	protected $css_url;
	protected $js_url;
	protected $dir;
	protected $file;
	protected $template_dir;

	public function minimum_php_version() {
		return $this->minimum_php_version;
	}

	public function minimum_wp_version() {
		return $this->minimum_wp_version;
	}

	public function version() {
		return $this->version;
	}

	public function url() {
		return trailingslashit( $this->url );
	}

	public function css_url() {
		return trailingslashit( $this->css_url );
	}

	public function js_url() {
		return trailingslashit( $this->js_url );
	}

	public function dir() {
		return trailingslashit( $this->dir );
	}

	public function file() {
		return $this->file;
	}

	/**
	 * Determines if debug mode is enabled.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if debug mode is enabled, otherwise false.
	 */
	public function is_debug_mode_enabled() {
		$is_invalid_request = defined( 'WP_TESTS_DOMAIN' ) || defined( 'WP_CLI' ) || wp_doing_ajax() || wp_doing_cron() || defined( 'REST_REQUEST' );

		// Bail early if this is not a valid request for debug mode.
		if ( $is_invalid_request ) {
			return false;
		}

		$debug_enabled_with_querystring = isset( $_GET['underpin_debug'] ) && '1' === $_GET['underpin_debug'];

		if ( $debug_enabled_with_querystring ) {
			return true;
		}

		$debug_enabled_option = underpin()->options()->get( 'debug_mode_enabled' );
		if ( isset( $_POST[ $debug_enabled_option->key ] ) && 'on' === $_POST[ $debug_enabled_option->key ] ) {
			return false;
		}

		return (bool) $debug_enabled_option->get();
	}

	public function template_dir() {
		return trailingslashit( $this->template_dir );
	}

	abstract protected function _setup();

	/**
	 * Fetches the specified class, and constructs the class if it hasn't been constructed yet.
	 *
	 * @since 1.0.0
	 *
	 * @param $class
	 * @return mixed
	 */
	protected function _get_class( $class ) {
		if ( ! isset( $this->class_registry[ $class ] ) ) {
			if ( class_exists( $class ) ) {
				$this->class_registry[ $class ] = new $class;
			} else {
				$this->class_registry[ $class ] = new WP_Error(
					'class_could_not_be_found',
					'The specified class could not be located',
					[ 'class' => $class ]
				);
			}
		}

		return $this->class_registry[ $class ];
	}

	protected function _get_loader( $loader ) {
		$class = underpin()->_get_class( 'Underpin\Loaders\\' . $loader );

		// If this is not a core loader, attempt to get it from this plugin.
		if ( is_wp_error( $class ) ) {
			$class = $this->_get_class( $this->root_namespace . '\\Loaders\\' . $loader );
		}

		return $class;
	}

	public static function export() {
		$results = [];

		foreach ( Underpin::$instances as $key => $instance ) {
			if ( $instance instanceof Underpin ) {
				$results = $instance->export_registered_items( $results );
			}
		}

		return $results;
	}

	/**
	 * Retrieves a list of registered loader items from the registry.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function export_registered_items( $results = [] ) {
		foreach ( $this->class_registry as $key => $class ) {
			if ( $class instanceof Loader_Registry ) {
				if ( ! empty( $class ) ) {
					ob_start();
					foreach ( $class as $registered_key => $registered_class ) {
						echo "******************************";
						if ( isset( $registered_class->name ) ) {
							echo "\n" . $registered_class->name;
							unset( $registered_class->name );
						}
						if ( isset( $registered_class->description ) ) {
							echo "\n" . $registered_class->description;
							unset( $registered_class->description );
						}
						echo "\n" . $registered_key;

						echo "\n******************************\n";

						if ( method_exists( $registered_class, 'export' ) ) {
							var_dump( $registered_class->export() );
						} else {
							var_dump( $registered_class );
						}
					}

					$key             = explode( '\\', $key );
					$key             = array_pop( $key );
					$results[ $key ] = ob_get_clean();
				}
			}
		}

		return $results;
	}

	/**
	 * Sends a notice if the WordPress or PHP version are below the minimum requirement.
	 *
	 * @since 1.0.0
	 */
	public function below_version_notice() {
		global $wp_version;

		if ( version_compare( $wp_version, $this->minimum_wp_version, '<' ) ) {
			echo '<div class="error">
							<p>' . __( sprintf( "Underpin plugin is not activated. The plugin requires at least WordPress %s to function.", $this->minimum_wp_version() ), 'underpin' ) . '</p>
						</div>';
		}

		if ( version_compare( phpversion(), $this->minimum_php_version, '<' ) ) {
			echo '<div class="error">
							<p>' . __( sprintf( "Underpin plugin is not activated. The plugin requires at least PHP %s to function.", $this->minimum_php_version() ), 'underpin' ) . '</p>
						</div>';
		}
	}

	/**
	 * Registers the autoloader.
	 *
	 * @sicne 1.0.0
	 *
	 * @return bool|string
	 */
	protected function _setup_autoloader() {
		try{
			spl_autoload_register( function( $class ) {
				$class = explode( '\\', $class );

				$root = trailingslashit( $this->dir ) . 'lib/';

				$root_namespace = array_shift( $class );

				// Bail early if the namespace roots do not match.
				if($this->root_namespace !== $root_namespace){
					return false;
				}

				$file_name = array_pop( $class );
				$directory = str_replace( '_', '-', strtolower( implode( DIRECTORY_SEPARATOR, $class ) ) );
				$file      = $root . $directory . '/' . $file_name . '.php';

				// If the file exists in this form, use it.
				if ( file_exists( $file ) ) {
					require_once $file;

					return true;
				}

				$lowercase_file = strtolower( $file );

				// If it does not, try to retrieve a lowercase version. Some operating systems are sensitive to this.
				if ( file_exists( $lowercase_file ) ) {
					require_once $lowercase_file;

					return true;
				}

				return false;
			} );
		}catch( Exception $e ){
			$this->logger()->log_exception( 'autoload_failed', $e );

			return $e->getMessage();
		}

		return false;
	}

	/**
	 * Fetches the Logger instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Logger
	 */
	public function logger() {
		return $this->_get_loader( 'Logger' );
	}

	/**
	 * Fetches the Options instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Options
	 */
	public function options() {
		return $this->_get_loader( 'Options' );
	}

	/**
	 * Fetches the Batch_Tasks instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Batch_Tasks
	 */
	public function batch_tasks() {
		return $this->_get_loader( 'Batch_Tasks' );
	}

	/**
	 * Fetches the Batch_Tasks instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Decision_Lists
	 */
	public function decision_lists() {
		return $this->_get_loader( 'Decision_Lists' );
	}

	/**
	 * Retrieves the scripts loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Scripts
	 */
	public function scripts() {
		return $this->_get_loader( 'Scripts' );
	}

	/**
	 * Retrieves the cron jobs loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Cron_Jobs
	 */
	public function cron_jobs() {
		return $this->_get_loader( 'Cron_Jobs' );
	}

	/**
	 * Retrieves the debug bar items loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Debug_Bar_Sections
	 */
	public function debug_bar_sections() {
		return $this->_get_loader( 'Debug_Bar_Sections' );
	}

	/**
	 * Retrieves the cron jobs loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Erasers
	 */
	public function erasers() {
		return $this->_get_loader( 'Erasers' );
	}

	/**
	 * Retrieves the cron jobs loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Exporters
	 */
	public function exporters() {
		return $this->_get_loader( 'Exporters' );
	}

	/**
	 * Retrieves the blocks loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Blocks
	 */
	public function blocks() {
		return $this->_get_loader( 'Blocks' );
	}

	/**
	 * Retrieves the admin bar menus loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Admin_Bar_Menus
	 */
	public function admin_bar_menus() {
		return $this->_get_loader( 'Admin_Bar_Menus' );
	}

	/**
	 * Retrieves the admin menus loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Admin_Menus
	 */
	public function admin_menus() {
		return $this->_get_loader( 'Admin_Menus' );
	}

	/**
	 * Retrieves the rest endpoints loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Rest_Endpoints
	 */
	public function rest_endpoints() {
		return $this->_get_loader( 'Rest_Endpoints' );
	}

	/**
	 * Retrieves the custom post types loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Custom_Post_Types
	 */
	public function custom_post_types() {
		return $this->_get_loader( 'Custom_Post_Types' );
	}

	/**
	 * Retrieves the taxonomies loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Taxonomies
	 */
	public function taxonomies() {
		return $this->_get_loader( 'Taxonomies' );
	}

	/**
	 * Retrieves the shortcodes loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Shortcodes
	 */
	public function shortcodes() {
		return $this->_get_loader( 'Shortcodes' );
	}

	/**
	 * Fetches the Batch_Tasks instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Admin_Notices
	 */
	public function admin_notices() {
		return $this->_get_loader( 'Admin_Notices' );
	}

	/**
	 * Retrieves the widgets loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Widgets
	 */
	public function widgets() {
		return $this->_get_loader( 'Widgets' );
	}

	/**
	 * Retrieves the admin_sub_menus loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Admin_Sub_Menus
	 */
	public function admin_sub_menus() {
		return $this->_get_loader( 'Admin_Sub_Menus' );
	}

	/**
	 * Retrieves the Styles loader.
	 *
	 * @since 1.0.0
	 *
	 * @return Loaders\Styles
	 */
	public function styles() {
		return $this->_get_loader( 'Styles' );
	}

	/**
	 * Checks if the PHP version meets the minimum requirements.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the minimum requirements are met, false otherwise.
	 */
	public function supports_php_version() {
		return version_compare( phpversion(), $this->minimum_php_version, '>=' );
	}

	/**
	 * Checks if the WP version meets the minimum requirements.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the minimum requirements are met, false otherwise.
	 */
	public function supports_wp_version() {
		global $wp_version;

		return version_compare( $wp_version, $this->minimum_wp_version, '>=' );
	}

	/**
	 * Checks if all minimum requirements are met.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the minimum requirements are met, false otherwise.
	 */
	public function plugin_is_supported() {
		return $this->supports_wp_version() && $this->supports_php_version();
	}

	/**
	 * A set of actions that run when this plugin does not meet the minimum requirements.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function unsupported_actions() {
		global $wp_version;

		self::$instances[ __CLASS__ ] = new WP_Error(
			'minimum_version_not_met',
			__( sprintf(
				"The Underpin plugin requires at least WordPress %s, and PHP %s.",
				$this->minimum_wp_version,
				$this->minimum_php_version
			), 'underpin' ),
			array( 'current_wp_version' => $wp_version, 'php_version' => phpversion() )
		);

		add_action( 'admin_notices', array( $this, 'below_version_notice' ) );
	}

	/**
	 * Actions that run when this plugin meets the specified minimum requirements.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup() {

		// Set up the autoloader for everything else.
		$this->_setup_autoloader();

		/**
		 * Fires just before the bootstrap starts up.
		 *
		 * @since 1.0.0
		 */
		do_action( 'underpin/before_setup', get_called_class() );


		// Set up classes that register things.
		$this->_setup();

		/**
		 * Fires just after the bootstrap is completely set-up.
		 *
		 * @since 1.0.0
		 */
		do_action( 'underpin/after_setup', get_called_class() );
	}

	protected function _setup_params( $file ) {

		// Root file for this plugin. Used in activation hooks.
		$this->file = $file;

		// The URL for this plugin. Used in asset loading.
		$this->url = plugin_dir_url( $file );

		// Root directory for this plugin.
		$this->dir = plugin_dir_path( $file );

		// The CSS URL for this plugin. Used in asset loading.
		$this->css_url = $this->url . 'assets/css/build';

		// The JS URL for this plugin. Used in asset loading.
		$this->js_url = $this->url . 'assets/js/build';

		// The template directory. Used by the template loader to determine where templates are stored.
		$this->template_dir = $this->dir . 'templates/';
	}

	/**
	 * Fires up the plugin.
	 *
	 * @since        1.0.0
	 *
	 * @param string $file The complete path to the root file in this plugin. Usually the __FILE__ const.
	 * @return self
	 * @noinspection PhpUndefinedMethodInspection
	 */
	public function get( $file ) {
		$class = get_called_class();
		if ( ! isset( self::$instances[ $class ] ) ) {
			$this->_setup_params( $file );

			// First, check to make sure the minimum requirements are met.
			if ( $this->plugin_is_supported() ) {
				self::$instances[ $class ] = $this;

				// Setup the plugin, if requirements were met.
				self::$instances[ $class ]->setup();

			} else {
				// Run unsupported actions if requirements are not met.
				$this->unsupported_actions();
			}
		}

		return self::$instances[ $class ];
	}
}