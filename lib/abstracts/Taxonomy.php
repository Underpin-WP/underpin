<?php
/**
 * Taxonomy Abstraction.
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
 * Class Taxonomy
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Taxonomy {
	use Feature_Extension;
	/**
	 * The taxonomy name.
	 *
	 * @since 1.0.0
	 *
	 * @var string The post type "$type" argument.
	 */
	protected $name = '';

	/**
	 * The post type args.
	 *
	 * @since 1.0.0
	 *
	 * @var array The list of post type args. See https://developer.wordpress.org/reference/functions/register_post_type/
	 */
	protected $args = [];

	/**
	 * The post type, or types to use.
	 *
	 * @var string|array A single post type, or an array of registered post types.z
	 */
	protected $post_type = 'post';

	/**
	 * @inheritDoc
	 */
	public function do_actions() {
		add_action( 'init', [ $this, 'register' ], 11 );
	}

	/**
	 * Registers the post type.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$registered = register_taxonomy( $this->name, $this->post_type, $this->args );

		if ( is_wp_error( $registered ) ) {
			underpin()->logger()->log_wp_error( 'error', $registered );
		} else {
			underpin()->logger()->log(
				'notice',
				'registered_taxonomy',
				'The taxonomy ' . $this->name . ' has been registered to ' . $this->post_type . '.',
				[ 'ref' => $this->name, 'post_type' => $this->post_type ]
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