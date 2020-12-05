<?php
/**
 * Admin Bar Menu Abstraction
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts;


use Underpin\Traits\Feature_Extension;
use WP_Admin_Bar;
use WP_User;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Bar_Menu
 * Handles creating custom admin bar menus
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Admin_Bar_Menu {
use Feature_Extension;

	/**
	 * The children of this admin bar menu.
	 *
	 * @var array
	 */
	public $children = [];

	/**
	 * Minimum capability required to see this in the admin menu.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $capability = 'administrator';

	/**
	 * Unique identifier.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Array of arguments. Parsed in the constructor.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $args;

	/**
	 * The position in which this menu item will be placed. Higher numbers go further to the right.
	 * Lower numbers go further to the left.
	 *
	 * @var int
	 */
	public $position = 500;

	/**
	 * Admin_Bar_Menu constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $item_id unique ID for this item.
	 * @param array  $args    Array of args to create this menu.
	 */
	public function __construct( $item_id, $args ) {
		$defaults = [
			'title'  => 'Underpin',
			'href'   => '#',
			'parent' => false,
			'meta'   => [],
		];

		$this->id   = $item_id;
		$this->args = wp_parse_args( $args, $defaults );
	}

	/**
	 * Checks if the user can view this menu.
	 *
	 * @since 1.0.0
	 *
	 * @param false|int|WP_User $user The user to check. If not specified, this will use the current user.
	 * @return bool True if the user can view the menu. False otherwise.
	 */
	public function user_can_view_menu( $user = false ) {
		if ( false === $user ) {
			$can_view_menu = current_user_can( $this->capability );
		} else {
			$can_view_menu = user_can( $user, $this->capability );
		}

		return $can_view_menu;
	}

	/**
	 * @inheritDoc
	 */
	public function do_actions() {
		add_action( 'admin_bar_menu', [ $this, 'add_admin_bar' ], $this->position );
	}

	/**
	 * Adds the admin bar.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Admin_Bar $admin_bar The admin bar object.
	 */
	public function add_admin_bar( WP_Admin_Bar $admin_bar ) {
		if ( $this->user_can_view_menu() ) {
			$args       = $this->args;
			$args['id'] = $this->id;

			underpin()->logger()->log(
				'notice',
				'child_menu_added',
				'An admin bar menu item, ' . $this->id . ' was added'
			);

			$admin_bar->add_menu( $args );

			foreach ( $this->children as $id => $child ) {
				$child['id']     = $id;
				$child['parent'] = $this->id;

				$admin_bar->add_menu( $child );

				underpin()->logger()->log(
					'notice',
					'child_menu_added',
					'A child menu item, ' . $id . ' was added to the ' . $this->name . ' admin bar menu.'
				);
			}
		} else {
			underpin()->logger()->log(
				'warning',
				'user_cannot_view_menu',
				'The specified user cannot view the ' . $this->name . ' menu. It will not be displayed.',
				array( 'ref' => $this->id, 'capability_required' => $this->capability )
			);
		}
	}
}