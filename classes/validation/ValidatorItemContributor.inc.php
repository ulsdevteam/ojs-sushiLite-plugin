<?php

/**
 * @file classes/validation/ValidatorItemContributor.inc.php
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * @class ValidatorItemContributor
 * @ingroup validation
 * @see Validator
 *
 * @brief Validation check SUSHI Lite Item Contributor
 */

import('lib.pkp.classes.validation.ValidatorRegExp');
import('plugins.generic.sushiLite.classes.SushiItemContributor');

class ValidatorItemContributor extends ValidatorRegExp {

	/**
	 * Constructor.
	 */
	function ValidatorItemContributor() {
		parent::ValidatorRegexp('/^((?P<role>[^:]+):)?(?P<type>[^:]+):(?P<value>.+)$/');
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
		// Must be a type:value form
		if (!parent::isValid($value)) return false;

		// extract the scope, type and value
		$contribMatches = $this->getMatches();
		$test = new SushiItemContributor($contribMatches['role'], $contribMatches['type'], $contribMatches['value']);
		return $test->isValid();
	}

}
?>
