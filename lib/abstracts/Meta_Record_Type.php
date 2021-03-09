<?php

namespace Underpin\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Meta_Record_Type {

	protected $key = '';

	protected $default_value = '';

	protected $type = '';

	/**
	 * Option constructor.
	 *
	 * @sinced 1.1.1
	 *
	 * @param string $key           The option key
	 * @param string $description   A human-readable description of this option
	 * @param string $name          Human readable name.
	 * @param mixed  $default_value The default value to set for this setting
	 */
	public function __construct( $key, $description, $name, $default_value = [] ) {
		$this->key           = $key;
		$this->description   = $description;
		$this->name          = $name;
		$this->default_value = $default_value;
	}

	/**
	 * Adds the metadata.
	 *
	 * @since 1.1.1
	 *
	 * @return bool
	 */
	public function add( $object_id, $unique = false ) {
		return add_metadata( $this->type, $object_id, $this->key, $this->default_value, $unique );
	}

	/**
	 * Retrieves the record.
	 *
	 * @since 1.1.1
	 *
	 * @param int  $object_id   ID of the object metadata is for.
	 * @param bool $single      Optional. If true, return only the first value of the specified meta_key.
	 *                          This parameter has no effect if meta_key is not specified. Default false.
	 *
	 * @return mixed|void
	 */
	public function get( $object_id, $single = false ) {
		return get_metadata( $this->type, $object_id, $this->key, $single );
	}

	/**
	 * Updates the record to the specified value.
	 *
	 * @since 1.1.1
	 *
	 * @param int   $object_id   ID of the object metadata is for.
	 * @param mixed $value       Metadata value. Must be serializable if non-scalar.
	 * @param mixed $prev_value  Optional. Previous value to check before updating.
	 *                           If specified, only update existing metadata entries with
	 *                           this value. Otherwise, update all entries. Default empty.
	 *
	 * @return bool True if updated, otherwise false
	 */
	public function update( $object_id, $value, $prev_value = '' ) {
		return update_metadata( $this->type, $object_id, $this->key, $value, $prev_value );
	}

	/**
	 * Deletes the record.
	 *
	 * @since 1.1.1
	 *
	 * @param int   $object_id   ID of the object metadata is for.
	 * @param mixed $value       Optional. Metadata value. Must be serializable if non-scalar.
	 *                           If specified, only delete metadata entries with this value.
	 *                           Otherwise, delete all entries with the specified meta_key.
	 *                           Pass `null`, `false`, or an empty string to skip this check.
	 *                           (For backward compatibility, it is not possible to pass an empty string
	 *                           to delete those entries with an empty string for a value.)
	 *
	 * @return bool
	 */
	public function delete( $object_id, $value = '' ) {
		return delete_metadata( $this->type, $object_id, $this->key, $value );
	}

	/**
	 * Resets the record to the default value.
	 *
	 * @since 1.0.0
	 *
	 * @param int $object_id ID of the object metadata is for.
	 *
	 * @return bool
	 */
	public function reset( $object_id ) {
		return $this->update( $object_id, $this->default_value );
	}

}