<h2>Editing Preferences for <?=$view->getInfoValue('fname')?> <?=$view->getInfoValue('lname')?></h2>
<br /><br />
<div id="prefsForm" style="width:300px">
<strong>General:</strong>
<hr />
<form action="<?=API::printUrl($view->module, 'edit');?>" method="post">
<input type="hidden" name="uid" value="<?=$view->getInfoValue('uid')?>" />
<div><label>First Name</label><span class="frmTxtInput"><input type="text" name="fname" value="<?=$view->getInfoValue('fname')?>" /></span></div>
<div><label>Last Name</label><span class="frmTxtInput"><input type="text" name="lname" value="<?=$view->getInfoValue('lname')?>" /></span></div>
<?=API::callHooks("prefs", "index", "view", $data);?>
<div><span class="frmButtons"><input class="formButton" type="submit" name="save" value="Save" /><input class="formButton" type="submit" name="cancel" id="btn_Cancel" value="Cancel" /></span></div>
</form>
</div>
