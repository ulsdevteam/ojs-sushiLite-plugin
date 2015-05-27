<?php

/**
 * @file plugins/generic/sushiLite/SushiLiteSettingsForm.inc.php
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

class SushiLiteSettingsForm extends Form {

	/** @var $journalId int */
	var $journalId;

	/** @var $plugin object */
	var $plugin;

	/**
	 * Constructor
	 * @param $plugin object
	 * @param $journalId int
	 */
	function SushiLiteSettingsForm(&$plugin, $journalId) {
		$this->journalId = $journalId;
		$this->plugin =& $plugin;

		parent::Form($plugin->getTemplatePath() . DIRECTORY_SEPARATOR . 'settingsForm.tpl');

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
		$vars = array();
		$this->readUserVars($vars);
	}

	/**
	 * Execute the form.
	 */
	function execute() {
		$plugin =& $this->plugin;
		$journalId = $this->journalId;
	}

}

?>
