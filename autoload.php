<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register( function ( $class ) {
	$class = explode( '\\', $class );

	$root = plugin_dir_path(__FILE__ ) . 'lib/';

	$root_namespace = array_shift( $class );

	// Bail early if the namespace roots do not match.
	if ( 'Underpin' !== $root_namespace ) {
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