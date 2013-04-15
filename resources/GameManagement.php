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

        $fp = fopen("actionlog.php", "w");
        fwrite($fp, "requestNewGame() in Game Management called");
        fclose($fp);

        //Check and make sure the amount of live games isn't at the current limit
        $currentGameCount = $this->checkGameCount();

        //If not, create a new game
        if ($currentGameCount < 51) {
            $newGame = new BlackjackGame();
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

        $fp = fopen("actionlog.php", "w");
        fwrite($fp, "joinExistingGame() in Game Management called");
        fclose($fp);

        //Find the first game with an available spot

        //Add new player to the Game Players
    }

    private function retrieveCurrentGames(){


        $fp = fopen("actionlog.php", "w");
        fwrite($fp, "retrieveCurrentGames() in Game Management called");
        fclose($fp);
    }

    private function removePlayerFromGAme(){

        $fp = fopen("actionlog.php", "w");
        fwrite($fp, "removePlayerFromGAme() in Game Management called");
        fclose($fp);

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
}

?>