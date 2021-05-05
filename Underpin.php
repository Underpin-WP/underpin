<?php
/**
 * Core functionality for Underpin
 *
 * @since
 * @package
 */


namespace Underpin;


use Underpin\Abstracts\Underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'Underpin\underpin' ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'lib/abstracts/Underpin.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'lib/traits/Instance_Setter.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'lib/traits/Feature_Extension.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'lib/traits/Templates.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'lib/factories/Underpin_Instance.php' );

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
		return Underpin::make_class()->get( __FILE__ );
	}

	underpin();
}