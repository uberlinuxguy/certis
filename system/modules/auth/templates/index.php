
<a class="lt_box" href="<?=API::printUrl($view->module, "display")?>">Add New User</a><br /><br />
<? $view->printPages(); ?>
<? if(is_array($data['info'])) : ?>
	<? foreach($data['info'] as $user) : ?>
	<a class="lt_box" href="<?=API::printUrl($view->module, 'display', $user->uid)?>"><?=$user->name?></a> &nbsp;
	<? if($user->name != "admin") : ?>
	<a class="lt_box" href="<?=API::printUrl($view->module, 'delete_confirm', $user->uid)?>">X</a>
	<? endif; ?><br />

	<? endforeach; ?>
<? elseif(is_object($data['info'])) : ?>
	<a class="lt_box" href="<?=API::printUrl($view->module, 'display', $data['info']->uid)?>"><?=$data['info']->name?></a> &nbsp;
	<? if($user->name != "admin") : ?>
	<a class="lt_box" href="<?=API::printUrl($view->module, 'delete_confirm', $user->uid)?>">X</a>
	<? endif; ?><br />
<? endif; ?>

<br /><br /><? $view->printPages(); ?>
