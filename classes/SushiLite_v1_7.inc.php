<?php

/**
 * @file plugins/generic/sushiLite/classes/SushiLite_v1_7.inc.php
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * @class SushiLite_v1_7
 * @ingroup plugins_generic_sushilite
 *
 * @brief A SUSHI Lite request
 */
define('SUSHI_LITE_METHOD_INDEX', '');
define('SUSHI_LITE_METHOD_GETREPORT', 'GetReport');
define('SUSHI_LITE_METHOD_GETSTATUS', 'GetStatus');
define('SUSHI_LITE_METHOD_GETMETHODS', 'GetMethods');
define('SUSHI_LITE_METHOD_GETREGISTRYENTRY', 'GetRegistryEntry');

import('plugins.generic.sushiLite.classes.SushiLite');

class SushiLite_v1_7 extends SushiLite {
	
	/**
	 * Constructor
	 * @param string $parentPluginCategory
	 * @param string $parentPluginName
	 */
	function SushiLite_v1_7($parentPluginCategory, $parentPluginName) {
		parent::SushiLite($parentPluginCategory, $parentPluginName);
	}
	

	/**
	 * Process the request
	 * @param string $method
	 * @param object $request
	 */
	function processRequest($method, $request) {
		$params = $request->getQueryArray();
		if ($method == SUSHI_LITE_METHOD_INDEX) {
			// The index method is just a human readable page
			$templateMgr = TemplateManager::getManager();
			$templateMgr->assign('sushiLiteVersion', '1.7');
			$plugin = $this->getParentPlugin();
			if (method_exists($plugin, 'getTemplateResource')) {
				// OJS 3.1.2 and later
				$templateMgr->display($plugin->getTemplateResource('describeService.tpl'));
			} else {
				// OJS 3.1.1 and earlier
				$templateMgr->display($plugin->getTemplatePath() . 'describeService.tpl');
			}
		} else {
			// The programatic methods should be authorized and then parsed then printed
			if ($this->authorize($params)) {
				$this->setFormat($params);
				switch ($method) {
					case SUSHI_LITE_METHOD_GETSTATUS:
						$this->createError(1, SUSHI_ERROR_SEVERITY_INFO, __('plugins.generic.sushiLite.error.GetStatusNotImplemented'));
						break;
					case SUSHI_LITE_METHOD_GETMETHODS:
						$this->createError(1, SUSHI_ERROR_SEVERITY_INFO, __('plugins.generic.sushiLite.error.GetMethodsNotImplemented'));
						break;
					case SUSHI_LITE_METHOD_GETREGISTRYENTRY:
						$this->createError(1, SUSHI_ERROR_SEVERITY_INFO, __('plugins.generic.sushiLite.error.GetRegistryEntryNotImplemented'));
						break;
					case SUSHI_LITE_METHOD_GETREPORT:
						$this->parseFilters($params);
						$this->parseAttributes($params);
						$this->parseReport($params);
						// Don't bother initializing the report for ERROR and FATAL conditions
						if ($this->getErrorSeverity() < SUSHI_ERROR_SEVERITY_ERROR) {
							$counter = $this->getCounterPlugin();
							$reporter = $counter->getReporter($this->_selected_report, $this->_selected_release);
							if ($reporter) {
								$xmlResult = $reporter->createXML($reporter->getReportItems($this->_metrics_columns, $this->_metrics_filter, $this->_metrics_orderBy, $this->_metrics_range));
								if ($xmlResult) {
									$xml = new DOMDocument();
									$xml->loadXML($xmlResult);
									foreach ($xml->childNodes as $node) {
										$this->_results = $node;
									}
								}
								$exceptions = $reporter->getErrors();
								if ($exceptions) {
									foreach ($exceptions as $ex) {
										$handled = false;
										if ($ex->getCode() & COUNTER_EXCEPTION_BAD_COLUMNS || $ex->getCode() & COUNTER_EXCEPTION_BAD_FILTERS || $ex->getCode() & COUNTER_EXCEPTION_BAD_ORDERBY || $ex->getCode() & COUNTER_EXCEPTION_BAD_RANGE) {
											$this->createError(3050, $ex->getCode() & COUNTER_EXCEPTION_ERROR ? SUSHI_ERROR_SEVERITY_ERROR : SUSHI_ERROR_SEVERITY_WARNING, __('plugins.generic.sushiLite.error.internalError'), NULL, $ex->getMessage());
											$handled = true;
										}
										if ($ex->getCode() & COUNTER_EXCEPTION_NO_DATA) {
											$this->createError(3030, $ex->getCode() & COUNTER_EXCEPTION_ERROR ? SUSHI_ERROR_SEVERITY_ERROR : SUSHI_ERROR_SEVERITY_WARNING, __('plugins.generic.sushiLite.error.internalError'), NULL, $ex->getMessage());
											$handled = true;
										}
										if ($ex->getCode() & COUNTER_EXCEPTION_PARTIAL_DATA) {
											$this->createError(3040, $ex->getCode() & COUNTER_EXCEPTION_ERROR ? SUSHI_ERROR_SEVERITY_ERROR : SUSHI_ERROR_SEVERITY_WARNING, __('plugins.generic.sushiLite.error.internalError'), NULL, $ex->getMessage());
											$handled = true;
										}
										if (!$handled) {
											$this->createError(1000, $ex->getCode() & COUNTER_EXCEPTION_ERROR ? SUSHI_ERROR_SEVERITY_ERROR : SUSHI_ERROR_SEVERITY_WARNING, __('plugins.generic.sushiLite.error.internalError'), NULL, $ex->getMessage().' '.$ex->getTraceAsString());
										}
									}
								}
							} else {
								$this->createError(1000, SUSHI_ERROR_SEVERITY_ERROR, __('plugins.generic.sushiLite.error.internalError'));
							}
						}
						break;
					default:
						Dispatcher::handle404();
					}
			}
			$this->printResponse($this->createResponse());
		}
	}

	/**
	 * Authorize the request
	 * @param array $params
	 * @return boolean
	 */
	function authorize($params) {
		import('lib.pkp.classes.validation.ValidatorRegExp');
		$validator = new ValidatorRegExp('/^[a-h0-9]{8}-[a-h0-9]{4}-[a-h0-9]{4}-[a-h0-9]{4}-[a-h0-9]{12}$/');
		// Check the RequestorID, CustomerID and APIKey combination
		// TODO: can also use Request::getIpAddress(), or logged-in user information
		if (!isset($params['RequestorID']) || strtolower($params['RequestorID']) == SUSHI_LITE_REQUESTOR_ANONYMOUS) {
			$this->setRequestor();
		} elseif (isset($params['RequestorID']) && $validator->isValid($params['RequestorID'])) {
			if (!$this->setRequestor($params['RequestorID'])) {
				return false;
			}
		} else {
			$this->createError(2000, SUSHI_LITE_ERROR_SEVERITY_ERROR);
			return false;
		}
		if (isset($params['CustomerID'])) {
			switch ($params['CustomerID']) {
				// TODO: Allow for customer definitions for access?
				default:
					$this->createError(2010, SUSHI_LITE_ERROR_SEVERITY_ERROR);
					return false;
			}
		}
		// TODO: Authorize Requestor against Customer
		if (isset($params['APIKey'])) {
			if ($validator->isValid($params['APIKey'])) {
				$this->_apikey = $params['APIKey'];
			} else {
				$this->createError(0201, SUSHI_LITE_ERROR_SEVERITY_WARNING, __('plugins.generic.sushiLite.testForm.apiKeyNotUUID'));
			}
		}
		return true;
	}

	/**
	 * Validate the filter
	 * @param array $params
	 * Side Effect: populates the error array and one or more metrics parameters
	 */
	function parseFilters($params) {
		// Check each possible Filter. If not invalid, convert it to a metric parameter (probably $this->_metrics_filter).  If invalid, flag an error.
		// record the recognized (valid or invalid) filter in $this->_filters for reporting in the ReportDefinition response element
		if (!class_exists('ValidatorDate')) {
			import('plugins.generic.sushiLite.classes.validation.ValidatorDate');
		}
		$this->_filters = array();
		if (!isset($this->_metrics_filter)) {
			$this->_metrics_filter = array();
		}

		// BEGIN DATE and END DATE
		$dateValidator = new ValidatorDate();
		if (isset($params['BeginDate']) && !('' == $params['BeginDate'])) {
			if (!$dateValidator->isValid($params['BeginDate'])) {
				$this->createError(3020, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.DateBeginInvalid"));
			} else {
				$this->_metrics_filter[STATISTICS_DIMENSION_DAY]['from'] = date_format(date_create($params['BeginDate']), 'Ymd');
			}
		} else {
			$this->_metrics_filter[STATISTICS_DIMENSION_DAY]['from'] = date_format(date_create("first day of previous month"), 'Ymd');
		}
		if (isset($this->_metrics_filter[STATISTICS_DIMENSION_DAY]['from'])) {
			$this->_filters['BeginDate'] = implode('-', sscanf($this->_metrics_filter[STATISTICS_DIMENSION_DAY]['from'], '%4s%2s%2s'));
		} else {
			$this->_filters['BeginDate'] = $params['BeginDate'];
		}
		if (isset($params['EndDate']) && !('' == $params['EndDate'])) {
			if (!$dateValidator->isValid($params['EndDate'])) {
				$this->createError(3020, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.DateEndInvalid"));
			} else {
				$this->_metrics_filter[STATISTICS_DIMENSION_DAY]['to'] = date_format(date_create($params['EndDate']), 'Ymd');
			}
		} else {
			$this->_metrics_filter[STATISTICS_DIMENSION_DAY]['to'] = date_format(date_create("last day of previous month"), 'Ymd');
		}
		if (isset($this->_metrics_filter[STATISTICS_DIMENSION_DAY]['to'])) {
			$this->_filters['EndDate'] = implode('-', sscanf($this->_metrics_filter[STATISTICS_DIMENSION_DAY]['to'], '%4s%2s%2s'));
		} else {
			$this->_filters['EndDate'] = $params['EndDate'];
		}
		if (isset($this->_metrics_filter[STATISTICS_DIMENSION_DAY]['from']) && isset($this->_metrics_filter[STATISTICS_DIMENSION_DAY]['to'])) {
			if (strtotime($this->_metrics_filter[STATISTICS_DIMENSION_DAY]['from']) > strtotime($this->_metrics_filter[STATISTICS_DIMENSION_DAY]['to'])) {
				$this->createError(3020, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.DateBeginAfterEnd"));
				unset($this->_metrics_filter[STATISTICS_DIMENSION_DAY]['from']);
				unset($this->_metrics_filter[STATISTICS_DIMENSION_DAY]['to']);
			}
			// TODO: validate that statistics have been processed for this date range?  How would we tell (Exception 3030 vs. 3031)?
		}

		// ITEM IDENTIFIER
		if (isset($params['ItemIdentifier']) && !('' == $params['ItemIdentifier'])) {
			$this->_filters['ItemIdentifier'] = $params['ItemIdentifier'];
			import('plugins.generic.sushiLite.classes.validation.ValidatorMultiItemIdentifier');
			$validator = new ValidatorMultiItemIdentifier();
			if (!$validator->isValid($params['ItemIdentifier'])) {
				$this->createError(3060, SUSHI_LITE_ERROR_SEVERITY_ERROR, '', NULL, $validator->getRejects($params['ItemIdentifier']));
			}
			import('plugins.generic.sushiLite.classes.SushiItemIdentifier');
			// Because we may start populating the item filter above, construct this additional filter separately, to be AND'd later
			$andFilter = array();
			foreach ($validator->sanitize($params['ItemIdentifier']) as $identifier) {
				$itemIdentifier = new SushiItemIdentifier($identifier['scope'], $identifier['type'], $identifier['value']);
				if ($itemIdentifier) {
					$newFilter = $itemIdentifier->getMetricFilter();
					if ($newFilter) {
						$newFilterKey = array_pop(array_keys($newFilter));
						if (array_key_exists($newFilterKey, $andFilter)) {
							$andFilter[$newFilterKey] = array_merge($andFilter[$newFilterKey], array($newFilter[$newFilterKey]));
						} else {
							$andFilter[$newFilterKey] = array($newFilter[$newFilterKey]);
						}
					} else {
						$this->createError(60, SUSHI_LITE_ERROR_SEVERITY_WARNING, '', NULL, join(':', array($identifier['scope'] ? $identifier['scope'] : '*', $identifier['type'], $identifier['value'])));
					}
				}
			}
			// AND the new filter with any old filter; if no old filter, the new filter replaces the empty filter key
			foreach (array_keys($andFilter) as $filterKey) {
				if (array_key_exists($filterKey, $this->_metrics_filter)) {
					$this->_metrics_filter[$filterKey] = array_intersect($this->_metrics_filter[$filterKey], $andFilter[$filterKey]);
				} else {
					$this->_metrics_filter[$filterKey] = $andFilter[$filterKey];
				}
			}
			// We raised warnings if particular items didn't match
			// If none of the items matched, raise an error
			if (!$andFilter) {
				$this->createError(3060, SUSHI_LITE_ERROR_SEVERITY_ERROR, __('plugins.generic.sushiLite.itemIdentifier.allDiscarded'), NULL, $params['ItemIdentifier']);
			}
		}

		// ITEM CONTRIBUTOR
		if (isset($params['ItemContributor']) && !('' == $params['ItemContributor'])) {
			$this->_filters['ItemContributor'] = $params['ItemContributor'];
			import('plugins.generic.sushiLite.classes.validation.ValidatorMultiItemContributor');
			$validator = new ValidatorMultiItemContributor();
			if (!$validator->isValid($params['ItemContributor'])) {
				$this->createError(3060, SUSHI_LITE_ERROR_SEVERITY_ERROR, '', NULL, $validator->getRejects($params['ItemContributor']));
			}
			import('plugins.generic.sushiLite.classes.SushiItemIdentifier');
			// Because we probably started populating the item filter above, construct this additional filter separately, to be AND'd later
			// TODO: This could get weird if ItemIdentifiers stacked with ItemContributors return divergent STATISTICS_DIMENSION keys
			// Right now we are presuming that getMetricFilter() will generally return a consistent STATISTICS_DIMENSION key
			$andFilter = array();
			foreach ($validator->sanitize($params['ItemContributor']) as $contributor) {
				$itemContributor = new SushiItemContributor($contributor['role'], $contributor['type'], $contributor['value']);
				if ($itemContributor) {
					$newFilter = $itemContributor->getMetricFilter();
					$newFilterKey = array_pop(array_keys($newFilter));
					if (array_key_exists($newFilterKey, $andFilter)) {
						$andFilter[$newFilterKey] = array_merge($andFilter[$newFilterKey], array($newFilter[$newFilterKey]));
					} else {
						$andFilter[$newFilterKey] = array($newFilter[$newFilterKey]);
					}
				}
			}
			// AND the new filter with any old filter; if no old filter, the new filter replaces the empty filter key
			foreach ($andFilter as $filterKey) {
				if (array_key_exists($filterKey, $this->metrics_filter)) {
					$this->_metrics_filter[$filterKey] = array_intersect($this->_metrics_filter[$filterKey], $andFilter[$filterKey]);
				} else {
					$this->_metrics_filter[$filterKey] = $andFilter[$filterKey];
				}
			}
			// We raised warnings if particular items didn't match
			// If none of the items matched, raise an error
			if (!$andFilter) {
				$this->createError(3060, SUSHI_LITE_ERROR_SEVERITY_ERROR, __('plugins.generic.sushiLite.itemContributor.allDiscarded'), NULL, $params['ItemContributor']);
			}
		}

		// PUBLISHER
		// TODO: Validate Publisher Filter?
		if (isset($params['Publisher']) && $params['Publisher']) {
			$this->_filters['Publisher'] = $params['Publisher'];
			$this->createError(3060, SUSHI_ERROR_SEVERITY_WARNING, __('plugins.generic.sushiLite.testForm.publisherInvalid'), null, $params['Publisher']);
		}

		// PLATFORM
		// Disallow Platform Filter
		if (isset($params['Platform']) && $params['Platform']) {
			$this->_filters['Platform'] = $params['Platform'];
			$this->createError(3060, SUSHI_ERROR_SEVERITY_WARNING, __('plugins.generic.sushiLite.testForm.platformInvalid'), null, $params['Platform']);
		}

		// TODO: Pick up conversion of $this->_filters to $this->_metrics_filter here
		// METRIC TYPES
		if (isset($params['MetricTypes']) && !('' == $params['MetricTypes'])) {
			$this->_filters['MetricTypes'] = $params['MetricTypes'];
			foreach (explode('|', $params['MetricTypes']) as $metrictype) {
				if (!$metrictype) {
					continue;
				}
				if (in_array($metrictype, $this->validMetrics())) {
					//$this->_metrics_filter['...'] = isset($this->filters['MetricTypes']) ? $this->filters['MetricTypes'] . '|' . $metrictype : $metrictype;
				} else {
					$this->createError(3060, SUSHI_ERROR_SEVERITY_WARNING, '', null, $metrictype);
				}
			}
		}
		if (isset($this->_filters['MetricTypes'])) {
			$this->createError(3050, SUSHI_LITE_ERROR_SEVERITY_WARNING, __("plugins.generic.sushiLite.testForm.MetricTypesNotSupported"));
		}

		// PUBLICATION YEARS
		if (isset($params['PubYr']) && !('' == $params['PubYr'])) {
			if (!$dateValidator->isValid($params['PubYr'])) {
				$this->createError(3020, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.PubYrInvalid"));
			} else {
				$this->_filters['PubYr'] = intval($params['PubYr']);
			}
		}
		if (isset($params['PubYrFrom']) && !('' == $params['PubYrFrom'])) {
			if (!$dateValidator->isValid($params['PubYrFrom'])) {
				$this->createError(3020, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.PubYrFromInvalid"));
			} else {
				$this->_filters['PubYrFrom'] = intval($params['PubYrFrom']);
			}
		}
		if (isset($params['PubYrTo']) && !('' == $params['PubYrTo'])) {
			if (!$dateValidator->isValid($params['PubYrTo'])) {
				$this->createError(3020, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.PubYrToInvalid"));
			} else {
				$this->_filters['PubYrTo'] = intval($params['PubYrTo']);
			}
		}
		if (isset($this->_filters['PubYrFrom']) && isset($this->_filters['PubYrTo'])) {
			if (intval($params['PubYrFrom']) > intval($params['PubYrTo'])) {
				$this->createError(3020, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.PubYrFromAfterTo"));
			}
		}
		if (isset($this->_filters['PubYrFrom']) && isset($this->_filters['PubYrTo']) && isset($this->_filters['PubYr'])) {
			if ($this->_filters['PubYrFrom'] != $this->_filters['PubYr'] || $this->_filters['PubYrTo'] != $this->_filters['PubYr']) {
				$this->createError(3020, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.PubYrFromToConflict"));
			}
		}
		if (isset($this->_filters['PubYrFrom']) || isset($this->_filters['PubYrTo']) || isset($this->_filters['PubYr'])) {
			$this->createError(3050, SUSHI_LITE_ERROR_SEVERITY_WARNING, __("plugins.generic.sushiLite.testForm.PubYrsNotSupported"));
		}

		// IS ARCHIVE
		if (isset($params['isArchive']) && !('' == $params['isArchive'])) {
			if (in_array(strtoupper($params['isArchive']), array('Y', 'N', 'YES', 'NO'))) {
				$this->createError(3060, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.IsArchiveInvalid"));
			} else {
				$this->_filters['isArchive'] = ucfirst(strtolower($params['isArchive']));
				$this->createError(3050, SUSHI_LITE_ERROR_SEVERITY_WARNING, __("plugins.generic.sushiLite.testForm.IsArchiveNotSupported"));
			}
		}
	}

	/**
	 * Validate the attributes
	 * @param array $params
	 * Side Effect: populates the error array
	 */
	function parseAttributes($params) {
		// Check each possible Attribute. If invalid flag an error.
		$this->_attributes = array();

		// GRANULARITY
		if (isset($params['Granularity']) && !('' == $params['Granularity'])) {
			$this->_attributes['Granularity'] = ucfirst(strtolower($params['Granularity']));
			if (!in_array(ucfirst(strtolower($params['Granularity'])), array('Totals', 'Yearly', 'Monthly', 'Daily'))) {
				$this->createError(3061, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.GranularityInvalid"));
			} else {
				// TODO: $this->_metrics_columns should be set based on the aggregation here
			}
			$this->createError(3050, SUSHI_LITE_ERROR_SEVERITY_WARNING, __("plugins.generic.sushiLite.testForm.GranularityNotSupported"));
		}

		// FORMAT and CALLBACK
		if (isset($params['Format']) && !('' == $params['Format'])) {
			if (!in_array($params['Format'], array('json', 'jsonp', 'xml'))) {
				$this->createError(3061, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.FormatInvalid"));
			}
		}
		if (isset($params['Callback']) && isset($params['Callback'])) {
			if (isset($params['Format']) && $params['Format'] == 'json') {
				$this->createError(3061, SUSHI_LITE_ERROR_SEVERITY_WARNING, __("plugins.generic.sushiLite.testForm.FormatJsonpAssumed"));
			} elseif (isset($params['Format']) && $params['Format'] != 'jsonp') {
				$this->createError(3061, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.CallbackInvalid"));
			}
		}

		// LIMIT and OFFSET
		import('lib.pkp.classes.db.DBResultRange');
		$range = new DBResultRange(-1, -1);
		if (isset($params['Limit']) && !('' == $params['Limit'])) {
			$this->_attributes['Limit'] = intval($params['Limit']);
			if ($this->_attributes['Limit'] != $params['Limit']) {
				$this->createError(3061, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.LimitInvalid"), null, $params['Limit']);
			} else {
				$range->setCount($this->_attributes['Limit']);
			}
		}
		if (isset($params['Offset']) && !('' == $params['Offset'])) {
			$this->_attributes['Offset'] = intval($params['Offset']);
			if ($range->getCount() == -1) {
				$this->createError(3061, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.OffsetWithoutLimit"));
			} elseif ($this->_attributes['Offset'] != $params['Offset']) {
				$this->createError(3061, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.OffsetInvalid"), null, $params['Offset']);
			} else {
				$range->setPage($this->_attributes['Offset']);
			}
		} else {
			$range->setPage(0);
		}
		if ($range->isValid()) {
			$this->_metrics_range = $range;
		}

		// ORDER BY
		if (isset($params['OrderBy']) && !('' == $params['OrderBy'])) {
			$this->_attributes['OrderBy'] = $params['OrderBy'];
			import('lib.pkp.classes.validation.ValidatorRegExp');
			$validator = new ValidatorRegexp('/^(?P<field>[^:]+)(:(?P<order>asc|desc))?$/');
			if ($validator->isValid($params['OrderBy'])) {
				$order = explode(':', $params['OrderBy']);
				import('plugins.generic.sushiLite.classes.SushiOrderBy');
				if (!isset($order[1])) {
					$order[1] = 'asc';
				}
				$this->_attributes['OrderBy'] = implode(':', $order);
				$test = new SushiOrderBy($order[0], $order[1]);
				if ($test->isValid()) {
					$this->_metrics_orderBy = $test->getMetricOrderBy();
				} else {
					$this->createError(3061, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.OrderByInvalid"), null, $params['OrderBy']);
				}
			} else {
				$this->createError(3061, SUSHI_LITE_ERROR_SEVERITY_ERROR, __("plugins.generic.sushiLite.testForm.OrderByInvalid"), null, $params['OrderBy']);
			}
			$this->createError(3050, SUSHI_LITE_ERROR_SEVERITY_WARNING, __("plugins.generic.sushiLite.testForm.OrderByNotSupported"));
		}
	}

	/**
	 * Validate the report parameter
	 * @param array $params
	 * @return string reportname
	 * Side Effect: populates the error array
	 */
	function parseReport($params) {
		$counter = $this->getCounterPlugin();
		// Check requested release: default it to the current release if not provided, unset release if not valid.
		if (!isset($params['Release'])) {
			$release = $counter->getCurrentRelease();
		} elseif ($params['Release'] == $counter->getCurrentRelease()) {
			$release = $params['Release'];
		} elseif ($params['Release'].'.0' == $counter->getCurrentRelease()) {
			$release = $params['Release'].'.0';
		} else {
			$release = "";
		}
		// Check the requested report. Warn if report is supported but requested release was invalid.
		if (isset($params['Report'])) {
			$reports = $counter->getValidReports();
			if (isset($reports[$params['Report']])) {
				$this->_report = $reports[$params['Report']];
				$this->_selected_report = $params['Report'];
				$this->_selected_release = str_replace('.', '_', $release);
				// if we found the report, but the release requested was wrong, this is a warning
				if (!$release) {
					$release = $counter->getCurrentRelease();
					$this->createError(3010, SUSHI_ERROR_SEVERITY_WARNING, __('plugins.generic.sushiLite.error.reportExistsInRelease', array('release' => $release)));
				}
			} else {
				$this->createError(3000, SUSHI_ERROR_SEVERITY_ERROR);
			}
		} else {
			$this->createError(3000, SUSHI_ERROR_SEVERITY_ERROR, __('plugins.generic.sushiLite.error.noReportProvided'));
		}
		return $this->_report;
	}
	
	/**
	 * List the valid filters
	 * @return array
	 */
	function validFilters() {
		return array('BeginDate', 'EndDate', 'ItemIdentifier', 'ItemContributor', 'Publisher', 'Platform', 'MetricTypes', 'PubYr', 'PubYrFrom', 'PubYrTo', 'IsArchive');
	}

	/**
	 * List the valid attributes
	 * @return array
	 */
	function validAttributes() {
		return array('Granularity', 'Format', 'Callback', 'Limit', 'Offset', 'OrderBy');
	}

	/**
	 * List the valid metric types
	 * @return array
	 */
	function validMetrics() {
		// TODO: Limit these to only those actually available
		return array('ft_ps', 'ft_ps_mobile', 'ft_pdf', 'ft_pdf_mobile', 'ft_html', 'ft_html_mobile', 'ft_epub', 'sectioned_html', 'ft_total', 'toc', 'abstract', 'reference', 'data_set', 'audio', 'video', 'image', 'podcast', 'multimedia', 'record_view', 'result_click', 'search_reg', 'turnaway', 'no_license', 'other');
	}
}
