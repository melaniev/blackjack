<?php

	$pageTitle = 'Play BlackJack';
	$pageId = 'table-top-page';
	
	// load config
	require_once("/resources/config.php");
	require_once("/resources/UserController.php");
	require_once("/resources/GameController.php");
	require_once(TEMPLATES_PATH . "/header.php");
?>

<?php
	if($_GET){

		$gametype = filter_input(INPUT_GET, 'game', FILTER_SANITIZE_STRING);

		if($gametype == 'newgame'){

			echo 'You want a new game!';
		}
		$newGameController = new BlackjackGame_Controller();
		$newGameController->createANewGame();
	}

?>


		<div id='logout-lobby-controls'>
			<a href='logout.php' >log out |</a>
			<a href='lobby.php'>lobby</a>
		</div>

		<div id='side-bar'>
			<div id='chat' class='text-panels'>
				<h2>Chat</h2>
			</div>
			<div id='stats' class='text-panels'>
				<h2>Stats or Whatever</h2>
			</div>


		</div>
		<div id='table-top'>
			<div class='a_player' id='dealer'>
				<h2 class='username'>Dealer</h2>
				<div class='card-total'><h3>Total:</h3><span class='current-total'>##</span></div>
				<div class='card'></div>
				<div class='card'></div>
				<div class='card'></div>
				<div class='card'></div>
				<div class='card'></div>
			</div>
			<div id='other-players'>

				<!-- Player 1 -->
				<div class='a_player not-dealer' id='player1'>
					<h2 class='username'>Player 1</h2>
					<div class='card-total'><h3>Total:</h3><span class='current-total'>##</span></div>
					<div class='card'></div>
					<div class='card'></div>
					<div class='card'></div>
					<div class='card'></div>
				</div>

				<!-- Player 2 -->
				<div class='a_player  not-dealer' id='player2'>
					<h2 class='username'>Player 2</h2>
					<div class='card-total'><h3>Total:</h3><span class='current-total'>##</span></div>
					<div class='card'></div>
					<div class='card'></div>
					<div class='card'></div>
					<div class='card'></div>
				</div>

				<!-- Player 3 -->
				<div class='a_player  not-dealer' id='player3'>
					<h2 class='username'>Player 3</h2>
					<div class='card-total'><h3>Total:</h3><span class='current-total'>##</span></div>
					<div class='card'></div>
					<div class='card'></div>
					<div class='card'></div>
					<div class='card'></div>
				</div>

				<!-- Player 4 -->
				<div class='a_player  not-dealer' id='player4'>
					<h2 class='username'>Player 4</h2>
					<div class='card-total'><h3>Total:</h3><span class='current-total'>##</span></div>
					<div class='card'></div>
					<div class='card'></div>
					<div class='card'></div>
					<div class='card'></div>
				</div>
			</div>

				<!-- Player 5 -->					
				<div class='a_player  not-dealer' id='player5'>
					<h2 class='username'>My Username</h2>
					<div class='card-total'><h3>Total:</h3><span class='current-total'>##</span></div>
					<div class='card'></div>
					<div class='card'></div>
					<div class='card'></div>
					<div class='card'></div>
					<div class='card'></div>
				</div>

		</div>

<?php
	require_once(TEMPLATES_PATH . "/footer.php");
?>
