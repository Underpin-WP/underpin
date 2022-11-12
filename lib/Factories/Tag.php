<?php

namespace Underpin\Factories;

use Underpin\Interfaces\Can_Convert_To_String;
use Underpin\Registries\Mutable_Collection_With_Remove;

class Tag implements Can_Convert_To_String {

	protected string                               $content;
	public readonly Mutable_Collection_With_Remove $attributes;
	protected string                               $tag;

	public function __construct( protected $force_close = false ) {
		$this->attributes = Mutable_Collection_With_Remove::make( Html_Attribute::class );
	}

	public function add_attribute( Html_Attribute $attribute ): static {
		$this->attributes->add( $attribute->get_id(), $attribute );

		return $this;
	}

	public function get_content(): ?string {
		return $this->content ?? null;
	}

	public function set_content( string $content ): static {
		$this->content = $content;

		return $this;
	}

	public function set_tag( string $tag ): static {
		$this->tag = $tag;

		return $this;
	}

	public function get_tag(): string {
		return $this->tag;
	}

	public function __toString(): string {
		return $this->to_string();
	}

	public function to_string(): string {
		$attributes = implode( ' ', $this->attributes->each( fn ( Html_Attribute $attribute ) => (string) $attribute ) );
		$output     = implode( ' ', [ '<' . $this->get_tag(), $attributes . '>' ] );

		if ( $this->get_content() ) {
			$output .= $this->get_content();
		}

		if ( $this->get_content() || $this->force_close ) {
			$output .= '</' . $this->get_tag() . '>';
		}

		return $output;
	}

}