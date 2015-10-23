{**
 * plugins/generic/sushiLite/templates/settingsForm.tpl
 *
 * Copyright (c) 2014 University of Pittsburgh
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * SUSHI-Lite plugin settings
 *
 *}
<script type="text/javascript">
{literal}
/**
 * Fast UUID generator, RFC4122 version 4 compliant.
 * @author Jeff Ward (jcward.com).
 * @license MIT license
 * @link http://stackoverflow.com/questions/105034/how-to-create-a-guid-uuid-in-javascript/21963136#21963136
 **/
var UUID = (function() {
	var self = {};
	var lut = []; for (var i=0; i<256; i++) { lut[i] = (i<16?'0':'')+(i).toString(16); };
	self.generate = function() {
		var d0 = Math.random()*0xffffffff|0;
		var d1 = Math.random()*0xffffffff|0;
		var d2 = Math.random()*0xffffffff|0;
		var d3 = Math.random()*0xffffffff|0;
		return lut[d0&0xff]+lut[d0>>8&0xff]+lut[d0>>16&0xff]+lut[d0>>24&0xff]+'-'+
		lut[d1&0xff]+lut[d1>>8&0xff]+'-'+lut[d1>>16&0x0f|0x40]+lut[d1>>24&0xff]+'-'+
		lut[d2&0x3f|0x80]+lut[d2>>8&0xff]+'-'+lut[d2>>16&0xff]+lut[d2>>24&0xff]+
		lut[d3&0xff]+lut[d3>>8&0xff]+lut[d3>>16&0xff]+lut[d3>>24&0xff];
	};
	return self;
})();
{/literal}
</script>
<div class="sushilite-requestor">
<fieldset>
<input type="hidden" name="stored_requestor" value="{$stored_requestor|escape}" />
<input type="hidden" name="new_context" value="{$new_context|escape}" />
<div>
	{fieldLabel required="true" name="requestor_name" key="plugins.generic.sushiLite.settingsForm.requestorName"}<input type="text" id="requestor_name" name="requestor_name" size="40" value="{$requestor_name|escape}" />
</div>
<div>
	{fieldLabel required="true" name="requestor_id" key="plugins.generic.sushiLite.settingsForm.requestorId"}<input type="text" id="requestor_id" name="requestor_id" size="36" maxlength="36" value="{$requestor_id|escape}" /><input type="button" onclick="document.getElementById('requestor_id').value = UUID.generate()" value="{translate key='plugins.generic.sushiLite.settingsForm.generateUUID'}">
</div>
<div>
	{fieldLabel required="true" key="plugins.generic.sushiLite.settingsForm.requestorIdReq"}
	<input type="radio" id="requestor_idRequired-true" name="requestor_idRequired-true" value="1" {if $requestor_idReq}checked="checked" {/if}/>{fieldLabel name="requestor_idRequired-true" key="plugins.generic.sushiLite.settingsForm.requestorIdReqTrue"}
	<input type="radio" id="requestor_idRequired-false" name="requestor_idRequired-false" value="0" {if !$requestor_idReq}checked="checked" {/if}/>{fieldLabel name="requestor_idRequired-false" key="plugins.generic.sushiLite.settingsForm.requestorIdReqFalse"}
</div>
<div>
	{fieldLabel name="requestor_apiKey" key="plugins.generic.sushiLite.settingsForm.requestorApiKey"}<input type="text" id="requestor_apiKey" name="requestor_apiKey" size="36" maxlength="36" value="{$requestor_key|escape}" /><input type="button" onclick="document.getElementById('requestor_apiKey').value = UUID.generate()" value="{translate key='plugins.generic.sushiLite.settingsForm.generateUUID'}">
	<div class="instruct">{translate key="plugins.generic.sushiLite.settingsForm.requestorApiKeyInstructions"}</div>
</div>
</fieldset>
</div>
