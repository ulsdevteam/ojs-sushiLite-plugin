<?php

/**
 * @file plugins/generic/sushiLite/SushiLiteSettingsForm.inc.php
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * @class SushiLiteSettingsForm
 * @ingroup plugins_generic_sushilite
 *
 * @brief Form for journal managers change SUSHI Lite settings
 */


import('lib.pkp.classes.form.Form');
import('plugins.generic.sushiLite.classes.SushiRequestor');

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
		
		$requestor = new SushiRequestor($plugin->getCategory(), $plugin->getName(), CONTEXT_ID_NONE);

		$requestors = $requestor->listIds($journalId);
		if (!is_array($requestors)) {
			$requestors = array();
		}
		$data = array();
		foreach ($requestors as $r) {
			$requestor = new SushiRequestor($plugin->getCategory(), $plugin->getName(), $journalId, $r);
			foreach ($requestor->_dataKeys() as $k) {
				$data[$r][$k] = $requestor->getData($k);
			}
		}
		$this->setData('requestors', $data);
		if (Validation::isSiteAdmin()) {
			$data = FALSE;
			$requestors = $requestor->listIds(CONTEXT_SITE);
			if (!is_array($requestors)) {
				$requestors = array();
			} else {
				$data = array();
			}
			foreach ($requestors as $r) {
				$requestor = new SushiRequestor($plugin->getCategory(), $plugin->getName(), CONTEXT_SITE, $r);
				foreach ($requestor->_dataKeys() as $k) {
					$data[$r][$k] = $requestor->getData($k);
				}
			}
			$this->setData('siteRequestors', $data);
			$this->setData('site_disallow_anonymous', $plugin->getSetting(CONTEXT_SITE, 'disableAnonymous'));
		}
		$this->setData('disallow_anonymous', $plugin->getSetting($journalId, 'disableAnonymous'));
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$vars = array('disallow_anonymous');
		if (Validation::isSiteAdmin()) {
			$vars[] = 'site_disallow_anonymous';
		}
		$this->readUserVars($vars);
	}

	/**
	 * Execute the form.
	 */
	function execute() {
		$plugin =& $this->plugin;
		$journalId = $this->journalId;

		$plugin->updateSetting($journalId, 'disableAnonymous', $this->getData('disallow_anonymous'), 'bool');
		if (Validation::isSiteAdmin()) {
			$plugin->updateSetting(CONTEXT_SITE, 'disableAnonymous', $this->getData('site_disallow_anonymous'), 'bool');
		}
	}

}

?>
