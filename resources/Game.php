<?php


/* 

User Class

*/

class BlackjackGame{

    /**
     * The database object
     *
     * @var object
     */
    private $_db;
    public $_gid;

  
    /**
     * Checks for a database object and creates one if none is found
     *
     * @param object $db
     * @return void
     */
    public function __construct($db=NULL)
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


        //Store this game in the database
        $nowActive = 1;
        $sql = "INSERT INTO games(gameState)
        VALUES(:gstate)";
        
        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":gstate", $nowActive, PDO::PARAM_BOOL);;
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

            $fp = fopen("actionlog.php", "w");
            fwrite($fp, "New Game Created with id " . $next_id);
            fclose($fp);
            
            $stmt->closeCursor();

            $this->_gid = $next_id;


        }


        //Create action log file
        $fp = fopen("actionlog.php", "w");
        fwrite($fp, "New Game Created");
        fclose($fp);
    }

    public function getGameID(){

        return $this->_gid;
    }

    public function addPlayer($gid){

        echo 'Add player called';

        $fp = fopen("actionlog.php", "w");
        fwrite($fp, "Add Player Called in Game");
        fclose($fp);


        $userID = $this->getUserID();
        echo 'UserId is: '.$userID;

        $sql = "INSERT INTO gameplayers(gameID, userID)
                VALUES(:g, :u)";
        
        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":g", $gid, PDO::PARAM_INT);
            $stmt->bindParam(":u", $userID, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
        }


    }
    public function removePlayer(){


    }
    public function dealHand(){

    }

    public function dealCard(){

    }

    private function getUserID(){

        echo 'getUserID called';

        $fp = fopen("actionlog.php", "w");

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

            
            fwrite($fp, "User ID retrieved, id: " . $us_id);
            fclose($fp);
            
            $stmt->closeCursor();

            return $us_id;
        }

    }


}    

?>