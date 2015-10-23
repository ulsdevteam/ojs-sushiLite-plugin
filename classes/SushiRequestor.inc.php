<?php

/**
 * @file plugins/generic/sushiLite/classes/SushiRequestor.inc.php
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * @class SushiLite
 * @ingroup plugins_generic_sushilite
 *
 * @brief A SUSHI Lite Requestor
 */

class SushiRequestor {

	var $_id;
	var $_saved_id;
	var $_context;
	
	var $_data;

	var $_parentPluginCategory;
	var $_parentPluginName;

	/**
	 * Constructor
	 * @param string $parentPluginCategory
	 * @param string $parentPluginName
	 * @return SushiRequestor object
	 */
	function SushiRequestor($parentPluginCategory, $parentPluginName, $context, $id = NULL) {
		$this->_parentPluginCategory = $parentPluginCategory;
		$this->_parentPluginName = $parentPluginName;
		
		$this->_context = $context;

		$plugin = $this->getParentPlugin();
		$requestorsSetting = $plugin->getSetting($context, 'requestors');
		if ($id) {
			$requestors = explode(':', $requestorsSetting);
			if (in_array($id, $requestors)) {
				$this->_saved_id = $id;
				foreach ($this->_dataKeys() as $key) {
					$plugin->getSetting($context, 'req-'.$id.'.'.$key);
				}
			}
		}
	}

	/**
	 * Check to see if this Requestor is in the database
	 * @return boolean
	 */
	function exists() {
		return isset($this->_saved_id);
	}

	/**
	 * Save this Requestor is in the database
	 * @return boolean
	 */
	function save() {
		$plugin = $this->getParentPlugin();
		$requestorsSetting = $plugin->getSetting($this->_context, 'requestors');
		$requestors = explode(':', $requestorsSetting);
		if (!$this->_id) {
			$id = String::generateUUID();
			while (in_array($id, $requestors)) {
				$id = String::generateUUID();
			}
			$this->_id = $id;
		}
		foreach ($this->_dataKeys() as $key) {
			$plugin->updateSetting($this->_context, 'req-'.$this->_id.'.'.$key, $this->_data[$key]);
		}
		if (!in_array($this->_id, $requestors)) {
			$requestors[] = $this->_id;
			$requestorsSetting = implode(':', $requestors);
			$plugin->updateSetting($this->_context, 'requestors', $requestorsSetting, 'string');
		}
		$this->_saved_id = $this->_id;
		return true;
	}

	/**
	 * Save this Requestor is in the database
	 * @return boolean
	 */
	function delete() {
		if ($this->_saved_id) {
			$pluginSettingsDao =& DAORegistry::getDAO('PluginSettingsDAO');
			foreach ($this->_dataKeys() as $key) {
				$pluginSettingsDao->deleteSetting($this->_context, $this->_parentPluginName, 'req-'.$this->_id.'.'.$key);
			}
			$plugin = $this->getParentPlugin();
			$requestorsSetting = trim(str_replace(':'.$this->_saved_id.':', '', ':'.$plugin->getSetting($this->_context, 'requestors').':'), ':');
			$plugin->updateSetting($this->_context, 'requestors', $requestorsSetting, 'string');
			$this->_saved_id = NULL;
		}
		return true;
	}

	/**
	 * Get the parent plugin
	 * @return object
	 */
	function &getParentPlugin() {
		$plugin =& PluginRegistry::getPlugin($this->_parentPluginCategory, $this->_parentPluginName);
		return $plugin;
	}

	/**
	 * Find by API Key
	 * @param $context
	 * @param $key
	 * @return object
	 */
	function findByAPIKey($context, $key) {
		$plugin = $this->getParentPlugin();
		$requestorsSetting = $plugin->getSetting($context, 'requestors');
		$requestors = explode(':', $requestorsSetting);
		// Check this context for a requestor with a matching API key
		foreach ($requestors as $requestor) {
			$test = new SushiRequestor($this->_parentPluginCategory, $this->_parentPluginName, $context, $requestor);
			if ($test->getData('apiKey') === $key) {
				// found!
				return $test;
			}
		}
		// Check at the site level if none found in this context
		if ($context != 0) {
			return $this->findByAPIKey(0, $key);
		}
		// None found, return a new Requestor
		return new SushiRequestor($this->_parentPluginCategory, $this->_parentPluginName, $context);
	}

	/**
	 * List by context
	 * @param $context
	 * @return array
	 */
	function listIds($context) {
		$plugin = $this->getParentPlugin();
		$requestorsSetting = $plugin->getSetting($context, 'requestors');
		if ($requestorsSetting) {
			$requestors = explode(':', $requestorsSetting);
		} else {
			$requestors = array();
		}
		return $requestors;
	}

	/**
	 * Get the value for the data key
	 * @param $key string
	 * @return mixed
	 */
	function getData($key) {
		switch ($key) {
			case 'name':
			case 'apiKey':
			case 'idRequired':
			case 'ip':
				return $this->_data[$key];
			case('id'):
				return $this->_id;
			default:
				return NULL;
		}
	}

	/**
	 * Set the data element
	 * @param $key string
	 * @param $value string
	 * @return boolean success
	 */
	function setData($key, $value) {
		switch ($key) {
			case 'name':
			case 'apiKey':
			case 'idRequired':
			case 'ip':
				$this->_data[$key] = $value;
				return true;
			default:
				return false;
		}
	}

	/**
	 * Return the possible data keys
	 * @return array
	 */
	function _dataKeys() {
		return array('name', 'apiKey', 'idRequired', 'ip');
	}

}

?>
