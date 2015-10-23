<?php

/**
 * @file plugins/generic/sushiLite/SushiLiteRequestorForm.inc.php
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * @class SushiLiteRequestorForm
 * @ingroup plugins_generic_sushilite
 *
 * @brief Form for SUSHI Lite requestor management
 */


import('lib.pkp.classes.form.Form');
import('plugins.generic.sushiLite.classes.SushiRequestor');

class SushiLiteRequestorForm extends Form {

	/** @var $journalId int */
	var $journalId;

	/** @var $plugin object */
	var $plugin;

	/**
	 * Constructor
	 * @param $plugin object
	 * @param $journalId int
	 */
	function SushiLiteRequestorForm(&$plugin, $journalId) {
		$this->journalId = $journalId;
		$this->plugin =& $plugin;

		parent::Form($plugin->getTemplatePath() . DIRECTORY_SEPARATOR . 'settingsForm.tpl');
		$this->addCheck(new FormValidator($this, 'requestor_name', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.sushiLite.settingsForm.requestorNameRequired'));
		$this->addCheck(new FormValidator($this, 'requestor_id', FORM_VALIDATOR_REQUIRED_VALUE, 'plugins.generic.sushiLite.settingsForm.requestorIdRequired'));
		$this->addCheck(new FormValidatorRegExp($this, 'requestor_id', FORM_VALIDATOR_OPTIONAL_VALUE, 'plugins.generic.sushiLite.error.requestorIdNotUUID', '/^[a-h0-9]{8}-[a-h0-9]{4}-[a-h0-9]{4}-[a-h0-9]{4}-[a-h0-9]{12}$/'));
		$this->addCheck(new FormValidatorRegExp($this, 'requestor_key', FORM_VALIDATOR_OPTIONAL_VALUE, 'plugins.generic.sushiLite.error.apiKeyNotUUID', '/^[a-h0-9]{8}-[a-h0-9]{4}-[a-h0-9]{4}-[a-h0-9]{4}-[a-h0-9]{12}$/'));

		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$journalId = $this->journalId;
		$plugin =& $this->plugin;
		
		$requestor = new SushiRequestor($plugin->getCategory(), $plugin->getName(), CONTEXT_ID_NONE);
		
		if (Request::getUserVar('edit')) {
			$requestor = new SushiRequestor($plugin->getCategory(), $plugin->getName(), $journalId, Request::getUserVar('stored_requestor'));
			if (!$requestor->exists() && Validation::isSiteAdmin()) {
				$requestor = new SushiRequestor($plugin->getCategory(), $plugin->getName(), $journalId, Request::getUserVar('stored_requestor'));
			}
			if ($requestor->exists()) {
				foreach (array('name', 'apiKey', 'idRequired', 'id') as $key) {
					$this->setData('requestor_'.$key, $requestor->getData($key));
				}
				$this->setData('stored_requestor', $requestor->getData('id'));
			} else {
				$user =& Request::getUser();
				import('classes.notification.NotificationManager');
				$notificationManager = new NotificationManager();
				$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_FORM_ERROR);
			}
		}
		if (Request::getUserVar('siteNew')) {
			$this->setData('new_context', CONTEXT_SITE);
		} else {
			$this->setData('new_context', CONTEXT_JOURNAL);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$vars = array('stored_requestor', 'requestor_name', 'requestor_id', 'requestor_key', 'new_context');
		$this->readUserVars($vars);
	}

	/**
	 * Execute the form.
	 */
	function execute() {
		$plugin =& $this->plugin;
		$journalId = $this->journalId;

		// save new or update existing requestor
		$requestor = new SushiRequestor($plugin->getCategory(), $plugin->getName(), $journalId, Request::getUserVar('stored_requestor'));
		foreach (array('name', 'apiKey', 'idRequired', 'id') as $key) {
			$requestor->setData($key, $this->getData('requestor_'.$key));
		}
		if (!$requestor->save()) {
			$user =& Request::getUser();
			import('classes.notification.NotificationManager');
			$notificationManager = new NotificationManager();
			$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_FORM_ERROR);
		}
	}

}

?>
