<?php

/**
 * @file plugins/generic/sushiLite/SushiLitePlugin.inc.php
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * @class CounterReportPlugin
 * @ingroup plugins_generic_sushilite
 *
 * @brief Sushi Lite plugin provides a SUSHI-Lite implementation for usage statistics harvesting.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class SushiLiteGenericPlugin extends GenericPlugin {

	/**
	 * @copydoc LazyLoadPlugin::register()
	 */
	function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path, $mainContextId);
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
	function getTemplatePath($inCore = false) {
		return parent::getTemplatePath($inCore) . 'templates';
	}	 

	/**
	 * @see PKPPlugin::isSitePlugin()
	 */
	function isSitePlugin() {
		return true;
	}

	/**
	 * Get page handler path for this plugin.
	 * @return string Path to plugin's page handler
	 */
	function getHandlerPath() {
		return 'pages';
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
			$this->import($this->getHandlerPath().DIRECTORY_SEPARATOR.HANDLER_CLASS);
			$params[1] = 'index';
			return true;
		}
		return false;
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
