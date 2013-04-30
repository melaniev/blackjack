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

    public $log;


  
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
        $this->played_turn = 0;
        $this->card_count = 0;

        $this->log   = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
        $this->log->logInfo('New Player Created');

    }

    public function addCard($card){

         $this->log->logInfo('addCard called in Player.php');

        array_push($this->hand, $card );

       
    }
    public function addToCardTotal($cardvalue){

        $this->card_count = $this->card_count + $cardvalue;

    }  

}    

?>