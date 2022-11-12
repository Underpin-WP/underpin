<?php

namespace Underpin\Traits;

trait With_Instance {

	/**
	 * @var static $instance
	 */
	protected static $instance;

	/**
	 * @return static
	 */
	public static function instance(): static {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static;
		}

		return static::$instance;
	}

}