<?php
//header('Content-Type: text/html; charset=utf-8');
//header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');


//////////////////////////////////////////////////////////////////////////////////////

include_once 'config.php';
include_once ('FirePHPCore/FirePHP.class.php');


//////////////////////////////////////////////////////////////////////////////////////




//
//Debug feature
//

function SetAdminDebug($a, $b = NULL)
{
    global $debug_cookie;
    

    $fp = FirePHP::getInstance(TRUE);
    
    $fp->setEnabled(TRUE);

    if (is_null($b))
    {
        $c = $debug_cookie;
        $v = $a;
    } else {
        $c = $debug_cookie.'_'.$a;
        $v = $b;
    }
        
    setcookie($c,  $v, time() + 172800, $path, $domainName); //Expire in 2 days!

//    $fp->group('SetAdminDebug');
//    $fp->log( "SetAdminDebug ($a,$b): c = $c" );
//    $fp->log( "setcookie($c,  $v, ...)");
//    $fp->groupEnd();

}

function GetAdminDebug($a = NULL)
{
    global $debug_cookie;
    
    if (is_null($a))
    {
        $c = $debug_cookie;        
    } else {
        $c = $debug_cookie.'_'.$a;        
    }
        
    return isset($_COOKIE[$c]) ? $_COOKIE[$c] : 'NONE'; 
}

$fp = FirePHP::getInstance(TRUE);

$debugOn = FALSE;

if (GetAdminDebug() == 'Y') 
{
   $debugOn = TRUE;
   $fp->setEnabled(TRUE);
//   $fp->log(GetAdminDebug('obstart'), 'obstart Cookie');
   if (GetAdminDebug('obstart') > 0) ob_start();
}
else
{
   $fp->setEnabled(FALSE);
}

$debugOn = TRUE;//FALSE;
$fp->setEnabled(TRUE);


function cache_burner()
{ //Keep it simple now. Later we will have something different for production and development
    return '?'.rand();
}


function TRACE($a, $b)
{
    global $fp;
    $fp->log ("TRACE($a): $b");
}

// array_intersect that splits the needle array into two - one filled with "intersected" results, and one filled with the remainder
function array_intersect_split(&$needle, $haystack, $preserve_keys = false) {
    if(!is_array($needle) || !is_array($haystack)) return false;
    $new_arr = array();
    foreach($needle as $key => $value) {
        if(($loc = array_search($value, $haystack))!==false) {
            if(!$preserve_keys) $new_arr[] = $value;
            else $new_arr[$key] = $value;
            unset($needle[$key]);
        }
    }
    return $new_arr;
}


/////////////////////////////////////////////////////////////////////////////////////////
//
/////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////


# Access to GET/POST/COOKIE parameters the easy way
function g($param) {
    global $_GET, $_POST, $_COOKIE;

    if (isset($_COOKIE[$param])) return $_COOKIE[$param];
    if (isset($_POST[$param])) return $_POST[$param];
    if (isset($_GET[$param])) return $_GET[$param];
    return false;
}

function gt($param) {
    $val = g($param);
    if ($val === false) return false;
    return trim($val);
}


/////////////////////////////////////////////////////
//    I am adding here some utility function they may be move somewhere else

function NoResult($mess = null)
{
    global $timer;

    if ($mess !== null)
        echo '{"status": "NORESULT", "results": "'.$mess.'"}';
    else
        echo '{"status": "NORESULT"}';    

    if(isset($timer))
    {
        $timer->setMarker('NORESULT');
        InsertBenchmarkDB($timer);  //Save the data on DB
    }
    die();
}

function error($e = null)
{
    global $timer;

    error_log("ERROR: $e");
    if ($e !== null)
        echo '{"status": "ERROR", "results":"'.$e.'"}';
    else
        echo '{"status": "ERROR"}';
    if(isset($timer))
    {
        $timer->setMarker('ERROR');
        InsertBenchmarkDB($timer);  //Save the data on DB
    }
    die();
}

//Log on the log file the error associate with $stms and return a JSON with a nice error. problem on the database 
function myPDOerror($stmt, $extra = null, $returnJSON = true)
{      
    ob_start();
     echo 'TJC ERROR: ';
     if ($extra)
         echo $extra;
     print_r($stmt->errorInfo());
     $stmt->debugDumpParams();
    $out = ob_get_clean();
    error_log($out);

    if($returnJSON) error("Problem on the Database. A message has been sent to the Administator. We will take care of the Problem as soon as possible. Thank you for your patience.");
}

//execute the statement and if there is a problem write on the log file and return a nice JSON error to the user.
function myPDOexecute($stmt, $param = null)
{
    if(!$stmt->execute($param))
    {
        myPDOerror($stmt);
    }
}


?>