
<a class="lt_box" href="<?=API::printUrl($view->module, "display")?>">Add New Host</a><br /><br />
<?php $view->printPages(); ?>
<?php foreach($data['info'] as $host) : ?>
<a class="lt_box" href="<?=API::printUrl($view->module, 'display', $host->id)?>"><?=$host->name?></a> &nbsp;
<a class="lt_box" href="<?=API::printUrl($view->module, 'delete_confirm', $host->id)?>">X</a><br />

<?php endforeach; ?>
<br /><br /><?php $view->printPages(); ?>
