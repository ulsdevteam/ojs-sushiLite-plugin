<?php

/**
 * @file plugins/generic/sushiLite/classes/SushiLite.inc.php
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
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

	const NAMESPACE_SUSHI = 'http://www.niso.org/schemas/sushi';
	const NAMESPACE_COUNTER = 'http://www.niso.org/schemas/counter';
	const NAMESPACE_SUSHI_COUNTER = 'http://www.niso.org/schemas/sushi/counter';

	
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
		$plugin = PluginRegistry::getPlugin($this->_parentPluginCategory, $this->_parentPluginName);
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
		$error = $doc->appendChild($doc->createElementNS('http://www.niso.org/schemas/sushi', 's:Exception'));
		$error->appendChild($doc->createElementNS('http://www.niso.org/schemas/sushi', 's:Number', $number));
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
		$error->appendChild($doc->createElementNS('http://www.niso.org/schemas/sushi', 's:Severity', $severityString));
		$error->appendChild($doc->createElementNS('http://www.niso.org/schemas/sushi', 's:Message', __('plugins.generic.sushiLite.error.'.sprintf('%04d', $number)).($message ? ' '.$message : '')));
		if ($helpUrl) {
			$error->appendChild($doc->createElementNS('http://www.niso.org/schemas/sushi', 's:HelpUrl', $helpUrl));
		}
		if ($data) {
			$error->appendChild($doc->createElementNS('http://www.niso.org/schemas/sushi', 's:Data', $data));
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
		$doc->formatOutput = true;
		$response = $doc->appendChild($doc->createElementNS(self::NAMESPACE_SUSHI_COUNTER, 'sc:ReportResponse'));
		$response->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:s', self::NAMESPACE_SUSHI);
		$response->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:c', self::NAMESPACE_COUNTER);
		$response->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sc', self::NAMESPACE_SUSHI_COUNTER);
		$response->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
		$xmlns = $doc->createAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation');
		$xmlns->value = self::NAMESPACE_SUSHI.' http://www.niso.org/schemas/sushi/sushi1_7.xsd '.
			self::NAMESPACE_SUSHI_COUNTER.' http://www.niso.org/schemas/sushi/sushi_counter4_1.xsd ';
			self::NAMESPACE_COUNTER.' http://www.niso.org/schemas/sushi/counter4_1.xsd';
		$response->appendChild($xmlns);
		foreach ($this->_errors as $error) {
			if (get_class($error) == 'DOMElement') {
				$response->appendChild($doc->importNode($error, true));
			}
		}
		$requestor = $doc->createElementNS(self::NAMESPACE_SUSHI, 's:Requestor');
		$requestor->appendChild($doc->createElementNS(self::NAMESPACE_SUSHI, 's:ID', $this->_requestor));
		$requestor->appendChild($doc->createElementNS(self::NAMESPACE_SUSHI, 's:Name'));
		$requestor->appendChild($doc->createElementNS(self::NAMESPACE_SUSHI, 's:Email'));
		$response->appendChild($requestor);
		$customer = $doc->createElementNS(self::NAMESPACE_SUSHI, 's:CustomerReference');
		$customer->appendChild($doc->createElementNS(self::NAMESPACE_SUSHI, 's:ID', $this->_customer));
		$response->appendChild($customer);
		$definition = $doc->createElementNS(self::NAMESPACE_SUSHI, 's:ReportDefinition');
		$definition->setAttribute('Name', $this->_selected_report);
		$definition->setAttribute('Release', str_replace('_', '.', $this->_selected_release));
		$filters = $doc->createElementNS(self::NAMESPACE_SUSHI, 's:Filters');
		$range = $doc->createElementNS(self::NAMESPACE_SUSHI, 's:UsageDateRange');
		$range->appendChild($doc->createElementNS(self::NAMESPACE_SUSHI, 's:Begin', $this->_filters['BeginDate']));
		$range->appendChild($doc->createElementNS(self::NAMESPACE_SUSHI, 's:End', $this->_filters['EndDate']));
		$filters->appendChild($range);
		foreach ($this->validFilters() as $filter) {
			if ($filter != 'BeginDate' && $filter != 'EndDate' && isset($this->_filters[$filter])) {
				$e = $doc->createElementNS(self::NAMESPACE_SUSHI, 's:Filter', $this->_filters[$filter]);
				$e->setAttribute('Name', $filter);
				$filters->appendChild($e);
			}
		}
		foreach ($this->validAttributes() as $attr) {
			if (isset($this->_attributes[$attr])) {
				$e = $doc->createElementNS(self::NAMESPACE_SUSHI, 's:ReportAttribute', $this->_attributes[$attr]);
				$e->setAttribute('Name', $attr);
				$filters->appendChild($e);
			}
		}
		$definition->appendChild($filters);
		$response->appendChild($definition);
		$reports = $doc->createElementNS(self::NAMESPACE_SUSHI_COUNTER, 'sc:Report');
		// Incoming report's root element is <Reports>
		// We are interested in appending each child <Report> from that element
		if (get_class($this->_results) == 'DOMElement' && $this->_results->hasChildNodes()) {
			// Dummy report establishes the c: namespace for the imported nodes
			$dummyReport = $reports->appendChild($doc->createElementNS(self::NAMESPACE_COUNTER, 'c:Report'));
			foreach ($this->_results->childNodes as $child) {
				$report = $doc->importNode($child, true);
				$reports->appendChild($report);
			}
			$response->appendChild($reports);
			// Remove dummy report after establishing $reports
			$reports->removeChild($dummyReport);
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
	 * Get the filter allowed
	 * @return array
	 */
	function validFilters() {
		return array();
	}

	/**
	 * Get the attributes allowed
	 * @return array
	 */
	function validAttributes() {
		return array();
	}

	/**
	 * Get the requestor
	 * @return string
	 */
	function getRequestor() {
		return $this->_requestor;
	}
}