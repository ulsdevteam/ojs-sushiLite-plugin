<?php

/**
 * @file plugins/generic/sushiLite/classes/SushiItemContributor.inc.php
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * @class SushiLite
 * @ingroup plugins_generic_sushilite
 *
 * @brief A SUSHI Lite Item Contributor
 */

class SushiItemContributor {

	var $_role;
	var $_type;
	var $_value;
	
	var $_isValid;
		
	/**
	 * Constructor
	 * @param string $role defines the relationship (or role) of the identifier.
	 * @param string $type is the well known type of identifier that will be used as the namespace for the scope and value.
	 * @param string $value is the actual identifier value.
	 * @return SushiItemContributor object
	 */
	function SushiItemContributor($role, $type, $value) {
		$this->_role = ($role ? $role : 'author');
		$this->_type = $type;
		$this->_value = $value;
		$this->_isValid = true;

		// validate by role
		switch ($this->_role) {
			case 'author':
			case 'editor':
			case 'funder':
				break;
			default:
				$this->_isValid = false;
		}

		$validator = NULL;
		// validate by type
		switch ($this->_type) {
			case 'orcid':
				import('lib.pkp.classes.validation.ValidatorORCID');
				$validator = new ValidatorORCID();
				break;
			case 'isni':
				import('lib.pkp.classes.validation.ValidatorISNI');
				$validator = new ValidatorISNI();
				break;
			// TODO: validate these as well
			case 'proprietary':
				$validator = null;
				break;
			default:
				$validator = null;
				$this->_isValid = false;
		}
		// if additional validation can be performed on the value, do so.
		if ($validator && !$validator->isValid($this->_value)) {
			$this->_isValid = false;
		}
	}
	

	/**
	 * Get the metric filter representation of this item
	 * @return array() populated as key-value pairs for $filter in MetricsDAO::getMetrics(), or empty if no matches found
	 */
	function getMetricFilter() {
		if (!$this->_isValid) {
			return array();
		}
		switch ($this->_role) {
			case 'author':
				return $this->_getFilterAuthor();
			case 'editor':
				return $this->_getFilterEditor();
			case 'funder':
				return $this->_getFilterFunder();
			default:
				return array();
		}
	}

	/**
	 * Get the metric filter representation of this item by Author identifier
	 * @return array() populated as key-value pairs for $filter in MetricsDAO::getMetrics(), or empty if no matches found
	 */
	function _getFilterAuthor() {
		switch ($this->_type) {
			case 'orcid':
				return array(); // TODO;
			case 'isni':
				return array(); // TODO
			case 'proprietary':
				return array(); // TODO
			default:
				return array();
		}
	}

	/**
	 * Get the metric filter representation of this item by Editor identifier
	 * @return array() populated as key-value pairs for $filter in MetricsDAO::getMetrics(), or empty if no matches found
	 */
	function _getFilterEditor() {
		switch ($this->_type) {
			case 'orcid':
				return array(); // TODO;
			case 'isni':
				return array(); // TODO
			case 'proprietary':
				return array(); // TODO
			default:
				return array();
		}
	}

	/**
	 * Get the metric filter representation of this item by Funder identifier
	 * @return array() populated as key-value pairs for $filter in MetricsDAO::getMetrics(), or empty if no matches found
	 */
	function _getFilterFunder() {
		switch ($this->_type) {
			case 'orcid':
				return array(); // TODO;
			case 'isni':
				return array(); // TODO
			case 'proprietary':
				return array(); // TODO
			default:
				return array();
		}
	}

	/**
	 * Is the parameterization sane?
	 * @return boolean
	 */
	function isValid() {
		return $this->_isValid;
	}

}

?>
