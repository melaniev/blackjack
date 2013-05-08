<?php


/* 

Deck Class

*/

class Deck{

    /**
     * The database object
     *
     * @var object
     */
    private $_db;
    private $cards;
    public $log;

  
    // /**
    //  * Checks for a database object and creates one if none is found
    //  *
    //  * @param object $db
    //  * @return void
    //  */
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

        // //if this is an existing game, get the deck generate from the hands

        $this->cards = array("01_club", "02_club", "03_club", "04_club", "05_club", "06_club", "07_club", "08_club", "09_club", "10_club", "11_club", "12_club", "13_club",
                            "01_heart", "02_heart", "03_heart", "04_heart", "05_heart", "06_heart", "07_heart", "08_heart", "09_heart", "10_heart", "11_heart", "12_heart", "13_heart",
                            "01_spade", "02_spade", "03_spade", "04_spade", "05_spade", "06_spade", "07_spade", "08_spade", "09_spade", "10_spade", "11_spade", "12_spade", "13_spade",
                            "01_diamond", "02_diamond", "03_diamond", "04_diamond", "05_diamond", "06_diamond", "07_diamond", "08_diamond", "09_diamond", "10_diamond", "11_diamond", "12_diamond", "13_diamond"
            );

        shuffle($this->cards);

        $this->log   = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);

    }

    public function deal($game=NULL){

        //Get the cards that are left in the deck

        if ($game == NULL) {

            $new_card = array_shift($this->cards);
        }else{

            $this->log->logInfo('Deal called from a hit with game id: ', $game);

            $cardsLeft = array("01_club", "02_club", "03_club", "04_club", "05_club", "06_club", "07_club", "08_club", "09_club", "10_club", "11_club", "12_club", "13_club",
                            "01_heart", "02_heart", "03_heart", "04_heart", "05_heart", "06_heart", "07_heart", "08_heart", "09_heart", "10_heart", "11_heart", "12_heart", "13_heart",
                            "01_spade", "02_spade", "03_spade", "04_spade", "05_spade", "06_spade", "07_spade", "08_spade", "09_spade", "10_spade", "11_spade", "12_spade", "13_spade",
                            "01_diamond", "02_diamond", "03_diamond", "04_diamond", "05_diamond", "06_diamond", "07_diamond", "08_diamond", "09_diamond", "10_diamond", "11_diamond", "12_diamond", "13_diamond"
            );

            //Get the cards that are left in the deck

            //First get rid of all the players hands
                $sql = "SELECT card AS cards
                        FROM hand
                        WHERE gameID=:gID";

                if($stmt = $this->_db->prepare($sql)) {

                    $stmt->bindParam(":gID", $game, PDO::PARAM_INT);
                    $stmt->execute();
                    $myCards = $stmt->fetchAll();
                                
                    $stmt->closeCursor();
                }

                foreach ($myCards as $aCard) {

                    $card = $aCard['cards'];
                    if (in_array($card, $cardsLeft )) {

                        $index = array_search($card, $cardsLeft);
                        
                        array_splice($cardsLeft, $index, 1);
                    }

                }

                //get rid of the cards in the dealers hand too
                $sql = "SELECT card AS cards
                    FROM dealer
                    WHERE gameID=:gID";

                if($stmt = $this->_db->prepare($sql)) {

                    $stmt->bindParam(":gID", $game, PDO::PARAM_INT);
                    $stmt->execute();
                    $myCards = $stmt->fetchAll();
                                
                    $stmt->closeCursor();
                }

                foreach ($myCards as $aCard) {

                    $card = $aCard['cards'];
                    if (in_array($card, $cardsLeft )) {

                        $index = array_search($card, $cardsLeft);
                        
                        array_splice($cardsLeft, $index, 1);
                    }

                }
                $cardsLeftCount = count($cardsLeft);
                $this->log->logInfo('There are this many cards remaining in the deck ', $cardsLeftCount);
                $new_card = array_shift($cardsLeft);

            }
        
        return $new_card;
    }

}    

?>