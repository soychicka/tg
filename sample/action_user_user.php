<?php
 require_once ('benchmarks.php');   
 require_once ('FbAuth.php');
 require_once ('model_user_user.php');
 require_once ('controllers.php');

//###To make more secure ajax call look use this: http://insecureweb.com/web-security/secure-your-ajax-request-with-jquery/

//I create timer object and start it (TRUE)
$timer = new Benchmark_Timer();
$timer->start();

//Establish connection
try {
    DB::get()->connect();
} catch (Exception $e) {
 print_r($e); //### It must be handle better: writing in the log file (that must be set) and 
} 

$u = FbAuth::requireAuthenticate(true, false); //### This must change later to true and take vcare to pass the cookie
if (!$u)
{
    error('Not logged in: Try to refresh the page');
}

//The other user
$v = isset($_REQUEST['v']) ? intval($_REQUEST['v']) : '0';
if (!$v)
{
    error('Error - Other user not specified');
}


//In order to debug XML add "?T3ST=1" in the URL 
if(isset($_REQUEST[$debug_parameter]))
    $XML_debug = true;

//$c = new People();
$fOk = false;

$timer->setMarker('setup');

if (isset($_REQUEST['follow']))
{
    if ($_REQUEST['follow'] == 0) Controller::p_unfollow($u, $v);
    else Controller::p_follow($u, $v);
}
/*
elseif (isset($_REQUEST['colleague']))
{
    if ($_REQUEST['colleague'] == 0) $c->unColleague($u, $v);
    else $c->makeColleague($u, $v);//requestColleague($u, $v);
}*/
else
{
    error('unrecognized command');
}
    
$timer->setMarker('query');

InsertBenchmarkDB($timer);  //Save the data on DB

echo '{"results": "OK"}';

if (isset($XML_debug))
{
    $timer->display(true); // if you want to display immidiately and to output html formated

    //$profiling = $timer->getProfiling(); // get the profiler info as an associative array    
    //echo "<br> This is the array: <br> <pre>";
    //print_r($profiling);
    //echo "</pre><br>";
}

?>