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

function updateBoard($blah){

    echo '<br />Updating Board</br />';
    $fp = fopen("plays.php", "a");
    fwrite($fp, "board updated again");
    fwrite($fp, $blah);
    fclose($fp);

}
function hit(){

    global $gmanager;
    $gmanager->makeAHit('hit', $_SESSION['GameID']);
    updateBoard('a hit in Game Controller');

    $log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
    $log->logInfo('a Hit in GameController ');

}
function stay(){

    global $gmanager;
    $gmanager->makeAStay('stay', $_SESSION['GameID']);
    updateBoard('a stay in Game Controller');

    $log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
    $log->logInfo('a Stay in GameController ');

}




?>