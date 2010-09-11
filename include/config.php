<?php
//header('Content-Type: text/html; charset=utf-8');
//header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

/*
    Ex: of the REST-like API

    http://www.togethr.us/action_user?action=setlocation&place=palo+alto,Ca
    http://www.togethr.us/action_event?action=getnearbyevents
    http://www.togethr.us/action_event?action=createevent&name=soccer&description=fun+play&location=mountain+view&time=10008&phone_number=911&hide_lastname=0&quota=10

*/




//Fix the dreamhost PEAR crap
ini_set( 
  'include_path', 
  ini_get( 'include_path' ) . PATH_SEPARATOR . "/home/togethr/.pear/usr/local/php5/lib/pear/"
);  
$pear_user_config = '/home/togethr/.pearrc';


ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);
ini_set('log_errors',TRUE);
ini_set('html_errors',FALSE);
ini_set('error_log','/home/togethr/logs/php_logs.txt');
ini_set('display_errors',TRUE);


//////////////////////////////////////////////////////////////////////////////////////
// IMPORTANT: Other parameters need to be changed in MyAuth.php to make the app secure
//////////////////////////////////////////////////////////////////////////////////////



//
//  Base directories URL and paths
//
$homePath        = '/home/togethr/';               //user home folder on the server
$localBasePath   = '/home/togethr/togethr.us/';    //base web directory on the server
$domainName      = 'togethr.us';
$path            = '/togethr.us/';
$baseCallbackURL = 'http://togethr.us/';   //Callback called by Facebook that point to the address.
$appLoginUrl     = $baseCallbackURL.'login';
$appAfterLoginUrl= $baseCallbackURL;

//
//Admin ID
//

$adminUserIDs = array('1', '2'); //### Need changes

         
//
//DB Connections for cannection to MySQL through PDO
//   
$strDB_DSN      = 'mysql:dbname=togethr_store;host=server.togethr.us';
$strDB_Username = 'togethr_db';
$strDB_Password = 'db_admin_135';


//
//Debug parameters
//

$debug_parameter = 'T3ST'; //The "magic" parameter that if added produce extra info (add ?...&T3ST=1 to get extra debug info)
$debug_cookie = 'myDebugCoOkIe'; //The "magic" parameter that if added produce extra info (add ?...&T3ST=1 to get extra debug info)


?>