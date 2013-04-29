<?php


/* 

BlackjackGame Class

*/

require_once("/resources/Deck.php");
require_once("/resources/Player.php");
require_once("/resources/Klogger.php");



class BlackjackGame{

    /**
     * The database object
     *
     * @var object
     */
    private $_db;
    public $_gid;
    private $deck;
    private $dealer;
    private $players = array();

  
    /**
     * Checks for a database object and creates one if none is found
     *
     * @param object $db
     * @return void
     */
    public function __construct($db=NULL, $id=NULL)
    {
        //echo "<br/>Blackjack Constructed called with id: ". $id.'<br />';
        if(is_object($db))
        {
            $this->_db = $db;
        }
        else
        {
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
            $this->_db = new PDO($dsn, DB_USER, DB_PASS);
        }

        if ($id != NULL) {
            
            $this->_gid = $id;
        }

        //Create a dealer
        $this->dealer = new Player($db, 'dealer');

        $log   = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
        $log->logInfo('Blackjack Constructed called with id: ', $id);


    }


    public function newBJG(){

        //Store this game in the database
        $nowActive = 1;
        $count = 0;
        $sql = "INSERT INTO games(gameState, playerCount)
        VALUES(:gstate, :pcount)";
        
        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":gstate", $nowActive, PDO::PARAM_BOOL);
            $stmt->bindParam(":pcount", $count, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
        }

        //Get the Game ID
        $sql = "SELECT MAX(gameID) AS nextID
                FROM games";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->execute();
            $id = $stmt->fetch();
            $next_id = $id['nextID'];
            
            $stmt->closeCursor();

            $this->_gid = $next_id;


        }

        

    }

    public function getGameID(){

        return $this->_gid;
    }

    public function addPlayer($gid){

        $new_player = new Player($db, $_SESSION['Username']);

        echo 'Add player called';
        array_push ($this->players, $new_player);

        $incr = 1;
        $userID = $this->getUserID();
        echo 'UserId is: '.$userID;

        //Add this player into the current players
        $sql = "INSERT INTO gameplayers(gameID, userID)
                VALUES(:g, :u)";
        
        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":g", $gid, PDO::PARAM_INT);
            $stmt->bindParam(":u", $userID, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
        }

        //Update the playercount in the game
        $sql = "UPDATE games
                SET playerCount = playerCount + :incr
                WHERE gameID=:gID";

        if($stmt = $this->_db->prepare($sql)) {      
            $stmt->bindParam(":gID", $gid , PDO::PARAM_INT);
            $stmt->bindParam(":incr", $incr , PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
        }


    }
    public function removePlayer($idofGame){

        $userID = $this->getUserID();

        //Remove that player from that games current players
        $sql = "DELETE FROM gameplayers
                WHERE gameID = :gID AND userID = :uID";

        if($stmt = $this->_db->prepare($sql)) {      
            $stmt->bindParam(":gID", $idofGame , PDO::PARAM_INT);
            $stmt->bindParam(":uID", $userID , PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
        }

        //Update the count of the number of players
        $sql = "SELECT playerCount AS players
        FROM games
        WHERE gameID = :gID";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":gID", $idofGame , PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            $numOfPlayrs = $row['players'];

            $stmt->closeCursor();
        }
        //If the game no longer has players, make the game inactive
        if ($numOfPlayrs - 1 <= 0) {

            $inactive = 0;

            $sql = "UPDATE games
                    SET gameState =:inactive
                    WHERE gameID=:gID";

            if($stmt = $this->_db->prepare($sql)) {      
                $stmt->bindParam(":gID", $idofGame, PDO::PARAM_INT);
                $stmt->bindParam(":inactive", $inactive , PDO::PARAM_BOOL);
                $stmt->execute();
                $stmt->closeCursor();
            }

        }else{
            //update the game with one less player

            $incr = 1;

            $sql = "UPDATE games
                    SET playerCount = playerCount - :incr
                    WHERE gameID=:gID";

            if($stmt = $this->_db->prepare($sql)) {      
                $stmt->bindParam(":gID", $idofGame, PDO::PARAM_INT);
                $stmt->bindParam(":incr", $incr , PDO::PARAM_INT);
                $stmt->execute();
                $stmt->closeCursor();
            }

        }

    }
    public function playRound(){

        $this->dealHand();

        echo "<br />NOW:".time()."<br />";

        $time = time();

    }
    public function dealHand(){

        echo "Dealing hand...";

        $this->deck = new Deck();

        // Deal a card to each player
        foreach ($this->players as $player) {

            $card = $this->deck->deal();
            $player->addCard($card);
            $player->addCard($card);
            $valueofCard = getCardValue($card);
            $player->addToCardTotal($valueofCard);

        }

        //Deal to the dealer
        $card = $this->deck->deal();
        $this->dealer->addCard($card);
        $valueofCard = getCardValue($card);
        $this->dealer->addToCardTotal($valueofCard);

        $this->updateGameState();

        // Deal another card to each player
         foreach ($this->players as $player) {

            $card = $this->deck->deal();
            $player->addCard( $card );
            $valueofCard = getCardValue( $card );
            $player->addToCardTotal( $valueofCard );
            
        }

        //Deal another to the dealer
        $card = $this->deck->deal();
        $this->dealer->addCard( $card );
        $valueofCard = getCardValue( $card );
        $this->dealer->addToCardTotal( $valueofCard );

        print_r($this->deck);

        $this->updateGameState();

    }
    public function hit( $p ){

        echo "<p>Hit!</p>";
        $log->logInfo('hit');

            $fp = fopen("../plays.php", "a");
            fwrite( $fp, "hit hit hit!!!");
            fclose( $fp );

        $player_turn = getPlayerByName( $p );

        //If player hasn't already finished turn
        if ($player_turn->played_turn != 1) {
                
            $newCard = $this->deck->deal();

            //first check and make sure an object was returned!!
            $player_turn->addCard( $newCard );
            $valueofCard = getCardValue( $card );
            $player_turn->addToCardTotal( $newCard );
        }

        updateTurn();
    }
    public function stay( $p ){

        echo "<p>Stay!</p>";
        $log->logInfo('stay');

        $fp = fopen("../plays.php", "a");
        fwrite($fp, "stay stay stay!!!");
        fclose($fp);



        $player_turn = getPlayerByName($p);

        if ($player_turn->played_turn != 1) {
        //first check and make sure an object was returned!!
            $player_turn->played_turn = 1;
            updateTurn();
        }

    }
    public function updateTurn(){

        $playsmade = 0;

        foreach ($this->players as $player) {

            if($player->played_turn == 1){
                
                $playsmade++;
            }

            if ($playsmade == count($this->players)) {
                
                finishRound();
            }

        } 
    }

    private function finishRound(){


    }
    private function getCardValue($card_delt){

        $card_value = substr($card_delt, 0, 2);

        switch ($card_value) {
            case "01":
                return 1;
                break;
            case "02":
                return 2;
                break;
            case "03":
                return 3;
                break;
            case "04":
                return 4;
                break;
            case "05":
                return 5;
                break;
            case "06":
                return 6;
                break;
            case "07":
                return 7;
                break;
            case "08":
                return 8;
                break;
            case "09":
                return 9;
                break;
            case "10":
                return 10;
                break;
            case "11":
                return 10;
                break;
            case "12":
                return 10;
                break;
        }


    }
    private function getPlayerByName($p){

        foreach ($this->players as $player) {

            if($player->username == $p){
                
                
            }

        } 
    }

    public function updateGameState(){

        $gamestate = array(
                array(
                    'name'=> 'dealer',
                    'count' => $this->dealer->card_count,
                    'cards'=> json_encode($this->dealer->hand)
                )
        );

        foreach ($this->players as $player) {

            $playerInfo = array(

                'name' => $player->username,
                'count' => $player->card_count,
                'cards'=> json_encode($player->hand)

                );

            array_push($gamestate, $playerInfo);
        }


            $fp = fopen("plays.php", "w");
            fwrite($fp, json_encode($gamestate));
            fclose($fp);
    }

    private function getUserID(){

        echo 'getUserID called';

        $un = $_SESSION['Username'];

        echo "Username is the session object is: ". $un;

        //Get the Game ID
        $sql = "SELECT userID AS uID
                FROM users
                WHERE username=:uN";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(':uN', $un , PDO::PARAM_STR);
            $stmt->execute();
            $id = $stmt->fetch();
            $us_id = $id['uID'];

            
            $stmt->closeCursor();

            return $us_id;
        }

    }


}    

?>