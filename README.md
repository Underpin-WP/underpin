# Underpin WordPress Framework

The goal of Underpin is to provide a pattern that makes building scaleable WordPress plugins and themes easier. It provides support for useful utilities that plugins need as they mature, such as a solid error logging utility, a batch processor for upgrade routines, and a decision tree class that
makes extending _and_ debugging multi-layered decisions way easier than traditional WordPress hooks.

## Installation
Underpin can be installed in any place you can write code for WordPress, including:

1. As a part of a WordPress plugin.
1. As a part of a WordPress theme.
1. As a part of a WordPress must-use plugin.

### Via Composer
`composer install alexstandiford/underpin`

**Note** This will add Underpin as a `mu-plugin`, but due to how WordPress handles must-use plugins, this does _not actually add the plugin to your site_. You must also manually require the file in a mu-plugin PHP file:

```php
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Underpin
$underpin = plugin_dir_path( __FILE__ ) . 'vendor/alexstandiford/underpin/Underpin.php';

if ( file_exists( $underpin ) ) {
	require_once( $underpin );
}
```

### Manually
If you're developing Underpin directly, or simply don't want to use Composer, follow these steps to use:

1. Clone this repository, preferably in the `mu-plugins` directory.
1. Require `Underpin.php`, preferably as a `mu-plugin`.

## Boilerplates
Check out the [Theme](https://github.com/alexstandiford/underpin-theme-boilerplate) and [Plugin](https://github.com/alexstandiford/underpin-plugin-boilerplate) boilerplates that use Underpin. This will give you some examples on how Underpin can be used, and also provide you with a good starting point for your next project.

## Local Dev Environment With Underpin

Along with these options, I also put together a local development environment using Docker Compose and Composer with Underpin. We use this for our custom site builds at [DesignFrame](https://www.designframesolutions.com), and it works pretty well. You can learn more about that [here](https://github.com/DesignFrame/website-template).

### Recommended step - set up your own service provider
This makes it possible to create your own loader registries, and makes it possible to better tailor your plugin to your needs. Check out the [Theme](https://github.com/alexstandiford/underpin-theme-boilerplate) and [Plugin](https://github.com/alexstandiford/underpin-plugin-boilerplate) boilerplates that use Underpin to see how this works.

## Minimum Requirements

1. WordPress `5.1` or better.
1. PHP `5.6` or better.

## Service Provider

Absolutely everything, except for one-off helper functions in `functions.php` is encapsulated in a singleton-instance
service provider. A key benefit to this pattern is that only _necessary_ components of the plugin get loaded into
WordPress on each server request. This is because the service provider will only require, and set up a class when it is
called to do so.

This service provider also serves as a "directory" of sorts, where a third-party developer can easily see all of the
places in which they can interact with the plugin.

Multiple, unique versions of the service provider can be created. This provides an effective way to structure plugin extensions.

## Loaders

A frustrating thing about WordPress is the myriad number of ways things get "added". Everything works _just a little
differently_, and this means a lot of time is spent looking up "how do I do that, again?"

Loaders make it so that everything uses an identical pattern to add items to WordPress. With this system, all of these
things use nearly _exact_ same set of steps to register:

1. Shortcodes
1. Scripts
1. Styles
1. Widgets
1. Cron Jobs
1. REST Endpoints
1. Admin Submenu Pages
1. Menu bar Items
1. Post Types
1. Taxonomies
1. Blocks
1. Admin Pages, Admin sections, and settings fields
1. Theme menus
1. Sidebars
1. Admin notices

This plugin also comes bundled with a handful of custom loaders that most plugins _need_ but WordPress doesn't _offer_
out of the box.

1. **Decision Lists** - These make it possible to create a prioritized list of decisions to choose from. It's extend-able, 
and provides plenty of opportunities to better-log _what_ was chosen, and _why_.
1. **Batch Tasks** - This creates a way to add a notice in the WP Admin screen to run a large task in smaller chunks.
Useful for db upgrade routines.
1. **Error Logger** - This system comes with a highly-extendable error logging utility.

It is also fairly straightforward to create custom loaders, so if you have your own extend-able registry of items, you
can add those as well.

### Registering Things

Everything is registered using a PHP class, in one of three ways:

1. A string reference to a class name
1. An anonymous class
1. An array containing the class name and the constructor arguments

The class name you register must be an instance of the loader's `abstraction_class` value, so if you wanted to register a shortcode, you must make a class that extends `Underpin\Abstracts\Shortcode`.

The examples below work with _any_ loader class, and work in basically the same way. The extended class houses all of the logic necessary to tie everything together.

### EXAMPLE: Register A Shortcode

Expanding on this example, let's say you wanted to register a new shortcode. It might look something like this:

First you would create your Shortcode class. This class happens to have an abstract method, `shortcode_actions`.
```php

class Hello_World extends \Underpin\Abstracts\Shortcode {
	
	$shortcode = 'hello_world';
	
	public function shortcode_actions() {
		// TODO: Implement shortcode_actions() method.
	}
}

```

Looking at the `Shortcode` abstract, we can see that our shortcode atts are stored in `$this->atts`, so we could access that directly if we needed. Since this is a simple example, however, we're simply going to return 'Hello world!"

```php

Namespace Underpin\Shortcodes;

class Hello_World extends \Underpin\Abstracts\Shortcode {
	
	$shortcode = 'hello_world';
	
	public function shortcode_actions() {
		return 'Hello world!'
	}
}

```

Now that our class has been created, we need to register this shortcode. This is done like this:

```php
underpin()->shortcodes()->add( 'hello_world','Underpin\Shortcodes\Hello_World' );
```

Alternatively, you can simply pass this as an abstract class directly if that's your thing. I don't personally like this because it registers the class into memory even when it is _not being used_.

```php
underpin()->shortcodes()->add( 'hello_world',new class extends \Underpin\Abstracts\Shortcode {
	
	public $shortcode = 'hello_world';
	
	public function shortcode_actions() {
		return 'Hello world!'
	}
} );
```

Either way, this shortcode can be accessed using `do_shortcode('hello_world');`, or you can access the class, and its methods directly with `underpin()->shortcodes()->get( 'hello_world' )`;

### Example with constructor

Sometimes, it makes more sense dynamically register things using a constructor. This pattern works in the same manner as above, the only difference is how you pass your information to the `add()` method.

Let's say you want to register a shortcode for every post type on the site. You could do with the help of a constructor. something like:

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

The key part here is how differently we handled the `add` method. Instead of simply providing a instance name, we instead provide an array containing the `class`, and an array of ordered `args` to pass directly into the contstructor. As a result, we register this class to be constructed if it is ever needed.

## Template System Trait

This plugin also includes a powerful template system. This system clearly separates HTML markup from business logic, and
provides ways to do things like set default params for values, and declare if a template should be public or private. Any time a class needs to output HTML on a screen, this trait can be used.

### Example: Expand Hello World Shortcode into a Template

Let's take the registered `Hello_World` class above, and modify it so that it uses the template loader trait to get some actual HTML output, and a user name.

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

`get_templates` returns an array of templates that this class supports, as well as each template's visibility. This makes it possible for a plugin to create a template that can be overwritten by a theme by settin the template to `public`.

`get_template_group` determines the subdirectory name to look for the templates, and `get_template_root_path` determines the path to the template directory root.

Finally, `get_template` actually calls the template method, and passes the instance of the object into the included file. It also passes an array of paramaters.

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

`get_param` provides a second argument to provide a fallback value should the specified param not be set, or be invalid. In this case, if a name wasn't provided, the template will automatically replace it with `stranger`.

### Nesting Templates

Since the template loader passes the instance into the template, it's possible to load sub-templates inside of the template. A WordPress loop may look something like this:

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

Where `post.php` and `no-posts.php` are separate PHP files in the same directory, and registered to `The_Loop` under `get_templates`.

## Debug Logger
If you're logged in and add `underpin_debug=1` to the end of any URL, an "Underpin events" button appears in the admin bar. This provides a debugging interface that dumps out all of the items that were registered in the request, as well as any events that were logged in that request. This context can be useful, especially in production environments where debugging can be difficult.

## Event Logging Utility

This plugin includes a utility that makes it possible to log events in this plugin. These logs are written to files in
the `wp_uploads` directory, and comes equipped with a cron job that automatically purges old logs. Additinally, the method in which the logger saves data can be extended by creating a custom Writer class.

### Using the Error Logger

This plugin comes with 3 event types - `error`, `warning`, and `notice`. `error` events get written to a log,
and `warning` or `notice` only display in the on-screen console when `WP_DEBUG` is enabled. This allows you to add
a ton of `notices` and `warnings` without bogging down the system with a lot of file writing.

To write to the logger, simply chain into the `logger` method.

```php
plugin_name_replace_me()->logger()->log(
'error',
'error_code',
'error_message',
['arbitrary' => 'data', 'that' => 'is relevant', 'ref' => 1]
);
```

You can also log `WP_Error` objects directly.

```php
$error = new \WP_Error('code','Message',['data' => 'to use']);
plugin_name_replace_me()->logger()->log_wp_error('error',$error);
```

Caught exceptions can be captured, too.

```php
try{
echo 'hi';
}catch(Exception $e ){
plugin_name_replace_me()->logger()->log_exception('error', $e);
}
```

By default, the logger will return a `Log_Item` class, but you can also _return_ a `WP_Error` object, instead with `log_as_error`

```php
$wp_error_object = plugin_name_replace_me()->logger()->log_as_error(
'error',
'error_code',
'error_message',
['arbitrary' => 'data', 'that' => 'is relevant']
);

var_dump($wp_error_object); // WP_Error...
```

### Gather Errors

Sometimes, you will run several functions in a row that could potentially return an error. Gather errors will lump them
into a single `WP_Error` object, if they are actually errors.

```php
$item_1 = function_that_returns_errors();
$item_2 = another_function_that_returns_errors();

$errors = underpin()->logger()->gather_errors($item_1,$item_2);

if($errors->has_errors()){
  // Do do something if either of the items were a WP Error.
} else{
 // All clear, proceed.
}
```

### Event Types

You can register your own custom event types if you want to log things that do not fit in any of the three defaults. A 
common example is when a background process runs - it would be nice to have a log of when that runs, and what happened.

To do this, you would need to create a custom event type. That is done by extending the `Event_Type` class.

```php

namespace Plugin_Name_Replace_Me\Event_Types;
/**
 * Class Background_Process
 * Error event type.
 *
 * @since 1.0.0
 *
 * @since
 * @package
 */
class Background_Process extends Event_Type {

	/**
	 * Event type
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $type = 'background_process';

	/**
	 * Writes this to the log.
	 * Set this to true to cause this event to get written to the log.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $write_to_log = true;

	/**
	 * @var inheritDoc
	 */
	public $description = 'Logs when background processes run.';

	/**
	 * @var inheritDoc
	 */
	public $name = "Background Processes";
}
```

Then, you need to add this item to your logger registry. This is usually done in the `setup` method inside `Service_Locator`

```php
	/**
	 * Set up active loader classes.
	 *
	 * This is where you can add anything that needs "registered" to WordPress,
	 * such as shortcodes, rest endpoints, blocks, and cron jobs.
	 *
	 * All supported loaders come pre-packaged with this plugin, they just need un-commented here
	 * to begin using.
	 *
	 * @since 1.0.0
	 */
	protected function _setup() {
      plugin_name_replace_me()->logger()->add('background_process', '\Plugin_Name_Replace_Me\Event_Types\Background_Process');
	}
```

That's it! Now you can use the background process event type anywhere you want.

### Writers

The Event_Type uses a class, called a `Writer` to write error logs to a file. Underpin comes bundled with a file writing
system that works for most situations, but if for some reason you wanted your logger to write events in a different manner,
a good way to-do that is by overriding the `$writer_class` variable of your event type.

Let's say we wanted to receive an email every time our background process logged an event. Writers can help us do that.
 First, we specify the namespace and class name of the writer that we're going to create.

```php

namespace Plugin_Name_Replace_Me\Event_Types;
/**
 * Class Background_Process
 * Error event type.
 *
 * @since 1.0.0
 *
 * @since
 * @package
 */
class Background_Process extends Event_Type {

	/**
	 * Event type
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $type = 'background_process';

	/**
	 * Writes this to the log.
	 * Set this to true to cause this event to get written to the log.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $write_to_log = true;

	/**
	 * @var inheritDoc
	 */
	public $description = 'Logs when background processes run.';

	/**
	 * @var inheritDoc
	 */
	public $name = "Background Processes";


	/**
	 * The class to instantiate when writing to the error log.
	 *
	 * @since 1.0.0
	 *
	 * @var string Namespaced instance of writer class.
	 */
	public $writer_class = 'Plugin_Name_Replace_Me\Factories\Email_Logger';
}
```

Then, we create the class in the correct directory that matches our namespace. It should extend the `Writer` class.

## Decision Lists

Typically, WordPress plugins rely solely on WordPress hooks to determine extended logic. This works for simple solutions,
but it becomes cumbersome very fast as soon as several plugins are attempting to override one-another. The biggest issue
 is that the actual logic that determines the decision is _decentralized_. Since there isn't a single source-of-truth to
 dictate the order of logic, let along what the actual _choices are_, you have no easy way of understanding _why_ a plugin
 decided to-do what it did.
 
 Decision lists aim to make this easier to work with by making the extensions all _centralized_ in a single registry. This
 registry is exported in the Underpin console when `WP_DEBUG` is enabled, so it is abundantly clear _what_ the actual hierarchy
 is for this site.
 
 If you're debugging a live site, you can output the decision list using a PHP console tool, such as debug bar console.
 
 ```php
 var_dump(plugin_name_replace_me()->decision_lists()->get('email'));
```
 
 ### Set Up
 
 Fundamentally a Decision List is nothing more than a loader class, and can be treated in the same way.
 
 Let's say we wanted to create a decision list that allows people to override an email address in a plugin. This would
 need to check an options value for an email address, and fallback to a hard-coded address. It also needs to be possible
 to override this value with other plugins.
 
 In traditional WordPress, you would probably see this done using `apply_filters` at the end of the function, something
 like this:
 
 ```php
function get_email_address(){
  
  return apply_filters('plugin_name_replace_me_email_address',get_option('email_address', 'admin@webmaster.com'));
}
```

With a decision list, however, this is put inside of a class, and that class can be extended. Like so:

```php

/**
 * Class Email To
 * Class Email to list
 *
 * @since   1.1.0
 * @package DFS_Monitor\Factories
 */
class Email_To extends Decision_List {

	public $description = 'Determines which email address this plugin should use.';
	public $name = 'Email Address';

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {

		$this->add( 'option', new class extends Integration_Frequency_Decision {

			public $id = 'option';
			public $name = 'Option Value';
			public $description = 'Uses the value of the db option, if it is set.';
			public $priority = 100;


			public function is_valid( $params = [] ) {
				if(!is_email(get_option('email_address'))){
				  return plugin_name_replace_me()->logger()->log(
				    'notice',
                    'email_address_option_invalid',
                    'A decision tree did not use the option value because it is not set.'
                  );
                } else{
                  return true;  
              }             
			}

			/**
			 * @inheritDoc
			 */
			public function valid_actions( $params = [] ) {
				return get_option('email_address');
			}
		} );


		$this->add( 'hard_coded', new class extends Integration_Frequency_Decision {

			public $id = 'hard_coded';
			public $name = 'Hard coded email';
			public $description = 'Uses a hard-coded email address for this site.';
			public $priority = 1000;

			public function is_valid( $params = [] ) {
				return true;
			}

			public function valid_actions( $params = [] ) {
				return 'admin@webmaster.com';
			}
		} );
	}
}
```

Notice that I'm using anonymous classes here, just to keep everything in a single file. You absolutely _do not_ have to
use anonymous classes. In fact, in most cases you shouldn't. If you pass a reference to the class as a string, it will
not instantiate the class unless it's explicitly called. This saves on resources and keeps things fast.

The `$priority` value inside each class tells the decision tree which option to try to use first. If it returns a `WP_Error`, it moves on
to the next one. As soon as it finds an option that returns `true`, it grabs the value from the `valid_actions` method, and move on.

Like the custom logger class, this needs to be registered inside `Service_Locator`.


```php
	/**
	 * Set up active loader classes.
	 *
	 * This is where you can add anything that needs "registered" to WordPress,
	 * such as shortcodes, rest endpoints, blocks, and cron jobs.
	 *
	 * All supported loaders come pre-packaged with this plugin, they just need un-commented here
	 * to begin using.
	 *
	 * @since 1.0.0
	 */
	protected function _setup() {
      plugin_name_replace_me()->decision_lists()->add('email', '\Plugin_Name_Replace_Me\Decision_Lists\Email_To');
	}
```

Finally, we can use this decision list directly in our `get_email_address` function:

 ```php
function get_email_address(){
  
  // Decide which action we should take.
  $decide = plugin_name_replace_me()->decision_lists()->get('email')->decide();

  // Return the valid decision.
  if(!is_wp_error($decide) && $decide['decision'] instanceof Decision){
    return $decide['decision']->valid_actions();
  }

  // Bubble up the error, otherwise.
  return $decide;
}
```

Now that we have this set up, it can be extended by other plugins using the `add` method. The example below would force
the decision list to run this _before_ any other option.

```php
plugin_name_replace_me()->decision_lists()->get('email')->add('custom_option',new class extends \Underpin\Abstracts\Decision{

  // Force this to run before all other options
  public $priority = 50;
  public $name = 'Custom Option Name';
  public $description = 'This custom name is used in an extension, and overrides the default';

  public function is_valid($params = []){
    // TODO: Implement is_valid() method.
  }

  public function valid_actions($params = []){
  // TODO: Implement valid_actions() method.
  }


});
```

## Admin Field Builder

One _powerful_ feature this plugin comes with is a series of pre-built settings fields classes. When used
with the template loader, these fields make it easy to generate form fields using the `place` method.

## Working With Scripts

This boilerplate comes with baked-in support for working with scripts and styles. This uses the same pattern as any other
loader registry. To register a script, you need to do the following:

1. Add an entry point for your script in `webpack.config.js`
1. Create a new Script, or Style loader
1. Add the loader to the appropriate registry.
1. Enqueue with `plugin_name_replace_me()->scripts()->enqueue('script-handle')`

### 1.) Add an entry point

First off, you need to add the entry point to the `webpack.config.js` file.

```js
// webpack.config.js
//...
	entry: {
		// JS.
		scriptName: './assets/js/src/script-name.js'
	}
//...
```

### 2.) Create the PHP Loader

Create a new loader in `lib/loaders/scripts/` directory. You can pass the same params through `parent::__construct()` as
what is typically passed to `wp_register_script`, however you only _need_ to specify the `handle`.

If no path is specified, it will automatically assume the path to the script is:
`./assets/js/build/HANDLE.min.js`

So, as long as your `handle` matches what you specify for the `entry` in `webpack.config.js`, you need not
specify a path at all.

You can also specify what should be passed to the Javascript via `wp_localize_script`. This is done by returning an
array of values in `get_localized_params`.

The script is localized just before it is enqueued. If you need to localize earlier, you can manually run
`Script::localize()` at any time.

```php
<?php

namespace Plugin_Name_Replace_Me\Loaders\Scripts;


use Plugin_Name_Replace_Me\Abstracts\Script;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_Script extends Script {

	public function __construct() {
		parent::__construct( 'scriptName' );
	}

	public function get_localized_params() {
		return [
			'scriptName' => 'This goes to the javascript',
		];
	}

}

```

## Register the script in `registries/loaders/Scripts.php`

Register the script in `set_default_items`.

```php
<?php
//...

	/**
	 * @inheritDoc
	 */
	protected function set_default_items() {
        // Registers the test script
		$this->add( 'scriptName', '\Plugin_Name_Replace_Me\Loaders\Scripts\Test_Script' );
	}

//...
```

## Working With Styles

Styles work in the exact same fashion as scripts. The only difference is you work with the `Styles` abstraction and the
`Styles` loader registry.

## Autoloader

This boilerplate includes a basic autoloading system. By default, the namespace will represent the subdirectories within
the `lib` directory of the plugin.

For Example, any file with `namespace Example_Plugin\Cron` would need to be located in `lib/cron/`.

As long as your namespaces line up, and you utilize the registries in the manners detailed in this document, you should
_never_ need to manually require a file.
