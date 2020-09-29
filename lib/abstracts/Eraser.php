<?php
/**
 * Registers a personal data eraser
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts;

use Underpin\Traits\Feature_Extension;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shortcode
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Eraser {
	use Feature_Extension;

	/**
	 * A unique identifier for this eraser.
	 *
	 * @since 1.0.0
	 *
	 * @var
	 */
	public $id;

	/**
	 * Number of items to query per page.
	 * Used in is_done check, and should be used in queries to limit number of items retrieved.
	 *
	 * @var int
	 */
	public $per_page = 100;

	/**
	 * @inheritDoc
	 */
	public function do_actions() {
		add_filter( 'wp_privacy_personal_data_erasers', [ $this, 'register_eraser' ] );

		underpin()->logger()->log(
			'notice',
			'eraser_added',
			'An eraser has been added',
			[ 'ref' => $this->name ]
		);
	}

	/**
	 * Register this eraser.
	 *
	 * @since 1.0.0
	 *
	 * @param $erasers
	 * @return mixed
	 */
	public function register_eraser( $erasers ) {
		$erasers[ $this->id ] = array(
			'eraser_friendly_name' => $this->name,
			'callback'             => [ $this, 'erase' ],
		);

		return $erasers;
	}

	/**
	 * The actual eraser callback. Handles a request of erased content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email The email address to remove
	 * @param int    $page  The current page
	 * @return mixed The shortcode action result.
	 */
	public function erase( $email, $page ) {

		// First, get the list of items.
		$items  = $this->get_items( $email, $page );
		$result = [
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => [],
		];

		// Now, loop through and erase each.
		foreach ( $items as $item ) {
			$erased = $this->erase_item( $item );

			// Add a message, if one was sent
			if ( isset( $erased['message'] ) ) {
				$result['messages'][] = $erased['message'];
			}

			// Note the status
			if ( isset( $erased['status'] ) ) {
				if ( 'retained' === $erased['status'] ) {
					$result['items_retained'] = true;
				} elseif ( 'removed' === $erased['status'] ) {
					$result['items_removed'] = true;
				}
			}
		}

		// Determine if this is done, or not.
		$result['done'] = $this->is_done( $items );

		return $result;
	}

	/**
	 * Get the items to erase.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email The email address to query against.
	 * @param int    $page  The page this query should use.
	 * @return array list of items to update
	 */
	abstract function get_items( $email, $page );

	/**
	 * Erase a single item
	 *
	 * @param mixed $item The item to erase.
	 * @return array Array containing the status of the erase, and any messages to append.
	 *                    Status can be 'retained', or 'removed'.
	 */
	abstract function erase_item( $item );

	/**
	 * Returns true if this item is finished.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items List of items that were updated.
	 * @return bool true if done, otherwise false.
	 */
	public function is_done( $items ) {
		return count( $items ) < $this->per_page;
	}

	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new WP_error( 'eraser_param_not_set', 'The eraser key ' . $key . ' could not be found.' );
		}
	}

}