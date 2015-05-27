<?php

/**
 * @file plugins/generic/sushiLite/classes/SushiItemIdentifier.inc.php
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * @class SushiLite
 * @ingroup plugins_generic_sushilite
 *
 * @brief A SUSHI Lite Item Identifier
 */

class SushiItemIdentifier {

	var $_scope;
	var $_type;
	var $_value;
	
	var $_isValid;
	
	/**
	 * Constructor
	 * @param string $scope defines the level (or scope) of the identifier.
	 * @param string $type is the well known type of identifier that will be used as the namespace for the scope and value.
	 * @param string $value is the actual identifier value.
	 * @return SushiItemIdentifier object
	 */
	function SushiItemIdentifier($scope, $type, $value) {
		$this->_scope = $scope;
		$this->_type = $type;
		$this->_value = $value;

		$this->_isValid = true;
		// map valid types to known scopes
		$types = array();
		switch ($this->_scope) {
			case 'journal':
				$types = array('issn', 'doi');
				break;
			case 'issue':
			case 'article':
			case 'chapter':
				$types = array('doi');
				break;
			case 'book':
				$types = array('isbn', 'doi');
				break;
			case 'publisher':
				$types = array('isni');
				break;
			default:
				$this->isValid = false;
		}
		$types[] = 'proprietary';
		// ensure selected type is valid for the selected scope
		import('lib.pkp.classes.validation.ValidatorInSet');
		$validator = new ValidatorInSet($types);
		if (!$validator->isValid($this->_type)) {
			$this->_isValid = false;
		}
		// if type is well known, ensure value is correctly formed
		switch ($this->_type) {
			case 'issn':
				import('lib.pkp.classes.validation.ValidatorISSN');
				$validator = new ValidatorISSN();
				break;
			case 'isni':
				import('lib.pkp.classes.validation.ValidatorISNI');
				$validator = new ValidatorISNI();
				break;
			//TODO: add validators for these formats
			case 'doi':
			case 'isbn':
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
		switch ($this->_scope) {
			case 'journal':
				return $this->_getFilterJournal();
			case 'issue':
				return $this->_getFilterIssue();
			case 'article':
				return $this->_getFilterArticle();
			case 'chapter':
			case 'book':
				return array();
			case 'publisher':
				return $this->_getFilterPublisher();
			default:
				return array();
		}
	}

	/**
	 * Get the metric filter representation of this item by Journal identifier
	 * @return array() populated as key-value pairs for $filter in MetricsDAO::getMetrics(), or empty if no matches found
	 */
	function _getFilterJournal() {
		switch ($this->_type) {
			case 'issn':
				return array(); // TODO;
			case 'doi':
				return array(); // TODO
			case 'proprietary':
				return array(); // TODO
			default:
				return array();
		}
	}

	/**
	 * Get the metric filter representation of this item by Issue identifier
	 * @return array() populated as key-value pairs for $filter in MetricsDAO::getMetrics(), or empty if no matches found
	 */
	function _getFilterIssue() {
		switch ($this->_type) {
			case 'doi':
				return array(); // TODO
			case 'proprietary':
				return array(); // TODO
			default:
				return array();
		}
	}

	/**
	 * Get the metric filter representation of this item by Article identifier
	 * @return array() populated as key-value pairs for $filter in MetricsDAO::getMetrics(), or empty if no matches found
	 */
	function _getFilterArticle() {
		switch ($this->_type) {
			case 'doi':
				return array(); // TODO
			case 'proprietary':
				return array(); // TODO
			default:
				return array();
		}
	}

	/**
	 * Get the metric filter representation of this item by Publisher identifier
	 * @return array() populated as key-value pairs for $filter in MetricsDAO::getMetrics(), or empty if no matches found
	 */
	function _getFilterPublisher() {
		switch ($this->_type) {
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
