<?php
 
 require_once ('include/benchmarks.php');   
 require_once ('include/MyAuth.php');
 require_once ('include/model_user.php');

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

/* 
$u = MyAuth::checkAuthentication(false); //### This must change later to true and take vcare to pass the cookie
if (!$u)
{
    error('Not logged in');
}
*/

//In order to debug XML add "?T3ST=1" in the URL 
if(isset($_REQUEST[$debug_parameter]))
    $XML_debug = true;

if (isset($_REQUEST['action']))
{
    if ($_REQUEST['action'] == "register")
    {
        $fp->log("register");
        $email = $_REQUEST['email'];
        $pwd = $_REQUEST['pwd'];

        if (!User::RegisterUser($_REQUEST['first_name'], $_REQUEST['last_name'], $email, $pwd, $_REQUEST['gender']))
        {
            echo '{"status":"ERROR","results":{"user":"0", "mess":"Problem on the DB"}}';
        }else{
            $u = User::getByEmail($_REQUEST['email']);
            $uid = $u['uid'];
            MyAuth::setLoginAuthenticate($uid);
            unset($r['pwd']);
            echo '{"status":"OK","results":'.json_encode($u).'}';
        }        
    }
    else if ($_REQUEST['action'] == "login")
    {
        $email = $_REQUEST['email'];
        $pwd = $_REQUEST['pwd'];

        $u = User::CheckUser($email, $pwd);        
        $fp->log("User::CheckUser($email, $pwd)");
        if ($u)
        {
            $uid = $u['uid'];
            $fp->log("uid:$uid");
            MyAuth::setLoginAuthenticate($uid);
            unset($u['pwd']);
            echo '{"status":"OK","results":'.json_encode($u).'}';
        } else {
            echo '{"status":"ERROR","results":{"user":"0", "mess":"login failed"}}';
        }
    }
    else if ($_REQUEST['action'] == "logoff")
    {
         $fp->log("user::logoff");
         MyAuth::logoff();
         echo '{"status":"OK","results":"Successfully disconnected through logoff"}';
    }
    else if ($_REQUEST['action'] == "setlocationplace" || $_REQUEST['action'] == "setlocation")
    {
        $fp->log("setlocationplace");
        $u = MyAuth::checkAuthentication(false);
        if($u) 
        {
            $place = $_REQUEST['place'];
            $fp->log("User::setLocationByText($u, $place)");
            if(User::setLocationByText($u, $place))
            {
                echo '{"status":"OK","results":"{cookie:'.json_encode($_COOKIE).'}"}';
            }
            else
            {
                echo '{"status":"ERROR","results":"Problem on DB", c:{cookie:'.json_encode($_COOKIE).'}}'; 
            }
                                  
        }
        else
        {
            $fp->log("not in");
            echo '{"status":"ERROR","results":"Problem on DB", c:{cookie:'.json_encode($_COOKIE).'}}'; 
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

if (isset($XML_debug))
{
    $timer->display(true); // if you want to display immidiately and to output html formated

    //$profiling = $timer->getProfiling(); // get the profiler info as an associative array    
    //echo "<br> This is the array: <br> <pre>";
    //print_r($profiling);
    //echo "</pre><br>";
}

?>