<div align="center">
Are you sure you<br /> want to delete <?=$data->name?>?
<form action="<?=API::printURL($view->module,'delete',$data->id)?>" method="get">

<input type="submit" name="delete" value="Yes" /> <input type="submit" name="cancel" value="No" />

</form>
</div>
