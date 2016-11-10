
<a class="lt_box" href="<?=API::printUrl($view->module, "display")?>">Add New User</a><br /><br />
<?php $view->printPages(); ?>
<?php if(is_array($data['info'])) : ?>
	<?php foreach($data['info'] as $user) : ?>
	<a class="lt_box" href="<?=API::printUrl($view->module, 'display', $user->uid)?>"><?=$user->name?></a> &nbsp;
	<?php if($user->name != "admin") : ?>
	<a class="lt_box" href="<?=API::printUrl($view->module, 'delete_confirm', $user->uid)?>">X</a>
	<?php endif; ?><br />

	<?php endforeach; ?>
<?php elseif(is_object($data['info'])) : ?>
	<a class="lt_box" href="<?=API::printUrl($view->module, 'display', $data['info']->uid)?>"><?=$data['info']->name?></a> &nbsp;
	<?php if($user->name != "admin") : ?>
	<a class="lt_box" href="<?=API::printUrl($view->module, 'delete_confirm', $user->uid)?>">X</a>
	<?php endif; ?><br />
<?php endif; ?>

<br /><br /><?php $view->printPages(); ?>
