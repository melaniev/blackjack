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

  
    // /**
    //  * Checks for a database object and creates one if none is found
    //  *
    //  * @param object $db
    //  * @return void
    //  */
    public function __construct($db=NULL, $id=NULL)
    {

        echo "<p>Deck constructor</p>";        

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

        $this->cards = array("01_club", "02_club", "03_club", "04_club", "05_club", "06_club", "07_club", "08_club", "9_club", "10_club", "11_club", "12_club",
                            "01_heart", "02_heart", "03_heart", "04_heart", "05_heart", "06_heart", "07_heart", "08_heart", "09_heart", "10_heart", "11_heart", "12_heart",
                            "01_spade", "02_spade", "03_spade", "04_spade", "05_spade", "06_spade", "07_spade", "08_spade", "09_spade", "10_spade", "11_spade", "12_spade",
                            "01_diamond", "02_diamond", "03_diamond", "04_diamond", "05_diamond", "06_diamond", "07_diamond", "08_diamond", "09_diamond", "10_diamond", "11_diamond", "12_diamond",
            );

        shuffle($this->cards);

        echo "shuffled cards: ";
        print_r($this->cards);

    }

    public function deal(){

        $new_card = array_shift($this->cards);

        return $new_card;
    }

}    

?>