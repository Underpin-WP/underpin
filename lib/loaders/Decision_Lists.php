<?php
/**
 * Decision_Lists
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */


namespace Underpin\Loaders;

use Underpin\Abstracts\Registries\Loader_Registry;
use Underpin\Abstracts\Registries\Decision_List;
use WP_Error;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Decision_Lists
 * Registry to run Decision_Lists
 *
 * @since   1.0.0
 * @package Underpin\Registries\Loaders
 */
class Decision_Lists extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Abstracts\Registries\Decision_List';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
		$this->add( 'event_type_purge_frequency', 'Underpin\Decisions\Event_Type_Purge_Frequency\Event_Type_Purge_Frequency' );
	}

	/**
	 * @param string $key
	 * @return Decision_List|WP_Error Script Resulting block class, if it exists. WP_Error, otherwise.
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

	/**
	 * Runs a decision list.
	 *
	 * @param string $key Decision list key
	 * @param array $params List of params to use in the decision list.
	 * @return mixed|WP_Error The decision list if successful, otherwise WP Error.
	 */
	public function decide( $key, $params ) {
		$decision_list = $this->get( $key );

		if ( is_wp_error( $decision_list ) ) {
			underpin()->logger()->log_wp_error( 'error', $decision_list );

			return $decision_list;
		}

		$decision_result = $decision_list->decide( $params );

		// This is logged in the method directly.
		if ( is_wp_error( $decision_result ) ) {
			return $decision_result;
		}

		underpin()->logger()->log(
			'notice',
			'decision_was_made',
			'A decision list was resolved.',
			[ 'key' => $key, 'result' => $decision_result, 'params' => $params ]
		);

		$decision = $decision_result['decision'];

		return $decision->valid_actions( $params );
	}

}