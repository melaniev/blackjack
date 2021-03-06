<?php


/* 

User Class

*/

require("/resources/PasswordHash.php");

class BlackjackUser{

    /**
     * The database object
     *
     * @var object
     */
    private $_db;
    public $log;
 
 
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

        $this->log   = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
        $this->log->logInfo('User Object Created');
    }


    public function createUserAccountInfo($un, $pass1){

        $this->log->logInfo('createUserAccountInfo');


        //$hsh_un = $this->hashUsername($un);
        $hsh_pass = $this->hashPassword($pass1);

        $checkAlreadyUsed = $this->checkIfAccountAlreadyExists($un);


        if ($checkAlreadyUsed != -1){

            session_regenerate_id(true);
            $sess = session_id();

            $userID = $this->insertUserInfoIntoDatabase($un, $hsh_pass, $sess);
            $this->giveThemABlankRecord($userID);

            $_SESSION['Username'] = $un;
            $_SESSION['LoggedIn'] = 1;

            return 1;
        }
        else{
            //Sorry that account already exists, do something about it

            return 0;
        }
    }

    public function loginReturningUser($thisUsername, $thisPassword){

        //$return_hsh_un = $this->hashUsername($thisUsername);

        
        $sql = "SELECT blackj_pass
                FROM users
                WHERE username=:user
                LIMIT 1";
        try
        {
            $stmt = $this->_db->prepare($sql);
            $stmt->bindParam(':user', $thisUsername, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch();
            if($stmt->rowCount()==1)
            {

                $returnedHashPass = $row['blackj_pass'];

                $success = $this->checkHashedPassword($returnedHashPass, $thisPassword);

                if ($success) {

                    $a = session_id();
                    if(empty($a)) session_start();

                    $_SESSION['Username'] = $thisUsername;
                    $_SESSION['LoggedIn'] = 1;                    
                }
                //update session
                $sql = "UPDATE users
                        SET sessID = :newSess
                        WHERE username=:uN";

                if($stmt = $this->_db->prepare($sql)) {    
                    $stmt->bindParam(":newSess", $a , PDO::PARAM_STR);  
                    $stmt->bindParam(":uN", $thisUsername , PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt->closeCursor();
                }

                return 1;
            }
            else
            {
                return 0;
            }
        }
        catch(PDOException $e)
        {
            return FALSE;
        }
    }

    /**
     * Checks if a username is already in the database
     * 
     * @return -1 if username not available; 1 if available
     */
    private function checkIfAccountAlreadyExists($hsh_un){

        $sql = "SELECT COUNT(username) AS theCount
                FROM users
                WHERE username=:usern";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":usern", $hsh_un, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch();
            if($row['theCount']!=0) {
                return -1;
            }
            else{

                return 1;
            }

            $stmt->closeCursor();
        }

    }

    /**
     * Inserts a new user into the database
     * 
     * @return 
     */
    private function insertUserInfoIntoDatabase($hsh_un, $hsh_pass, $s){

        $sql = "INSERT INTO users(username, blackj_pass, sessID)
                VALUES(:hun, :bjp, :s)";
        
        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":hun", $hsh_un, PDO::PARAM_STR);
            $stmt->bindParam(":bjp", $hsh_pass, PDO::PARAM_STR);
            $stmt->bindParam(":s", $s, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();
        }

                //Get the Game ID
        $sql = "SELECT MAX(userID) AS nextID
                FROM users";

        if($stmt = $this->_db->prepare($sql)) {
            $stmt->execute();
            $id = $stmt->fetch();
            $next_id = $id['nextID'];
            
            $stmt->closeCursor();

            return $next_id;
        }

    }
    private function giveThemABlankRecord($usnm){

        $this->log->logInfo('giveThemABlankRecord');

        $wins = 0;
        $losses = 0;
        $draws = 0;

        $sql = "INSERT INTO records(userID, wins, losses, draws)
                VALUES(:un, :win, :loss, :drw)";
        
        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":un", $usnm, PDO::PARAM_INT);
            $stmt->bindParam(":win", $wins, PDO::PARAM_INT);
            $stmt->bindParam(":loss", $losses, PDO::PARAM_INT);
            $stmt->bindParam(":drw", $draws, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
        }

    }
    private function checkHashedPassword($stored_hash, $pw){

        $hasher = new PasswordHash(8, false);
        $check = $hasher->CheckPassword($pw, $stored_hash);

        return $check;
    }

    private function hashPassword($pass1){

        $hasher = new PasswordHash(8, false);
        $hash = $hasher->HashPassword($pass1);

        return $hash;
    }


}    

?>