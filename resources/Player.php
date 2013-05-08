<?php


/* 

Player Class

*/

class Player{

    /**
     * The database object
     *
     * @var object
     */
    private $_db;
    public $hand = array();
    public $bankroll;
    public $username;
    public $played_turn;
    public $card_count;
    public $has_ace;
    public $uID;
    public $gID;

    public $log;


  
    // /**
    //  * Checks for a database object and creates one if none is found
    //  *
    //  * @param object $db
    //  * @return void
    //  */
    public function __construct($db=NULL, $sessuser, $uid, $gid)
    {

        echo "<p>Player constructor</p>";        

        if(is_object($db))
        {
            $this->_db = $db;
        }
        else
        {
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
            $this->_db = new PDO($dsn, DB_USER, DB_PASS);
        }

        $this->username = $sessuser;
        $this->uID =  $uid;
        $this->gID =  $gid;
        $this->bankroll = 0;
        $this->played_turn = 0;
        $this->card_count = 0;

        $this->log   = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
        $this->log->logInfo('New Player Created for game', $gid);
        $this->log->logInfo('and with User ID', $uid);


    }

    public function addCard($card){

         $this->log->logInfo('addCard called in Player.php for user: ', $this->uID);

        array_push($this->hand, $card );

        $cardcol = count($this->hand);

        if ($this->uID != 0) {

             $sql = "INSERT INTO hand(gameID, userID, card)
                    VALUES(:gid, :uid, :car)";
            
            if($stmt = $this->_db->prepare($sql)) {

                $stmt->bindParam(":gid", $this->gID, PDO::PARAM_INT);
                $stmt->bindParam(":uid", $this->uID, PDO::PARAM_INT);
                $stmt->bindParam(":car", $card, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->closeCursor();
            }           # code...
        }else{
            //its the dealer!
             $sql = "INSERT INTO dealer(gameID, card)
                    VALUES(:gid, :car)";
            
            if($stmt = $this->_db->prepare($sql)) {

                $stmt->bindParam(":gid", $this->gID, PDO::PARAM_INT);
                $stmt->bindParam(":car", $card, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->closeCursor();
            }                
        }


       
    }
    public function addToCardTotal($cardvalue){

        $this->card_count = $this->card_count + $cardvalue;

    } 
    public function getMyHand(){

    }
    public function setUpOrGetHand($gid, $aUID){

        //Does this user/game hand already have a row in the 'ol db?

        $this->log->logInfo(' setUpOrGetHand user ID:', $this->uID);
        $this->log->logInfo(' setUpOrGetHand and game ID:', $gid);

        if ($aUID != 0) {

            $sql = "SELECT card AS cards
                    FROM hand
                    WHERE gameID=:gID AND userID = :uID";

            if($stmt = $this->_db->prepare($sql)) {

                $stmt->bindParam(":gID", $this->gID, PDO::PARAM_INT);
                $stmt->bindParam(":uID", $this->uID , PDO::PARAM_INT);
                $stmt->execute();
                $myCards = $stmt->fetchAll();
                            
                $stmt->closeCursor();
            }
        }else{
            //Its the dealer!
            $sql = "SELECT card AS cards
                    FROM dealer
                    WHERE gameID=:gID";

            if($stmt = $this->_db->prepare($sql)) {

                $stmt->bindParam(":gID", $this->gID, PDO::PARAM_INT);
                $stmt->execute();
                $myCards = $stmt->fetchAll();
                            
                $stmt->closeCursor();
            }

        }

        $myHand = array();

        foreach ($myCards as $aCard) {

            array_push($myHand, $aCard['cards'] );

        }

        return $myHand;
    }

}    

?>