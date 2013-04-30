<?php




require_once("/resources/config.php");
require_once("/resources/GameManagement.php");
require_once("/resources/Klogger.php");

$log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);


function createANewGame($db=NULL){

       global $gmanager;
        $gmanager->requestNewGame();

        $log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
        $log->logInfo('createANewGame in GameController ');

}

function joinGame($db=NULL){

       global $gmanager;
        $gmanager->joinExistingGame();

        $log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
        $log->logInfo('joinGame in GameController ');

}

function hit(){

    global $gmanager;
    $gmanager->makeAHit($_SESSION['GameID']);

    $log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
    $log->logInfo('a Hit in GameController ');

}
function stay(){

    global $gmanager;
    $gmanager->makeAStay($_SESSION['GameID']);

    $log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
    $log->logInfo('a Stay in GameController ');

}




?>