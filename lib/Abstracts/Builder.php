<?php

namespace Underpin\Abstracts;

use Underpin\Exceptions\Item_Not_Found;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Helpers\Array_Helper;
use Underpin\Interfaces\Can_Convert_To_Array;
use Underpin\Interfaces\Can_Create;
use Underpin\Interfaces\Can_Delete;
use Underpin\Interfaces\Can_Read;
use Underpin\Interfaces\Can_Update;
use Underpin\Interfaces\Model;
use Underpin\Interfaces\Query;
use Underpin\WordPress\Builders\Meta_Query_Builder;
use Underpin\WordPress\Builders\Post_Query_Builder;
use Underpin\WordPress\Interfaces\Can_Convert_To_WP_Post;
use Underpin\WordPress\Loaders\Custom_Post_Types\Data_Stores\Post_Type_Data_Store;
use WP_Post;
use WP_Query;

abstract class Builder implements Can_Convert_To_Array {

	protected array $args = [];

	protected function set_bool( string $key, bool $value ): static {
		$this->args[ $key ] = $value;

		return $this;
	}

	protected function set_string( string $key, string $value ): static {
		$this->args[ $key ] = $value;

		return $this;
	}

	protected function set_array( string $key, array $values ): static {
		$this->args[ $key ] = $values;

		return $this;
	}

	protected function set_int( string $key, int $value ): static {
		$this->args[ $key ] = $value;

		return $this;
	}

	protected function set_varidic( string $single_key, string $multi_key, array $values ): static {
		if ( count( $values ) === 1 ) {
			$this->args[ $single_key ] = $values[0];
		} else {
			$this->args[ $multi_key ] = $values;
		}

		return $this;
	}

}