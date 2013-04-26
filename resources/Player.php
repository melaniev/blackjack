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


  
    // /**
    //  * Checks for a database object and creates one if none is found
    //  *
    //  * @param object $db
    //  * @return void
    //  */
    public function __construct($db=NULL, $sessuser)
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
        $this->bankroll = 0;
        $played_turn = 0;


    }

    public function addCard($card){

        array_push($this->hand, $card );

       
    }
   

}    

?>