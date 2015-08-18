

  <form action="<?=API::printUrl($view->module, $data['action'])?>" method="post">
	<div class="hostPrimaryInfo">
  		<input type="hidden" name="id" value="<?=$view->getInfoValue('id')?>" />

	  	<div><label>Host: </label><input type="text" name="name" value="<?=$view->getInfoValue("name");?>" /><br /></div>
  		<div><label>Alias: </label><input type="text" name="alias" value="<?=$view->getInfoValue("alias");?>" /><br /></div>
  		<div><label>Primary<br />MAC Address: </label><input style="margin-top: 12px;" type="text" name="primary_mac" value="<?=$view->getInfoValue("primary_mac");?>" /><br /></div>
  	</div>
  	<br />
  	
  	<!--  BEGIN HOSTS HOOKS -->
  	<div class="hostHookInfo">
  		<? API::callHooks($view->module, $view->action, 'display', $view->getInfoValue('id'));?>
  	</div>
	<!--  END HOSTS HOOKS -->
	
	<br />
	<div class="formButtons">
		<input class="formButton" type="submit" value="Save" /> <input class="formButton" id="btn_Cancel"  type="submit" name="cancel" value="Cancel" />
	</div>

  </form>

