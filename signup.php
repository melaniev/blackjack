<?php

	$pageTitle = 'Sign Up';
	$pageId = 'signup-page';
	
	// load config
	require_once("/resources/config.php");
	require_once(TEMPLATES_PATH . "/header.php");

?>

		<div id='home-page-login-container'>
			<form action="/lobby.php" method="post" name='blackjack-signup'>

				<div id='pretty-login'>
					<span class='login-label'>username: </span><input type='text' name='signup-username' class='login-input' id='login-username'/><br />
					<span class='login-label'>password: </span><input type='text' name='signup-password' class='login-input' id='login-password'/><br />
					<span class='login-label'>password, again: </span><input type='text' name='signup-password-2' class='login-input' id='login-password-2'/><br />
					<a href='login.php'><span style='font-size:140%;' class='login-label'>Already have an account? Login &#62; </span></a>
				</div>

					<div style='margin-top:10px;'>
						<input type='submit' value="Sign up" name='signup-new-user' class='button' style='display:block;float:right;'/>
					</div>
			</form>
		</div>

<?php
	require_once(TEMPLATES_PATH . "/footer.php");
?>