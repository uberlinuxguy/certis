<div class="permsForm" style="width: 350px"> 
	<form action="<?=API::printUrl("perms","display")?>">
	Select a user to set perms for: 
	<input type="submit" value="Go" style="float: right;" />
	<select name='user'>
<?php foreach ($data['users'] as $user) :?>
		<option value="<?=$user?>"><?=$user?></option>
<?php pendforeach;?>
	</select> 

	</form>
</div>
