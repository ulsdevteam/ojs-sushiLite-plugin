<?php

/**
 * @file plugins/generic/sushiLite/classes/SushiItemIdentifier.inc.php
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
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
			case '':
				$types = array('issn', 'doi', 'isbn');
				break;
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
			default:
				$this->isValid = false;
		}
		// Any scope can have a proprietary id, but an empty scope cannot because proprietary ids are scope specific
		if ($this->_scope != '') {
			$types[] = 'proprietary';
		}
		// ensure selected type is valid for the selected scope
		if (!in_array($this->_type, $types)) {
			$this->_isValid = false;
		}
		// if type is well known, ensure value is correctly formed
		switch ($this->_type) {
			case 'issn':
				import('lib.pkp.classes.validation.ValidatorISSN');
				$validator = new ValidatorISSN();
				break;
			case 'proprietary':
				import('lib.pkp.classes.validation.ValidatorRegExp');
				$validator = new ValidatorRegExp('/^[0-9]+$/');
			//TODO: add validators for these formats
			case 'doi':
			case 'isbn':
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
			case '':
				$retVal = $this->_getFilterArticle();
				if (!$retVal) {
					$retVal = $this->_getFilterIssue();
				}
				if (!$retVal) {
					$retVal = $this->_getFilterJournal();
				}
				return $retVal;
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
		$journalDao = DAORegistry::getDAO('JournalDAO');
		$journal = NULL;
		switch ($this->_type) {
			case 'issn':
				$journals = $journalDao->getBySetting('printIssn', $this->_value);
				while (!$journals->eof()) {
					$journal = $journals->next();
				}
				if (!$journal) {
					$journals = $journalDao->getBySetting('onlineIssn', $this->_value);
					while (!$journals->eof()) {
						$journal = $journals->next();
					}
				}
				break;
			case 'doi':
				// There is not currently a DOI at the journal title level in OJS 2.x
				// There should be in OJS 3.x
				break;
			case 'proprietary':
				$journal = $journalDao->getById($this->_value);
			default:
				//unrecognized type
		}
		if ($journal) {
			return array(STATISTICS_DIMENSION_CONTEXT_ID => $journal->getId());
		} else {
			return array();
		}
	}

	/**
	 * Get the metric filter representation of this item by Issue identifier
	 * @return array() populated as key-value pairs for $filter in MetricsDAO::getMetrics(), or empty if no matches found
	 */
	function _getFilterIssue() {
		$issueDao = DAORegistry::getDAO('IssueDAO');
		$issue = NULL;
		switch ($this->_type) {
			case 'doi':
				$issue = $issueDao->getIssueByPubId($this->_type, $this->_value);
				break;
			case 'proprietary':
				$issue = $issueDao->getIssueById($this->_value);
				break;
			default:
				// unrecognized type
		}
		if ($issue) {
			/*
			$articleDao = DAORegistry::getDAO('PublishedArticleDAO');
			$articles = $articleDao->getPublishedArticles($issue->getId());
			$articleList = array();
			foreach ($articles as $article) {
				$articleList[] = $article->getId();
			}
			return array(STATISTICS_DIMENSION_SUBMISSION_ID => array($articleList));
			 */
			return array(STATISTICS_DIMENSION_ISSUE_ID => $issue->getId());
		} else {
			return array();
		}
	}

	/**
	 * Get the metric filter representation of this item by Article identifier
	 * @return array() populated as key-value pairs for $filter in MetricsDAO::getMetrics(), or empty if no matches found
	 */
	function _getFilterArticle() {
		$articleDao = DAORegistry::getDAO('PublishedArticleDAO');
		$article = NULL;
		switch ($this->_type) {
			case 'doi':
				$article = $articleDao->getPublishedArticleByPubId($this->_type, $this->_value);
				break;
			case 'proprietary':
				$article = $articleDao->getPublishedArticleByArticleId($this->_value);
				break;
			default:
				// unrecognized type
		}
		if ($article) {
			return array(STATISTICS_DIMENSION_SUBMISSION_ID => $article->getId());
		} else {
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