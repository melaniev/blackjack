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
	echo "Session ID: ". $a;

	echo "Game type: ".$_SESSION['gametype']."<br />";
	
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
					<h2 class='username'><?php echo $_SESSION['Username']; ?></h2>
					<div class='card-total'><h3>Total:</h3><span class='current-total'>##</span></div>
					<div class='card'></div>
					<div class='card'></div>
					<div class='card'></div>
					<div class='card'></div>
					<div class='card'></div>
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
			alert('stay!');

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

// 	(function poll() {
//     setTimeout(function () {

//         $.ajax({
//             type: 'POST',
//             dataType: 'html',
//             url: '<?php echo SITE_URL; ?>/actionlog.php',
//             success: function (data) {

//             	//$('#side-bar #stats #status-feed').append('Info from server recieved<br />');
//             	$('#side-bar #stats #status-feed').append(data + '<br />');

//             },
//             complete: poll
//         });
//     }, 2000);
// })();


	(function updateboard() {
    setTimeout(function () {

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo SITE_URL; ?>/plays<?php echo $_SESSION['GameID'] ?>.php',
            success: function (data) {

            	//updateBoard(JSON.stringify(data));
            	updateBoard(data);

            },
            complete: updateboard
        });
    }, 2000);
})();

function updateBoard(gamedata){

// 	var obj = jQuery.parseJSON('{"name":"John"}');
// alert( obj.name === "John" );

	var gameinfo = gamedata;
	var spot;

	    $.each(gameinfo, function(index, element) {

	    	

	        if (element.name = 'dealer') {

	        	$('#player0').children('.card').css({'background-color': 'yellow', 'border': '5px solid red'});
	        	spot = 0;

	        	

	        }
	        else if (element.name = '<?php echo $_SESSION['Username']; ?>') {

	        	$('#player5').children('.card').css({'background-color': 'blue', 'border': '5px solid red'});
	        	spot = 5;


	        }else{

	        }

	        var cardstr = element.cards;
	        var cards = cardstr.split(',');
	        for (var i = 0; i < cards.length; i++) {

	        	var cleanCard = cards[i].replace('[','');
	        	var cleanCard = cleanCard.replace(']','');
	        	alert(cleanCard);

	        };
	    });

}

</script>