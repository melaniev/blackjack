<?php


/* 

Game Manager Class

*/



class Game_Manager{


    private static $counter = 0;
    private static $game_holder = array(50);
    
    final public function __construct() {
        if (self::$counter) {
            throw new Exception('Cannot be instantiated more than once');
        }
        self::$counter++;
        

    }

   public function requestNewGame(){

        echo 'hhiiiiiiiiiiiiiiiii';

    }



}

?>