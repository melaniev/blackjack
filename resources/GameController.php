<?php




require_once("/resources/config.php");
require_once("/resources/GameManagement.php");
require_once("/resources/Game.php");
require_once("/resources/Klogger.php");

$log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);


function createANewGame($db=NULL){

        global $gmanager;
        $gmanager->requestNewGame();

        $log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
        $log->logInfo('createANewGame in GameController ');

        $_SESSION['gametype'] = "crnt";
}

function joinGame($db=NULL){

       global $gmanager;
        $gmanager->joinExistingGame();

        $log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
        $log->logInfo('joinGame in GameController ');

        $_SESSION['gametype'] = "crnt";

}

function hit(){

    $player = session_id();
    $game = new BlackjackGame();
    $game->hit($player);

    $log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
    $log->logInfo('a Hit in GameController ');

}
function stay(){

    global $gmanager;
    $gmanager->makeAStay($_SESSION['GameID']);

    $log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
    $log->logInfo('a Stay in GameController ');

}




    // public function makeAHit($thisPlayersGameID){

    //     $this->log->logInfo('makeaHit called in GameManagement');
    //     $this->log->logInfo('Looking for a Game from a player with id: ', $thisPlayersGameID);

    //     //find a game by id
    //     //pass that move and player to that game
    //     foreach ($this->game_holder as $game) {

    //             $this->log->logInfo('checking game with gameid: ', $game->_gid);

    //             if ($game->_gid == $thisPlayersGameID){

    //                 $this->log->logInfo('game found in Game Management with game id', $thisPlayersGameID);
                    
    //                 $game->hit($_SESSION['Username']);

    //                 $this->log->logInfo('sending a hit request to Game with username: ', $_SESSION['Username']);

    //             }  
    //     }
        
    // }
    // public function makeAStay($thisPlayersGameID){

    //     //find a game by id
    //     //pass that move and player to that game
    //     foreach ($this->game_holder as $game) {

    //             if ($game->_gid == $thisPlayersGameID){
                    
    //                 echo "<p>A Game found for that Move</p>";
    //                 $game->stay($_SESSION['Username']);
    //             }  
    //     }
 
    // }


?>