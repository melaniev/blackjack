<?php


/* 

User Class

*/

class BlackjackUser{

    /**
     * The database object
     *
     * @var object
     */
    private $_db;
 
 
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
    }


    public function createUserAccountInfo($un, $pass1, $pass2){

        //DO SOME CRYPTOGRAPHY ON THE PASSWORD!!!


        //HASH THE USERNAME TOO!!

        $hsh_un = $this->hashUsername($un);
        $hsh_pass = $this->hashPassword($pass1);

        $checkAlreadyUsed = $this->checkIfAccountAlreadyExists($hsh_un);


        if ($checkAlreadyUsed != -1){

            $this->insertUserInfoIntoDatabase($hsh_un, $hsh_pass);

            return 1;
        }
        else{
            //Sorry that account already exists, do something about it

            return 0;
        }
    }

    public function loginReturningUser($thisUsername, $thisPassword){

        $return_hsh_un = $this->hashUsername($thisUsername);
        $return_hsh_pass = $this->hashPassword($thisPassword);

        $sql = "SELECT username
                FROM users
                WHERE username=:user
                AND blackj_pass=:pass
                LIMIT 1";
        try
        {
            $stmt = $this->_db->prepare($sql);
            $stmt->bindParam(':user', $return_hsh_un , PDO::PARAM_STR);
            $stmt->bindParam(':pass', $return_hsh_pass , PDO::PARAM_STR);
            $stmt->execute();
            if($stmt->rowCount()==1)
            {

                $_SESSION['Username'] = $thisUsername;
                $_SESSION['LoggedIn'] = 1;


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
    private function insertUserInfoIntoDatabase($hsh_un, $hsh_pass){

        $sql = "INSERT INTO users(username, blackj_pass)
                VALUES(:hun, :bjp)";
        
        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":hun", $hsh_un, PDO::PARAM_STR);
            $stmt->bindParam(":bjp", $hsh_pass, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();
        }

    }

    private function hashUsername($un){

        return $un;
    }

    private function hashPassword($pass1){

        return $pass1;
    }
}    

?>