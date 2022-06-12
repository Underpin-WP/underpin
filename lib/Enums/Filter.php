<?php

namespace Underpin\Enums;

enum Filter: string {

	case not_in = 'not_in';
	case in = 'in';
	case and = 'and';
	case equals = 'equals';

	public function field( string $field ): string {
		return "{$field}__$this->value";
	}

	public function key(): string {
		return $this->field( 'filter_enum_key' );
	}

}