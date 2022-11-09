<?php

namespace Underpin\Interfaces;


interface Observer {

	/**
	 * @param                    $instance
	 * @param Data_Provider|null $provider
	 *
	 * @return void
	 */
	public function update( $instance, ?Data_Provider $provider ): void;
	/**
	 * @return string
	 */
	public function get_id(): string;
}