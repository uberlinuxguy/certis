
<h1>Editing <?=$view->getInfoValue('name'); 
	$full_name = $view->getInfoValue('gecos');
	if(!empty($full_name)) {
 		echo " - " . $full_name; 
 	}?></h1>

<form action="<?=API::printURL("prefs", $data['action'],$view->getInfoValue('uid'))?>" method="post">
	<input type="hidden" name="set_perms" value="1" />
	<input type="hidden" name="uid" value="<?=$view->getInfoValue('uid')?>" />
	Permissions: <br /><br /> 
	<select name="perms[]" multiple>

<? foreach  ($data['perms'] as $perm) :?>
		
		<? if($view->CertisInst->Perms->checkPerm($view->getInfoValue('uid'), $perm->name)) {
				$selected='SELECTED';
			} else {
				$selected="";
			}
		?>
		<option value="<?=$perm->id?>" <?=$selected?>><?=$perm->descr?></option>
<? endforeach;?>

	</select>
	
	<br /><br />
	<input type="submit" value="Save" />
</form>