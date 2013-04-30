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

    	//FILTER THE INPUT HERE

        //COMPARE THE PASSWORDS TO MAKE SURE THEY ARE THE SAME

    	//Create a new User
    	$newUser = new BlackjackUser($db);
    	$success = $newUser->createUserAccountInfo($un, $pass1);

        echo "Success = ". $success;

    	return ($success == 1) ? 1 : 0;

    }


}

?>