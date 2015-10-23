{**
 * plugins/generic/sushiLite/templates/settingsForm.tpl
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
{if $editing}
{include file="../plugins/generic/sushiLite/templates/requestorSubform.tpl"}
{/if}
{if $siteRequestors !== FALSE}
<fieldset>
<legend>{translate key="plugins.generic.sushiLite.settingsForm.siteSettings"}</legend>
<div>
	{fieldLabel name="site_disallow_anonymous" key="plugins.generic.sushiLite.settingsForm.disallowAnonymous"}</label><input type="checkbox" name="site_disallow_anonymous"{if $site_disallow_anonymous} checked="checked"{/if}{if $editing} disabled="disabled"{/if} />
</div>
<table width="100%">
	<tr>
	<th>{translate key="plugins.generic.sushiLite.settingsForm.requestorName"}</th>
	<th>{translate key="plugins.generic.sushiLite.settingsForm.requestorId"}</th>
	<th>{translate key="plugins.generic.sushiLite.settingsForm.requestorIdRequired"}</th>
	<th>{translate key="plugins.generic.sushiLite.settingsForm.requestorApiKey"}</th>
	<th>{translate key="plugins.generic.sushiLite.settingsForm.actions"}</th>
	</tr>
	{if $siteRequestors}
	{foreach name=requestor from=$siteRequestors key=ridx item=requestor}
	<tr>
	<td>{$requestor.name|escape}</td>
	<td>{$requestor.id|escape}</td>
	<td>{$requestor.idRequired|escape}</td>
	<td>{$requestor.apiKey|escape}</td>
	<td><input type="button" onClick="document.forms['sushiLite-delete-{$ridx|escape}'].submit();" value="{translate key='plugins.generic.sushiLite.settingsForm.delete'}"{if $editing} disabled="disabled"{/if} /><input type="button" onClick="document.forms['sushiLite-edit-{$ridx|escape}'].submit();" value="{translate key='plugins.generic.sushiLite.settingsForm.edit'}"{if $editing} disabled="disabled"{/if} /></td>
	</tr>
	{/foreach}
	{/if}
</table>
<input type="submit" name="siteNew" class="button" value="{translate key='plugins.generic.sushiLite.settingsForm.new'}"{if $editing} disabled="disabled"{/if}/>
</fieldset>
{/if}
<fieldset>
<legend>{translate key="plugins.generic.sushiLite.settingsForm.journalSettings"}</legend>
<div>
		{fieldLabel name="disallow_anonymous" key="plugins.generic.sushiLite.settingsForm.disallowAnonymous"}</label><input type="checkbox" name="disallow_anonymous"{if $disallow_anonymous} checked="checked"{/if}{if $editing} disabled="disabled"{/if} />
</div>
<table width="100%">
	<tr>
	<th>{translate key="plugins.generic.sushiLite.settingsForm.requestorName"}</th>
	<th>{translate key="plugins.generic.sushiLite.settingsForm.requestorId"}</th>
	<th>{translate key="plugins.generic.sushiLite.settingsForm.requestorIdRequired"}</th>
	<th>{translate key="plugins.generic.sushiLite.settingsForm.requestorApiKey"}</th>
	<th>{translate key="plugins.generic.sushiLite.settingsForm.actions"}</th>
	</tr>
	{if $requestors}
	{foreach name=requestor from=$requestors key=ridx item=requestor}
	<tr>
	<td>{$requestor.name|escape}</td>
	<td>{$requestor.id|escape}</td>
	<td>{$requestor.idRequired|escape}</td>
	<td>{$requestor.apiKey|escape}</td>
	<td><input type="button" onClick="document.forms['sushiLite-delete-{$ridx|escape}'].submit();" value="{translate key='plugins.generic.sushiLite.settingsForm.delete'}"{if $editing} disabled="disabled"{/if} /><input type="button" onClick="document.forms['sushiLite-edit-{$ridx|escape}'].submit();" value="{translate key='plugins.generic.sushiLite.settingsForm.edit'}"{if $editing} disabled="disabled"{/if} /></td>
	</tr>
	{/foreach}
	{/if}
</table>
<input type="submit" name="new" class="button" value="{translate key='plugins.generic.sushiLite.settingsForm.new'}"{if $editing} disabled="disabled"{/if}/>
</fieldset>
<input type="submit" name="save{if $editing}Edit{/if}" class="button defaultButton" value="{translate key="common.save"}"/><input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.go(-1);"/>
</form>
{if $requestors}
{foreach name=requestor from=$requestors key=ridx item=requestor}
<form name="sushiLite-delete-{$ridx|escape}" id="sushiLite-delete-{$ridx|escape}" action="{plugin_url path="settings/delete"}" method="post" />
<input type="hidden" name="requestorId" value="{$ridx|escape}" />
</form>
<form name="sushiLite-edit-{$ridx|escape}" id="sushiLite-edit-{$ridx|escape}" action="{plugin_url path="settings/edit"}" method="get" />
<input type="hidden" name="requestorId" value="{$ridx|escape}" />
</form>
{/foreach}
{/if}
{if $siteRequestors}
{foreach name=requestor from=$siteRequestors key=ridx item=requestor}
<form name="sushiLite-delete-{$ridx|escape}" id="sushiLite-delete-{$ridx|escape}" action="{plugin_url path="settings/delete"}" method="post" />
<input type="hidden" name="requestorId" value="{$ridx|escape}" />
</form>
<form name="sushiLite-edit-{$ridx|escape}" id="sushiLite-edit-{$ridx|escape}" action="{plugin_url path="settings/edit"}" method="get" />
<input type="hidden" name="requestorId" value="{$ridx|escape}" />
</form>
{/foreach}
{/if}
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</div>
{include file="common/footer.tpl"}
