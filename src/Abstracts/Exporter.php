<?php
/**
 * Registers a personal data exporter
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
 * Class Exporter
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Exporter {
	use Feature_Extension;

	/**
	 * A unique identifier for this exporter.
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
		add_filter( 'wp_privacy_personal_data_exporters', [ $this, 'register_exporter' ] );

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
	public function register_exporter( $exporters ) {
		$exporters[ $this->id ] = [
			'exporter_friendly_name' => $this->name,
			'callback'               => [ $this, 'export_data' ],
		];

		return $exporters;
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
	public function export_data( $email, $page ) {

		// First, get the list of items.
		$items  = $this->get_items( $email, $page );
		$result = [];

		// Now, loop through and export each.
		foreach ( $items as $item ) {
			$result[] = [
				'group_id'          => $this->get_group_id( $item ),
				'group_label'       => $this->get_group_label( $item ),
				'group_description' => $this->get_group_description( $item ),
				'item_id'           => $this->get_item_id( $item ),
				'data'              => $this->get_data( $item ),
			];
		}

		return [ 'data' => $result, 'done' => $this->is_done( $items ) ];
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
	 * @param mixed $item The item to export.
	 * @return array Array containing the status of the erase, and any messages to append.
	 *                    Status can be 'retained', or 'removed'.
	 */
	abstract function get_data( $item );

	/**
	 * Retrieves the ID of this item.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $item The item to export.
	 * @return mixed The item ID
	 */
	public function get_item_id( $item ) {
		return $item->id;
	}

	/**
	 * Retrieves group ID for this item.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $item The item to export.
	 * @return mixed the group ID
	 */
	public function get_group_id( $item ) {
		return $this->id;
	}

	/**
	 * Retrieves the label for this item.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $item The item to export.
	 * @return mixed The group label
	 */
	public function get_group_label( $item ) {
		return $this->name;
	}

	/**
	 * Retrieves the label for this item.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $item The item to export.
	 * @return mixed The group label
	 */
	public function get_group_description( $item ) {
		return $this->description;
	}

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
			return new WP_error( 'exporter_param_not_set', 'The exporter key ' . $key . ' could not be found.' );
		}
	}

}