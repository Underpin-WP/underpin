<?php

namespace Underpin\Helpers\Processors;


use Underpin\Abstracts\Sort_Method;
use Underpin\Enums\Direction;
use Underpin\Enums\Types;
use Underpin\Exceptions\Invalid_Field;
use Underpin\Exceptions\Operation_Failed;
use Underpin\Factories\Sort_Methods\Basic;
use Underpin\Helpers\Object_Helper;
use Underpin\Traits\Sort_Params;

class List_Sorter {

	use Sort_Params;

	public function __construct( protected array $items ) {

	}

	public static function seed( $items, $args ): static {
		$self            = new static( $items );
		$self->sort_args = $args;

		return $self;
	}

	/**
	 * @param $key
	 *
	 * @return array
	 */
	protected function prepare_field( $key ): array {
		// Process the argument key
		$processed = explode( '__', $key );

		return [ 'field' => $processed[0], 'direction' => Direction::from( $processed[1] ) ];
	}

	/**
	 * @return array
	 * @throws Operation_Failed
	 */
	public function sort(): array {
		foreach ( $this->sort_args as $field__direction => $method ) {
			/* @var string $field */
			/* @var Direction $direction */
			extract( $this->prepare_field( $field__direction ) );

			if ( ! $method ) {
				$method = Basic::class;
			}

			usort( $this->items, function ( $a, $b ) use ( $field, $direction, $method ) {
				/* @var Sort_Method $instance */
				$instance = Object_Helper::make_class( $method, Sort_Method::class );

				return $instance->sort( $a, $b, $field, $direction );
			} );
		}

		return $this->items;
	}

}