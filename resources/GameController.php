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
function stay($g){

    $player = session_id();

    $log = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
    $log->logInfo('a Stay in GameController with gameid', $g);
    $log->logInfo('a Stay in GameController from player', $player);

    $game = new BlackjackGame(NULL, $g);
    $game->stay($player, $_SESSION['GameID']);


}

function getRecord($db){

        $us_id;
        $u = session_id();

        //Get the Game ID
        $sql = "SELECT userID AS uID
                FROM users
                WHERE sessID=:sID";

        if($stmt = $db->prepare($sql)) {
            $stmt->bindParam(':sID', $u , PDO::PARAM_STR);
            $stmt->execute();
            $id = $stmt->fetch();
            $us_id = $id['uID'];

            $stmt->closeCursor();
        }

        $sql = "SELECT *
                FROM records
                WHERE userID=:uID";

        if($stmt = $db->prepare($sql)) {
            $stmt->bindParam(':uID', $us_id , PDO::PARAM_STR);
            $stmt->execute();
            $record = $stmt->fetch();

            $wins = $record['wins'];
            $losses = $record['losses'];
            $draws = $record['draws'];

            $stmt->closeCursor();

            return array($wins, $losses, $draws);
        }
}

function getRecord2($db, $un){

    $us_id;

       $sql = "SELECT userID
                FROM users
                WHERE username=:user
                LIMIT 1";

           if($stmt = $db->prepare($sql)) {
            $stmt->bindParam(':user',$un, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch();

            $us_id = $row['userID'];

            $stmt->closeCursor();
        }

        $sql = "SELECT *
                FROM records
                WHERE userID=:uID";

        if($stmt = $db->prepare($sql)) {
            $stmt->bindParam(':uID', $us_id , PDO::PARAM_STR);
            $stmt->execute();
            $record = $stmt->fetch();

            $wins = $record['wins'];
            $losses = $record['losses'];
            $draws = $record['draws'];

            $stmt->closeCursor();

            return array($wins, $losses, $draws);
        }
}

function getOthersRecord($db){

        $otherUsers = array();

        $sql = "SELECT username
                FROM users";

        if($stmt = $db->prepare($sql)) {
            $stmt->execute();
            $record = $stmt->fetchAll();

            foreach ($record as $anotherUser) {
                
                array_push($otherUsers, $anotherUser['username']);
            }

            $stmt->closeCursor();

            return $otherUsers;
        }
}

?>