<?php
/**
 * File holding the ValidatorFormats class.
 *
 * @file ValidatorFormats.php
 * @ingroup Validator
 *
 * @author Jeroen De Dauw
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

/**
 * Class holding variouse static methods for the appliance of output formats.
 *
 * @ingroup Validator
 *
 * @author Jeroen De Dauw
 */
final class ValidatorFormats {
	
	/**
	 * Ensures the value is an array.
	 * 
	 * @param $value
	 * @param string name The name of the parameter.
	 * @param array $parameters Array containing data about the so far handled parameters.
	 */
	public static function format_array( &$value, $name, array $parameters ) {
		if ( ! is_array( $value ) ) $value = array( $value );
	}
	
	/**
	 * Returns an array containing only the specified values.
	 * 
	 * @param $value
	 * @param string name The name of the parameter.
	 * @param array $parameters Array containing data about the so far handled parameters.
	 */
	public static function format_filtered_array( &$value, $name, array $parameters ) {
		// TODO: It's possible the way the allowed values are passed here is quite inneficient...
		$params = func_get_args();
		array_shift( $params ); // Ommit the value

		self::format_array( $value, $name, $parameters );
		$filtered = array();
		foreach ( $value as $item ) if ( in_array( $item, $params ) ) $filtered[] = $item;
		
		return $filtered;
	}
	
	/**
	 * Changes the value to list notation, by separating items with a delimiter, 
	 * and/or adding wrappers before and after the items. Intended for lists, but
	 * will also work for single values.
	 * 
	 * @param $value
	 * @param string name The name of the parameter.
	 * @param array $parameters Array containing data about the so far handled parameters.
	 * @param $delimiter
	 * @param $wrapper
	 */
	public static function format_list( &$value, $name, array $parameters, $delimiter = ',', $wrapper = '' ) {
		self::format_array( $value, $name, $parameters );
		$value =  $wrapper . implode( $wrapper . $delimiter . $wrapper, $value ) . $wrapper;
	}

	/**
	 * Changes every value into a boolean.
	 * 
	 * TODO: work with a list of true-values.
	 * 
	 * @param $value
	 * @param string name The name of the parameter.
	 * @param array $parameters Array containing data about the so far handled parameters.
	 */
	public static function format_boolean( &$value, $name, array $parameters ) {
		if ( is_array( $value ) ) {
			$boolArray = array();
			foreach ( $value as $item ) $boolArray[] = in_array( $item, array( 'yes', 'on' ) );
			$value = $boolArray;
		}
		else {
			$value = in_array( $value, array( 'yes', 'on' ) );
		}
	}
	
	/**
	 * Changes every value into a boolean, represented by a 'false' or 'true' string.
	 * 
	 * @param $value
	 * @param string name The name of the parameter.
	 * @param array $parameters Array containing data about the so far handled parameters.
	 */
	public static function format_boolean_string( &$value, $name, array $parameters ) {
		self::format_boolean( $value, $name, $parameters );
		if ( is_array( $value ) ) {
			$boolArray = array();
			foreach ( $value as $item ) $boolArray[] = $item ? 'true' : 'false';
			$value = $boolArray;
		}
		else {
			$value = $value ? 'true' : 'false';
		}
	}

	/**
	 * Changes lists into strings, by enumerating the items using $wgLang->listToText.
	 * 
	 * @param $value
	 * @param string name The name of the parameter.
	 * @param array $parameters Array containing data about the so far handled parameters.
	 */
	public static function format_string( &$value, $name, array $parameters ) {
		if ( is_array( $value ) ) {
			global $wgLang;
			$value = $wgLang->listToText( $value );
		}
	}
	
	/**
	 * Removes duplicate items from lists.
	 * 
	 * @param $value
	 * @param string name The name of the parameter.
	 * @param array $parameters Array containing data about the so far handled parameters.
	 */
	public static function format_unique_items( &$value, $name, array $parameters ) {
		if ( is_array( $value ) ) $value = array_unique( $value );
	}
	
}