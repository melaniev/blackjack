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

			<span>Logged in as, <?php echo $_SESSION['Username']; ?></span><span id='log-out'><a href='lobby.php' style='color:white;'>Lobby</a> | <a href='logout.php' style='color:white;' >Log out</a></span>
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
		            	$moveStuff;

		            	$sql = "SELECT *
		                FROM moves
		                WHERE gameID=:gID";

				        if($stmt = $db->prepare($sql)) {
				            $stmt->bindParam(":gID", $thisGameID, PDO::PARAM_INT);
				            $stmt->execute();
				            $moveStuff = $stmt->fetchAll();

				            $stmt->closeCursor();

		            	}

		            	echo "<h3>Game ".$aGame['gameID']."</h3>";
		            	echo '<h4>Total Moves: '. count($moveStuff);

		            	foreach ($moveStuff as $move) {

		            		$username;

					        $sql = "SELECT username AS uname
					                FROM users
					                WHERE userID=:uID";

					        if($stmt = $db->prepare($sql)) {
					            $stmt->bindParam(':uID', $move['userID'] , PDO::PARAM_STR);
					            $stmt->execute();
					            $id = $stmt->fetch();
					            $username = $id['uname'];

					            $stmt->closeCursor();
					        }
					        echo "<div id='a-move-list'>";
		            		echo "<p><span>Hand: ".$move['handID']."</span>";
		            		echo "<span>User: ".$username."</span>";
		            		echo "<span>Play: ".$move['playtype']."</span>";

		            		if ($move['card'] != NULL) {
		            			echo "<span>Card: ".$move['card']."</span>";
		            		}

		            		echo "<span>Count Total: ".$move['newTotal']."</span></p>";
		            		echo "</div>";
		            	}

		            	
		            	


		            }
		        }

		    ?>

		</div>		




<?php
	require_once(TEMPLATES_PATH . "/footer.php");
?>
