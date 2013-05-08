<?php



require_once("/resources/GameController.php");
require_once("/resources/Klogger.php");

$log   = KLogger::instance(dirname(__FILE__), KLogger::DEBUG);
$log->logInfo('Move.php');

    if($_POST){

        if(isset($_POST['name'])){

            $playtype = $_POST['name'];

            if ($playtype == 'Hit') {
                
                hit($_SESSION['GameID']);
                $log->logInfo('Hit in Move.php');

            }
            if ($playtype == 'Stay') {
               
               stay();
               $log->logInfo('Stay in Move.php');

            }
        }
        
    }




?>