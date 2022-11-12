<?php

namespace Underpin\Traits;


use Closure;
use ReflectionException;
use ReflectionFunction;
use SplFileObject;

trait With_Closure_Converter {

	/**
	 * Converts a closure to something that can be safely converted to a string.
	 *
	 * @param Closure $data
	 *
	 * @return array
	 * @throws ReflectionException
	 */
	private static function convert_closure( Closure $data ): array {
		$ref  = new ReflectionFunction( $data );
		$file = new SplFileObject( $ref->getFileName() );
		$file->seek( $ref->getStartLine() - 1 );
		$content = '';
		while ( $file->key() < $ref->getEndLine() ) {
			$content .= $file->current();
			$file->next();
		}

		return array(
			$content,
			$ref->getStaticVariables(),
		);
	}
}