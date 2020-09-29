<?php
/**
 * Core functionality for Underpin
 *
 * @since
 * @package
 */


namespace Underpin;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'Underpin\underpin' ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'lib/abstracts/Underpin.php' );

	/**
	 * Class Underpin
	 *
	 *
	 * @since
	 * @package
	 */
	class Underpin_Setup extends Abstracts\Underpin {

		protected $minimum_php_version = '5.6';
		protected $minimum_wp_version = '5.0';
		protected $version = '1.0.0';
		protected $root_namespace = 'Underpin';


		protected function _setup_params( $file ) {
			/**
			 * Filters the root directory for Underpin.
			 * This makes it possible to load Underpin as a separate plugin.
			 *
			 * @since 1.0.0
			 */
			$this->file         = $file;
			$this->dir          = plugin_dir_path( $file );
			$this->url          = plugin_dir_url( $this->file );
			$this->css_url      = $this->url . 'assets/css/build';
			$this->js_url       = $this->url . 'assets/js/build';
			$this->template_dir = $this->dir . 'templates/';
		}

		protected function _setup() {
			$this->cron_jobs();
			$this->admin_bar_menus();
			$this->scripts();
			$this->styles();
			$this->options();
			$this->logger();
			$this->decision_lists();
		}
	}


	/**
	 * Fetches the instance of the plugin.
	 * This function makes it possible to access everything else in this plugin.
	 * It will automatically initiate the plugin, if necessary.
	 * It also handles autoloading for any class in the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return Underpin_Setup|Abstracts\Underpin The bootstrap for this plugin
	 */
	function underpin() {
		return ( new Underpin_Setup )->get( __FILE__ );
	}

	underpin();
}