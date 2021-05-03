<?php
/**
 * Plugin Underpin
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts;

use Exception;
use Underpin\Loaders;
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
 * @method Loaders\Admin_Menus|\WP_Error        admin_menus
 * @method Loaders\Admin_Notices|\WP_Error      admin_notices
 * @method Loaders\Admin_Sub_Menus|\WP_Error    admin_sub_menus
 * @method Loaders\Batch_Tasks|\WP_Error        batch_tasks
 * @method Loaders\Blocks|\WP_Error             blocks
 * @method Loaders\Cron_Jobs|\WP_Error          cron_jobs
 * @method Loaders\Custom_Post_Types|\WP_Error  custom_post_types
 * @method Loaders\Debug_Bar_Sections|\WP_Error debug_bar_sections
 * @method Loaders\Decision_Lists|\WP_Error     decision_lists
 * @method Loaders\Erasers|\WP_Error            erasers
 * @method Loaders\Exporters|\WP_Error          exporters
 * @method Loaders\Menus|\WP_Error              menus
 * @method Loaders\Options|\WP_Error            options
 * @method Loaders\Post_Meta|\WP_Error          post_meta
 * @method Loaders\Rest_Endpoints|\WP_Error     rest_endpoints
 * @method Loaders\Roles|\WP_Error              roles
 * @method Loaders\Scripts|\WP_Error            scripts
 * @method Loaders\Shortcodes|\WP_Error         shortcodes
 * @method Loaders\Sidebars|\WP_Error           sidebars
 * @method Loaders\Styles|\WP_Error             styles
 * @method Loaders\Taxonomies|\WP_Error         taxonomies
 * @method Loaders\User_Meta|\WP_Error          user_meta
 * @method Loaders\Widgets|\WP_Error            widgets
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

	protected $text_domain = 'underpin';

	protected $minimum_php_version;
	protected $minimum_wp_version;
	protected $version;
	protected $url;
	protected $css_url;
	protected $js_url;
	protected $dir;
	protected $file;
	protected $template_dir;

	/**
	 * Dynamically calls methods.
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

		// Try to get the extension.
		if ( ! is_wp_error( $this->extensions() ) ) {
			// If the loader does not exist, get the extension
			$loader = $this->extensions()->get( $method );

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
	 * @return \Underpin\Loaders\Extensions|WP_Error
	 */
	public function extensions() {
		return $this->loader_registry->get( 'extensions' );
	}

	public function loaders() {
		return $this->loader_registry;
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

		return apply_filters( 'underpin/debug_mode_enabled', false );
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
		try {
			spl_autoload_register( function( $class ) {
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
		$this->loader_registry = new Loader_Registry( get_called_class() );
		$this->loaders()->add( 'extensions', [ 'instance' => '\\Underpin\Abstracts\Extension' ] );

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

		// Root directory for this plugin.
		$this->dir = plugin_dir_path( $file );

		// The URL for this plugin. Used in asset loading.
		if ( false !== strpos( "/wp-content" . DIRECTORY_SEPARATOR . "plugins/", $this->dir ) ) {
			$this->url = plugin_dir_url( $file );
		} else {
			$template              = '/' . get_template() . '/';
			$template_dir_position = strpos( dirname( $file ), $template ) + strlen( $template );
			$root                  = trailingslashit( get_stylesheet_directory_uri() );
			$this->url             = trailingslashit( $root . substr( dirname( $file ), $template_dir_position ) );
		}

		// The CSS URL for this plugin. Used in asset loading.
		$this->css_url = $this->url . 'assets/css/build';

		// The JS URL for this plugin. Used in asset loading.
		$this->js_url = $this->url . 'assets/js/build';

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
	 * @param string $text   Text to translate.
	 * @return string Translated text.
	 */
	public function __( $text ) {
		return __( $text, $this->text_domain );
	}

	/**
	 * Fires up the plugin.
	 *
	 * @since        1.0.0
	 *
	 * @param string $file The complete path to the root file in this plugin. Usually the __FILE__ const.
	 * @return self
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
