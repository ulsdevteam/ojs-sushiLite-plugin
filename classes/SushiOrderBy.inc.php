<?php

/**
 * @file plugins/generic/sushiLite/classes/SushiOrderBy.inc.php
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * @class SushiLite
 * @ingroup plugins_generic_sushilite
 *
 * @brief A SUSHI Lite Item Contributor
 */

class SushiOrderBy {

	var $_field;
	var $_order;
	var $_isValid;
	
	/**
	 * Constructor
	 * @param string $field identifies the sortable field
	 * @param string $order identifies the ordering, ascending or descending
	 * @return SushiOrderBy object
	 */
	function SushiOrderBy($field, $order = 'asc') {
		$this->_field = $field;
		$this->_order = $order;
		$this->_isValid = true;

		// validate the field
		// TODO: translate $field into a STATISTICS_DIMENSION_* constant
		
		// validate the order
		if ($order != 'asc' && $order != 'desc') {
			$this->_isValid = false;
		}

	}

	/**
	 * Get the metric orderBy representation of this item
	 * @return array() populated as key-value pairs for $orderBy in MetricsDAO::getMetrics(), or empty if not valid
	 */
	function getMetricOrderBy() {
		if (!$this->_isValid) {
			return array();
		}
		return array($this->_field => $this->_order);
	}
	
	/**
	 * Is the parameterization sane?
	 * @return boolean
	 */
	function isValid() {
		return $this->_isValid;
	}

}