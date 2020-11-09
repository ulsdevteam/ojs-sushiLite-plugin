<?php

/**
 * @file plugins/generic/sushiLite/pages/SushiLiteHandler.inc.php
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * @class SushiLiteHandler
 * @ingroup plugins_generic_sushilite
 *
 * @brief Handle SUSHI-Lite method requests.
 */

import('classes.handler.Handler');

define('SUSHI_ERROR_SEVERITY_INFO', 1);
define('SUSHI_ERROR_SEVERITY_DEBUG', 1);
define('SUSHI_ERROR_SEVERITY_WARNING', 2);
define('SUSHI_ERROR_SEVERITY_ERROR', 4);
define('SUSHI_ERROR_SEVERITY_FATAL', 8);


class SushiLiteHandler extends Handler {

	/**
	 * Index handler
	 * @param $args array
	 * @param $request Request
	 */
	function index($args, $request) {
		$version = $request->getRequestedOp($request);
		$plugin = PluginRegistry::getPlugin('generic', 'SushiLiteGenericPlugin');
		$sushi = $plugin->getSushi($version);
		$sushi->processRequest(count($args) ? $args[0] : '', $request);
	}

}
