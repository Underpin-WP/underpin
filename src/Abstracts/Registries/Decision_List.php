<?php
/**
 * Provides an extend-able way to make a hierarchial decision
 *
 * @since   1.0.0
 * @package Underpin\Abstracts\Registries
 */


namespace Underpin\Abstracts\Registries;


use Underpin\Abstracts\Decision;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Decision_List
 * Class Decision_List
 *
 * @since   1.0.0
 * @package Underpin\Abstracts\Registries
 */
abstract class Decision_List extends Loader_Registry {

	/**
	 * @inheritDoc
	 */
	protected $abstraction_class = 'Underpin\Abstracts\Decision';

	protected $params = [];

	/**
	 * Make a decision based on the provided params.
	 *
	 * @since 1.0.0
	 *
	 * @param array $params
	 * @return array|\WP_Error
	 */
	public function decide( $params = [] ) {
		if ( empty( $this ) ) {
			return underpin()->logger()->log_as_error(
				'error',
				'decision_list_has_no_decisions',
				'A decision list ran, but there were no decisions to make.',
				[ 'ref' => $this->get_registry_id() ]
			);
		}

		// Sort the params. This is necessary to ensure the cache key is consistent.
		ksort( $params );
		$invalid_decisions = [];
		$decision          = null;

		// Attempt to fetch the decision from the cache.
		$result = wp_cache_get( serialize( $params ), $this->get_registry_id() );

		// If the decision isn't in the cache, run this decision list.
		if ( false === $result ) {
			// Sort decisions before looping through them.
			$this->sort_decisions();

			foreach ( $this as $decision ) {

				// Determine if the decision is valid based on provided params.
				$valid = $decision->is_valid( $params );

				// If the decision generated a WP_Error, it's not valid.
				if ( is_wp_error( $valid ) ) {

					// Record a notice for future reference.
					underpin()->logger()->log_wp_error( 'notice', $valid );

					// Add this to the list of invalid decisions, with the error explaining why.
					$invalid_decisions[ $decision->id ] = $valid;
				} else {

					// Otherwise, we have found the decision to run, so break outta here.
					break;
				}
			}

			// If the decision did not get set, return an error.
			if ( ! isset( $decision ) ) {
				$decision = underpin()->logger()->log_as_error(
					'error',
					'decision_list_could_not_decide',
					'A decision list ran, but all decisions returned false.',
					[ 'ref' => $this->get_registry_id() ]
				);
			}

			$result = [ 'decision' => $decision, 'invalid_decisions' => $invalid_decisions ];
		}

		// Fire actions specific to this list that should happen every time a decision list runs.
		$this->decision_actions( $decision, $invalid_decisions, $params );

		// Return the pertinent information.
		return $result;
	}

	/**
	 * Fires actions that run on entire decision lists.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $decision          The decision that was made
	 * @param array $invalid_decisions The list of invalid decision WP Errors that were made.
	 * @param array $params            The decision params
	 */
	protected function decision_actions( $decision, $invalid_decisions, $params ) {
		wp_cache_add( serialize( $params ), [
			'decision'          => $decision,
			'invalid_decisions' => $invalid_decisions,
		], $this->get_registry_id() );
	}

	/**
	 * Clears the cache based on the provided params.
	 *
	 * @since 1.0.0
	 *
	 * @param $params
	 */
	public function clear_cache( $params ) {
		wp_cache_delete( serialize( $params ), $this->get_registry_id() );
	}

	/**
	 * Sorts decisions by their priority.
	 * Items with lower priority numbers get a chance to be chosen first.
	 *
	 * @since 1.0.0
	 */
	public function sort_decisions() {
		$this->uasort( function( Decision $a, Decision $b ) {
			return $a->priority < $b->priority ? -1 : 1;
		} );
	}

}