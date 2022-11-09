<?php

namespace Underpin\Interfaces;


interface Observer extends Item_With_Dependencies {

	/**
	 * @param                    $instance
	 * @param Data_Provider|null $provider
	 *
	 * @return void
	 */
	public function update( $instance, ?Data_Provider $provider ): void;

}