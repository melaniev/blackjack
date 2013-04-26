<?php



require_once("/resources/GameController.php");
require_once("/resources/Klogger.php");

$log   = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
$log->logInfo('Move.php');

echo 'blah blah';

stay();

    if($_POST){

        updateBoard('\n In move...POST is' . $_POST['name'] );

        if(isset($_POST['name'])){

            $playtype = $_POST['name'];

            if ($playtype == 'Hit') {
                
                hit();
                $log->logInfo('Hit in Move.php');

            }
            if ($playtype == 'Stay') {
               
               stay();
               $log->logInfo('Stay in Move.php');

            }
        }
        
    }




?>