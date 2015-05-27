<?php

/**
 * @file plugins/generic/sushiLite/SushiLiteTestForm.inc.php
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * @class SushiLiteTestForm
 * @ingroup plugins_generic_sushilite
 *
 * @brief Form for journal managers to test SUSHI Lite requests
 */


import('lib.pkp.classes.form.Form');

class SushiLiteTestForm extends Form {

	/** @var $journalId int */
	var $journalId;

	/** @var $plugin object */
	var $plugin;

	/**
	 * Constructor
	 * @param $plugin object
	 * @param $journalId int
	 */
	function SushiLiteTestForm(&$plugin, $journalId) {
		$this->journalId = $journalId;
		$this->plugin =& $plugin;

		parent::Form($plugin->getTemplatePath() . DIRECTORY_SEPARATOR . 'testForm.tpl');

		$this->addCheck(new FormValidator($this, 'Report', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.sushiLite.testForm.reportName.required'));
		foreach ($this->getSelectOptions() as $k => $v) {
			$this->addCheck(new FormValidatorInSet($this, $k, FORM_VALIDATOR_OPTIONAL_VALUE, 'plugins.generic.sushiLite.testForm.'.$k.'Invalid', array_keys($v)));
		}
		foreach (array('DateBegin', 'DateEnd') as $v) {
			$this->addCheck(new FormValidatorDate($this, $v, FORM_VALIDATOR_OPTIONAL_VALUE, 'plugins.generic.sushiLite.testForm.'.$v.'Invalid'));
		}
		foreach (array('PubYr', 'PubYrFrom', 'PubYrTo') as $v) {
			$this->addCheck(new FormValidatorDate($this, $v, FORM_VALIDATOR_OPTIONAL_VALUE, 'plugins.generic.sushiLite.testForm.'.$v.'Invalid', DATE_FORMAT_ISO, VALIDATOR_DATE_SCOPE_YEAR, VALIDATOR_DATE_SCOPE_YEAR));
		}
		$this->addCheck(new FormValidatorRegExp($this, 'APIKey', FORM_VALIDATOR_OPTIONAL_VALUE, 'plugins.generic.sushiLite.testForm.apiKeyNotUUID', '/^[a-h0-9]{8}-[a-h0-9]{4}-[a-h0-9]{4}-[a-h0-9]{4}-[a-h0-9]{12}$/'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$journalId = $this->journalId;
		$plugin =& $this->plugin;

		$this->_data = array();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$vars = array_merge($this->getTextElements(), array_keys($this->getSelectOptions()));
		$this->readUserVars($vars);
	}

	/**
	 * Execute the form.
	 */
	function execute() {
		$plugin =& $this->plugin;
		$journalId = $this->journalId;
	}

	function assignTemplateValues(&$templateManager) {
		$plugin =& $this->plugin;
		
		$selectOpts = $this->getSelectOptions();
		// Add allowed unselected values
		foreach (array('ItemIdentifierScope', 'ItemIdentifierType', 'ItemContributorType', 'ItemContributorRole') as $v) {
			$selectOpts[$v] = array_reverse($selectOpts[$v]);
			$selectOpts[$v][''] = '';
			$selectOpts[$v] = array_reverse($selectOpts[$v]);
		}
		foreach ($selectOpts as $k => $v) {
			$templateManager->assign($k.'Options', $v);
		}
		$templateManager->assign('localTemplatePath', $plugin->getTemplatePath());
	}

	/**
	 * Return a nested array of SUSHI-Lite options.
	 * @return 2D array in a format for {html_options_translate options=$array}
	 */
	function getSelectOptions() {
		return array(
			'Report' => array(
				'AR1' => 'plugins.generic.sushiLite.testForm.reportName.AR1',
				'JR1' => 'plugins.generic.sushiLite.testForm.reportName.JR1',
			),
			'ItemIdentifierScope' => array(
				'journal' => 'plugins.generic.sushiLite.testForm.itemIdentifier.scope.journal',
				'issue' => 'plugins.generic.sushiLite.testForm.itemIdentifier.scope.issue',
				'article' => 'plugins.generic.sushiLite.testForm.itemIdentifier.scope.article',
				'book' => 'plugins.generic.sushiLite.testForm.itemIdentifier.scope.book',
				'chapter' => 'plugins.generic.sushiLite.testForm.itemIdentifier.scope.chapter',
				'publisher' => 'plugins.generic.sushiLite.testForm.itemIdentifier.scope.publisher',
			),
			'ItemIdentifierType' => array(
				'issn' => 'plugins.generic.sushiLite.testForm.itemIdentifier.type.issn',
				'doi' => 'plugins.generic.sushiLite.testForm.itemIdentifier.type.doi',
				'isbn' => 'plugins.generic.sushiLite.testForm.itemIdentifier.type.isbn',
				'isni' => 'plugins.generic.sushiLite.testForm.itemIdentifier.type.isni',
				'proprietary' => 'plugins.generic.sushiLite.testForm.itemIdentifier.type.proprietary',
			),
			'ItemContributorRole' => array(
				'author' => 'plugins.generic.sushiLite.testForm.itemContributor.role.author',
				'editor' => 'plugins.generic.sushiLite.testForm.itemContributor.role.editor',
				'funder' => 'plugins.generic.sushiLite.testForm.itemContributor.role.funder',
			),
			'ItemContributorType' => array(
				'orcid' => 'plugins.generic.sushiLite.testForm.itemContributor.type.orcid',
				'isni' => 'plugins.generic.sushiLite.testForm.itemContributor.type.isni',
				'proprietary' => 'plugins.generic.sushiLite.testForm.itemContributor.type.proprietary',
			),
			'Granularity' => array(
				'totals' => 'plugins.generic.sushiLite.testForm.granularity.totals',
				'yearly' => 'plugins.generic.sushiLite.testForm.granularity.yearly',
				'monthly' => 'plugins.generic.sushiLite.testForm.granularity.monthly',
				'daily' => 'plugins.generic.sushiLite.testForm.granularity.daily',
			),
			'Format' => array(
				'json' => 'plugins.generic.sushiLite.testForm.format.json',
				'xml' => 'plugins.generic.sushiLite.testForm.format.xml',
			),
			'IsArchive' => array(
				'yes' => 'common.yes',
				'no' => 'common.no',
			)
		);
	}

	/**
	 * Return a nested array of SUSHI-Lite options.
	 * @return 2D array in a format for {html_options_translate options=$array}
	 */
	function getTextElements() {
		return array(
			'RequestorID',
			'CustomerID',
			'APIKey',
			'DateBegin',
			'DateEnd',
			'ItemIdentifier',
			'ItemContributor',
			'Publisher',
			'Platform',
			'MetricTypes',
			'PubYr',
			'PubYrFrom',
			'PubYrTo',
			'Callback',
			'Limit',
			'Offset',
			'OrderBy', // TODO: should be select
		);
	}
	
}

?>
