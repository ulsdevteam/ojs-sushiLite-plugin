<?php

/**
 * @file classes/validation/ValidatorMultiItemIdentifier.inc.php
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * @class MultiValidatorItemIdentifier
 * @ingroup validation
 * @see Validator
 *
 * @brief Validation check SUSHI Lite Item Identifiers (Multiset)
 */

import('plugins.generic.sushiLite.classes.validation.ValidatorItemIdentifier');

class ValidatorMultiItemIdentifier extends ValidatorItemIdentifier {

	/**
	 * Constructor.
	 */
	function ValidatorMultiItemIdentifier() {
		parent::ValidatorItemIdentifier();
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
		$identifiers = explode('|', $value);
		foreach ($identifiers as $id) {
			if (!empty($id) && !parent::isValid($id)) return false;
		}
		return true;
	}

	/**
	 * Return only valid components
	 * @param $value string
	 * @return array() of getMatches() hashes
	 */
	function sanitize($value) {
		$identifiers = explode('|', $value);
		$valid = array();
		foreach ($identifiers as $id) {
			if (!empty($id) && parent::isValid($id)) $valid[] = $this->getMatches();
		}
		return $valid;
	}

	/**
	 * Return only invalid components
	 * @param $value string
	 * @return string
	 */
	function getRejects($value) {
		$identifiers = explode('|', $value);
		$invalid = array();
		foreach ($identifiers as $id) {
			if (!empty($id) && !parent::isValid($id)) $invalid[] = $id;
		}
		return implode('|', $invalid);
	}

}