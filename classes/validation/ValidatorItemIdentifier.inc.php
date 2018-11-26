<?php

/**
 * @file classes/validation/ValidatorItemIdentifier.inc.php
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * @class ValidatorItemIdentifier
 * @ingroup validation
 * @see Validator
 *
 * @brief Validation check SUSHI Lite Item Identifiers
 */

import('lib.pkp.classes.validation.ValidatorRegExp');
import('plugins.generic.sushiLite.classes.SushiItemIdentifier');

class ValidatorItemIdentifier extends ValidatorRegExp {

	/**
	 * Constructor.
	 */
	function ValidatorItemIdentifier() {
		parent::ValidatorRegexp('/^((?P<scope>[^:]+):)?(?P<type>[^:]+):(?P<value>.+)$/');
	}


	//
	// Implement abstract methods from Validator
	//
	/**
	 * @see Validator::isValid()
	 * @param $value mixed
	 * @return boolean
	 */
	function isValid($value) {
		// Must be a scope:type:value form
		if (!parent::isValid($value)) return false;

		// extract the scope, type and value
		$idMatches = $this->getMatches();
		$test = new SushiItemIdentifier($idMatches['scope'], $idMatches['type'], $idMatches['value']);
		return $test->isValid();
	}

}