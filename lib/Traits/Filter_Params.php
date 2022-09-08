<?php

namespace Underpin\Traits;


use Underpin\Enums\Filter;

trait Filter_Params {

	protected array $filter_args = [];

	/**
	 * Sets the query to only include items that are not an of the provided instances.
	 *
	 * @param array $values The values to filter.
	 *
	 * @return $this
	 */
	public function not_instance_of( ...$values ): static {
		$this->filter_args[ Filter::not_in->field( 'instanceof' ) ] = $values;

		return $this;
	}

	/**
	 * Sets the query to only include items that are an instance of all the provided instances.
	 *
	 * @param array $values The values to filter.
	 *
	 * @return $this
	 */
	public function has_all_instances( ...$values ): static {
		$this->filter_args[ Filter::and->field( 'instanceof' ) ] = $values;

		return $this;
	}

	/**
	 * Sets the query to only include items that are instance of any the provided instances.
	 *
	 * @param array $values The values to filter.
	 *
	 * @return $this
	 */
	public function has_any_instances( ...$values ): static {
		$this->filter_args[ Filter::in->field( 'instanceof' ) ] = $values;

		return $this;
	}

	/**
	 * Sets the query to only include items that are instance provided instances.
	 *
	 * @param string $value the instance
	 *
	 * @return $this
	 */
	public function instance_of( string $value ): static {
		$this->filter_args[ Filter::equals->field( 'instanceof' ) ] = $value;

		return $this;
	}

	/**
	 * Sets the query to filter out items whose field has any of the provided values.
	 *
	 * @param string $field  The field to check against.
	 * @param array  $values The values to filter.
	 *
	 * @return $this
	 */
	public function not_in( string $field, ...$values ): static {
		$this->filter_args[ Filter::not_in->field( $field ) ] = $values;

		return $this;
	}

	/**
	 * Sets the query to filter out items whose field does not have all the provided values.
	 *
	 * @param string $field  The field to check against.
	 * @param array  $values The values to filter.
	 *
	 * @return $this
	 */
	public function and( string $field, ...$values ): static {
		$this->filter_args[ Filter::and->field( $field ) ] = $values;

		return $this;
	}

	/**
	 * Sets the query to filter out items whose field does not have all the provided values.
	 *
	 * @param string $field  The field to check against.
	 * @param array  $values The values to filter.
	 *
	 * @return $this
	 */
	public function in( string $field, ...$values ): static {
		$this->filter_args[ Filter::in->field( $field ) ] = $values;

		return $this;
	}


	/**
	 * Sets the query to filter out items whose value is not identical to the provided value.
	 *
	 * @param string $field The field to check against.
	 * @param mixed  $value The value to check.
	 *
	 * @return $this
	 */
	public function equals( string $field, mixed $value ): static {
		$this->filter_args[ Filter::equals->field( $field ) ] = $value;

		return $this;
	}

	/**
	 * Sets the query to filter out items whose key has any of the provided values.
	 *
	 * @param array $values The values to filter.
	 *
	 * @return $this
	 */
	public function key_not_in( ...$values ): static {
		$this->filter_args[ Filter::not_in->key() ] = $values;

		return $this;
	}

	/**
	 * Sets the query to filter out items whose key does not have all the provided values.
	 *
	 * @param array $values The values to filter.
	 *
	 * @return $this
	 */
	public function key_in( ...$values ): static {
		$this->filter_args[ Filter::in->key() ] = $values;

		return $this;
	}

}