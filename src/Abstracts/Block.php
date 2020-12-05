<?php
/**
 * WordPress Block Abstraction
 *
 * @since   1.0.0
 * @package Lib\Core\Abstracts
 */


namespace Underpin\Abstracts;

use Underpin\Traits\Feature_Extension;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Block
 * WordPress Block Class
 *
 * @since   1.0.0
 * @package Lib\Core\Abstracts
 */
abstract class Block {
	use Feature_Extension;

	/**
	 * The registered block.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	public $type = false;

	/**
	 * Args to pass when registering the block.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $args = [];

	/**
	 * The script that should be registered alongside this block, if any.
	 * This expects to be the key for a registered script.
	 *
	 * @since 1.0.0
	 *
	 * @var string|bool|Script Class name, or declaration. False if no script is used.
	 */
	public $script = false;

	/**
	 * The style that should be registered alongside this block, if any.
	 * This expects to be the name of a Style class that can be instantiated.
	 *
	 * @since 1.0.0
	 *
	 * @var string|bool|Style Class name, or declaration. False if no style is used.
	 */
	public $style = false;

	/**
	 * Block constructor.
	 */
	public function __construct() {

		if ( false === $this->type ) {
			underpin()->logger()->log(
				'error',
				'invalid_block_type',
				'The provided block does not appear to have a type set',
				[ 'class' => get_class( $this ), 'type' => $this->type, 'expects' => 'string' ]
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function do_actions() {
		add_action( 'init', [ $this, 'register' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles_and_scripts' ] );
	}

	/**
	 * Prepares the script. Generally used to localize last-minute params without overriding the enqueue method.
	 *
	 * @since 1.0.0
	 */
	public function prepare_script() {
		$script = underpin()->scripts()->get( $this->script );
		$script->set_param( 'nonce', wp_create_nonce( 'wp_rest' ) );
		$script->set_param( 'rest_url', get_rest_url() );
	}

	/**
	 * Enqueues admin styles and scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles_and_scripts() {
		if ( ! is_wp_error( underpin()->scripts()->get( $this->script ) ) ) {
			$this->prepare_script();
			underpin()->scripts()->get( $this->script )->enqueue();
		}

		$style = underpin()->styles()->get( $this->style );

		if ( ! is_wp_error( $style ) ) {
			$style->enqueue();
		}
	}


	/**
	 * Registers the block type.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$registered = register_block_type( $this->type, $this->args );
		if ( false === $registered ) {
			underpin()->logger()->log(
				'error',
				'block_not_registered',
				'The provided block failed to register. Register block type provides a __doing_it_wrong warning explaining more.',
				[ 'ref' => $this->type, 'expects' => 'string' ]
			);
		} else {
			underpin()->logger()->log(
				'notice',
				'block_registered',
				'The provided block was registered successfully.',
				[ 'ref' => $this->type, 'args' => $this->args ]
			);
		}
	}
	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new WP_error( 'block_param_not_set', 'The batch task key ' . $key . ' could not be found.' );
		}
	}

}