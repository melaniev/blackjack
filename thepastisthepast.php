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

        if($_SESSION['LoggedIn'] != 1){
        	//try again ?
        	header( 'Location:'.SITE_URL.'/login.php' );
        }


?>
		<div class='lobby-container' id='welcome'>

			<span>Logged in as, <?php echo $_SESSION['Username']; ?></span><a href='lobby.php'>Lobby</a> | <a href='logout.php' id='log-out'>Log out</a>  <!-- SANITIZE THIS DATAAA!!! -->
		</div>	

		<div class='lobby-container' id='pastgames'>
			<h2>Game History</h2>

			<?php
			    $inactive = 0;
        
		        $sql = "SELECT gameID
		                FROM games
		                WHERE gamestate=:inactive";

		        if($stmt = $db->prepare($sql)) {
		            $stmt->bindParam(":inactive", $inactive, PDO::PARAM_BOOL);
		            $stmt->execute();
		            $row = $stmt->fetchAll();

		            $stmt->closeCursor();

		            foreach ($row as $aGame) {

		            	$thisGameID = $aGame['gameID'];

		            	$sql = "SELECT *
		                FROM moves
		                WHERE gameID=:gID";

				        if($stmt = $db->prepare($sql)) {
				            $stmt->bindParam(":gID", $thisGameID, PDO::PARAM_INT);
				            $stmt->execute();
				            $moveStuff = $stmt->fetchAll();

				            $stmt->closeCursor();

				            print_r($moveStuff);
		            	}

		            }
		        }

		    ?>

		</div>		




<?php
	require_once(TEMPLATES_PATH . "/footer.php");
?>
