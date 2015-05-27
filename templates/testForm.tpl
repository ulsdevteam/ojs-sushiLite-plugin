{**
 * plugins/generic/sushiLite/templates/sushiLiteTestForm.tpl
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * Form for testing SUSHI Lite requests
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.sushiLite.testForm.pageTitle"}
{include file="common/header.tpl"}
{/strip}
<p>{translate key="plugins.generic.sushiLite.testForm.description"}</p>
<div>
	<form action="{plugin_url path="test"}" method="POST">
		{include file="common/formErrors.tpl"}
		<!-- TODO: Method "GetReport" -->
		<h3>{translate key="plugins.generic.sushiLite.testForm.reportRequest"}</h3>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.reportName"}:</span>
			<select name="Report" id="Report">
				{html_options_translate options=$ReportOptions selected="$Report"}
			</select>
		</label>
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.requestorId"}:</span> <input name="RequestorID" type="text" value="{$RequestorID|escape}" id="RequestorID" /></label>
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.customerId"}:</span> <input name="CustomerID" type="text" value="{$CustomerID|escape}" id="CustomerID" /></label>
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.apiKey"}:</span> <input name="APIKey" type="text" value="{$APIKey|escape}" id="APIKey" maxlength="36" size="36" /></label>
		</div>
		<h3>{translate key="plugins.generic.sushiLite.testForm.reportFilters"}</h3>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.dateRange"}:</span></label>
		<input name="DateBegin" type="text" size="10" maxlength="10" value="{$DateBegin|escape}" id="DateBegin" />/ 
		<input name="DateEnd" type="text" size="10" maxlength="10" value="{$DateEnd|escape}" id="DateEnd" />
		{translate key="plugins.generic.sushiLite.testForm.dateRangeFormat"}
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.itemIdentifier"}:</span></label>
			<select name="ItemIdentifierScope" id="ItemIdentifierScope">
				{html_options_translate options=$ItemIdentifierScopeOptions selected="$ItemIdentifierScope"}
			</select>
			<select name="ItemIdentifierType" id="ItemIdentifierType">
				{html_options_translate options=$ItemIdentifierTypeOptions selected="$ItemIdentifierType"}
			</select>
		<input name="ItemIdentifier" value="{$ItemIdentifier|escape}" type="text" id="ItemIdentifier" />
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.itemContributor"}:</span></label>
			<select name="ItemContributorType" id="ItemContributorType">
				{html_options_translate options=$ItemContributorTypeOptions selected="$ItemContributorType"}
			</select>
		<input name="ItemContributor" value="{$ItemContributor|escape}" type="text" id="ItemContributor" />
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.publisher"}:</span>
		<input name="Publisher" value="{$Publisher|escape}" type="text" id="Publisher" /></label>
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.platform"}:</span>
		<input name="Platform" value="{$Platform|escape}" type="text" id="Platform" /></label>
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.metricTypes"}:</span>
		<input name="MetricTypes" value="{$MetricTypes|escape}" type="text" id="MetricTypes" /></label>
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.pubYr"}:</span>
		<input name="PubYr" value="{$PubYr|escape}" type="text" size="4" maxlength="4" id="PubYr" /></label>
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.pubYrFrom"}:</span>
		<input name="PubYrFrom" value="{$PubYrFrom|escape}" type="text" size="4" maxlength="4" id="PubYrFrom" /></label>
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.pubYrTo"}:</span>
		<input name="PubYrTo" value="{$PubYrTo|escape}" type="text" size="4" maxlength="4" id="PubYearTo" /></label>
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.isArchive"}:</span>
			<select name="IsArchive" id="IsArchive">
				{html_options_translate options=$IsArchiveOptions selected="$IsArchive"}
			</select>
		</label>
		</div>
		<h3>{translate key="plugins.generic.sushiLite.testForm.reportAttributes"}</h3>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.granularity"}:</span> 
			<select name="Granularity" id="Granularity">
				{html_options_translate options=$GranularityOptions selected="$Granularity"}
			</select>
		</label>
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.format"}:</span>
			<select name="Format" id="Format">
				{html_options_translate options=$FormatOptions selected="$Format"}
			</select>
		</label>
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.callback"}:</span> <input name="Callback" value="{$Callback|escape}" type="text" id="Callback" /></label>
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.limit"}:</span> <input name="Limit" value="{$Limit|escape}" type="text" id="Limit" /></label>
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.offset"}:</span> <input name="Offset" value="{$Offset|escape}" type="text" id="Offset" /></label>
		</div>
		<div>
		<label><span>{translate key="plugins.generic.sushiLite.testForm.orderBy"}:</span> <input name="OrderBy" value="{$OrderBy|escape}" type="text" id="OrderBy" /></label>
		</div>
		<div>
		<input class="button" name="formatSushiLite" value="{translate key="plugins.generic.sushiLite.testForm.submit"}" type="submit" />
		</div>
	</form>
</div>
{include file="common/footer.tpl"}