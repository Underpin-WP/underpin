<?php
/**
 * Admin Sub Menu abstraction
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin\Abstracts;


use Underpin\Traits\Feature_Extension;
use function Underpin\Underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Page
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Admin_Sub_Menu {
	use Feature_Extension;

	/**
	 * Parent slug for this menu. This can either be a slug, or a registered primary menu key
	 * If no slug is specified, this menu will still be registered, but it will not appear in the WordPress menu.
	 *
	 * @since 1.0.0
	 *
	 * @var string the parent slug.
	 */
	protected $parent_menu = 'options-general.php';

	/**
	 * The title to display in the admin menu.
	 *
	 * @since 1.0.0
	 *
	 * @var string the menu title.
	 */
	protected $menu_title = '';

	/**
	 * The page title to display on the page.
	 *
	 * @since 1.0.0
	 *
	 * @var string the page title.
	 */
	protected $page_title = '';

	/**
	 * The capability required to visit this admin page.
	 *
	 * @since 1.0.0
	 *
	 * @var string the capability.
	 */
	protected $capability = 'administrator';

	/**
	 * The unique identifier for this menu.
	 *
	 * @since 1.0.0
	 *
	 * @var string the menu slug.
	 */
	protected $menu_slug = '';

	/**
	 * The position in the menu order this item should appear.
	 *
	 * @since 1.0.0
	 *
	 * @var int the menu position.
	 */
	protected $position = null;

	/**
	 * Callback function to render the actual settings content.
	 *
	 * @since 1.0.0
	 */
	abstract public function render_callback();

	/**
	 * Admin_Page constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args List of arguments used to create this menu page.
	 */
	public function __construct() {
		$this->page_title = empty( $this->page_title ) ? $this->name : $this->page_title;
	}

	/**
	 * @inheritDoc
	 */
	public function do_actions() {
		add_action( 'admin_menu', [ $this, 'register_sub_menu' ] );
	}

	/**
	 * Determines if the current page is this admin page.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_admin_page() {
		return is_admin() && isset( $_GET['page'] ) && $this->menu_slug === $_GET['page'];
	}

	/**
	 * Retrieves the parent menu slug from the parent menu.
	 * This will attempt to get the parent menu from the registry. If it fails, it will pass the menu directly.
	 * This makes it possible to directly pass a menu slug, or the registry ID.
	 *
	 * @since 1.1.0
	 *
	 * @return string The menu slug
	 */
	public function get_parent_menu_slug() {
		$parent_menu = Underpin()->admin_menus()->get( $this->parent_menu );

		if ( is_wp_error( $parent_menu ) ) {
			return $this->parent_menu;
		}

		return $parent_menu->menu_slug;
	}

	/**
	 * Registers sub menus
	 *
	 * @since 1.0.0
	 */
	public function register_sub_menu() {
		add_submenu_page(
			$this->get_parent_menu_slug(),
			$this->page_title,
			$this->menu_title,
			$this->capability,
			$this->menu_slug,
			[ $this, 'render_callback' ],
			$this->position
		);

		Underpin()->logger()->log(
			'notice',
			'submenu_page_added',
			'The submenu page ' . $this->page_title . ' Has been added.',
			[ 'ref' => $this->parent_menu, 'menu_title' => $this->menu_title ]
		);
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

		$url = get_admin_url();
		$url .= $this->parent_menu;
		$url .= '?page=' . $this->menu_slug;
		$url = add_query_arg( $query, $url );

		return $url;
	}
}