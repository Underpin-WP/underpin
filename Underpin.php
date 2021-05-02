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
	class Underpin extends Abstracts\Underpin {

		protected $minimum_php_version = '5.6';
		protected $minimum_wp_version = '5.0';
		protected $version = '1.1.0';
		protected $root_namespace = 'Underpin';


		protected function _setup() {
			$this->cron_jobs();

			// Maybe setup the admin bar.
			if ( underpin()->is_debug_mode_enabled() && ! is_wp_error( $this->admin_bar_menus() ) ) {
				$this->admin_bar_menus()->add( 'debug_bar', 'Underpin\Utilities\Debug_Bar' );
			}

			$this->scripts();
			$this->styles();
			$this->options();
			$this->logger();
			$this->decision_lists();
			$this->extensions();
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
	 * @return Underpin|Abstracts\Underpin The bootstrap for this plugin
	 */
	function underpin() {
		return ( new Underpin )->get( __FILE__ );
	}

	underpin();
}