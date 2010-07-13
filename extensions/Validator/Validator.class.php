<?php

/**
 * File holding the Validator class.
 *
 * @file Validator.class.php
 * @ingroup Validator
 *
 * @author Jeroen De Dauw
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

/**
 * Class for parameter validation.
 *
 * @ingroup Validator
 *
 * @author Jeroen De Dauw
 *
 * TODO: break on fatal errors, such as missing required parameters that are dependencies 
 * TODO: correct invalid parameters in the main loop, as to have correct dependency handling
 * TODO: settings of defaults should happen as a default behaviour that can be overiden by the output format,
 * 		 as it is not wished for all output formats in every case, and now a hacky approach is required there.
 */
final class Validator {

	/**
	 * @var boolean Indicates whether parameters not found in the criteria list
	 * should be stored in case they are not accepted. The default is false.
	 */
	public static $storeUnknownParameters = false;

	/**
	 * @var boolean Indicates whether parameters not found in the criteria list
	 * should be stored in case they are not accepted. The default is false.
	 */
	public static $accumulateParameterErrors = false;
	
	/**
	 * @var boolean Indicates whether parameters that are provided more then once 
	 * should be accepted, and use the first provided value, or not, and generate an error.
	 */
	public static $acceptOverriding = false;
	
	/**
	 * @var boolean Indicates if errors in list items should cause the item to be omitted,
	 * versus having the whole list be set to it's default.
	 */
	public static $perItemValidation = true;
	
	/**
	 * @var mixed  The default value for parameters the user did not set and do not have their own
	 * default specified.
	 */
	public static $defaultDefaultValue = '';
	
	/**
	 * @var string The default delimiter for lists, used when the parameter definition does not
	 * specify one.
	 */
	public static $defaultListDelimeter = ',';
	
	/**
	 * @var array Holder for the validation functions.
	 */
	private static $mValidationFunctions = array(
		'in_array' => array( 'ValidatorFunctions', 'in_array' ),
		'in_range' => array( 'ValidatorFunctions', 'in_range' ),
		'is_numeric' => array( 'ValidatorFunctions', 'is_numeric' ),
		'is_float' => array( 'ValidatorFunctions', 'is_float' ),
		'is_integer' => array( 'ValidatorFunctions', 'is_integer' ),
		'not_empty' => array( 'ValidatorFunctions', 'not_empty' ),
		'has_length' => array( 'ValidatorFunctions', 'has_length' ),
		'regex' => array( 'ValidatorFunctions', 'regex' ),
	);
	
	/**
	 * @var array Holder for the list validation functions.
	 */
	private static $mListValidationFunctions = array(
		'item_count' => array( 'ValidatorFunctions', 'has_item_count' ),
		'unique_items' => array( 'ValidatorFunctions', 'has_unique_items' ),
	);

	/**
	 * @var array Holder for the formatting functions.
	 */
	private static $mOutputFormats = array(
		'array' => array( 'ValidatorFormats', 'format_array' ),
		'list' => array( 'ValidatorFormats', 'format_list' ),
		'boolean' => array( 'ValidatorFormats', 'format_boolean' ),
		'boolstr' => array( 'ValidatorFormats', 'format_boolean_string' ),
		'string' => array( 'ValidatorFormats', 'format_string' ),
		'unique_items' => array( 'ValidatorFormats', 'format_unique_items' ),
		'filtered_array' => array( 'ValidatorFormats', 'format_filtered_array' ),
	);

	/**
	 * An array containing the parameter definitions. The keys are main parameter names,
	 * and the values are associative arrays themselves, consisting out of elements that 
	 * can be seen as properties of the parameter as they would be in the case of objects.
	 * 
	 * @var associative array
	 */
	private $mParameterInfo;
	
	/**
	 * An array initially containing the user provided values. Adittional data about
	 * the validation and formatting processes gets added later on, and so stays 
	 * available for validation and formatting of other parameters.
	 * 
	 * original-value
	 * default
	 * position
	 * original-name
	 * formatted-value
	 * 
	 * @var associative array
	 */
	private $mParameters = array();
	
	/**
	 * Arrays for holding the (main) names of valid, invalid and unknown parameters. 
	 */
	private $mValidParams = array();
	private $mInvalidParams = array();
	private $mUnknownParams = array();

	/**
	 * Holds all errors and their meta data. 
	 * 
	 * @var associative array
	 */
	private $mErrors = array();

	/**
	 * Determines the names and values of all parameters. Also takes care of default parameters. 
	 * After that the resulting parameter list is passed to Validator::setParameters
	 * 
	 * @param array $rawParams
	 * @param array $parameterInfo
	 * @param array $defaultParams
	 * @param boolean $toLower Indicates if the parameter values should be put to lower case. Defaults to true.
	 */
	public function parseAndSetParams( array $rawParams, array $parameterInfo, array $defaultParams = array(), $toLower = true ) {
		$parameters = array();

		$nr = 0;
		$defaultNr = 0;
		
		foreach ( $rawParams as $arg ) {
			// Only take into account strings. If the value is not a string,
			// it is not a raw parameter, and can not be parsed correctly in all cases.
			if ( is_string( $arg ) ) {
				$parts = explode( '=', $arg, 2 );
				
				// If there is only one part, no parameter name is provided, so try default parameter assignment.
				if ( count( $parts ) == 1 ) {
					// Default parameter assignment is only possible when there are default parameters!
					if ( count( $defaultParams ) > 0 ) {
						$defaultParam = array_shift( $defaultParams );
						$parameters[strtolower( $defaultParam )] = array(
							'original-value' => trim( $toLower ? strtolower( $parts[0] ) : $parts[0] ),
							'default' => $defaultNr,
							'position' => $nr
						);
						$defaultNr++;
					}
					else {
						// It might be nice to have some sort of warning or error here, as the value is simply ignored.
					}
				} else {
					$paramName = trim( strtolower( $parts[0] ) );
					
					$parameters[$paramName] = array(
						'original-value' => trim( $toLower ? strtolower( $parts[1] ) : $parts[1] ),
						'default' => false,
						'position' => $nr
					);
					
					// Let's not be evil, and remove the used parameter name from the default parameter list.
					// This code is basically a remove array element by value algorithm.
					$newDefaults = array();
					
					foreach( $defaultParams as $defaultParam ) {
						if ( $defaultParam != $paramName ) $newDefaults[] = $defaultParam;
					}
					
					$defaultParams = $newDefaults;
				}
			}
			$nr++;
		}	

		$this->setParameters( $parameters, $parameterInfo, false );
	}
	
	/**
	 * Loops through a list of provided parameters, resolves aliasing and stores errors
	 * for unknown parameters and optionally for parameter overriding.
	 * 
	 * @param array $parameters Parameter name as key, parameter value as value
	 * @param array $parameterInfo Main parameter name as key, parameter meta data as valu
	 * @param boolean $toLower Indicates if the parameter values should be put to lower case. Defaults to true.
	 */
	public function setParameters( array $parameters, array $parameterInfo, $toLower = true ) {
		$this->mParameterInfo = $parameterInfo;

		// Loop through all the user provided parameters, and destinguise between those that are allowed and those that are not.
		foreach ( $parameters as $paramName => $paramData ) {
			$paramName = trim( strtolower( $paramName ) );
			
			// Attempt to get the main parameter name (takes care of aliases).
			$mainName = self::getMainParamName( $paramName );

			// If the parameter is found in the list of allowed ones, add it to the $mParameters array.
			if ( $mainName ) {
				// Check for parameter overriding. In most cases, this has already largely been taken care off, 
				// in the form of later parameters overriding earlier ones. This is not true for different aliases though.
				if ( !array_key_exists( $mainName, $this->mParameters ) || self::$acceptOverriding ) {
					// If the valueis an array, this means it has been procesed in parseAndSetParams already.
					// If it is not, setParameters was called directly with an array of string parameter values.
					if ( is_array( $paramData ) && array_key_exists( 'original-value', $paramData ) ) {
						$paramData['original-name'] = $paramName;
						$this->mParameters[$mainName] = $paramData;							
					}
					else {
						if ( is_string( $paramData ) ) {
							$paramData = trim( $paramData );
							if ( $toLower ) $paramData = strtolower( $paramData );
						}
						$this->mParameters[$mainName] = array(
							'original-value' => $paramData,
							'original-name' => $paramName,
						);						
					}
				}
				else {
					$this->mErrors[] = array( 'type' => 'override', 'name' => $mainName );
				}
			}
			else { // If the parameter is not found in the list of allowed ones, add an item to the $this->mErrors array.
				if ( self::$storeUnknownParameters ) $this->mUnknownParams[] = $paramName;
				$this->mErrors[] = array( 'type' => 'unknown', 'name' => $paramName );
			}		
		}
	}
	
	/**
	 * Returns the main parameter name for a given parameter or alias, or false
	 * when it is not recognized as main parameter or alias.
	 *
	 * @param string $paramName
	 *
	 * @return string or false
	 */
	private function getMainParamName( $paramName ) {
		$result = false;

		if ( array_key_exists( $paramName, $this->mParameterInfo ) ) {
			$result = $paramName;
		}
		else {
			foreach ( $this->mParameterInfo as $name => $data ) {
				if ( array_key_exists( 'aliases', $data ) && in_array( $paramName, $data['aliases'] ) ) {
					$result = $name;
					break;
				}
			}
		}

		return $result;
	}	
	
	/**
	 * First determines the order of parameter handling based on the dependency definitons,
	 * and then goes through the parameters one by one, first validating and then formatting,
	 * storing any encountered errors along the way.
	 * 
	 * The 'value' element is set here, either by the cleaned 'original-value' or default.
	 */
	public function validateAndFormatParameters() {
		$dependencyList = array();
		
		foreach ( $this->mParameterInfo as $paramName => $paramInfo ) {
			$dependencyList[$paramName] = 
				array_key_exists( 'dependencies', $paramInfo ) ? (array)$paramInfo['dependencies'] : array();
		}
		
		$sorter = new TopologicalSort( $dependencyList, true );
		$orderedParameters = $sorter->doSort();

		foreach ( $orderedParameters as $paramName ) {
			$paramInfo = $this->mParameterInfo[$paramName];
			
			// If the user provided a value for this parameter, validate and handle it.
			if ( array_key_exists( $paramName, $this->mParameters ) ) {

				$this->cleanParameter( $paramName );

				if ( $this->validateParameter( $paramName ) ) {
					// If the validation succeeded, add the parameter to the list of valid ones.
					$this->mValidParams[] = $paramName;
					$this->setOutputTypes( $paramName );
				}
				else {
					// If the validation failed, add the parameter to the list of invalid ones.
					$this->mInvalidParams[] = $paramName;
				}
			}
			else {
				// If the parameter is required, add a new error of type 'missing'.
				// TODO: break when has dependencies
				if ( array_key_exists( 'required', $paramInfo ) && $paramInfo['required'] ) {
					$this->mErrors[] = array( 'type' => 'missing', 'name' => $paramName );
				}
				else {
					// Set the default value (or default 'default value' if none is provided), and ensure the type is correct.
					$this->mParameters[$paramName]['value'] = array_key_exists( 'default', $paramInfo ) ? $paramInfo['default'] : self::$defaultDefaultValue; 
					$this->mValidParams[] = $paramName; 
					$this->setOutputTypes( $paramName );
				}
			}
		}
	}

	/**
	 * Ensures the parameter info is valid and parses list types.
	 * 
	 * @param string $name
	 */
	private function cleanParameter( $name ) {
		// Ensure there is a criteria array.
		if ( ! array_key_exists( 'criteria', $this->mParameterInfo[$name] ) ) {
			$this->mParameterInfo[$name]['criteria'] = array();
		}
		
		// Ensure the type is set in array form.
		if ( ! array_key_exists( 'type', $this->mParameterInfo[$name] ) ) {
			$this->mParameterInfo[$name]['type'] = array( 'string' );
		}
		elseif ( ! is_array( $this->mParameterInfo[$name]['type'] ) ) {
			$this->mParameterInfo[$name]['type'] = array( $this->mParameterInfo[$name]['type'] );
		}
		
		if ( array_key_exists( 'type', $this->mParameterInfo[$name] ) ) {
			// Add type specific criteria.
			switch( strtolower( $this->mParameterInfo[$name]['type'][0] ) ) {
				case 'integer':
					$this->addTypeCriteria( $name, 'is_integer' );
					break;
				case 'float':
					$this->addTypeCriteria( $name, 'is_float' );
					break;
				case 'number': // Note: This accepts non-decimal notations! 
					$this->addTypeCriteria( $name, 'is_numeric' );
					break;
				case 'boolean':
					// TODO: work with list of true and false values. 
					// TODO: i18n
					$this->addTypeCriteria( $name, 'in_array', array( 'yes', 'no', 'on', 'off' ) );
					break;
				case 'char':
					$this->addTypeCriteria( $name, 'has_length', array( 1, 1 ) );
					break;
			}
		}
		
		// If the original-value element is set, clean it, and store as value.
		if ( array_key_exists( 'original-value', $this->mParameters[$name] ) ) {
			$value = $this->mParameters[$name]['original-value'];
			
			if ( count( $this->mParameterInfo[$name]['type'] ) > 1 && $this->mParameterInfo[$name]['type'][1] == 'list' ) {
				// Trimming and splitting of list values.
				$delimiter = count( $this->mParameterInfo[$name]['type'] ) > 2 ? $this->mParameterInfo[$name]['type'][2] : self::$defaultListDelimeter;
				$value = preg_replace( '/((\s)*' . $delimiter . '(\s)*)/', $delimiter, $value );
				$value = explode( $delimiter, $value );
			}
			elseif ( count( $this->mParameterInfo[$name]['type'] ) > 1 && $this->mParameterInfo[$name]['type'][1] == 'array' && is_array( $value ) ) {
				// Trimming of array values.
				for ( $i = count( $value ); $i > 0; $i-- ) $value[$i] = trim( $value[$i] );
			}			
			
			$this->mParameters[$name]['value'] = $value;
		}
	}
	
	private function addTypeCriteria( $paramName, $criteriaName, $criteriaArgs = array() ) {
		$this->mParameterInfo[$paramName]['criteria'] = array_merge(
			array( $criteriaName => $criteriaArgs ),
			$this->mParameterInfo[$paramName]['criteria']
		);
	}
	
	/**
	 * Valides the provided parameter. 
	 * 
	 * This method itself validates the list criteria, if any. After this the regular criteria
	 * are validated by calling the doItemValidation method.
	 *
	 * @param string $name
	 *
	 * @return boolean Indicates whether there the parameter value(s) is/are valid.
	 */
	private function validateParameter( $name ) {
		$hasNoErrors = $this->doListValidation( $name );
		
		if ( $hasNoErrors || self::$accumulateParameterErrors ) {
			$hasNoErrors = $hasNoErrors && $this->doItemValidation( $name );
		}
		
		return $hasNoErrors;
	}
	
	/**
	 * Validates the list criteria for a parameter, if there are any.
	 * 
	 * @param string $name
	 */
	private function doListValidation( $name ) {
		$hasNoErrors = true;

		if ( array_key_exists( 'list-criteria', $this->mParameterInfo[$name] ) ) {
			foreach ( $this->mParameterInfo[$name]['list-criteria'] as $criteriaName => $criteriaArgs ) {
				// Get the validation function. If there is no matching function, throw an exception.
				if ( array_key_exists( $criteriaName, self::$mListValidationFunctions ) ) {
					$validationFunction = self::$mListValidationFunctions[$criteriaName];
					$isValid = $this->doCriteriaValidation( $validationFunction, $this->mParameters['value'], $name, $criteriaArgs );
					
					// Add a new error when the validation failed, and break the loop if errors for one parameter should not be accumulated.
					if ( ! $isValid ) {
						$hasNoErrors = false;
						
						$this->mErrors[] = array( 'type' => $criteriaName, 'args' => $criteriaArgs, 'name' => $name, 'list' => true, 'value' => $this->mParameters['original-value'] );
						
						if ( !self::$accumulateParameterErrors ) {
							break;
						}
					}
				}
				else {
					$hasNoErrors = false;
					throw new Exception( 'There is no validation function for list criteria type ' . $criteriaName );
				}
			}
		}
		
		return $hasNoErrors;
	}
	
	/**
	 * Valides the provided parameter by matching the value against the item criteria for the name.
	 * 
	 * @param string $name
	 * 
	 * @return boolean Indicates whether there the parameter value(s) is/are valid.
	 */
	private function doItemValidation( $name ) {
		$hasNoErrors = true;
		
		$value = &$this->mParameters[$name]['value'];
		
		// Go through all item criteria.
		foreach ( $this->mParameterInfo[$name]['criteria'] as $criteriaName => $criteriaArgs ) {
			// Get the validation function. If there is no matching function, throw an exception.
			if ( array_key_exists( $criteriaName, self::$mValidationFunctions ) ) {
				$validationFunction = self::$mValidationFunctions[$criteriaName];
				
				if ( is_array( $value ) ) {
					// Handling of list parameters
					$invalidItems = array();
					$validItems = array();
					
					// Loop through all the items in the parameter value, and validate them.
					foreach ( $value as $item ) {
						$isValid = $this->doCriteriaValidation( $validationFunction, $item, $name, $criteriaArgs );
						if ( $isValid ) {
							// If per item validation is on, store the valid items, so only these can be returned by Validator.
							if ( self::$perItemValidation ) $validItems[] = $item;
						}
						else {
							// If per item validation is on, store the invalid items, so a fitting error message can be created.
							if ( self::$perItemValidation ) {
								$invalidItems[] = $item;
							}
							else {
								// If per item validation is not on, an error to one item means the complete value is invalid.
								// Therefore it's not required to validate the remaining items.
								break;
							}
						}
					}
					
					if ( self::$perItemValidation ) {
						// If per item validation is on, the parameter value is valid as long as there is at least one valid item.
						$isValid = count( $validItems ) > 0;
						
						// If the value is valid, but there are invalid items, add an error with a list of these items.
						if ( $isValid && count( $invalidItems ) > 0 ) {
							$value = $validItems;
							$this->mErrors[] = array( 'type' => $criteriaName, 'args' => $criteriaArgs, 'name' => $name, 'list' => true, 'invalid-items' => $invalidItems );
						}
					}
				}
				else {
					// Determine if the value is valid for single valued parameters.
					$isValid = $this->doCriteriaValidation( $validationFunction, $value, $name, $criteriaArgs );
				}

				// Add a new error when the validation failed, and break the loop if errors for one parameter should not be accumulated.
				if ( !$isValid ) {
					$isList = is_array( $value );
					if ( $isList ) $value = $this->mParameters[$name]['original-value'];
					$this->mErrors[] = array( 'type' => $criteriaName, 'args' => $criteriaArgs, 'name' => $name, 'list' => $isList, 'value' => $value );
					$hasNoErrors = false;
					if ( !self::$accumulateParameterErrors ) break;
				}
			}
			else {
				$hasNoErrors = false;
				throw new Exception( 'There is no validation function for criteria type ' . $criteriaName );
			}
		}
		
		return $hasNoErrors;
	}
	
	/**
	 * Calls the validation function for the provided list or single value and returns it's result.
	 * The call is made with these parameters:
	 * - value: The value that is the complete list, or a single item.
	 * - parameter name: For lookups in the param info array.
	 * - parameter array: All data about the parameters gathered so far (this includes dependencies!).
	 * - output type info: Type info as provided by the parameter definition. This can be zero or more parameters.
	 * 
	 * @param $validationFunction
	 * @param mixed $value
	 * @param string $name
	 * @param array $criteriaArgs
	 * 
	 * @return boolean
	 */
	private function doCriteriaValidation( $validationFunction, $value, $name, array $criteriaArgs ) {
		// Call the validation function and store the result.
		$parameters = array( &$value, $name, $this->mParameters );
		$parameters = array_merge( $parameters, $criteriaArgs );		
		return call_user_func_array( $validationFunction, $parameters );
	}
	
	/**
	 * Changes the invalid parameters to their default values, and changes their state to valid.
	 */
	public function correctInvalidParams() {
		while ( $paramName = array_shift( $this->mInvalidParams ) ) {
			if ( array_key_exists( 'default', $this->mParameterInfo[$paramName] ) ) {
				$this->mParameters[$paramName]['value'] = $this->mParameterInfo[$paramName]['default'];
			} 
			else {
				$this->mParameters[$paramName]['value'] = self::$defaultDefaultValue;
			}
			$this->setOutputTypes( $paramName );
			$this->mValidParams[] = $paramName;
		}
	}
	
	/**
	 * Ensures the output type values are arrays, and then calls setOutputType.
	 * 
	 * @param string $name
	 */
	private function setOutputTypes( $name ) {
		$info = $this->mParameterInfo[$name];
		
		if ( array_key_exists( 'output-types', $info ) ) {
			for ( $i = 0, $c = count( $info['output-types'] ); $i < $c; $i++ ) {
				if ( ! is_array( $info['output-types'][$i] ) ) $info['output-types'][$i] = array( $info['output-types'][$i] );
				$this->setOutputType( $name, $info['output-types'][$i] );
			}
		}
		elseif ( array_key_exists( 'output-type', $info ) ) {
			if ( ! is_array( $info['output-type'] ) ) $info['output-type'] = array( $info['output-type'] );
			$this->setOutputType( $name, $info['output-type'] );
		}
		
	}
	
	/**
	 * Calls the formatting function for the provided output format with these parameters:
	 * - parameter value: ByRef for easy manipulation.
	 * - parameter name: For lookups in the param info array.
	 * - parameter array: All data about the parameters gathered so far (this includes dependencies!).
	 * - output type info: Type info as provided by the parameter definition. This can be zero or more parameters.
	 * 
	 * @param string $name
	 * @param array $typeInfo
	 */
	private function setOutputType( $name, array $typeInfo ) {
		// The output type is the first value in the type info array.
		// The remaining ones will be any extra arguments.
		$outputType = strtolower( array_shift( $typeInfo ) );
		
		if ( !array_key_exists( 'formatted-value', $this->mParameters[$name] ) ) {
			$this->mParameters[$name]['formatted-value'] = $this->mParameters[$name]['value'];
		}
		
		if ( array_key_exists( $outputType, self::$mOutputFormats ) ) {
			$parameters = array( &$this->mParameters[$name]['formatted-value'], $name, $this->mParameters );
			$parameters = array_merge( $parameters, $typeInfo );
			call_user_func_array( self::$mOutputFormats[$outputType], $parameters );
		}
		else {
			throw new Exception( 'There is no formatting function for output format ' . $outputType );
		}
	}

	/**
	 * Returns the valid parameters.
	 *
	 * @param boolean $includeMetaData
	 *
	 * @return array
	 */
	public function getValidParams( $includeMetaData ) {
		if ( $includeMetaData ) {
			return $this->mValidParams;
		}
		else {
			$validParams = array();
			
			foreach( $this->mValidParams as $name ) {
				$key = array_key_exists( 'formatted-value', $this->mParameters[$name] ) ? 'formatted-value' : 'value';
				$validParams[$name] =  $this->mParameters[$name][$key];
			}
			
			return $validParams;			
		}
	}

	/**
	 * Returns the unknown parameters.
	 *
	 * @return array
	 */
	public static function getUnknownParams() {
		$unknownParams = array();
		
		foreach( $this->mUnknownParams as $name ) {
			$unknownParams[$name] = $this->mParameters[$name];
		}		
		
		return $unknownParams;
	}

	/**
	 * Returns the errors.
	 *
	 * @return array
	 */
	public function getErrors() {
		return $this->mErrors;
	}
	
	/**
	 * @return boolean
	 */
	public function hasErrors() {
		return count( $this->mErrors ) > 0;
	}
	
	/**
	 * Returns wether there are any fatal errors. Fatal errors are either missing or invalid required parameters,
	 * or simply any sort of error when the validation level is equal to (or bigger then) Validator_ERRORS_STRICT.
	 * 
	 * @return boolean
	 */
	public function hasFatalError() {
		global $egValidatorErrorLevel;
		$has = $this->hasErrors() && $egValidatorErrorLevel >= Validator_ERRORS_STRICT;
		
		if ( !$has ) {
			foreach ( $this->mErrors as $error ) {
				if ( $error['type'] == 'missing' ) {
					$has = true;
					break;
				}
			}
		}

		return $has;
	}	

	/**
	 * Adds a new criteria type and the validation function that should validate values of this type.
	 * You can use this function to override existing criteria type handlers.
	 *
	 * @param string $criteriaName The name of the cirteria.
	 * @param array $functionName The functions location. If it's a global function, only the name,
	 * if it's in a class, first the class name, then the method name.
	 */
	public static function addValidationFunction( $criteriaName, array $functionName ) {
		self::$mValidationFunctions[$criteriaName] = $functionName;
	}
	
	/**
	 * Adds a new list criteria type and the validation function that should validate values of this type.
	 * You can use this function to override existing criteria type handlers.
	 *
	 * @param string $criteriaName The name of the list cirteria.
	 * @param array $functionName The functions location. If it's a global function, only the name,
	 * if it's in a class, first the class name, then the method name.
	 */
	public static function addListValidationFunction( $criteriaName, array $functionName ) {
		self::$mListValidationFunctions[strtolower( $criteriaName )] = $functionName;
	}
	
	/**
	 * Adds a new output format and the formatting function that should validate values of this type.
	 * You can use this function to override existing criteria type handlers.
	 *
	 * @param string $formatName The name of the format.
	 * @param array $functionName The functions location. If it's a global function, only the name,
	 * if it's in a class, first the class name, then the method name.
	 */
	public static function addOutputFormat( $formatName, array $functionName ) {
		self::$mOutputFormats[strtolower( $formatName )] = $functionName;
	}
}