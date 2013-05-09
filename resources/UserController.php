<?php


/* 

User Controller Class

*/

require_once("/resources/User.php");

class BlackjackUser_Controller{


    /**
     * 
     *
     * 
     * 
     */
    public function __construct()
    {
    	

    }

    function loginAccount($db=NULL, $thisUsername, $thisPassword){

        //FILTER THE INPUT HERE

        $returningUser = new BlackjackUser($db);
        $loginSuccess = $returningUser->loginReturningUser($thisUsername, $thisPassword);

        return ($loginSuccess == 1) ? 1 : 0;

    }

    function createAccount($db=NULL, $un, $pass1, $pass2){

    	
        //COMPARE THE PASSWORDS TO MAKE SURE THEY ARE THE SAME
        if ($pass1 == $pass2) {
            
            //preg_match("/^[A-Za-z][A-Za-z0-9!@#$%_]{8,20}$/", $newUsername);
            if(preg_match("/^[A-Za-z][A-Za-z0-9!@#$%_]{8,20}$/", $pass1)){

                //Create a new User
                $newUser = new BlackjackUser($db);
                $success = $newUser->createUserAccountInfo($un, $pass1);

                return ($success == 1) ? 1 : 0;
            }

            return 0;

        }

        return 0;

    }

    function getRecord($db=NULL){



    }

}

?>