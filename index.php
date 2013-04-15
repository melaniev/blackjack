<?php

	$pageTitle = 'BlackJack';
	$id = 'home-page';
	
	// load config
	require_once("/resources/config.php");
	require_once(TEMPLATES_PATH . "/header.php");
?>


<?php

	if(isset($_SESSION['LoggedIn'])){

		if($_SESSION['LoggedIn'] != 1){

			header( 'Location:'.SITE_URL.'/login.php' );

		}else{

			header( 'Location:'.SITE_URL.'/lobby.php' );
		}
	}
	else{

		header( 'Location:'.SITE_URL.'/lobby.php' );
	}



?>


<?php
	require_once(TEMPLATES_PATH . "/footer.php");
?>