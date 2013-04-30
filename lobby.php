<?php

	$pageTitle = 'BlackJack';
	$pageId = 'lobby-page';
	
	// load config
	require_once("/resources/config.php");
	require_once("/resources/UserController.php");
	require_once("/resources/GameController.php");
	require_once(TEMPLATES_PATH . "/header.php");

?>

<?php
	//User Login
	if($_POST['home-log-in']){


		$thisUsername = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
		$thisPassword = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);


		$thisUserController = new BlackjackUser_Controller();
		$loggedIn = $thisUserController->loginAccount($db, $thisUsername, $thisPassword);

		$loggedIn != 1;

        if($loggedIn != 1){
        	//try again ?
        	header( 'Location:'.SITE_URL.'/login.php' );
        }
	}
	//New User Registration
	else if($_POST['signup-new-user']){


		/******** SECURITY FEATURE ********************/
		//using php filter_input function, allowing only strings

		$newUsername = filter_input(INPUT_POST, 'signup-username', FILTER_SANITIZE_STRING);
		$first_password = filter_input(INPUT_POST, 'signup-password', FILTER_SANITIZE_STRING);
		$first_password_2 = filter_input(INPUT_POST, 'signup-password-2', FILTER_SANITIZE_STRING);

		
        $newUserController = new BlackjackUser_Controller();
        $accountCreateSuccess = $newUserController->createAccount($db, $newUsername, $first_password, $first_password_2);

        if($accountCreateSuccess != 1){
        	//try again ?
        	header( 'Location:'.SITE_URL.'/signup.php' );
        }
        
	}else{

		//confirmed that they are logged in or redirect them away from this page!!

		if($_SESSION['LoggedIn'] != 1){

			header( 'Location:'.SITE_URL.'/login.php' );

		}

	}
?>

		<!-- Lobby Section - Welcome -->
		<div class='lobby-container' id='welcome'>
			<span>Logged in as, <?php echo $_SESSION['Username']; ?></span><a href='logout.php' id='log-out'>Log out</a>  <!-- SANITIZE THIS DATAAA!!! -->
		</div>		
		<!-- Lobby Section - Game Buttons -->
		<div class='lobby-container' id='game-options'>
			<h2>Games</h2>

			<a href='play.php?game=newgame' class='button'>New Game</a>
			<a href='play.php?game=join' class='button'>Join Game</a>
			<a href='' class='button'>View Past Games</a>


		</div>

		<!-- Lobby Section - Recored-->
		<div class='lobby-container' id='my-record'>
			<h2>Records</h2>
			<h3>Wins</h3>
			<p>XX</p>
			<h3>Losses</h3>
			<p>XX</p>
			<h3>Draws</h3>
			<p>XX</p>
			<h3>$$</h3>
			<p>XX</p>
		</div>

<?php
	require_once(TEMPLATES_PATH . "/footer.php");
?>
