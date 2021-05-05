# Underpin WordPress Framework

The goal of Underpin is to provide a pattern that makes building scaleable WordPress plugins and themes easier. It
provides support for useful utilities that plugins need as they mature, such as a solid error logging utility, a batch
processor for upgrade routines, and a decision tree class that makes extending _and_ debugging multi-layered decisions
way easier than traditional WordPress hooks.

## Installation

Underpin can be installed in any place you can write code for WordPress, including:

1. As a part of a WordPress plugin.
1. As a part of a WordPress theme.
1. As a part of a WordPress must-use plugin.

### Via Composer

`composer install underpin/underpin`

**Note** This will add Underpin as a `mu-plugin`, but due to how WordPress handles must-use plugins, this does _not
actually add the plugin to your site_. You must also manually require the file in a mu-plugin PHP file:

```php
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Underpin, and its dependencies.
$autoload = plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

require_once( $autoload );
```

### Manually

If you're developing Underpin directly, or simply don't want to use Composer, follow these steps to use:

1. Clone this repository, preferably in the `mu-plugins` directory.
1. Require `Underpin.php`, preferably as a `mu-plugin`.

## Boilerplates

Check out the [Theme](https://github.com/alexstandiford/underpin-theme-boilerplate)
and [Plugin](https://github.com/alexstandiford/underpin-plugin-boilerplate) boilerplates that use Underpin. This will
give you some examples on how Underpin can be used, and also provide you with a good starting point for your next
project.

## Local Dev Environment With Underpin

Along with these options, I also put together a local development environment using Docker Compose and Composer with
Underpin. We use this for our custom site builds at [DesignFrame](https://www.designframesolutions.com), and it works
pretty well. You can learn more about that [here](https://github.com/DesignFrame/website-template).

## Minimum Requirements

1. WordPress `5.1` or better.
1. PHP `7.0` or better.

## The Bootstrap

Underpin's bootstrap class encapsulates everything a singleton-instance service provider. This class has a number of key
purposes.

1. It serves as a service provider. It autoloads your namespaced files, and only loads _necessary_ components of the
   plugin on each server request.
1. It has a series of preflight checks to ensure that the environment the plugin is running on meets the minimum
   requirements.
1. It includes a place to retrieve plugin-wide values, like the plugin URL, the text translation domain.

### Autoloader

This boostrap includes a basic autoloading system. By default, the namespace will represent the subdirectories within
the `lib` directory of the plugin.

For Example, any file with `namespace Example_Plugin\Cron` would need to be located in `lib/cron/`.

As long as your namespaces line up, and you utilize the registries in the manners detailed in this document, you should
_never_ need to manually require a file.

### Create your own Bootstrap

While it is possible to work directly with the `underpin` function as your bootstrap, it's considered a best practice to
make your own bootstrap function that creates its own instance of `Underpin`. This keeps each plugin's registries
separate from one-another, and helps prevent code collisions and other unexpected problems.

### Basic Example

The simplest example of the bootstrap makes use of the `make_class` function. This handy function is used throughout
Underpin, and behind the scenes it spins up a pre-determined PHP class from the arguments provided. In this case, an
instance of `Underpin\Factories\Underpin_Instance` is created.

```php
/**
 * Fetches the instance of the plugin.
 * This function makes it possible to access everything else in this plugin.
 * It will automatically initiate the plugin, if necessary.
 * It also handles autoloading for any class in the plugin.
 *
 * @since 1.0.0
 *
 * @return \Underpin\Factories\Underpin_Instance The bootstrap for this plugin.
 */
function plugin_name_replace_me() {
	return Underpin\Abstracts\Underpin::make_class( [
		'root_namespace'      => 'Plugin_Name_Replace_Me',
		'text_domain'         => 'plugin_name_replace_me',
		'version'             => '1.0.0',
		'minimum_wp_version'  => '5.1',
		'minimum_php_version' => '7.0',
		'setup_callback'      => function( $instance ){
           // Actions that happen when this plugin is started up.
		}
	] )->get( __FILE__ );
}
```

### Extending The Bootstrap

There are many circumstances in-which you will need to extend your bootstrap class. Perhaps you want to change how
minimum requirements behave, or maybe you want to add a custom method to the class. To-do this, you must create your own
instance of `Underpin\Abstracts\Underpin`.

The example above could be converted into a class that looks like this:

```php

class Plugin_Name_Replace_Me extends Underpin\Abstracts\Underpin{

  
	/**
	 * The namespace for loaders. Used for loader autoloading.
	 *
	 * @since 1.0.0
	 *
	 * @var string Complete namespace for all loaders.
	 */
	protected $root_namespace = "Plugin_Name_Replace_Me";

	/**
	 * Translation Text domain.
	 *
	 * Used by translation method for translations.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $text_domain = 'plugin_name_replace_me';

	/**
	 * Minimum PHP Version.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $minimum_php_version = '7.0';

	/**
	 * Current Version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $version = '1.0.0';

	/**
	 * Minimum WordPress Version.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $minimum_wp_version = '5.1';

    protected function _setup(){
      // Actions that happen when this plugin is started up.
    }
}
```

From there, you can create function like so:

```php
/**
 * Fetches the instance of the plugin.
 * This function makes it possible to access everything else in this plugin.
 * It will automatically initiate the plugin, if necessary.
 * It also handles autoloading for any class in the plugin.
 *
 * @since 1.0.0
 *
 * @return \Underpin\Factories\Underpin_Instance The bootstrap for this plugin.
 */
function plugin_name_replace_me() {
	return ( new Plugin_Name_Replace_Me )->get( __FILE__ );
}
```

### Extending From a Factory

A common theme in WordPress is to have several add-ons that all use the same copy-pasted bootstrap, however the
bootstrap contents are identical between each plugin. In these cases, you would want to use _one extended class for all
plugins_.

Fortunately, this is trivial with `Underpin::make_class`.

Instead of extending the abstract class, extend the `Underpin_Instance` class. This is the default class that is used by
`Underpin::make_class`, and by extending it you can customize what arguments get passed in your bootstrap.

```php

class Plugin_Name_Replace_Me extends Underpin\Factories\Underpin_Instance{
  use Underpin\Traits\Instance_Setter;
  // Add any public, or protected paramaters you wish to be override-able with Underpin::make_class 
  protected $custom_param;
  
  protected $custom_function_callback;
  
  // If a function needs to be override-able, you can use set_callable
  protected function custom_function( $args ){
    return $this->set_callable( $this->custom_function_callback, $args );
  }
}
```

Now, simply call this class using `Underpin::make_class`.

```php
/**
 * Fetches the instance of the plugin.
 * This function makes it possible to access everything else in this plugin.
 * It will automatically initiate the plugin, if necessary.
 * It also handles autoloading for any class in the plugin.
 *
 * @since 1.0.0
 *
 * @return \Underpin\Factories\Underpin_Instance The bootstrap for this plugin.
 */
function plugin_name_replace_me() {
	return Underpin\Abstracts\Underpin::make_class( [
	    'class' => 'Plugin_Name_Replace_Me',
	    'args'  => [
	        'custom_param'        => 'Custom paramater value',
            'root_namespace'      => 'Plugin_Name_Replace_Me',
            'text_domain'         => 'plugin_name_replace_me',
            'version'             => '1.0.0',
            'minimum_wp_version'  => '5.1',
            'minimum_php_version' => '7.0',
            'custom_callback'     => function( $args ){
              // Action that fires on the custom_callback function.
            },
            'setup_callback'      => function( $instance ){
               // Actions that happen when this plugin is started up.
            }
		]
	] )->get( __FILE__ );
}
```

## Loaders

A frustrating thing about WordPress is the myriad number of ways things get "added". Everything works _just a little
differently_, and this means a lot of time is spent looking up "how do I do that, again?"

Loaders make it so that everything uses an identical pattern to add items to WordPress. With this system, all of these
things use nearly _exact_ same set of steps to register.

Currently, there are 23 different loaders that can be installed alongside Underpin, and used to extend its core
functionality.

1. [Admin Bar Menu Loader](https://github.com/Underpin-WP/admin-bar-menu-loader) Create custom menus on the WP Admin
   Bar.
1. [Admin Notice Loader](https://github.com/Underpin-WP/admin-notice-loader/) Loader That assists with adding admin
   notices to a WordPress website.
1. [Admin Pages](https://github.com/Underpin-WP/admin-page-loader) Quickly spin up admin settings pages.
1. [Batch Task Loader](https://github.com/Underpin-WP/batch-task-loader) Create, register, and implement batch tasks.
1. [Block Loader](https://github.com/Underpin-WP/underpin-block-loader) Create, register, and manage WordPress blocks.
1. [Cron Job Loader](https://github.com/Underpin-WP/cron-job-loader/) Create, manage, and execute cron jobs.
1. [Custom Post Type Loader](https://github.com/Underpin-WP/custom-post-type-loader) Loader That assists with adding
   custom Post Types to a WordPress website.
1. [Decision List Loader](https://github.com/Underpin-WP/decision-list-loader) Create decision list registries that
   makes custom logic easy to extend.
1. [Eraser Loader](https://github.com/Underpin-WP/eraser-loader) Loader That assists with adding GDPR-compliant erasers
   to a WordPress website.
1. [Exporter Loader](https://github.com/Underpin-WP/underpin-exporter-loader) Loader That assists with adding
   GDPR-compliant exporters to a WordPress website.
1. [Logger Loader](https://github.com/Underpin-WP/logger-loader) Log events, and errors, and write them to a debug log
1. [Menu Loader](https://github.com/Underpin-WP/menu-loader) Register, and manage custom theme nav menus
1. [Meta Loader](https://github.com/Underpin-WP/meta-loader) Manage custom meta to store in various meta tables
1. [Option Loader](https://github.com/Underpin-WP/option-loader) Register , and manage values to store in wp_options
1. [Rest Endpoint Loader](https://github.com/Underpin-WP/rest-endpoint-loader) Create, register, and manage REST
   endpoints
1. [Role Loader](https://github.com/Underpin-WP/role-loader) Create, and register custom roles
1. [Script Loader](https://github.com/Underpin-WP/script-loader) Create, and enqueue scripts
1. [Shortcode Loader](https://github.com/Underpin-WP/shortcode-loader) Create, and render custom shortcodes
1. [Sidebar Loader](https://github.com/Underpin-WP/sidebar-loader) Create, and manage WordPress sidebars
1. [Style Loader](https://github.com/Underpin-WP/style-loader) Create, and enqueue styles
1. [Taxonomy Loader](https://github.com/Underpin-WP/taxonomy-loader) Create, and manage custom taxonomies
1. [Underpin BerlinDB](https://github.com/Underpin-WP/underpin-berlindb) Register, and manage custom database tables
   with [BerlinDB](https://github.com/berlindb/core/)
1. [Widget Loader](https://github.com/Underpin-WP/widget-loader) Create widgets, complete with admin settings.

### Creating Custom Loaders

It is also fairly straightforward to create custom loaders, so if you have your own extend-able registry of items, you
can add those as well.

### Registering Things

Everything is registered with `Underpin::make_class`, and can be registered in one of three ways:

1. A string reference to a class name
1. An anonymous class
1. An array containing the class name and the constructor arguments
1. An array containing constructor arguments.

The class name you register must be an instance of the loader's `abstraction_class` value, so if you wanted to register
a shortcode, you must make a class that extends `Underpin\Abstracts\Shortcode`.

The examples below work with _any_ loader class, and work in basically the same way. The extended class houses all of
the logic necessary to tie everything together.

### EXAMPLE: Register A Shortcode

Expanding on this example, let's say you wanted to register a new shortcode. It might look something like this:

```php

class Hello_World extends \Underpin_Shortcodes\Abstracts\Shortcode {
	
	protected $shortcode = 'hello_world';
	
	public function shortcode_actions() {
		// TODO: Implement shortcode_actions() method.
	}
}

```

#### Extended Class

First you would create your Shortcode class. This class happens to have an abstract method, `shortcode_actions`.

Looking at the `Shortcode` abstract, we can see that our shortcode atts are stored in `$this->atts`, so we could access
that directly if we needed. Since this is a simple example, however, we're simply going to return 'Hello world!"

```php

Namespace Underpin\Shortcodes;

class Hello_World extends \Underpin_Shortcodes\Abstracts\Shortcode {
	
	protected $shortcode = 'hello_world';
	
	public function shortcode_actions() {
		return 'Hello world!';
	}
}

```

Now that our class has been created, we need to register this shortcode. This is done like this:

```php
underpin()->shortcodes()->add( 'hello_world','Underpin\Shortcodes\Hello_World' );
```

#### Register Inline

Alternatively, you can register the class inline. This will automatically use a default instance of the Shortcode with
no customizations.

```php
underpin()->shortcodes()->add( 'hello_world', [
	'shortcode'                  => 'hello_world',                   // Required. Shortcode name.
	'shortcode_actions_callback' => function ( $parsed_atts ) {      // Required. Shortcode action.
		return 'Hello world!'; // 'value'
	},
] );
```

#### Register Inline With Factory

Finally, you can register the class inline, using a different class for the factory. This makes it possible to customize
the factory that is used.

This is particualrly useful in cases where multiple registered items need similar treatment. It provides a way to extend
classes without creating unique classes in the process.

First, extend the instance in whatever way you want.

```php

Namespace Underpin\Factories;

class Hello_World_Instance extends \Underpin_Shortcodes\Factories\Shortcode_Instance {

  /* Cusotmize the class */

}

```

Finally, instruct Underpin to use a different class.

```php
underpin()->shortcodes()->add( 'hello_world', [
    'class' => 'Underpin\Factories\Hello_World_Instance',
    'args'  => [
	  'shortcode'                  => 'hello_world',                   // Required. Shortcode name.
	  'shortcode_actions_callback' => function ( $parsed_atts ) {      // Required. Shortcode action.
	  	  return 'Hello world!';                                       // 'value'
	  },
	]
] );
```

Either way, this shortcode can be accessed using `do_shortcode('hello_world');`, or you can access the class, and its
methods directly with `underpin()->shortcodes()->get( 'hello_world' )`;

### Example with constructor

Sometimes, it makes more sense dynamically register things using a constructor. This pattern works in the same manner as
above, the only difference is how you pass your information to the `add()` method.

Let's say you want to register a shortcode for every post type on the site. You could do with the help of a constructor.
something like:

```php

class Post_Type_Shortcode extends \Underpin\Abstracts\Shortcode {

	public function __construct( $post_type ) {
		$this->shortcode = $post_type . '_is_the_best';

		$this->post_type = $post_type;
	}

	public function shortcode_actions() {
		echo $this->post_type . ' is the best post type';
	}
}

```

And then register each one like so:

```php

add_action( 'init', function() {
	$post_types    = get_post_types( [], 'objects' );
	$ignored_types = flare_wp_get_ignored_post_types();

	foreach ( $post_types as $post_type ) {
		if ( ! in_array( $post_type->name, $ignored_types ) ) {
			$this->shortcodes()->add( $post_type->name . '_shortcode', [
				'class' => 'Flare_WP\Shortcodes\Post_Type_Shortcode',
				'args'  => [ $post_type ],
			] );
		}
	}
} );
```

The key part here is how differently we handled the `add` method. Instead of simply providing a instance name, we
instead provide an array containing the `class`, and an array of ordered `args` to pass directly into the contstructor.
As a result, we register this class to be constructed if it is ever needed.

## Template System Trait

This plugin also includes a templating system. This system clearly separates HTML markup from business logic, and
provides ways to do things like set default params for values, and declare if a template should be public or private.
Any time a class needs to output HTML on a screen, this trait can be used.

### Example: Expand Hello World Shortcode into a Template

Let's take the registered `Hello_World` class above, and modify it so that it uses the template loader trait to get some
actual HTML output, and a user name.

```php

Namespace Underpin\Shortcodes;


class Hello_World extends \Underpin\Abstracts\Shortcode {
	use \Underpin\Traits\Templates;

	public $shortcode = 'hello_world';

	public function shortcode_actions() {
		return 'Hello world!';
	}

	public function get_templates() {
		// TODO: Implement get_templates() method.
	}

	protected function get_template_group() {
		// TODO: Implement get_template_group() method.
	}

	protected function get_template_root_path() {
		// TODO: Implement get_template_root_path() method.
	}
}

```

The Template loader needs some fundamental information before it can be used futher. Let's fill those out a bit.

```php

class Hello_World extends \Underpin\Abstracts\Shortcode {
	use \Underpin\Traits\Templates;

	public $shortcode = 'hello_world';

	public function shortcode_actions() {
		
		$params = [];
		
		if(is_user_logged_in()){
			$params['name'] = wp_get_current_user()->user_nicename;
		}
		return $this->get_template( 'index', $params );
	}

	public function get_templates() {
		return [
			'index' => 'public',
		];
	}

	protected function get_template_group() {
		return 'hello-world';
	}

	protected function get_template_root_path() {
		underpin()->template_dir();
	}
}

```

`get_templates` returns an array of templates that this class supports, as well as each template's visibility. This
makes it possible for a plugin to create a template that can be overwritten by a theme by settin the template
to `public`.

`get_template_group` determines the subdirectory name to look for the templates, and `get_template_root_path` determines
the path to the template directory root.

Finally, `get_template` actually calls the template method, and passes the instance of the object into the included
file. It also passes an array of paramaters.

So based on this, we would need to add a new PHP file: `/path/to/directory/root/hello-world/index.php`

And that file would look something like:

```php
<?php

if ( ! isset( $template ) || ! $template instanceof Hello_World ) {
	return;
}

?>

<h1>Hello <?= $template->get_param( 'name', 'stranger' ) ?>!</h1>
```

`get_param` provides a second argument to provide a fallback value should the specified param not be set, or be invalid.
In this case, if a name wasn't provided, the template will automatically replace it with `stranger`.

### Nesting Templates

Since the template loader passes the instance into the template, it's possible to load sub-templates inside of the
template. A WordPress loop may look something like this:

```php
<?php

if ( ! isset( $template ) || ! $template instanceof The_Loop ) {
	return;
}

?>

<div>
<?php if( $template->query->has_posts() ): while( $template->query->has_posts() ) :$template->query->the_post() ?>
	<?= $template->get_template( 'post' ) ?>
<?php endwhile; ?>
<?php else: ?>
	<?= $template->get_template( 'no-posts' ); ?>
<?php endif; ?>
</div>
```

Where `post.php` and `no-posts.php` are separate PHP files in the same directory, and registered to `The_Loop`
under `get_templates`.

## Fields API

The fields API is a collection of pre-built factories that render, sanitize, and display fields. These fields make it
possible to display form fields. This API is used extensively in some loaders, like
the [Widgets loader](https://github.com/Underpin-WP/widget-loader) and
the [Admin Page loader](https://github.com/Underpin-WP/admin-page-loader).

This API does _not_ actually interact with the database in any fashion. Instead, it handles sanitizing, rendering, and
retrieving the field's values. You then _use_ the fields to actually interact with the database in whatever manner you
require.

### Basic Example

```php
$text_field = new \Underpin\Factories\Settings_Fields\Text( 'field value', [
    'name'        => 'name', // See WP_Widget get_field_name
    'description' => underpin()->__( 'Human-readable description' ),
    'label'       => underpin()->__( 'Field Name' ),
] );

// Render the field
echo $text_field->place();

// Update the field value
$text_field->update_value( 'new field value' );

// Store the value in the database. This would save the option key of "name" as "new field value"
update_option( $text_field->get_setting_key(), $text_field->get_field_value() );
```

### Overriding the Option Key

Sometimes it is necessary to have a different name, ID, and option key to store in the database. The widgets API is a
good example of this.

`Settings_Field::get_setting_key` will automatically use `setting_key` if it is explicitly specified. If not, it will
fallback to the `name` field as illustrated above.

```php
$text_field = new \Underpin\Factories\Settings_Fields\Text( $name, [
    'name'        => $widget->get_field_name( 'name' ), // See WP_Widget get_field_name
    'id'          => $widget->get_field_id( 'name' ),   // See WP_Widget get_field_id
    'setting_key' => 'name',                            // Must match field name and field ID
    'description' => underpin()->__( 'Human Readable Description' ),
    'label'       => underpin()->__( 'Field Name' ),
] );


// Render the field
echo $text_field->place();

// Update the field value
$text_field->update_value( 'new field value' );

// Store the value in the database. This would save the option key of "name" as "new field value"
update_option( $text_field->get_setting_key(), $text_field->get_field_value() );
```

### Custom Fields

It is possible to create custom settings fields by extending `Underpin\Abstracts\Settings_Field`. This allows you to
make customized fields that change the behavior in just about any way imagine-able.

If you're interested in creating a custom field, check out any of the existing factories to see how they're built.