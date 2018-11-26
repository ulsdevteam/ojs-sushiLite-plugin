<?php

/**
 * @file plugins/generic/sushiLite/SushiLitePlugin.inc.php
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * @class CounterReportPlugin
 * @ingroup plugins_generic_sushilite
 *
 * @brief Sushi Lite plugin provides a SUSHI-Lite implementation for usage statistics harvesting.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class SushiLiteGenericPlugin extends GenericPlugin {

	/**
	 * @see PKPPlugin::register($category, $path)
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		
		if($success && $this->getEnabled()) {
			HookRegistry::register('LoadHandler', array(&$this, 'setupPublicHandler'));
		}
		return $success;
	}

	/**
	 * @see PKPPlugin::getName()
	 */
	function getName() {
		return 'SushiLiteGenericPlugin';
	}

	/**
	 * @see PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.sushiLite.plugin.name');
	}

	/**
	 * @see PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.sushiLite.plugin.description');
	}

	/**
	 * @see PKPPlugin::getTemplatePath()
	 */
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates';
	}	 

	/**
	 * @see PKPPlugin::isSitePlugin()
	 */
	function isSitePlugin() {
		return true;
	}

	/**
	 * Set the page's breadcrumbs, given the plugin's tree of items
	 * to append.
	 * @param $isSubclass boolean
	 */
	function setBreadcrumbs($isSubclass = false) {
		$templateMgr =& TemplateManager::getManager();
		$pageCrumbs = array(
			array(
				Request::url(null, 'user'),
				'navigation.user'
			),
			array(
				Request::url(null, 'manager'),
				'user.role.manager'
			)
		);
		if ($isSubclass) {
			$pageCrumbs[] = array(
				Request::url(null, 'manager', 'plugins'),
				'manager.plugins'
			);
			$pageCrumbs[] = array(
				Request::url(null, 'manager', 'plugins', 'generic'),
				'plugins.categories.generic'
			);
		}

		$templateMgr->assign('pageHierarchy', $pageCrumbs);
	}

	/**
	 * Display verbs for the management interface.
	 * @return array of verb => description pairs
	 */
	function getManagementVerbs() {
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array('settings', __('plugins.generic.sushiLite.manager.settings'));
			$verbs[] = array('test', __('plugins.generic.sushiLite.manager.test'));
		}
		return parent::getManagementVerbs($verbs);
	}

	/**
	 * Execute a management verb on this plugin
	 * @param $verb string
	 * @param $args array
	 * @param $message string Result status message
	 * @param $messageParams array Parameters for the message key
	 * @return boolean
	 */
	function manage($verb, $args, &$message, &$messageParams) {
		if (!parent::manage($verb, $args, $message, $messageParams)) {
			// If enabling this plugin, go directly to the settings
			if ($verb == 'enable') {
				$verb = 'settings';
			} else {
				return false;
			}
		}

		switch ($verb) {
			case 'settings':
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
				$journal =& Request::getJournal();

				$this->import('SushiLiteSettingsForm');
				$form = new SushiLiteSettingsForm($this, $journal->getId());
				if (Request::getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						$user =& Request::getUser();
						import('classes.notification.NotificationManager');
						$notificationManager = new NotificationManager();
						$notificationManager->createTrivialNotification($user->getId());
						Request::redirect(null, 'manager', 'plugins', 'generic');
						return false;
					}
				} else {
					$form->initData();
				}
				$this->setBreadCrumbs(true);
				$form->display();
				return true;
			case 'test':
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
				$journal =& Request::getJournal();

				$this->import('SushiLiteTestForm');
				$form = new SushiLiteTestForm($this, $journal->getId());
				if (Request::getUserVar('formatSushiLite')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
					}
				} else {
					$form->initData();
				}
				$this->setBreadCrumbs(true);
				$form->assignTemplateValues($templateMgr);
				$form->display();
				return true;
			default:
				// Unknown management verb
				assert(false);
				return false;
		}
	}
	
	/**
	 * Get page handler path for this plugin.
	 * @return string Path to plugin's page handler
	 */
	function getHandlerPath() {
		return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'pages';
	}
	
	/**
	 * Get classes path for this plugin.
	 * @return string Path to plugin's classes
	 */
	function getClassPath() {
		return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'classes';
	}
	
	/**
	 * Hook callback: register pages for each sushi-lite method
	 * This URL is of the form: sushiLite/{version}/{Method}?{Parameters}
	 * @see PKPPageRouter::route()
	 */
	function setupPublicHandler($hookName, $params) {
		$page = $params[0];
		if ($page == 'sushiLite') {
			define('HANDLER_CLASS', 'SushiLiteHandler');
			AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
			$newop =& $params[1];
			$newop = 'index';
			$handlerFile =& $params[2];
			$handlerFile = $this->getHandlerPath() . DIRECTORY_SEPARATOR . HANDLER_CLASS . '.inc.php';
		}
	}	 

	
	/*
	 * Identify the available SUSHI Lite versions for this plugin
	 * These versions must exist as classes in the form of "SushiLite_" + $version + ".inc.php"
	 * Furthermore, these versions must be a numeric major version, with an optional underscore and a numeric minor version
	 * @return array List of versions
	 */
	function getAvailableVersions() {
		$versions = array();
		$head = $this->getClassPath().DIRECTORY_SEPARATOR.'SushiLite_';
		$tail = '.inc.php';
		foreach (glob($head.'*'.$tail) as $classfile) {
			$version = substr(substr($classfile, strlen($head)), 0, -strlen($tail));
			if ($version && preg_match($this->getVersionRegex(), $version)) {
				$versions[] = $version;
			}
		}
		return $versions;
	}
	
	/**
	 * Extend the {url ...} smarty to support this plugin.
	 */
	function smartyPluginUrl($params, &$smarty) {
		$path = array($this->getCategory(), $this->getName());
		if (is_array($params['path'])) {
			$params['path'] = array_merge($path, $params['path']);
		} elseif (!empty($params['path'])) {
			$params['path'] = array_merge($path, array($params['path']));
		} else {
			$params['path'] = $path;
		}

		return $smarty->smartyUrl($params, $smarty);
	}

	/*
	 * Regex for validating the version string
	 * This will be the suffix of the class and will appear in the URL
	 * @return string regex
	 */
	function getVersionRegex() {
		return '/^v[0-9]+(_[0-9]+)?$/';
	}
	
	/**
	 * @see PKPPlugin::getLocaleFilename($locale)
	 */
	function getLocaleFilename($locale) {
		$localeFilenames = parent::getLocaleFilename($locale);

		// Add dynamic locale keys.
		foreach (glob($this->getPluginPath() . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . '*.xml') as $file) {
			if (!in_array($file, $localeFilenames)) {
				$localeFilenames[] = $file;
			}
		}

		return $localeFilenames;
	}

	/*
	 * Get a SUSHI object
	 * @param string $version
	 * @return object
	 */
	function getSushi($version) {
		$classPath = str_replace('.', DIRECTORY_SEPARATOR, $this->getClassesClasspath().'.');
		$sushilite = 'SushiLite_'.$version;
		if (file_exists($classPath . $sushilite . '.inc.php')) {
			import($this->getClassesClasspath().'.'.$sushilite);
			return new $sushilite('generic', $this->getName());
		} else {
			// TODO: Get best version instead of "unavailable" abstract version?
			import($this->getClassesClasspath().'.SushiLite');
			return new SushiLite('generic', $this->getName());
		}
	}

	/**
	 * Return the class location in classpath format
	 * @return string
	 */
	function getClassesClasspath() {
		return 'plugins.generic.sushiLite.classes';
	}

	
}
?>
