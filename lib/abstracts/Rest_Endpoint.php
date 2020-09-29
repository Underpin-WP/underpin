<?php
/**
 * Rest Endpoint Abstraction
 *
 * @since 1.0.0
 * @package Underpin\Abstracts
 */

namespace Underpin\Abstracts;

use Underpin\Traits\Feature_Extension;
use WP_Rest_Request;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Rest_Endpoint
 *
 * @since   1.0.0
 *
 * @package Underpin\Abstracts
 */
abstract class Rest_Endpoint {
	use Feature_Extension;

	/**
	 * The REST API's namespace.
	 *
	 * @since 1.0.0
	 */
	public $rest_namespace = 'underpin/v1';

	public $route = '/';

	public $args = [ 'methods' => 'GET' ];

	/**
	 * Endpoint callback.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Rest_Request $request The request object.
	 * @return mixed the REST endpoint response.
	 */
	abstract function endpoint( WP_Rest_Request $request );

	/**
	 * Has permission callback.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Rest_Request $request The request object.
	 * @return mixed the REST endpoint response.
	 */
	abstract function has_permission( WP_Rest_Request $request );

	/**
	 * Rest_Endpoint constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->args['callback']            = [ $this, 'endpoint' ];
		$this->args['permission_callback'] = [ $this, 'has_permission' ];
	}

	/**
	 * @inheritDoc
	 */
	public function do_actions() {
		add_action( 'rest_api_init', [ $this, 'register' ] );
	}

	/**
	 * Registers the endpoints
	 *
	 * @since 1.0.0
	 *
	 * return void
	 */
	public function register() {
		$registered = register_rest_route( $this->rest_namespace, $this->route, $this->args );

		if ( false === $registered ) {
			underpin()->logger()->log(
				'error',
				'rest_route_was_not_registered',
				'The rest route ' . $this->route . ' was not registered. There is probably a __doing_it_wrong notice explaining this further.',
				[ 'route' => $this->route, 'namespace' => $this->rest_namespace, 'args' => $this->args ]
			);
		} else {
			underpin()->logger()->log(
				'notice',
				'rest_route_registered',
				'The rest route ' . $this->route . ' was registered successfully',
				[ 'route' => $this->route, 'namespace' => $this->rest_namespace, 'args' => $this->args ]
			);
		}
	}
	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new WP_error( 'batch_task_param_not_set', 'The batch task key ' . $key . ' could not be found.' );
		}
	}

}