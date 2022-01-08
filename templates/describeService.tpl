{**
 * plugins/generic/sushiLite/templates/describeService.tpl
 *
 * Copyright (c) University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * SUSHI-Lite getMethods response
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.sushiLite.plugin.name"}
{include file="templates/frontend/components/header.tpl"}
{/strip}
<h2>{$sushiLiteVersion}</h2>
<div id="sushiLiteSettings">
<div id="description">{translate key="plugins.generic.sushiLite.plugin.description"}</div>
{include file="templates/frontend/components/footer.tpl"}