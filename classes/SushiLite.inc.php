<?php

/**
 * @file plugins/generic/sushiLite/classes/SushiLite.inc.php
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * @class SushiLite
 * @ingroup plugins_generic_sushilite
 *
 * @brief A SUSHI Lite request
 */

define('SUSHI_LITE_ERROR_SEVERITY_INFO', 1);
define('SUSHI_LITE_ERROR_SEVERITY_WARNING', 2);
define('SUSHI_LITE_ERROR_SEVERITY_ERROR', 4);
define('SUSHI_LITE_ERROR_SEVERITY_FATAL', 8);

define('SUSHI_LITE_REQUESTOR_ANONYMOUS', "anonymous");

define('SUSHI_LITE_FORMAT_XML', 0);
define('SUSHI_LITE_FORMAT_JSON', 1);




class SushiLite {

	var $_parentPluginCategory;
	var $_parentPluginName;

	var $_format;
	var $_jsonp_callback;
	
	var $_metrics_columns;
	var $_metrics_filter;
	var $_metrics_orderBy;
	var $_metrics_range;
	var $_selectedItems;
	
	var $_filters;
	var $_attributes;
	var $_requestor;
	var $_customer;
	var $_report;
	var $_errors;
	var $_results;
	var $_max_error;
		
	var $_selected_report;
	var $_selected_release;

	/**
	 * Constructor
	 * @return SushiLite object
	 */
	function SushiLite($parentPluginCategory, $parentPluginName) {
		$this->_parentPluginCategory = $parentPluginCategory;
		$this->_parentPluginName = $parentPluginName;
		$this->_errors = array();
		$this->_max_error = 0;
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
	 * Stub to process the request
	 * Default method must be overridden by subclass
	 * @param string $method
	 * @param array $request
	 * @return string
	 */
	function processRequest($method, $request) {
		// Abstract not implemented: return Service Unavailable error
		$this->createError(1000, SUSHI_LITE_ERROR_SEVERITY_FATAL, __('plugins.generic.sushiLite.error.versionNotSupported'), NULL, __('plugins.generic.sushiLite.error.versionsSupported').' '.implode(', ', $this->getParentPlugin()->getAvailableVersions()));
		$this->printResponse($this->createResponse());
	}

	/**
	 * Get the COUNTER Report Plugin
	 * @return object
	 */
	function getCounterPlugin() {
		$plugin = PluginRegistry::getPlugin('reports', 'CounterReportPlugin');
		if (!$plugin) {
			PluginRegistry::loadPlugin('reports', 'counter');
			$plugin = PluginRegistry::getPlugin('reports', 'CounterReportPlugin');
		}
		return $plugin;
	}
	
	/**
	 * create a SUSHI error object
	 * @param int $number
	 * @param int $severity
	 * @param string $message
	 * @param string $helpUrl optional
	 * @param string $data optional
	 */
	function createError($number, $severity, $message = '', $helpUrl = NULL, $data = NULL) {
		$doc =  new DOMDocument();
		$error = $doc->appendChild($doc->createElement('Exception'));
		$error->appendChild($doc->createElement('Number', $number));
		if ($severity > $this->_max_error) {
			$this->_max_error = $severity;
		}
		$severityString = '';
		switch ($severity) {
			case SUSHI_LITE_ERROR_SEVERITY_INFO:
				$severityString = 'Info';
				break;
			case SUSHI_LITE_ERROR_SEVERITY_WARNING:
				$severityString = 'Warning';
				break;
			case SUSHI_LITE_ERROR_SEVERITY_ERROR:
				$severityString = 'Error';
				break;
			case SUSHI_LITE_ERROR_SEVERITY_FATAL:
				$severityString = 'Fatal';
		}
		$error->appendChild($doc->createElement('Severity', $severityString));
		$error->appendChild($doc->createElement('Message', __('plugins.generic.sushiLite.error.'.sprintf('%04d', $number)).($message ? ' '.$message : '')));
		if ($helpUrl) {
			$error->appendChild($doc->createElement('HelpUrl', $helpUrl));
		}
		if ($data) {
			$error->appendChild($doc->createElement('Data', $data));
		}
		$error->setAttribute('Created', date("c"));
		$this->_errors[] = $error;
	}

	/**
	 * returns the highest severity of existing errors
	 * @return int
	 */
	function getErrorSeverity() {
		return $this->_max_error;
	}
	
	/**
	 * create a SUSHI error object
	 * @return object DOMDocument
	 */
	function createResponse() {
		$doc =  new DOMDocument();
		$response = $doc->appendChild($doc->createElement('ReportResponse'));
		foreach ($this->_errors as $error) {
			if (get_class($error) == 'DOMElement') {
				$response->appendChild($doc->importNode($error, true));
			}
		}
		$requestor = $doc->createElement('Requestor');
		$requestor->appendChild($doc->createElement('ID', $this->_requestor));
		$requestor->appendChild($doc->createElement('Name'));
		$requestor->appendChild($doc->createElement('Email'));
		$response->appendChild($requestor);
		$customer = $doc->createElement('CustomerReference');
		$customer->appendChild($doc->createElement('ID', $this->_customer));
		$response->appendChild($customer);
		$definition = $doc->createElement('ReportDefinition');
		$definition->setAttribute('Name', $this->_selected_report);
		$definition->setAttribute('Release', str_replace('_', '.', $this->_selected_release));
		$filters = $doc->createElement('Filters');
		$range = $doc->createElement('UsageDateRange');
		$range->appendChild($doc->createElement('Begin', $this->_filters['BeginDate']));
		$range->appendChild($doc->createElement('End', $this->_filters['EndDate']));
		$filters->appendChild($range);
		foreach ($this->validFilters() as $filter) {
			if ($filter != 'BeginDate' && $filter != 'EndDate' && isset($this->_filters[$filter])) {
				$e = $doc->createElement('Filter', $this->_filters[$filter]);
				$e->setAttribute('Name', $filter);
				$filters->appendChild($e);
			}
		}
		foreach ($this->validAttributes() as $attr) {
			if (isset($this->_attributes[$attr])) {
				$e = $doc->createElement('ReportAttribute', $this->_attributes[$attr]);
				$e->setAttribute('Name', $attr);
				$filters->appendChild($e);
			}
		}
		$definition->appendChild($filters);
		$response->appendChild($definition);
		if (get_class($this->_results) == 'DOMElement') {
			$response->appendChild($doc->importNode($this->_results, true));
		}
		return $doc;
	}

	/**
	 * Set the return format
	 * @param array parameters
	 */
	function setFormat($params) {
		if (isset($params['Format']) && preg_match('/^json/i', $params['Format'])) {
			$this->_format = SUSHI_LITE_FORMAT_JSON;
			if (isset($params['Callback']) || strtoupper($params['Format']) == 'JSONP') {
				$this->_jsonp_callback = isset($params['Callback']) ? $params['Callback'] : 'callback';
			}
		} else {
			$this->_format = SUSHI_LITE_FORMAT_XML;
		}
	}

	/**
	 * Print the report response
	 * @param DOMDocument $xml
	 * @param boolean $json
	 */
	function printResponse($xml) {
		if ($this->_format == SUSHI_LITE_FORMAT_JSON) {
			header('Content-type: text/json');
			//header('Content-disposition: attachment; filename='.$this->getRequestId().'.json');
			$xslt = new DOMDocument();
			$xslt->load($this->getJsonXslt());
			$transformer = new XSLTProcessor();
			$transformer->importStylesheet($xslt);
			$payload = trim($transformer->transformToDoc($xml)->firstChild->wholeText);
			if (isset($this->_jsonp_callback)) {
				print $this->_jsonp_callback.'('.$payload.')';
			} else {
				print $payload;
			}
		} else {
			header('Content-type: text/xml');
			//header('Content-disposition: attachment; filename='.$this->getRequestId().'.xml');
			print $xml->saveXML();
		}
	}

	/**
	 * Return a request identifier
	 * @return string
	 */
	function getRequestId() {
		return sha1(Request::getQueryString().Request::getRemoteAddr());
	}

	/**
	 * Return the path to the XSLT which will transform XML to JSON
	 * @return string
	 */
	function getJsonXslt() {
		return $this->getParentPlugin()->getPluginPath().'/xml2json.xslt';
	}
	
	/**
	 * Set the requestor
	 * @param $id string
	 * @return boolean success
	 */
	function setRequestor($id = SUSHI_LITE_REQUESTOR_ANONYMOUS) {
		$this->_requestor = $id;
		// TODO: lookup and validate id in else-if
		if ($id === SUSHI_LITE_REQUESTOR_ANONYMOUS) {
			return true;
		} else {
			$this->createError(2000, SUSHI_LITE_ERROR_SEVERITY_ERROR);
			return false;
		}
	}

	/**
	 * Get the requestor
	 * @return string
	 */
	function getRequestor() {
		return $this->_requestor;
	}
}

?>
