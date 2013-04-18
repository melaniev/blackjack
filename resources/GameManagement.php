<?php


/* 

Game Manager Class

*/

require_once("/resources/Game.php");

class Game_Manager{

    private static $counter = 0;
    private static $game_holder = array(50);
    
    final public function __construct($db=NULL) {

        // if (self::$counter) {
        //     throw new Exception('Cannot be instantiated more than once');
        // }
        // self::$counter++;
        
        //Create database object if not already created
        if(is_object($db))
        {
            $this->_db = $db;
        }
        else
        {
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
            $this->_db = new PDO($dsn, DB_USER, DB_PASS);
        }

        //Create action log file
        $fp = fopen("actionlog.php", "w");
        fwrite($fp, "Game Management Object Created");
        fclose($fp);
    }

   public function requestNewGame(){

        //Remove them from current Game
        $this->removePlayerFromGame();

        $fp = fopen("actionlog.php", "w");
        fwrite($fp, "requestNewGame() in Game Management called");
        fclose($fp);

        //Check and make sure the amount of live games isn't at the current limit
        $currentGameCount = $this->checkGameCount();

        //If not, create a new game
        if ($currentGameCount < 51) {
            $newGame = new BlackjackGame();
            $newGame->newBJG();
            $gameid = $newGame->getGameID();

            //Add this player to the game
            $_SESSION['GameID'] = $gameid;

            //Add this to overall log

            //Add to to current Game Players
            $newGame->addPlayer($gameid);

            $fp = fopen("actionlog.php", "w");
            fwrite($fp, "from Game, returned game of id ". $gameid . 'to manager');
            fclose($fp);

        }else{
            echo "Sorry, already the maximum number of games being played";
        }
        
    }

   public function joinExistingGame(){

        $this->removePlayerFromGame();

        $fp = fopen("actionlog.php", "w");
        fwrite($fp, "joinExistingGame() in Game Management called");
        fclose($fp);

        //Find the first game with an available spot
        //Check and make sure there is at least one active game
        $currentGameCount = $this->checkGameCount();

        if ($currentGameCount > 0) {

            $gameid = $this->findGameWithLessThanMaxPlayers();
            //Add new player to the Game Players
            $oldGame = new BlackjackGame();
            $oldGame->addPlayer($gameid);

            //Add this player to the game
            $_SESSION['GameID'] = $gameid;

            //Add this to overall log

        }else{
            //Sorry... No available spots
            $this->requestNewGame();

        }


        
    }

    private function retrieveCurrentGames(){


        $fp = fopen("actionlog.php", "w");
        fwrite($fp, "retrieveCurrentGames() in Game Management called");
        fclose($fp);
    }

    private function removePlayerFromGame(){

        if($_SESSION['GameID']){

            //FILTER FILTER FILTER!!
            $old_game_id = $_SESSION['GameID'];
 
            $oldGame = new BlackjackGame();
            $oldGame->removePlayer($old_game_id);
        }



        //Remove this player from the Game Players
    }

    private function checkGameCount(){

        $fp = fopen("actionlog.php", "w");
        fwrite($fp, " checkGameCount() in Game Management called");

        $active = 1;
        

        $sql = "SELECT COUNT(gameID) AS theCount
                FROM games
                WHERE gamestate=:active";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":active", $active, PDO::PARAM_BOOL);
            $stmt->execute();
            $row = $stmt->fetch();
            if($row['theCount'] >= 50) {
                return -1;
            }
            else{

                return 1;
            }

            $stmt->closeCursor();
        }

        fclose($fp);
    }

    //Returns first Game ID where number of players is not at MAX
    private function findGameWithLessThanMaxPlayers(){

        $max = 5;

        $sql = "SELECT gameID
                FROM games
                WHERE playerCount <:Max
                LIMIT 1";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":Max", $max, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            $gameidtoJoin = $row['gameID'];

            echo $row.'<br />array: ';
            print_r($row);

            $stmt->closeCursor();

            return $gameidtoJoin;
        }
    }
}

?>