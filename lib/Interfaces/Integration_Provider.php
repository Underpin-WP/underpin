<?php

namespace Underpin\Interfaces;

use Underpin\Exceptions\Unmet_Requirements;

interface Integration_Provider {

	/**
	 * The full path to this plugin's base file.
	 *
	 * @return string
	 */
	public function get_file(): string;

	/**
	 * The fill URL path to the root directory of this plugin's base file.
	 *
	 * @return string
	 */
	public function get_url(): string;

	/**
	 * The full path to this plugin's root.
	 *
	 * @return string
	 */
	public function get_dir(): string;

	/**
	 * A human-readable name of this plugin.
	 *
	 * @return string
	 */
	public function get_name(): string;

	/**
	 * A human-readable description of this plugin.
	 *
	 * @return string
	 */
	public function get_description(): string;

	/**
	 * The plugin's current version
	 *
	 * @return string
	 */
	public function get_version(): string;

	/**
	 * @return true True if requirements are met.
	 * @throws Unmet_Requirements
	 */
	public function minimum_requirements_met(): bool;

}