{**
 * plugins/generic/plumAnalytics/settingsForm.tpl
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * SUSHI-Lite plugin settings
 *
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.sushiLite.settingsForm.pageTitle"}
{include file="common/header.tpl"}
{/strip}
<div id="sushiLiteSettings">
<div id="description">{translate key="plugins.generic.sushiLite.settingsForm.description"}</div>

<div class="separator"></div>

<form method="post" action="{plugin_url path="settings"}">
{include file="common/formErrors.tpl"}
<p>No settings at this time</p>
<input type="submit" name="save" class="button defaultButton" value="{translate key="common.save"}"/><input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.go(-1)"/>
</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</div>
{include file="common/footer.tpl"}
