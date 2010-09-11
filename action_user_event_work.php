<?php
 
 require_once ('include/benchmarks.php');   
 require_once ('include/MyAuth.php');
 require_once ('include/model_user_event.php');

/*
//I create timer object and start it (TRUE)
$timer = new Benchmark_Timer();
$timer->start();

//Establish connection
try {
 DB::get()->connect();
} catch (Exception $e) {
 print_r($e); //### It must be handle better: writing in the log file (that must be set) and 
} 
*/


$u = MyAuth::checkAuthentication(false); //### This must change later to true and take vcare to pass the cookie
$fp->log($u);
if (!$u)
{
    error('Not logged in');
}

//In order to debug XML add "?T3ST=1" in the URL 
if(isset($_REQUEST[$debug_parameter]))
    $XML_debug = true;

if (isset($_REQUEST['action']))
{
    if ($_REQUEST['action'] == "rsvp")
    {
        $fp->log("rsvp");

        $eid = $_REQUEST['eid'];     
        if(isset($_REQUEST['ans']) && $_REQUEST['ans'] == 'yes')
            $ans = 1;
        else if(isset($_REQUEST['ans']) && $_REQUEST['ans'] == 'no')
            $ans = -1;
        else
            error('rsvp needs "ans" set to "yes" or "no"');
        

        if (!UserEvent::rsvp($u, $eid, $ans))
        {
            echo '{"status":"ERROR","results":"Problem on DB"}';
        }else{
            echo '{"status":"OK","results":"DONE"}';
        }        
    }
    else
    {
        error('action unrecognized');
    }
}    
else
{
    error('no action specified');
}


//$timer->setMarker('setup');



//$timer->setMarker('query');
//InsertBenchmarkDB($timer);  //Save the data on DB

//echo '{""status": "OK",  "results": "Done"}';
/*
if (isset($XML_debug))
{
    $timer->display(true); // if you want to display immidiately and to output html formated

    //$profiling = $timer->getProfiling(); // get the profiler info as an associative array    
    //echo "<br> This is the array: <br> <pre>";
    //print_r($profiling);
    //echo "</pre><br>";
}
*/
?>