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

        $this->cards = array("1_club", "2_club", "3_club", "4_club", "5_club", "6_club", "7_club", "8_club", "9_club", "10_club", "11_club", "12_club",
                            "1_heart", "2_heart", "3_heart", "4_heart", "5_heart", "6_heart", "7_heart", "8_heart", "9_heart", "10_heart", "11_heart", "12_heart",
                            "1_spade", "2_spade", "3_spade", "4_spade", "5_spade", "6_spade", "7_spade", "8_spade", "9_spade", "10_spade", "11_spade", "12_spade",
                            "1_diamond", "1_diamond", "1_diamond", "1_diamond", "1_diamond", "1_diamond", "1_diamond", "1_diamond", "1_diamond", "1_diamond", "1_diamond", "1_diamond",
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