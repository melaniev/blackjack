<?php


/* 

User Controller Class

*/

include_once "config.php";
require_once("/resources/Game.php");
require_once("/resources/GameManagement.php");

class BlackjackGame_Controller{


    /**
     * 
     *
     * 
     * 
     */
    public function __construct()
    {
    	

    }

    public function createANewGame($db=NULL){

        $gmanager = new Game_Manager($db);
        $gmanager->requestNewGame();

    }
    public function joinGame($db=NULL){

        $gmanager = new Game_Manager($db);
        $gmanager->joinExistingGame();

    }



}

?>