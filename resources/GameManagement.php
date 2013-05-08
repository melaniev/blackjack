<?php


/* 

Game Manager Class

*/

require_once("/resources/Game.php");

class Game_Manager{

    private $game_holder = array(50);
    public $_db;

    public $log;

    public static function Instance()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new Game_Manager();
        }
        return $inst;
    }
    
    private function __construct($db=NULL) {
        
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

        $games = $this->retrieveCurrentGames();

        print_r($games);

        foreach ($games as $game) {

            $gameid = $game['gameID'];
            //$newGame = new BlackjackGame($this->_db, $gameid);

          //  echo 'Type: '.gettype($newGame). '<br />';

            //store the game
        array_push($this->game_holder, $gameid);
      
        }

         $this->log   = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
    }

   public function requestNewGame(){

        //Remove them from current Game
        $this->removePlayerFromGame();

        //Check and make sure the amount of live games isn't at the current limit
        $currentGameCount = $this->checkGameCount();

        //If not, create a new game
        if ($currentGameCount < 51) {

            $newGame = new BlackjackGame();
            $newGame->newBJG();
            $gameid = $newGame->getGameID();

            array_push($this->game_holder, $gameid);

            //Add this player to the game
            $_SESSION['GameID'] = $gameid;

            //Add this to overall log

            //Add to to current Game Players
            $newGame->addPlayer($gameid);
            $newGame->playRound();

        }else{
            echo "Sorry, already the maximum number of games being played";
        }
        
    }

   public function joinExistingGame(){

        $this->log->logInfo('joinExistingGame() in GameManagement.php');


        $this->removePlayerFromGame();

        //Find the first game with an available spot

        //Check and make sure there is at least one active game
        $currentGameCount = $this->checkGameCount();

        if ($currentGameCount > 0) {

            print_r($this->game_holder);

            $gameid = $this->findGameWithLessThanMaxPlayers();

            $game = new BlackjackGame( NULL, $gameid );
            //add player to this game
            $game->addPlayer( $gameid );

            //Add this player to the game
            $_SESSION['GameID'] = $gameid;


        }else{
            //Sorry... No available spots
            $this->requestNewGame();

        }


        
    }


    private function retrieveCurrentGames(){

        $active = 1;
        
        $sql = "SELECT *
                FROM games
                WHERE gamestate=:active";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":active", $active, PDO::PARAM_BOOL);
            $stmt->execute();
            $row = $stmt->fetchAll();

            $stmt->closeCursor();

            return $row;
        };
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

    }

    //Returns first Game ID where number of players is not at MAX
    private function findGameWithLessThanMaxPlayers(){

        $max = 4;

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