
<a class="lt_box" href="<?=API::printUrl($view->module, "display")?>">Add New Host</a><br /><br />
<? $view->printPages(); ?>
<? foreach($data['info'] as $host) : ?>
<a class="lt_box" href="<?=API::printUrl($view->module, 'display', $host->id)?>"><?=$host->name?></a> &nbsp;
<a class="lt_box" href="<?=API::printUrl($view->module, 'delete_confirm', $host->id)?>">X</a><br />

<? endforeach; ?>
<br /><br /><? $view->printPages(); ?>