<div class="permsForm" style="width: 350px"> 
	<form action="<?=API::printUrl("perms","display")?>">
	Select a user to set perms for: 
	<select name='uid' style="float: none;">
<?php foreach ($data['users'] as $uid => $user) :?>
		<option value="<?=$uid?>"><?=$user?></option>
<?php endforeach;?>
	</select> 
	<input type="submit" value="Go" style="float: none;" />

	</form>
</div>
