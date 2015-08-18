
	<h2>Login</h2>
	<div class="errors">

	</div>
  	<form action="<?=API::printUrl($view->module, $view->action)?>" method="post">
  	<div class="loginFrm">
  		<label for="uname">Username: </label> <input type="text" name="uname" value="" size="30" maxlength="40"/><br />
  		<label for="password">Password: </label><input type="password" name="password" size="30" maxlength="40"/><br />
  		<input class="formButton" type="submit" name="login" value="Login" />
  	</div>
  	</form>



