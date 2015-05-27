{**
 * plugins/generic/sushiLite/templates/desribeService.tpl
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * SUSHI-Lite getMethods response
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.sushiLite.serviceDescription.pageTitle"}
{include file="common/header.tpl"}
{/strip}
<h2>{$sushiLiteVersion}</h2>
<div id="sushiLiteSettings">
<div id="description">{translate key="plugins.generic.sushiLite.serviceDescription.description"}</div>
{include file="common/footer.tpl"}