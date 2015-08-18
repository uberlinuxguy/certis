
<br />

  <form action="<?=API::printUrl($view->module, $data['action'])?>" method="post">

  	<input type="hidden" name="id" value="<?=$view->req_id?>" />
  	<input type="hidden" name="action" value="<?=$view->data['action']?>" />
<? if($view->data['action'] == 'edit') : ?>
  	Username: <div class="frm_value"><?=$view->getInfoValue("name");?><br /></div>
<? else: ?>
	<div> <label>Username: </label><input type="text" name="uname" value="<?=$view->getInfoValue("name");?>" /><br /></div>
<? endif; ?>
  	<div><label>Password: </label><input type="password" name="password" value="" /><br /></div>
  	<div><label>Verify Password: </label><input type="password" name="password2" value="" /><br /></div>

  	<? API::callHooks($view->module, 'display', 'view', $data);?>
  	<br />
  	<input class="formButton" type="submit" value="Save" /> <input class="formButton" type="submit" name="cancel" value="Cancel" />

  </form>

