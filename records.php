<?php

	$pageTitle = 'Past Games';
	$pageId = 'past';
	
	// load config
	require_once("/resources/config.php");
	require_once("/resources/UserController.php");
	require_once("/resources/GameController.php");
	require_once(TEMPLATES_PATH . "/header.php");

?>

<?php
	
	$theseRecords;

        if($_SESSION['LoggedIn'] != 1){
        	//try again ?
        	header( 'Location:'.SITE_URL.'/login.php' );
        }
        if($_GET['user']){
        		$userToLookFor = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_ENCODED);

        		$userToLookFor = urldecode( $userToLookFor );

        		$theseRecords = getRecord2($db, $userToLookFor);
		}


?>
		<div class='lobby-container' id='welcome'>

			<span>Logged in as, <?php echo $_SESSION['Username']; ?></span><span id='log-out'><a href='lobby.php' style='color:white;'>Lobby</a> | <a href='logout.php' style='color:white;' >Log out</a></span>
		</div>	

		<div class='lobby-container' id='pastgames'>
			<h2><?php echo $userToLookFor ?>'s Record</h2>

				<h3>Wins</h3>
				<p><?php echo $theseRecords[0]; ?></p>
				<h3>Losses</h3>
				<p><?php echo $theseRecords[1]; ?></p>
				<h3>Draws</h3>
				<p><?php echo $theseRecords[2]; ?></p>



		</div>		




<?php
	require_once(TEMPLATES_PATH . "/footer.php");
?>
