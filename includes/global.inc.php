<?php

require_once('config.php');
require_once('en.inc.php');
require_once(PATH . '/libs/MySql/mySqli.php');
date_default_timezone_set('Asia/Kolkata');

if (DEBUG) {                      // application in debug mode
   ini_set('display_errors', 'On');
   error_reporting(E_ALL);         // show all notification
} else
   error_reporting(0);      // show only error

$objDB = new stdClass();
$objDB = getDBObj();

/* Create generic object */
function getDBObj()
{
   try {
      if (class_exists('mysqli')) {
         $objDB = new mySqliDB();
         $objDB->connect();
         $objDB->selectDB();
      } else throw new Exception('MySqli Class is not enabled');
   } catch (Exception $e) {
      echo '<h1>Database Maintenance</h1>' .
         'The database is presently undergoing emergency maintenance and will be down ' .
         'for a few more minutes.  We apologize for the delay.' .
         '<br><br>If the maintenance is taking too long, or you feel there may ' .
         'be an immediate problem, please contact the webmaster.';
      exit;
   }

   return $objDB;
}
