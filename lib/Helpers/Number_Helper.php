<?php

namespace Underpin\Helpers;




class Number_Helper {

	public static function get_percentage( $number, $divided_by, $precision = false ): float|int {
		if ( $number === 0 || $divided_by === 0 ) return 0;

		$percentage = 100 / ( $number / $divided_by );

		if ( is_int( $precision ) ) return round( $percentage, $precision );

		return $percentage;
	}

	public static function get_numeric_contraction( int $number ): string {
		return match ( $number ) {
			0       => '0',
			1       => '1st',
			2       => '2nd',
			3       => '3rd',
			default => $number . 'th'
		};
	}

}