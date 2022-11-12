# Underpin

The goal of Underpin is to provide a pattern that makes building PHP projects easier. It
provides support for useful utilities that plugins need as they mature, such as a solid error logging utility, a batch
processor for upgrade routines, and a decision tree class that makes extending _and_ debugging multi-layered decisions
way easier than traditional WordPress hooks.

## Installation

Underpin can be installed in any place you can write code.

### Via Composer

`composer require underpin/underpin`

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


I recently released the 3.0 version of this project, and unfortunately, with how I approached this build I did not document things as well as I should have. I KNOW shame on me. I'll get to it someday...

So many projects, so little time.
