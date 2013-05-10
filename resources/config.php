<?php


    // Include site constants
    include_once "constants.php";

    // Include Game Manager
    include_once "GameManagement.php";

	
defined("SITE_URL")
	or define("SITE_URL", 'https://localhost:44300');


defined("TEMPLATES_PATH")
	or define("TEMPLATES_PATH", realpath(dirname(__FILE__) . '/templates'));

defined("STYLE_PATH")
	or define("STYLE_PATH", realpath(dirname(__FILE__) . '/css'));


/*
	Error reporting.
*/
ini_set("error_reporting", "true");
error_reporting(E_ALL|E_STRCT);
ini_set("display_errors", 0);

// Start a PHP session
session_start();

setcookie("sid",                // Name
          session_id(),         // Value
          strtotime("+1 hour"), // Expiry
          "/",                  // Path
          ".wblinks.com",       // Domain
          true,                 // HTTPS Only
          true);                // HTTP Only

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
$gmanager = Game_Manager::Instance();

?>