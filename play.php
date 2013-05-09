<?php

	$pageTitle = '';
	$pageId = 'table-top-page';
	
	// load config
	require_once("/resources/config.php");
	require_once("/resources/UserController.php");
	require_once("/resources/GameController.php");
	require_once("/resources/Game.php");
	require_once(TEMPLATES_PATH . "/header.php");
?>

<?php

//check if logged in, if not, go to login page
	if($_SESSION['LoggedIn'] != 1){

		header( 'Location:'.SITE_URL.'/login.php' );

	}

	$a = session_id();
	
	if($_GET){

		$gametype = filter_input(INPUT_GET, 'game', FILTER_SANITIZE_STRING);

		if($gametype == 'newgame'){

			$_SESSION['gametype'] = "new";
			header( 'Location:'.SITE_URL.'/play.php' );
			
		}
		if($gametype == 'join'){

			$_SESSION['gametype'] = "join";
			header( 'Location:'.SITE_URL.'/play.php' );
			joinGame();
		}		
		if($gametype == 'current'){
			$_SESSION['gametype'] = "crnt";
			header( 'Location:'.SITE_URL.'/play.php' );
		}		
	}

	if(isset($_SESSION['gametype'])){

		if ($_SESSION['gametype'] == "new") {
			createANewGame();
		}
		elseif ($_SESSION['gametype'] == "join") {
			joinGame();
		}
		elseif ($_SESSION['gametype'] == "crnt") {

			if (isset($_SESSION['GameID'])) {
				
				$game = new BlackjackGame(NULL, $_SESSION['GameID']);

			}else{
				//hmmm, can check in the db and see if they have a game for you in there
			}
		}else{
			header( 'Location:'.SITE_URL.'/lobby.php' );
		}
	}
?>

		<div id='logout-lobby-controls'>
			<a href='logout.php' >log out |</a>
			<a href='lobby.php'>lobby</a>
		</div>

		<div id='side-bar'>
			<div id='chat' class='text-panels'>
				<h2>Chat</h2>

				<p>Game: <?php echo $_SESSION['GameID'] ?></p>
			</div>
			<div id='stats' class='text-panels'>
				<h2>Stats or Whatever</h2>
				<div id='status-feed'>

				</div>

			</div>


		</div>
		<div id='table-top'>
			<div class='a_player' id='player0'>
				<h2 class='username'>Dealer</h2>
				<div class='card-total'><h3>Total:</h3><span class='current-total'>##</span></div>
					<div class='card 3'></div>
					<div class='card 1'></div>
					<div class='card 2'></div>
					<div class='card 4'></div>
					<div class='card 5'></div>
			</div>
			<div id='other-players'>

				<!-- Player 1 -->
				<div class='a_player not-dealer' id='player1'>
					<h2 class='username'>Player 1</h2>
					<div class='card-total'><h3>Total:</h3><span class='current-total'>##</span></div>
					<div class='card 3'></div>
					<div class='card 1'></div>
					<div class='card 2'></div>
					<div class='card 4'></div>
				</div>

				<!-- Player 2 -->
				<div class='a_player  not-dealer' id='player2'>
					<h2 class='username'>Player 2</h2>
					<div class='card-total'><h3>Total:</h3><span class='current-total'>##</span></div>
					<div class='card 3'></div>
					<div class='card 1'></div>
					<div class='card 2'></div>
					<div class='card 4'></div>
				</div>

				<!-- Player 3 -->
				<div class='a_player  not-dealer' id='player3'>
					<h2 class='username'>Player 3</h2>
					<div class='card-total'><h3>Total:</h3><span class='current-total'>##</span></div>
					<div class='card 3'></div>
					<div class='card 1'></div>
					<div class='card 2'></div>
					<div class='card 4'></div>
				</div>

				<!-- Player 4 -->
				<div class='a_player  not-dealer' id='player4'>
					<h2 class='username'>Player 4</h2>
					<div class='card-total'><h3>Total:</h3><span class='current-total'>##</span></div>
					<div class='card 3'></div>
					<div class='card 1'></div>
					<div class='card 2'></div>
					<div class='card 4'></div>
				</div>
			</div>

				<!-- Player 5 -->					
				<div class='a_player  not-dealer' id='player5'>
					<h2 class='username'><?php echo $_SESSION['Username']; ?></h2>
					<div class='card-total'><h3>Total:</h3><span class='current-total'>##</span></div>
					<div class='card 3'></div>
					<div class='card 1'></div>
					<div class='card 2'></div>
					<div class='card 4'></div>
					<div class='card 5'></div>
					<form action='' method='post'>
						<input type='submit' id='staybutton' class='button playbutton' style='width:60px;' value='Stay' name='stay'/>
					</form>
					<form action='' method='post'>
						<input type='submit' id='hitbutton' class='button' style='width:60px;' value='Hit' name='hit' />
						<input type='text' />
					</form>
				</div>

		</div>

<?php
	require_once(TEMPLATES_PATH . "/footer.php");
?>

<script type="text/javascript">

	$(document).ready(function(){

		$('#hitbutton').click(function(event){
			event.preventDefault();
			alert('hit!');

			$.ajax({
	            type: 'POST',
	            data: { name: "Hit", bet: "$50" },
	            url: '<?php echo SITE_URL; ?>/move.php',
	            success: function (data) {

	            	alert('yes!');

	            },
	            
	        });
		});

		$('#staybutton').click(function(event){
			event.preventDefault();

			$.ajax({
	            type: 'POST',
	            data: { name: "Stay", bet: "$50" },
	            url: '<?php echo SITE_URL; ?>/move.php',
	            success: function (data) {

	            	alert('yes!');

	            },
	            
	        });
		});

	});

	(function updateboard() {
    setTimeout(function () {

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo SITE_URL; ?>/plays<?php echo $_SESSION['GameID'] ?>.php',
            success: function (data) {

            	updateBoard(data);

            },
            complete: updateboard
        });
    }, 2000);
})();

function updateBoard(gamedata){

	var gameinfo = gamedata;
	var total;
	var cardstr;
	var cards;

	   	for (var i = 0; i < gameinfo.length; i++) {

	    	var player = gameinfo[i];

	    	 total = player.count;
	    	 cardstr = player.cards;
    		 cards = cardstr.split(',');

	        if (player.name == 'dealer') {
	        	
	        	showCards(0, total, cards);

	        }
	        if (player.name == '<?php echo $_SESSION['Username']; ?>') {

	        	showCards(5, total, cards);


	        }
		}

}

function showCards(spot, cardCount, mycards){

	var spot = spot;
	var cardSpace = '#player'+spot;

    //Update total
    
    $(cardSpace + ' .current-total').text(cardCount);


    for (var i = 0; i < mycards.length; i++) {


    	var cleanCard = mycards[i].replace('[','');
    	var cleanCard = cleanCard.replace(']','');
    	var cleanCard = cleanCard.replace('"','');
    	var cleanCard = cleanCard.replace('"','');
    	var j = i+1;

    	var cardurl = 'url("/images/content/cards/'+cleanCard+'.png")';
    	
    	$(cardSpace).children('.card.'+j).css({'background-image': cardurl, 'border': '2px solid red'});

	};
}

</script>