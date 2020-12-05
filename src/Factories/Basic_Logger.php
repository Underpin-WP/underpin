<?php
/**
 * Logs events to a file.
 *
 * @since 1.0.0
 *
 * @since
 * @package
 */


namespace Underpin\Factories;


use Underpin\Abstracts\Event_Type;
use Underpin\Abstracts\Writer;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Basic Logger
 *
 *
 * @since
 * @package
 */
class Basic_Logger extends Writer {

	/**
	 * Log directory.
	 *
	 * @since 1.0.0
	 *
	 * @var string The log directory
	 */
	private $log_dir;

	public function __construct( Event_Type $event_type ) {

		// Construct the log dir
		$upload_dir    = wp_upload_dir( null, false );
		$this->log_dir = trailingslashit( trailingslashit( $upload_dir['basedir'] ) . 'underpin-event-logs/' );

		// If the log directory does not exist, create it and set permissions.
		if ( ! is_writeable( $this->log_dir ) ) {
			@mkdir( $this->log_dir );
			@chmod( $this->log_dir, 0664 );
		}

		parent::__construct( $event_type );
	}

	/**
	 * @inheritDoc
	 */
	public function write( Log_Item $item ) {
		$message = $item->format();
		$file    = $this->file();

		if ( ! is_wp_error( $file ) ) {
			@file_put_contents( $file, "\n\n" . $message, FILE_APPEND );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function clear() {
		$file = $this->file();

		if ( is_wp_error( $file ) ) {
			return $file;
		}

		unlink( $file );

		return true;
	}


	/**
	 * Gathers a list of log files.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of paths to log files.
	 */
	public function files() {
		$files = glob( $this->log_dir . '*.log' );

		if ( false === $files ) {
			$files = array();
		}

		return $files;
	}

	/**
	 * @inheritDoc
	 */
	public function purge( $max_file_age ) {
		$files = $this->files();

		// bail early if the max file age is less than zero.
		if ( $max_file_age < 0 ) {
			$error = new WP_Error(
				'invalid_max_age',
				'The provided max file age is less than zero. File age must be greater than zero.'
			);

			underpin()->logger()->log_wp_error( 'warning', $error );

			return $error;
		}

		$purged      = [];
		$oldest_date = date( 'U', strtotime( '-' . $max_file_age . ' days midnight' ) );

		foreach ( $files as $file ) {
			$file_info = $this->parse_file( $file );
			$file_date = date( 'U', strtotime( $file_info['date'] . ' midnight' ) );

			if ( ! is_wp_error( $file_info ) && $file_date < $oldest_date ) {
				$deleted = @unlink( $file_info['path'] );

				if ( true === $deleted ) {
					$purged[] = $file_info['path'];
				}
			}
		}

		return $purged;
	}


	/**
	 * Attempt to retrieve log type and date from the provided file name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path The path to the file, or the file name.
	 * @return array|WP_Error Parsed file, or \WP_Error object.
	 */
	public function parse_file( $path ) {
		$file       = basename( $path );
		$file_split = explode( '__', $file );
		$errors     = new WP_Error();

		if ( count( $file_split ) !== 2 ) {
			$errors->add(
				'parse_file_malformed_file',
				'The file provided is malformed. The file must contain exactly one double __ between the type and date.',
				compact( 'file', 'file_split' )
			);
		}

		if ( false === strpos( $file, '.log' ) ) {
			$errors->add(
				'parse_file_type_is_not_log',
				'The provided file is not an error log file',
				compact( 'file' )
			);
		}

		// Bail early if we have any errors.
		if ( $errors->has_errors() ) {
			underpin()->logger()->log(
				'warning',
				'errors_while_parsing_file',
				'A file failed to parse',
				[ 'file' => $file, 'errors' => $errors ]
			);

			return $errors;
		}

		// Remove file extension from date
		$raw_date = str_replace( '.log', '', $file_split[1] );

		// Set date
		$date = date( 'M-d-Y', strtotime( $raw_date ) );

		$path = $this->path( $date );

		return compact( 'date', 'path' );
	}

	/**
	 * Gets the file name for the specified event type.
	 * This will automatically create the file if it does not exist.
	 *
	 * @since 1.0.0
	 *
	 * @param string $date Optional. The log file date to retrieve. Default today.
	 * @return string|WP_Error
	 */
	public function file( $date = 'today' ) {

		$file = $this->path( $date );

		if ( is_writeable( $this->log_dir ) && ! is_wp_error( $file ) && ! @file_exists( $file ) ) {
			@fopen( $file, "w" );
		}

		return $file;
	}

	/**
	 * Retrieves the path for the specified log type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $date Optional. The log file date to retrieve. Default today.
	 * @return string Path to the specified event log.
	 */
	public function path( $date = 'today' ) {
		$date = strtolower( date( 'M-d-y', strtotime( $date ) ) );
		$type = str_replace( '__', '_', $this->event_type->type );

		return $this->log_dir . $type . '__' . $date . '.log';
	}
}