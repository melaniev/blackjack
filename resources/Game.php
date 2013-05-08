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
    private $update;

    public $log;
    /**
     * Checks for a database object and creates one if none is found
     *
     * @param object $db
     * @return void
     */
    public function __construct($db=NULL, $id=NULL)
    {
    
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
            $this->getPlayersInGame($this->_gid);

            //Create a dealer
            $this->dealer = new Player($db, 'dealer', 0, $this->_gid );
        }else{
            $this->dealer = NULL;
        }

        
        $this->update = 0;

        $this->log   = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
        $this->log->logInfo('Blackjack Constructed called with id: ', $id);

        

    }


    public function newBJG(){

        $this->deck = new Deck();

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
            $this->getPlayersInGame($this->_gid);


        }

        if ($this->dealer == NULL) {

           $this->dealer = new Player($db, 'dealer', 0, $this->_gid );
        }

    }

    public function getGameID(){

        return $this->_gid;
    }

    public function addPlayer($gid){

        $userID = $this->getUserID();

        $new_player = new Player($db, $_SESSION['Username'], $userID, $gid );

        array_push ($this->players, $new_player);

        $incr = 1;
        $userID = $this->getUserID();

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

        $this->log->logInfo('playRound called');

        $this->dealHand();

        $time = time();

    }
    public function dealHand(){

        $this->log->logInfo('dealHand called');

        
        // Deal a card to each player
        foreach ($this->players as $player) {

            $card = $this->deck->deal();
            $player->addCard($card);
            $valueofCard = $this->getCardValue( $card );
            $player->addToCardTotal( $valueofCard );

        }

        //Deal to the dealer
        $card = $this->deck->deal();
        $this->dealer->addCard($card);
        $valueofCard = $this->getCardValue( $card );
        $this->dealer->addToCardTotal( $valueofCard );

        $this->update= $this->update + 1;
        $this->updateGameState1($this->_gid);
        

        // Deal another card to each player
         foreach ($this->players as $player) {

            $card = $this->deck->deal();
            $player->addCard( $card );
            $valueofCard = $this->getCardValue( $card );
            $player->addToCardTotal( $valueofCard );
            
        }

        //Deal another to the dealer
        $card = $this->deck->deal();
        $this->dealer->addCard( $card );
        $valueofCard = $this->getCardValue( $card );
        $this->dealer->addToCardTotal( $valueofCard );

        $this->update= $this->update + 1;
        $this->updateGameState1($this->_gid);

    }
    public function hit( $p, $g ){

        $this->log->logInfo('a Hit in the Game from player: ', $p);

         $sql = "SELECT userID
                FROM users
                WHERE sessID=:sess
                LIMIT 1";

            $stmt = $this->_db->prepare($sql);
            $stmt->bindParam(':sess', $p, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch();
            if($stmt->rowCount()==1)
            {
                $user = $row['userID'];
            }


        //If player hasn't already finished turn

        $this->log->logInfo('Hmm are we sure this is returning a player: ', $user);

        //if ($player_turn->played_turn != 1) {
        if (true) {

            $this->log->logInfo('It is still players turn, player: ', $user);
            
            $this->deck = new Deck();
            $newCard = $this->deck->deal($g);

            $this->log->logInfo('Player delt card: ', $newCard);

            if ($user != 0) {

             $sql = "INSERT INTO hand(gameID, userID, card)
                    VALUES(:gid, :uid, :car)";
            
                if($stmt = $this->_db->prepare($sql)) {

                    $stmt->bindParam(":gid", $g, PDO::PARAM_INT);
                    $stmt->bindParam(":uid", $user, PDO::PARAM_INT);
                    $stmt->bindParam(":car", $newCard, PDO::PARAM_STR);
                    $stmt->execute();
                    $stmt->closeCursor();
                }          
            }else{
                //its the dealer!
                 $sql = "INSERT INTO dealer(gameID, card)
                        VALUES(:gid, :car)";
                
                if($stmt = $this->_db->prepare($sql)) {

                    $stmt->bindParam(":gid", $g, PDO::PARAM_INT);
                    $stmt->bindParam(":car", $newCard, PDO::PARAM_STR);
                    $stmt->execute();
                    $stmt->closeCursor();
                }                
            }

            //first check and make sure an object was returned!!
            //$player_turn->addCard( $newCard );
            $valueofCard = $this->getCardValue( $newCard );

            $this->log->logInfo('The value of the new card is ', $valueofCard);
           // $player_turn->addToCardTotal( $valueofCard );

           // $this->log->logInfo('Player now has total: ', $player_turn->card_count);

        }else{
            $this->log->logInfo('Players turn was already over, player: ', $user);
        }

        //updateTurn();
        $this->update= $this->update + 1;
        $this->updateGameState2($g);

        $this->log->logInfo('A Hit in Game.php ');
    }
    public function stay( $p ){

        $this->log->logInfo('Blackjack Constructed called with id: ', $id);

        $player_turn = getPlayerByName($p);

        if ($player_turn->played_turn != 1) {
        //first check and make sure an object was returned!!
            $player_turn->played_turn = 1;
            updateTurn();
        }

        $this->log->logInfo('A Stay in Game.php ');

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

        while ( $this->dealer->card_count < 17) {
            
            //Deal another to the dealer
            $card = $this->deck->deal();
            $this->dealer->addCard( $card );
            $valueofCard = $this->getCardValue( $card );
            $this->dealer->addToCardTotal( $valueofCard );
        }


    }
    public function getCardValue($card_delt){

        $this->log->logInfo('Checking for the value of the card in getCardValue in Game.php');

        $card_value = substr($card_delt, 0, 2);
        $this->log->logInfo('String version of card value: ', $card_value );

        switch ($card_value) {
            case "01":
                return 11;
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
            case "13":
                return 10;
                break;
        }

        return 0;

    }
    private function getPlayerByName($p){

        $this->log->logInfo('Checking for player in getPlayerByName in Game.php, player: ', $p );

        $myGameFile = "plays".$this->_gid.".php";

        $fp = fopen($myGameFile, "w");
            fwrite($fp, json_encode($this->players));
            fclose($fp);
 

        foreach ($this->players as $player) {

            $this->log->logInfo('Inside the for each loop' );

            if($player->username == $p){

                $this->log->logInfo('Inside the if loop' );

            $this->log->logInfo('correct player found');


                return $player;
                
            }

        } 
    }

    private function getPlayersInGame($g){

       $sql = "SELECT userID
        FROM gameplayers
        WHERE gameID=:gm";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":gm", $g, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetchAll();

            $stmt->closeCursor();

            foreach ($row as $player) {
                
                array_push($this->players, $player['userID']);
            }
        }

    }

    public function updateGameState1($gid){


        $this->log->logInfo('Updating Game State!');

        $gamestate = array(
                array(

                    'name'=> 'dealer',
                    'count' => $this->dealer->card_count,
                    'cards'=> json_encode($this->dealer->setUpOrGetHand($gid, 0)),
                    'updateCount' => $this->update
                )
        );

        foreach ($this->players as $player) {

            $this->log->logInfo('Updating Game State for player: ', $player);

            $playerInfo = array(

                'name' => $player->username,
                'count' => $player->card_count,
                'cards'=> json_encode($player->setUpOrGetHand($gid, $player))

            );

            $this->log->logInfo('username:', $player->username);

            array_push($gamestate, $playerInfo);
        }
            $myGameFile = "plays".$this->_gid.".php";

            $fp = fopen($myGameFile, "w");
            fwrite($fp, json_encode($gamestate));
            fclose($fp);
    }

    // public function updateGameState2($gid){


    //     $this->log->logInfo('Updating Game State 22222222!');

    //     $gamestate = array(
    //             // array(

    //             //     'name'=> 'dealer',
    //             //     'count' => $this->dealer->card_count,
    //             //     'cards'=> json_encode($this->dealer->setUpOrGetHand($gid, 0)),
    //             //     'updateCount' => $this->update
    //             // )
    //     );

    //     foreach ($this->players as $player) {

    //         $thisUsername;

    //         $this->log->logInfo('Updating Game State for player: ', $player);

    //         //Get the username
    //             $sql = "SELECT username AS un
    //             FROM users
    //             WHERE userID=:uID";

    //             if($stmt = $this->_db->prepare($sql)) {

    //                 $stmt->bindParam(':uID', $player , PDO::PARAM_INT);
    //                 $stmt->execute();
    //                 $row = $stmt->fetch();
    //                 $thisUsername = $row['un'];

    //                 $stmt->closeCursor();
    //             }

    //             $this->log->logInfo('in UG2, their username is: ', $thisUsername);

    //             //Get the hand
    //            $sql = "SELECT card AS cards
    //                 FROM hand
    //                 WHERE gameID=:gID AND userID = :uID";

    //             if($stmt = $this->_db->prepare($sql)) {

    //                 $stmt->bindParam(":gID", $gid, PDO::PARAM_INT);
    //                 $stmt->bindParam(":uID", $player , PDO::PARAM_INT);
    //                 $stmt->execute();
    //                 $myCards = $stmt->fetchAll();
                                
    //                 $stmt->closeCursor();
    //             }

    //             $myHand = array();
    //             $newCount = 0;
    //             foreach ($myCards as $aCard) {

    //                 $this->log->logInfo('in UG2, their cards are: ', $aCard['cards']);
    //                 array_push($myHand, $aCard['cards'] );
    //                 $newCount = $newCount + $this->getCardValue($aCard['cards']);

    //             }

    //             $this->log->logInfo('in UG2, their count is now: ', $newCount);

    //         $playerInfo = array(

    //             'name' =>$thisUsername,
    //             'count' => $newCount,
    //             'cards'=> json_encode($myHand)

    //         );

    //         array_push($gamestate, $playerInfo);
    //     }
    //         $myGameFile = "plays".$gid.".php";

    //         $fp = fopen($myGameFile, "w");
    //         fwrite($fp, json_encode($gamestate));
    //         fclose($fp);
    // }

    private function getUserID(){

        $u = session_id();

        //Get the Game ID
        $sql = "SELECT userID AS uID
                FROM users
                WHERE sessID=:sID";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(':sID', $u , PDO::PARAM_STR);
            $stmt->execute();
            $id = $stmt->fetch();
            $us_id = $id['uID'];

            
            $stmt->closeCursor();

            return $us_id;
        }

    }


}    

?>