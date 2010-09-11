<?php
 
 require_once ('include/benchmarks.php');   
 require_once ('include/MyAuth.php');
 require_once ('include/model_event.php');

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


function aq($val)
{
    return trim($val,"\x22\x27");

}

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
    if ($_REQUEST['action'] == "createevent")
    {
        $fp->log("createevent");

        $name = aq($_REQUEST['name']);
        $description = aq($_REQUEST['description']);
        $time = aq($_REQUEST['time']);
        $owner = $u;
        $phone_number = aq($_REQUEST['phone_number']);
        $hide_lastname = aq($_REQUEST['hide_lastname']);
        $quota = aq($_REQUEST['quota']);
        $gender = aq($_REQUEST['gender']);       
        $location = aq($_REQUEST['location']);

        if (!Event::createEvent($owner, $name, $description, $location, $time,  $phone_number, $hide_lastname, $quota, $gender))
        {
            echo '{"status":"ERROR","results":"Problem on DB"}';
        }else{
            echo '{"status":"OK","results":"DONE"}';
        }        
    }
    else if ($_REQUEST['action'] == "getuserevents")
    {
        $fp->log("getuserevents");
        
        $o = Event::getUserEvents($u);
        if ($o)
        {
            echo json_encode($o);
        }
        else
        {
            error("Problem with the DB");
        }

    }
    else if ($_REQUEST['action'] == "getnearbyevents")
    {
        $fp->log("getnearbyevents");
        $radius = $_REQUEST['radius'];
        $gender = $_REQUEST['gender'];
        
        $o = Event::getNearbyEvents($u, $radius, $gender);
        if ($o)
        {
            echo json_encode($o);
        }
        else
        {
            error("Problem with the DB");
        }

    }

    else if ($_REQUEST['action'] == "getlastevents")
    {
        $fp->log("getlastevents");

        $lon = $_REQUEST['lon'];
        $lat = $_REQUEST['lat'];
        $radius = isset($_REQUEST['radius']) ? $_REQUEST['radius'] : 20000;
        $start = isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
        $n = isset($_REQUEST['n']) ? $_REQUEST['n'] : 20;
        

        
        $o = Event::getLastEvents($lon, $lat, $radius, $start, $n);
        if ($o)
        {
            echo json_encode($o);
        }
        else
        {
            error("Problem with the DB");
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