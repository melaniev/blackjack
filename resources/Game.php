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

        $playtype = "dealt";

        $this->log->logInfo('dealHand called');

        
        // Deal a card to each player
        foreach ($this->players as $player) {

            $card = $this->deck->deal();
            $player->addCard($card);
            $valueofCard = $this->getCardValue( $card );
            $player->addToCardTotal( $valueofCard );

            $this->saveMove($this->_gid, $player->uID, $playtype, $card, $valueofCard);

        }

        //Deal to the dealer
        $card = $this->deck->deal();
        $this->dealer->addCard($card);
        $valueofCard = $this->getCardValue( $card );
        $this->dealer->addToCardTotal( $valueofCard );

        $this->saveDealerMove($this->_gid, $playtype, $card, $valueofCard);

        $this->update= $this->update + 1;
        $this->updateGameState1($this->_gid);
        

        // Deal another card to each player
         foreach ($this->players as $player) {

            $card = $this->deck->deal();
            $player->addCard( $card );
            $valueofCard = $this->getCardValue( $card );
            $player->addToCardTotal( $valueofCard );

            $oldTotal = $this->getHandTotal($this->_gid, $player->uID );
            $newTotal = $oldTotal + $valueofCard;
            $this->saveMove($this->_gid, $player->uID, $playtype, $card, $newTotal);
        }

        //Deal another to the dealer
        $card = $this->deck->deal();
        $this->dealer->addCard( $card );
        $valueofCard = $this->getCardValue( $card );
        $this->dealer->addToCardTotal( $valueofCard );

        $oldTotal = $this->getHandTotal($this->_gid, 0 );
        $newTotal = $oldTotal + $valueofCard;
        $this->saveDealerMove($this->_gid, $playtype, $card, $newTotal);

        $this->update= $this->update + 1;
        $this->updateGameState1($this->_gid);

        $this->updateTurn($g);

    }
    private function getHandTotal($g, $p){

        $lastMove;
        $oldTotal;

        if($p != 0){

            $sql = "SELECT MAX(moveID) AS lastMove
                    FROM moves
                    WHERE gameID=:gID AND userID=:uID";

            if($stmt = $this->_db->prepare($sql)) {

                $stmt->bindParam(":gID", $g, PDO::PARAM_INT);
                $stmt->bindParam(":uID", $p, PDO::PARAM_INT);
                $stmt->execute();
                $last = $stmt->fetch();

                $lastMove = $last['lastMove'];

                $stmt->closeCursor();
            }

            $sql = "SELECT newTotal
                    FROM moves
                    WHERE moveID=:mID";

            if($stmt = $this->_db->prepare($sql)) {

                $stmt->bindParam(":mID", $lastMove, PDO::PARAM_INT);
                $stmt->execute();
                $total = $stmt->fetch();

                $oldTotal = $total['newTotal'];

                $stmt->closeCursor();
            }

        }else{
            
                $sql = "SELECT MAX(moveID) AS lastMove
                    FROM dealerMoves
                    WHERE gameID=:gID";

            if($stmt = $this->_db->prepare($sql)) {

                $stmt->bindParam(":gID", $g, PDO::PARAM_INT);
                $stmt->execute();
                $last = $stmt->fetch();

                $lastMove = $last['lastMove'];

                $stmt->closeCursor();
            }

            $sql = "SELECT newTotal
                    FROM dealerMoves
                    WHERE moveID=:mID";

            if($stmt = $this->_db->prepare($sql)) {

                $stmt->bindParam(":mID", $lastMove, PDO::PARAM_INT);
                $stmt->execute();
                $total = $stmt->fetch();

                $oldTotal = $total['newTotal'];

                $stmt->closeCursor();
            }

        }
        return $oldTotal;
    }
    private function saveMove($g, $user, $type, $newCard, $newTotal){

        $sql = "INSERT INTO moves(gameID, userID, playtype, card, newTotal)
            VALUES(:gid, :uid, :pt, :car, :tot)";
    
        if($stmt = $this->_db->prepare($sql)) {

            $stmt->bindParam(":gid", $g, PDO::PARAM_INT);
            $stmt->bindParam(":uid", $user, PDO::PARAM_INT);
            $stmt->bindParam(":pt", $type, PDO::PARAM_STR);
            $stmt->bindParam(":car", $newCard, PDO::PARAM_STR);
            $stmt->bindParam(":tot", $newTotal, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
        }   
    }
    public function saveDealerMove($g, $type, $newCard, $newTotal){

        $sql = "INSERT INTO dealerMoves(gameID, playtype, card, newTotal)
            VALUES(:gid, :pt, :car, :tot)";
    
        if($stmt = $this->_db->prepare($sql)) {

            $stmt->bindParam(":gid", $g, PDO::PARAM_INT);
            $stmt->bindParam(":pt", $type, PDO::PARAM_STR);
            $stmt->bindParam(":car", $newCard, PDO::PARAM_STR);
            $stmt->bindParam(":tot", $newTotal, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
        }   
    }
    public function hit( $p, $g ){

        $playtype = 'hit';

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
            }else{
                $user = 0;
            }


        if (!$this->isTurnOver($g, $user) || $p == 0) {

            $this->log->logInfo('It is still players turn, player: ', $user);
            
            $this->deck = new Deck();
            $newCard = $this->deck->deal($g);

            $this->log->logInfo('Player delt card: ', $newCard);
            $valueofCard = $this->getCardValue( $newCard );

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

                $oldTotal = $this->getHandTotal($g, $user );
                $newTotal = $oldTotal + $valueofCard;
                $this->saveMove($g, $user, $playtype, $newCard, $newTotal);

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
                $oldTotal = $this->getHandTotal($g, 0);
                $newTotal = $oldTotal + $valueofCard;
                $this->saveDealerMove($g, 0, $playtype, $newCard, $newTotal);                
            }

            $this->log->logInfo('The value of the new card is ', $valueofCard);

        }else{

            $this->log->logInfo('Players turn was already over, player: ', $user);
        }

        $this->updateTurn($g);
        $this->update= $this->update + 1;
        $this->updateGameState2($g);

        $this->log->logInfo('A Hit in Game.php ');
    }

    public function stay( $p , $g){

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

        $this->makeTurnOver($g, $user);

        $this->updateGameState2($g);

    }
    public function updateTurn($g){

        $allGamePlayers = $this->getPlayersInGame($g);

        foreach ($allGamePlayers as $player) {
            
            $oldTotal = $this->getHandTotal($g, $player);

            if ($oldTotal > 21) {
                //bust
                $this->makeTurnOver($g, $player);
            }

            $cardCount = $this->numberOfMoves($g, $player);
            if ($cardCount == 4) {
               //maxCards

                $this->makeTurnOver($g, $player);

            }
        }

        $this->finishRound($g);

    }

    private function makeTurnOver($g, $player){

        $turndone = 1;

        $sql = "INSERT INTO turns(gameID, userID, turnOver )
        VALUES(:gID, :uID, :to)";
        
        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":gID", $g, PDO::PARAM_INT);
            $stmt->bindParam(":uID", $player, PDO::PARAM_INT);
            $stmt->bindParam(":to", $turndone, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
        }
    }
    private function isTurnOver($g, $player){

        $turndone = 1;

        $sql = "SELECT turnOver
        FROM turns
        WHERE userID=:uID AND gameID=:gID";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":uID", $player, PDO::PARAM_INT);
            $stmt->bindParam(":gID", $g, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();

            $turn = $row['turnOver'];

            $stmt->closeCursor();

            if ($turn == $turndone) {
                return 1;
            }else{
                return 0;
            }
        }
    }
    private function numberOfMoves($g, $player){

        $sql = "SELECT COUNT(moveID) AS cardCount
        FROM moves
        WHERE userID=:uID AND gameID=:gID";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":uID", $player, PDO::PARAM_INT);
            $stmt->bindParam(":gID", $g, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();

            $moveCount = $row['cardCount'];

            $stmt->closeCursor();
        }

        return $moveCount;
    }

    private function finishRound($g){

        $turnOverCount= 0;
        //Check if all the players are done with this hand
        $allGamePlayers = $this->getPlayersInGame($g);
        $numberOfPlayers = count($allGamePlayers);

        foreach ($allGamePlayers as $player) {
           
           if ($this->isTurnOver($g, $player)) {
               $turnOverCount++;
           }

        }

        if ($turnOverCount == $numberOfPlayers) {
            //Hand Done, Dealers Turn
            $this->playDealer($g);
        }

    }
    public function playDealer($g){

        // //dealer stands on 17s
        // if(($this->getHandTotal($g, 0)) < 17) {
        //     //dealer hits
        //    $this->hit( 0, $g );
        // }
        // //one more card allowed
        // if(($this->getHandTotal($g, 0)) < 17) {
        //     //dealer hits
        //    $this->hit( 0, $g );
        // }

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
    // private function getPlayerByName($p){

    //     $this->log->logInfo('Checking for player in getPlayerByName in Game.php, player: ', $p );

    //     $myGameFile = "plays".$this->_gid.".php";

    //     $fp = fopen($myGameFile, "w");
    //         fwrite($fp, json_encode($this->players));
    //         fclose($fp);
 

    //     foreach ($this->players as $player) {

    //         $this->log->logInfo('Inside the for each loop' );

    //         if($player->username == $p){

    //             $this->log->logInfo('Inside the if loop' );

    //         $this->log->logInfo('correct player found');


    //             return $player;
                
    //         }

    //     } 
    // }

    private function getPlayersInGame($g){

        $returnplayers = array();

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
                array_push($returnplayers, $player['userID']);
            }
        }
            return $returnplayers;
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

    public function updateGameState2($gid){


        $this->log->logInfo('Updating Game State 22222222!');

        $gamestate = array(
                // array(

                //     'name'=> 'dealer',
                //     'count' => $this->dealer->card_count,
                //     'cards'=> json_encode($this->dealer->setUpOrGetHand($gid, 0)),
                //     'updateCount' => $this->update
                // )
        );

        foreach ($this->players as $player) {

            $thisUsername;

            $this->log->logInfo('Updating Game State for player: ', $player);

            //Get the username
                $sql = "SELECT username AS un
                FROM users
                WHERE userID=:uID";

                if($stmt = $this->_db->prepare($sql)) {

                    $stmt->bindParam(':uID', $player , PDO::PARAM_INT);
                    $stmt->execute();
                    $row = $stmt->fetch();
                    $thisUsername = $row['un'];

                    $stmt->closeCursor();
                }

                $this->log->logInfo('in UG2, their username is: ', $thisUsername);

                //Get the hand
               $sql = "SELECT card AS cards
                    FROM hand
                    WHERE gameID=:gID AND userID = :uID";

                if($stmt = $this->_db->prepare($sql)) {

                    $stmt->bindParam(":gID", $gid, PDO::PARAM_INT);
                    $stmt->bindParam(":uID", $player , PDO::PARAM_INT);
                    $stmt->execute();
                    $myCards = $stmt->fetchAll();
                                
                    $stmt->closeCursor();
                }

                $myHand = array();
                $newCount = 0;
                foreach ($myCards as $aCard) {

                    $this->log->logInfo('in UG2, their cards are: ', $aCard['cards']);
                    array_push($myHand, $aCard['cards'] );
                    $newCount = $newCount + $this->getCardValue($aCard['cards']);

                }

                $this->log->logInfo('in UG2, their count is now: ', $newCount);

            $playerInfo = array(

                'name' =>$thisUsername,
                'count' => $newCount,
                'cards'=> json_encode($myHand)

            );

            array_push($gamestate, $playerInfo);
        }
            $myGameFile = "plays".$gid.".php";

            $fp = fopen($myGameFile, "w");
            fwrite($fp, json_encode($gamestate));
            fclose($fp);
    }

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