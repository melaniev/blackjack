<?php


    // Include site constants
    include_once "constants.php";

    // Include Game Manager
    include_once "GameManagement.php";


/*
	I will usually place the following in a bootstrap file or some type of environment
	setup file (code that is run at the start of every page request), but they work 
	just as well in your config file if it's in php (some alternatives to php are xml or ini files).
*/

/*
	Creating constants for heavily used paths makes things a lot easier.
	ex. require_once(LIBRARY_PATH . "Paginator.php")
*/


// defined("LIBRARY_PATH")
// 	or define("LIBRARY_PATH", realpath(dirname(__FILE__) . '/library'));
	
defined("SITE_URL")
	or define("SITE_URL", 'http://localhost:8976');


defined("TEMPLATES_PATH")
	or define("TEMPLATES_PATH", realpath(dirname(__FILE__) . '/templates'));

defined("STYLE_PATH")
	or define("STYLE_PATH", realpath(dirname(__FILE__) . '/css'));


/*
	Error reporting.
*/
ini_set("error_reporting", "true");
error_reporting(E_ALL|E_STRCT);
ini_set("display_errors", 1);

// Start a PHP session
session_start();

// Create a database object
try {

    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
    $db = new PDO($dsn, DB_USER, DB_PASS);
} catch (PDOException $e) {

    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

// Create the Game Manager
global $gmanager;
$gmanager = new Game_Manager();

//Create action log file
$fp = fopen("actionlog.php", "w");
fwrite($fp, "action log is open");
//fclose($fp);

?>