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
        $hand = 0;

        $sql = "INSERT INTO games(gameState, handID, playerCount)
        VALUES(:gstate, :pcount, :hand)";
        
        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":gstate", $nowActive, PDO::PARAM_BOOL);
            $stmt->bindParam(":pcount", $count, PDO::PARAM_INT);
            $stmt->bindParam(":hand", $hand, PDO::PARAM_INT);
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

    public function setHand($currentGame, $newHand){

        $sql = "UPDATE games
                SET handID = :nwHand
                WHERE gameID=:gID";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":gID", $currentGame, PDO::PARAM_INT);
            $stmt->bindParam(":nwHand", $newHand, PDO::PARAM_INT);
            $stmt->execute();
            
            $stmt->closeCursor();
        }

        return $curr_hand_id;
    }

    public function getHandID($currentGame){

        $curr_hand_id;

        $sql = "SELECT handID
                FROM games
                WHERE gameID=:gID";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":gID", $currentGame, PDO::PARAM_INT);
            $stmt->execute();
            $id = $stmt->fetch();
            $curr_hand_id = $id['handID'];
            
            $stmt->closeCursor();
        }

        return $curr_hand_id;
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

        return $userID;

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

        $time = time();

    }
    public function dealHand(){

        $playtype = "dealt";
      
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

        $this->updateTurn($this->_gid);

    }
    private function getHandTotal($g, $p){

        $lastMove;
        $oldTotal;
        $thisHand = $this->getHandID($g);

        if($p != 0){

            $sql = "SELECT MAX(moveID) AS lastMove
                    FROM moves
                    WHERE gameID=:gID AND userID=:uID AND handID=:hID";

            if($stmt = $this->_db->prepare($sql)) {

                $stmt->bindParam(":gID", $g, PDO::PARAM_INT);
                $stmt->bindParam(":uID", $p, PDO::PARAM_INT);
                $stmt->bindParam(":hID", $thisHand, PDO::PARAM_INT);

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
                    WHERE gameID=:gID AND handID=:hID";

            if($stmt = $this->_db->prepare($sql)) {

                $stmt->bindParam(":gID", $g, PDO::PARAM_INT);
                $stmt->bindParam(":hID", $thisHand, PDO::PARAM_INT);
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

        $thisHand = $this->getHandID($g);

        $sql = "INSERT INTO moves(gameID, handID, userID, playtype, card, newTotal)
            VALUES(:gid, :hid, :uid, :pt, :car, :tot)";
    
        if($stmt = $this->_db->prepare($sql)) {

            $stmt->bindParam(":gid", $g, PDO::PARAM_INT);
            $stmt->bindParam(":hid", $thisHand, PDO::PARAM_INT);
            $stmt->bindParam(":uid", $user, PDO::PARAM_INT);
            $stmt->bindParam(":pt", $type, PDO::PARAM_STR);
            $stmt->bindParam(":car", $newCard, PDO::PARAM_STR);
            $stmt->bindParam(":tot", $newTotal, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
        }   
    }
    public function saveDealerMove($g, $type, $newCard, $newTotal){

        $thisHand = $this->getHandID($g);

        $sql = "INSERT INTO dealerMoves(gameID, handID, playtype, card, newTotal)
            VALUES(:gid, :hid, :pt, :car, :tot)";
    
        if($stmt = $this->_db->prepare($sql)) {

            $stmt->bindParam(":gid", $g, PDO::PARAM_INT);
            $stmt->bindParam(":hid", $thisHand, PDO::PARAM_INT);
            $stmt->bindParam(":pt", $type, PDO::PARAM_STR);
            $stmt->bindParam(":car", $newCard, PDO::PARAM_STR);
            $stmt->bindParam(":tot", $newTotal, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
        }   
    }
    public function hit( $p, $g ){

        $playtype = 'hit';


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
            
            $this->deck = new Deck();
            $newCard = $this->deck->deal($g);

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
                $this->saveDealerMove($g, $playtype, $newCard, $newTotal);                
            }


        }else{

        }

        $this->updateTurn($g);
        $this->update= $this->update + 1;
        $this->updateGameState2($g);

    }

    public function stay( $p , $g){

        $playtype = 'stay';

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
        $oldTotal = $this->getHandTotal($g, $user );
        $this->saveMove($g, $user, $playtype, 'none', $oldTotal);

        $this->updateGameState2($g);
        $this->updateTurn($g);

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
        $row;

        $sql = "SELECT COUNT(userID) AS alreadyThere
        FROM turns
        WHERE userID=:usern AND gameID = :gID";

            if($stmt = $this->_db->prepare($sql)) {
                $stmt->bindParam(":usern", $player, PDO::PARAM_INT);
                $stmt->bindParam(":gID", $g, PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch();


                $stmt->closeCursor();
            }

            if($row['alreadyThere']!=0) {
                    
                    //Its already been inserted once

                    $sql = "UPDATE turns
                    SET turnOver = :Over
                    WHERE gameID=:gID AND userID=:uID";
                    
                    if($stmt = $this->_db->prepare($sql)) {
                        $stmt->bindParam(":gID", $g, PDO::PARAM_INT);
                        $stmt->bindParam(":uID", $player, PDO::PARAM_INT);
                        $stmt->bindParam(":Over", $turndone, PDO::PARAM_INT);
                        $stmt->execute();
                        $stmt->closeCursor();
                    }
            }
            else{

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

    }
    private function makeTurnUnder($g, $player){

        $turndone = 0;

        $sql = "UPDATE turns
        SET turnOver = :notOver
        WHERE gameID=:gID AND userID=:uID";
        
        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":gID", $g, PDO::PARAM_INT);
            $stmt->bindParam(":uID", $player, PDO::PARAM_INT);
            $stmt->bindParam(":notOver", $turndone, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
        }
    }    
    private function isTurnOver($g, $player){

        $turndone = 1;
        $thisHand = $this->getHandID($g);

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

        $thisHand = $this->getHandID($g);

        $sql = "SELECT COUNT(moveID) AS cardCount
        FROM moves
        WHERE userID=:uID AND gameID=:gID AND handID=:hID";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":uID", $player, PDO::PARAM_INT);
            $stmt->bindParam(":gID", $g, PDO::PARAM_INT);
            $stmt->bindParam(":hID", $thisHand, PDO::PARAM_INT);
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


        $dealerCount = $this->getHandTotal($g, 0);


        //dealer stands on 17s
        if(($dealerCount) < 17) {
            //dealer hits
           $this->hit( 0, $g );
        }

        $dealerCount = $this->getHandTotal($g, 0);
 
        //one more card allowed
        if($dealerCount < 17) {
            //dealer hits
           $this->hit( 0, $g );
        }

        $this->updateGameState2($g);

        //wrap up this hand
        $this->endHand($g);

    }
    public function endHand($g){

        //Who won? lost? draw?
        $dealerCount = $this->getHandTotal($g, 0);

        $allGamePlayers = $this->getPlayersInGame($g);

        foreach ($allGamePlayers as $player) {

            $thisPlayersFinalCount = $this->getHandTotal($g, $player);

            if ($thisPlayersFinalCount > 21) {
                //bust, lose
                //update loss record
                $this->winLossRecord($player, 'losses');
            }else{
                if ($thisPlayersFinalCount > $dealerCount) {
                    //win!

                    $this->winLossRecord($player, 'wins');
                }elseif($thisPlayersFinalCount < $dealerCount) {
                    //lose
                    $this->winLossRecord($player, 'losses');

                }elseif($thisPlayersFinalCount == $dealerCount){
                    //draw

                    $this->winLossRecord($player, 'draws');
                }
            }

        }
        sleep(6);
        $this->emptyHands($g);

        sleep(10);
        $this->newDeal($g);

    }
    public function emptyHands($g){

        $thisHand = $this->getHandID($g);

        //empty the dealers hand

            $sql = "DELETE 
            FROM dealer
            WHERE gameID=:gID";

            if($stmt = $this->_db->prepare($sql)) {
                
                $stmt->bindParam(":gID", $g, PDO::PARAM_INT);
                $stmt->execute();

                $stmt->closeCursor();
            }

        //empty all the players hands

        $allGamePlayers = $this->getPlayersInGame($g);

        foreach ($allGamePlayers as $player) {

            $sql = "DELETE 
            FROM hand
            WHERE userID=:uID AND gameID=:gID";

            if($stmt = $this->_db->prepare($sql)) {

                $stmt->bindParam(":uID", $player, PDO::PARAM_INT);
                $stmt->bindParam(":gID", $g, PDO::PARAM_INT);
                $stmt->execute();

                $stmt->closeCursor();
            }

            $this->makeTurnUnder($g, $player);

        }
    }

    public function newDeal($g){

        $thisHand = $this->getHandID($g) + 1;

        $thisHand = $this->setHand($g, $thisHand);

        $playtype = "dealt";
        $this->deck = new Deck();
        $allGamePlayers = $this->getPlayersInGame($g);


        // Deal a card to each player
        foreach ($allGamePlayers as $player) {

            $card = $this->deck->deal($g);

            $sql = "INSERT INTO hand(gameID, userID, card)
                    VALUES(:gid, :uid, :car)";
            
                if($stmt = $this->_db->prepare($sql)) {

                    $stmt->bindParam(":gid", $g, PDO::PARAM_INT);
                    $stmt->bindParam(":uid", $player, PDO::PARAM_INT);
                    $stmt->bindParam(":car", $card, PDO::PARAM_STR);
                    $stmt->execute();
                    $stmt->closeCursor();
            } 

            $valueofCard = $this->getCardValue( $card );
            $this->saveMove($g, $player, $playtype, $card, $valueofCard);
        }

        //Deal to the dealer
        $card = $this->deck->deal($g);

        $sql = "INSERT INTO dealer(gameID, card)
                VALUES(:gid, :car)";
        
        if($stmt = $this->_db->prepare($sql)) {

            $stmt->bindParam(":gid", $g, PDO::PARAM_INT);
            $stmt->bindParam(":car", $card, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();
        }
        $valueofCard = $this->getCardValue( $card );
        $this->saveDealerMove($g, $playtype, $card , $valueofCard); 


        //Update game
        $this->updateGameState2($g);

        // Deal another card to each player
        foreach ($allGamePlayers as $player) {

            $card = $this->deck->deal($g);

            $sql = "INSERT INTO hand(gameID, userID, card)
                    VALUES(:gid, :uid, :car)";
            
                if($stmt = $this->_db->prepare($sql)) {

                    $stmt->bindParam(":gid", $g, PDO::PARAM_INT);
                    $stmt->bindParam(":uid", $player, PDO::PARAM_INT);
                    $stmt->bindParam(":car", $card, PDO::PARAM_STR);
                    $stmt->execute();
                    $stmt->closeCursor();
            } 

            $valueofCard = $this->getCardValue( $card );
            $oldTotal = $this->getHandTotal($g, $player );
            $newTotal = $oldTotal + $valueofCard;
            $this->saveMove($g, $player, $playtype, $card, $newTotal);
        }

        // Deal another to the dealer
        $card = $this->deck->deal($g);

        $sql = "INSERT INTO dealer(gameID, card)
                VALUES(:gid, :car)";
        
        if($stmt = $this->_db->prepare($sql)) {

            $stmt->bindParam(":gid", $g, PDO::PARAM_INT);
            $stmt->bindParam(":car", $card, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();
        }

        $oldTotal = $this->getHandTotal($g, 0 );
        $newTotal = $oldTotal + $valueofCard;
        $this->saveDealerMove($g, $playtype, $card, $newTotal);


        //Update game
        $this->updateGameState2($g);

    }

    public function winLossRecord($pId, $result){

        //Update the players win, loss, or draw

        $incr = 1;

        if(strcmp($result, 'wins') == 0 ){

            $sql = "UPDATE records
                    SET wins = wins + :incr
                    WHERE userID=:uID";

        }elseif (strcmp($result, 'losses') == 0 ) {

            $sql = "UPDATE records
                    SET losses = losses + :incr
                    WHERE userID=:uID";
        }elseif (strcmp($result, 'draws') == 0 ) {
            
            $sql = "UPDATE records
                    SET draws = draws + :incr
                    WHERE userID=:uID";
        }



        if($stmt = $this->_db->prepare($sql)) {      
            $stmt->bindParam(":uID", $pId , PDO::PARAM_INT);
            $stmt->bindParam(":incr", $incr , PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();
        }

    }
    public function getCardValue($card_delt){

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


        $gamestate = array(
                array(

                    'name'=> 'dealer',
                    'count' => $this->dealer->card_count,
                    'cards'=> json_encode($this->dealer->setUpOrGetHand($gid, 0)),
                    'updateCount' => $this->update
                )
        );

        foreach ($this->players as $player) {

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

        //Dealers Total
        $dealerCount = $this->getHandTotal($gid, 0 );

         //Get dealers hand
       $sql = "SELECT card AS cards
            FROM dealer
            WHERE gameID=:gID";

        if($stmt = $this->_db->prepare($sql)) {

            $stmt->bindParam(":gID", $gid, PDO::PARAM_INT);
            $stmt->execute();
            $myCards = $stmt->fetchAll();
                        
            $stmt->closeCursor();
        }

        $dealerHand = array();
        $newCount = 0;
        foreach ($myCards as $aCard) {

            array_push($dealerHand, $aCard['cards'] );

        }

        $gamestate = array(
                array(

                    'name'=> 'dealer',
                    'count' => $dealerCount,
                    'cards'=> json_encode($dealerHand),
                )
        );

        $allGamePlayers = $this->getPlayersInGame($gid);

        foreach ($allGamePlayers as $player) {

            $thisUsername;


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

                    array_push($myHand, $aCard['cards'] );
                    $newCount = $newCount + $this->getCardValue($aCard['cards']);

                }


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