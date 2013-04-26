<?php

	$pageTitle = 'Login';
	$pageId = 'home-page';
	
	// load config
	//require_once("/resources/config.php");
	require_once(TEMPLATES_PATH . "/header.php");
?>

		<div id='home-page-login-container'>
			<form action="/lobby.php" method="post">

				<div id='pretty-login'>
					<span class='login-label'>username: </span><input type='text' name='username' class='login-input' id='login-username'/><br />
					<span class='login-label'>password: </span><input type='text' name='password' class='login-input' id='login-password'/><br />
					<a href='signup.php'><span style='font-size:140%;' class='login-label'>No Account? Sign Up! &#62; </span></a>

				</div>

					<div style='margin-top:10px;'>
						<input type='submit' value="Log In" name='home-log-in' class='button' style='display:block;float:right;'/>
					</div>
			</form>
		</div>

<?php
	require_once(TEMPLATES_PATH . "/footer.php");
?>