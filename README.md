# Underpin WordPress Framework

This boilerplate is designed to work well for large, complex WordPress plugins. The goal of this system is to create an
opinionated pattern for creating a plugin in WordPress. It also adds support for useful utilities that plugins need as
they mature, such as a solid error logging utility, a batch processor for upgrade routines, and a decision tree class that
makes extending _and_ debugging multi-layered decisions way easier than traditional WordPress hooks.

## Service Provider

Absolutely everything, except for one-off helper functions in `functions.php` is encapsulated in a singleton-instance
service provider. A key benefit to this pattern is that only _necessary_ components of the plugin get loaded into
WordPress on each server request. This is because the service provider will only require, and set up a class when it is
called to do so.

This service provider also serves as a "directory" of sorts, where a third-party developer can easily see all of the
places in which they can interact with the plugin.

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
1. Admin Pages, Admin sections, and settings fields that don't suck

This plugin also comes bundled with a handful of custom loaders that most plugins _need_ but WordPress doesn't _offer_
out of the box.

1. **Decision Lists** - These make it possible to create a prioritized list of decisions to choose from. It's extend-able, 
and provides plenty of opportunities to better-log _what_ was chosen, and _why_.
1. **Batch Tasks** - This creates a way to add a notice in the WP Admin screen to run a large task in smaller chunks.
Useful for db upgrade routines.
1. **Error Logger** - This system comes with a highly-extendable error logging utility.

It is also fairly straightforward to create custom loaders, so if you have your own extend-able registry of items, you
can add those as well.

## Template System Trait

This plugin also includes a powerful template system. This system clearly separates HTML markup from business logic, and
provides ways to do things like set default params for values, and declare if a template should be public or private.

## Event Logging Utility

This plugin includes a utility that makes it possible to log events in this plugin. These logs are written to files in
the `wp_uploads` directory, and comes equipped with a cron job that automatically purges old logs.

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

## Webpack

It comes with a webpack config that is tailored for WordPress. This works well-enough to make React apps in WordPress,
and has been sufficient for my needs so-far.

## Admin Field Builder

One _powerful_ feature this plugin comes with is a series of pre-built settings fields classes. When used
with the template loader, these fields make it easy to generate form fields using the `place` method.

## Initial Setup

1. Clone this repo, and delete the `.git` directory. Since this is a boilerplate, it's not intended to be updated by the source once cloned.
1. Replace `PLUGIN_NAME_REPLACE_ME` with the abbreviation of your plugin, using UPPER_CASE format.
1. Replace `Plugin_Name_Replace_Me` with the abbreviation of your plugin, using Upper_Snake_Case format.
1. Replace `plugin-name-replace-me` with the abbreviation of your plugin, using lower-dash-case format.
1. Replace `plugin_name_replace_me` with the abbreviation of your plugin, using snake_case format.
1. Replace `plugin name replace me` with the abbreviation of your plugin, using Plugin Name format.
1. (Optional) Open `bootstra.php` and change the constants as-necessary.
1. Start writing.

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
