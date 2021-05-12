<?php
/**
 * Plugin Underpin
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts;

use Exception;
use Underpin\Factories\Loader_Registry;
use WP_Error;

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
	 * @var Loader_Registry
	 */
	private $loader_registry;

	/**
	 * The namespace for loaders. Used for loader autoloading.
	 *
	 * @since 1.0.0
	 *
	 * @var string Complete namespace for all loaders.
	 */
	protected $root_namespace = "Underpin";

	/**
	 * Translation Text domain.
	 *
	 * Used by translation method for translations.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $text_domain = 'underpin';

	/**
	 * Minimum PHP Version.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $minimum_php_version = '7.0';

	/**
	 * Current Version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $version = '1.2.0';

	/**
	 * Minimum WordPress Version.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $minimum_wp_version = '5.1';

	/**
	 * Plugin URL.
	 *
	 * @since 1.0.0
	 *
	 * @var
	 */
	protected $url;

	/**
	 * URL to CSS directory root.
	 *
	 * @since 1.0.0
	 *
	 * @var
	 */
	protected $css_url;

	/**
	 * URL to JS Root.
	 *
	 * @since 1.0.0
	 *
	 * @var
	 */
	protected $js_url;

	/**
	 * Plugin Root Dir.
	 *
	 * @since 1.0.0
	 *
	 * @var
	 */
	protected $dir;

	/**
	 * Plugin Root __FILE__.
	 *
	 * @since 1.0.0
	 *
	 * @var
	 */
	protected $file;

	/**
	 * Plugin Template Dir.
	 *
	 * @since 1.0.0
	 *
	 * @var
	 */
	protected $template_dir;

	/**
	 * Plugin name.
	 *
	 * Used to identify this plugin in debug logs.
	 *
	 * @var string
	 */
	public $name = 'Underpin';

	/**
	 * Function to setup this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	abstract protected function _setup();

	/**
	 * Dynamically calls methods.
	 *
	 * @since 1.2.0
	 *
	 * @param string $method    The method to call
	 * @param array  $arguments The arguments to pass to the method.
	 *
	 * @return mixed|WP_Error
	 */
	function __call( $method, $arguments ) {
		// If this method exists, bail and just get the method.
		if ( method_exists( $this, $method ) ) {
			return $this->$method( ...$arguments );
		}

		// Try and get the loader.
		$loader = $this->loader_registry->get( $method );

		// If the loader was found, bail early and return it.
		if ( ! is_wp_error( $loader ) ) {
			return $loader;
		}

		// Otherwise, return and log an error.
		if ( is_wp_error( $loader ) ) {
			$loader = new WP_Error(
				'method_not_found',
				"The method could not be called. Either register this item as a loader, install an extension, or create a method for this call.",
				[
					'method'    => $method,
					'args'      => $arguments,
					'backtrace' => debug_backtrace(),
				]
			);

			// Try to log the error.
			if ( ! is_wp_error( $this->logger() ) ) {
				return $this->logger()->log_wp_error( 'warning', $loader );
			}
		}
		return $loader;
	}

	/**
	 * Minimum PHP Version Getter.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function minimum_php_version() {
		return $this->minimum_php_version;
	}

	/**
	 * Minimum WP Version Getter.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function minimum_wp_version() {
		return $this->minimum_wp_version;
	}

	/**
	 * Plugin Version.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function version() {
		return $this->version;
	}

	/**
	 * URL Getter.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function url() {
		return trailingslashit( $this->url );
	}

	/**
	 * CSS URL Getter.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function css_url() {
		return trailingslashit( $this->css_url );
	}

	/**
	 * JS URL Getter.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function js_url() {
		return trailingslashit( $this->js_url );
	}

	/**
	 * Directory Getter.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function dir() {
		return trailingslashit( $this->dir );
	}

	/**
	 * __FILE__ Getter.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function file() {
		return $this->file;
	}

	/**
	 * Template Directory Getter.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function template_dir() {
		return trailingslashit( $this->template_dir );
	}

	/**
	 * Loader registry getter.
	 *
	 * @since 1.2.0
	 *
	 * @return Loader_Registry
	 */
	public function loaders() {
		return $this->loader_registry;
	}

	/**
	 * Fetch logger instance.
	 *
	 * @since 1.0.0
	 *
	 * @since 1.2.0 This can now return a WP_Error if-loaded too early.
	 *
	 * It is possible for the logger to be called before it is loaded. This adds a check to catch these errors and prevent
	 * fatal errors.
	 *
	 * @return \Underpin_Logger\Loaders\Logger|WP_Error
	 */
	public function logger() {
		if ( ! isset( $this->loader_registry['logger'] ) ) {
			return new WP_Error(
				'logger_not_set',
				'The logger was called before it was ready.'
			);
		}

		return $this->loader_registry->get( 'logger' );
	}

	/**
	 * Determines if debug mode is enabled.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if debug mode is enabled, otherwise false.
	 */
	public static function is_debug_mode_enabled() {
		$is_invalid_request = defined( 'WP_TESTS_DOMAIN' ) || defined( 'WP_CLI' ) || wp_doing_ajax() || wp_doing_cron() || defined( 'REST_REQUEST' );

		// Bail early if this is not a valid request for debug mode.
		if ( $is_invalid_request ) {
			return false;
		}

		$debug_enabled_with_querystring = isset( $_GET['underpin_debug'] ) && '1' === $_GET['underpin_debug'];

		if ( $debug_enabled_with_querystring ) {
			return true;
		}

		// If WP DEBUG is enabled, turn on debug mode.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return true;
		}

		return apply_filters( 'underpin/debug_mode_enabled', false, get_called_class() );
	}


	/**
	 * Fetches the specified class, and constructs the class if it hasn't been constructed yet.
	 *
	 * @since 1.0.0
	 *
	 * @param $class
	 *
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

	public static function export() {
		$results = [];

		foreach ( Underpin::$instances as $key => $instance ) {
			if ( $instance instanceof Underpin ) {
				$results = Underpin::get_by_id( $key )->export_registered_items( $results );
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
		try {
			spl_autoload_register( function ( $class ) {
				$class = explode( '\\', $class );

				$root = trailingslashit( $this->dir ) . 'lib/';

				$root_namespace = array_shift( $class );

				// Bail early if the namespace roots do not match.
				if ( $this->root_namespace !== $root_namespace ) {
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
		} catch ( Exception $e ) {
			$this->logger()->log_exception( 'autoload_failed', $e );

			return $e->getMessage();
		}

		return false;
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
		$this->loader_registry = new Loader_Registry( $this->get_registry_key() );

		/**
		 * Fires just before the bootstrap starts up.
		 *
		 * @since 1.0.0
		 */
		do_action( 'underpin/before_setup', $this->file(), get_called_class() );


		// Set up classes that register things.
		$this->_setup();

		/**
		 * Fires just after the bootstrap is completely set-up.
		 *
		 * @since 1.0.0
		 */
		do_action( 'underpin/after_setup', $this->file(), get_called_class() );
	}

	/**
	 * Setup plugin params using the provided __FILE__
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function _setup_params( $file ) {

		// Root file for this plugin. Used in activation hooks.
		$this->file = $file;

		// Root directory for this plugin.
		$this->dir = plugin_dir_path( $file );

		// The URL for this plugin. Used in asset loading.
		if ( false === strpos( "/wp-content" . DIRECTORY_SEPARATOR . "themes/", $this->dir ) ) {
			$this->url = plugin_dir_url( $file );
		} else {
			$template              = '/' . get_template() . '/';
			$template_dir_position = strpos( dirname( $file ), $template ) + strlen( $template );
			$root                  = trailingslashit( get_stylesheet_directory_uri() );
			$this->url             = trailingslashit( $root . substr( dirname( $file ), $template_dir_position ) );
		}

		// The CSS URL for this plugin. Used in asset loading.
		$this->css_url = $this->url . 'build';

		// The JS URL for this plugin. Used in asset loading.
		$this->js_url = $this->url . 'build';

		// The template directory. Used by the template loader to determine where templates are stored.
		$this->template_dir = $this->dir . 'templates/';
	}

	/**
	 * Retrieve the translation of $text.
	 *
	 * If there is no translation, or the text domain isn't loaded, the original text is returned.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Text to translate.
	 *
	 * @return string Translated text.
	 */
	public function __( $text ) {
		return __( $text, $this->text_domain );
	}

	/**
	 * Helper function used to construct factory classes from a standard array syntax.
	 *
	 * @since 1.2
	 *
	 * @param mixed  $value           The value used to generate the class.
	 *                                Can be an array with "class" and "args", an associative array, a string, or a class
	 *                                instance. If it is an array with "class" and "args", make_class will construct the
	 *                                factory specified in
	 *                                "class" using the provided "args"
	 *                                If it is an associative array, make_class will construct the default factory,
	 *                                passing the array of arguments to the constructor. If it is a string, make_class
	 *                                will try to instantiate the class with no args. If it is already a class,
	 *                                make_class will simply return the class directly.
	 * @param string $default_factory The default factory to use if a class is not provided in $value.
	 *
	 * @return mixed The instantiated class.
	 */
	public static function make_class( $value = [], $default_factory = 'Underpin\Factories\Underpin_Instance' ) {
		// If the value is a string, assume it's a class reference.
		if ( is_string( $value ) ) {
			$class = new $value;

			// If the value is an array, the class still needs defined.
		} elseif ( is_array( $value ) ) {

			// If the class is specified, construct the class from the specified value.
			if ( isset( $value['class'] ) ) {
				$class = $value['class'];
				$args  = isset( $value['args'] ) ? $value['args'] : [];

				// Otherwise, fallback to the default, and use the value as an array of arguments for the default.
			} else {

				$class = $default_factory;
				$args  = $value;
			}

			$is_assoc = count( array_filter( array_keys( $args ), 'is_string' ) ) > 0;
			// Convert single-level associative array to first argument using the array.
			if ( $is_assoc ) {
				$args = [ $args ];
			}

			$class = new $class( ...$args );

			// Otherwise, assume the class is already instantiated, and return it directly.
		} else {
			$class = $value;
		}

		return $class;
	}

	/**
	 * Retrieves the registry key for this instance.
	 *
	 * @since 1.2.0
	 *
	 * @return string The registry hash.
	 */
	public function get_registry_key( $file = '', $class = '' ) {
		$file  = empty( $file ) ? $this->file() : $file;
		$class = empty( $class ) ? get_called_class() : $class;
		return md5( $class . $file );
	}

	/**
	 * Fetch an Underpin Instance by the registry key.
	 *
	 * @since 1.2
	 *
	 * @param string $key The instance key.
	 *
	 * @return Underpin|WP_Error The underpin instance if found, otherwise WP_Error.
	 */
	public static function get_by_id( $key ) {
		if ( isset( self::$instances[ $key ] ) ) {
			return self::$instances[ $key ];
		}

		return new WP_Error(
			'instance_not_found',
			'The instance key provided is not associated with an Underpin instance',
			[ 'key' => $key ]
		);
	}

	/**
	 * Fires up the plugin.
	 *
	 * @since        1.0.0
	 *
	 * @param string $file The complete path to the root file in this plugin. Usually the __FILE__ const.
	 *
	 * @return self
	 */
	public function get( $file, $class = '' ) {
		$key = $this->get_registry_key( $file, $class );
		if ( ! isset( self::$instances[ $key ] ) ) {
			$this->_setup_params( $file );

			// First, check to make sure the minimum requirements are met.
			if ( $this->plugin_is_supported() ) {
				self::$instances[ $key ] = $this;

				// Setup the plugin, if requirements were met.
				self::$instances[ $key ]->setup();

			} else {
				// Run unsupported actions if requirements are not met.
				$this->unsupported_actions();
			}
		}

		return self::$instances[ $key ];
	}
}
