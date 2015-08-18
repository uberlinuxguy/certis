<div align="center">
<!-- 
<? print_r($data); ?>
-->
Are you sure you<br /> want to delete user '<?=$data->name?>'?
<form action="<?=API::printURL($view->module,'delete',$data->uid)?>" method="get">

<input type="submit" name="delete" value="Yes" /> <input type="submit" name="cancel" value="No" />

</form>
</div>
