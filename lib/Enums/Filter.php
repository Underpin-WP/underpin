<?php

namespace Underpin\Enums;

enum Filter: string {

	case not_in = 'not_in';
	case in = 'in';
	case and = 'and';
	case equals = 'equals';
	case less_than = 'less_than';
	case greater_than = 'greater_than';
	case greater_than_or_equal_to = 'greater_than_or_equal_to';
	case less_than_or_equal_to = 'less_than_or_equal_to';
	case callback = 'callback';

	public function field( string $field ): string {
		return "{$field}__$this->value";
	}

	public function key(): string {
		return $this->field( 'filter_enum_key' );
	}

}