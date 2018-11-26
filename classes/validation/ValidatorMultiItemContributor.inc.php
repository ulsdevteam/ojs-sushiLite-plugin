<?php

/**
 * @file classes/validation/ValidatorMultiItemContributor.inc.php
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * @class MultiValidatorItemContributor
 * @ingroup validation
 * @see Validator
 *
 * @brief Validation check SUSHI Lite Item Contributors (Multiset)
 */

import('plugins.generic.sushiLite.classes.validation.ValidatorItemContributor');

class ValidatorMultiItemContributor extends ValidatorItemContributor {

	/**
	 * Constructor.
	 */
	function ValidatorMultiItemContributor() {
		parent::ValidatorItemContributor();
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
	 * @return string
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