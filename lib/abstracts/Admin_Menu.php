<?php
/**
 * Admin Menu Abstraction
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts;


use Underpin\Traits\Feature_Extension;
use WP_Admin_Bar;
use Underpin\Loaders\Logger;
use function Underpin\Underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Menu
 * Handles creating custom admin menus
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Admin_Menu {
	use Feature_Extension;

	/**
	 * The menu slug for this item.
	 *
	 * @var string
	 */
	public $menu_slug = '';

	/**
	 * The page title to display on the page. Defaults to $this->name
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $page_title = '';

	/**
	 * The title to display in the menu. Defaults to $this->name
	 *
	 * @since 1.0.0
	 *
	 * @var string The menu title.
	 */
	public $menu_title = '';

	/**
	 * Minimum capability required to see this in the admin menu.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $capability = 'administrator';

	/**
	 * Icon to display in menu.
	 *
	 * @var string The URL to the icon to be used for this menu. Can be a base64-encoded SVG, a dashicons helper class,
	 *      or an empty string, if you want to manipulate this via CSS.
	 */
	public $icon = '';

	/**
	 * The position in which this menu item will be placed. Higher numbers go further down the menu.
	 * Defaults to just under the Users menu.
	 *
	 * @var int
	 */
	public $position = 71;

	/**
	 * Admin_Menu constructor.
	 *
	 * @since 1.0.0
	 *
	 */
	public function __construct() {
		if ( empty( $this->menu_title ) ) {
			$this->menu_title = $this->name;
		}

		if ( empty( $this->page_title ) ) {
			$this->page_title = $this->name;
		}
	}

	/**
	 * Renders the output of the admin page.
	 *
	 * @since 1.1.0
	 *
	 * @return mixed
	 */
	abstract public function render_callback();

	/**
	 * @inheritDoc
	 */
	public function do_actions() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
	}

	public function validate() {
		$errors = new \WP_Error();

		if ( empty( $this->name ) ) {
			Logger::extract( $errors, Underpin()->logger()->log(
				'warning',
				'underpin_primary_menu_name_missing',
				'A name for the primary menu is required.',
				[ 'menu_slug' => $this->menu_slug ]
			) );
		}

		if ( empty( $this->menu_slug ) ) {
			Logger::extract( $errors, Underpin()->logger()->log(
				'warning',
				'underpin_primary_menu_slug_missing',
				'A slug for the primary menu is required.',
				[ 'menu_name' => $this->name ]
			) );
		}

		return $errors->has_errors() ? $errors : true;
	}

	/**
	 * Adds the admin bar.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Admin_Bar $admin_bar The admin bar object.
	 */
	public function add_admin_menu() {

		$valid = $this->validate();

		if ( true === $valid ) {
			add_menu_page(
				$this->page_title,
				$this->menu_title,
				$this->capability,
				$this->menu_slug,
				[ $this, 'render_callback' ],
				$this->icon,
				$this->position
			);
		}
	}

	/**
	 * Retrieves the admin url of this admin page.
	 *
	 * @since 1.1.0
	 *
	 * @param array $query
	 * @return string
	 */
	public function get_url( $query = [] ) {

		$query['page'] = $this->menu_slug;

		$url = get_admin_url();
		$url .= 'admin.php';
		$url = add_query_arg( $query, $url );

		return $url;
	}
}