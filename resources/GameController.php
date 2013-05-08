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

function hit($g){

    $player = session_id();

    $log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
    $log->logInfo('a Hit in GameController with gameid', $g);
    $log->logInfo('a Hit in GameController from player', $player);

    $game = new BlackjackGame(NULL, $g);
    $game->hit($player, $_SESSION['GameID']);



}
function stay(){

    global $gmanager;
    $gmanager->makeAStay($_SESSION['GameID']);

    $log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
    $log->logInfo('a Stay in GameController ');

}

?>