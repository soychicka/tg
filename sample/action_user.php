<?php
 
 /*
 #
 # Update ONE (and only one at the time now) attribute in the article/user relationship 
 # The command line must have a valid command specified :
 #
 # its_mine  (0 remove the article from the list of articles claimed as "mine", 1 add the article to uid user's articles)
 # delete    (0 remove deleted article form the list of the deleted articles, 1 delete the article)
 # readlater (0 remove article form the list of the "read later" articles, add to "read later" article list)
 # follow    (0 unfollow the article, 1 follow the article)
 # pdfread   (contain the number of time the article ha been open (### or probably the link # that has been clicked.).
 # comments  ### Look at the file XXX
 # bookmark  the string identify the article book marked words. "" it is not bookmarked.
 # persnote  the string in the new personal note. "" remove the personal note.
 #
 
 db table:
 
CREATE TABLE `user_article` (
  `tti` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ttu` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `uid` bigint(20) NOT NULL DEFAULT '0',
  `aid` int(11) NOT NULL DEFAULT '0',
  `its_mine` tinyint NOT NULL DEFAULT '0',
  `its_mine_t` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted` tinyint NOT NULL DEFAULT '0',
  `deleted_t` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `read_later` tinyint NOT NULL DEFAULT '0',
  `read_later_t` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `followed` tinyint NOT NULL DEFAULT '0',
  `followed_t` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pdfread` smallint NOT NULL DEFAULT '0',
  `pdfread_t` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',  
  `tot_comments` smallint NOT NULL DEFAULT '0',
  `tot_comments_t` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',  
  `deleted_comments` smallint NOT NULL DEFAULT '0',
  `deleted_comments_t` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',  
  `bookmarked` varchar(100) NOT NULL DEFAULT '',
  `bookmarked_t` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `personal_note` varchar(500) NOT NULL DEFAULT '',
  `personal_note_t` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`uid`,`aid`),
  KEY `key_user_article_its_mine` (`its_mine`),
  KEY `key_user_article_deleted` (`deleted`),
  KEY `key_user_article_read_later` (`read_later`),
  KEY `key_user_article_followed` (`followed`),
  KEY `key_user_article_uid` (`uid`),
  KEY `key_user_article_aid` (`aid`)
);

  `comments` varchar(10) NOT NULL DEFAULT '',
 
 */
 require_once ('benchmarks.php');   
 require_once ('FbAuth.php');
 require_once ('model_user.php');

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

$realUser = 0;
$u = FbAuth::requireAuthenticate(true, false, $realUser); //### This must change later to true and take vcare to pass the cookie
if (!$u)
{
    error('Not logged in: Try to refresh the page');
}


//In order to debug XML add "?T3ST=1" in the URL 
if(isset($_REQUEST[$debug_parameter]))
    $XML_debug = true;

if (isset($_REQUEST['f']) && $_REQUEST['f'] == "position")
{
    $col = 'position_text';
    $value  = $_REQUEST['v'];
}
elseif (isset($_REQUEST['f']) && $_REQUEST['f'] == "institution")
{
    $col = 'institution_text';
    $value  = $_REQUEST['v'];
}
elseif (isset($_REQUEST['f']) && $_REQUEST['f'] == "degrees")
{
    $col = 'degrees_text';
    $value  = $_REQUEST['v'];
}
elseif (isset($_REQUEST['f']) && $_REQUEST['f'] == "research")
{
    $col = 'research_text';
    $value  = $_REQUEST['v'];
}
elseif (isset($_REQUEST['f']) && $_REQUEST['f'] == "honors")
{
    $col = 'honors_text';
    $value  = $_REQUEST['v'];
}
elseif (isset($_REQUEST['f']) && $_REQUEST['f'] == "login")
{    
    $col = 'login';    
    $value  = $_REQUEST['v']; //Look likes if I send a JSON object it gets automatically converted in an array. 
    if (!isset($value['f']) || !isset($value['l']) || !isset($value['v']))
    {
        error('missing parameters');
    }
   
}
//This one should not be called. It is just to insert people in the database for administrative purpouse (see insert_user_in_user_table.php)
elseif (isset($_REQUEST['f']) && $_REQUEST['f'] == "insert_user_XX")
{
    $col = 'login';    
    $value  = $_REQUEST['v']; //Look likes if I send a JSON object it gets automatically converted in an array. 
    if (!isset($value['f']) || !isset($value['l']) || !isset($value['v']) || !isset($value['u']))
    {
        error('missing parameters');
    }
    $u = $value['u'];
}


/*elseif (isset($_REQUEST['']))
{
    $col = '';
    $value  = $_REQUEST[''];
}*/
else
{
    error('unrecognized command');
}


$timer->setMarker('setup');


//print "u: $u, ids_text: $ids_text<br>";



if ($col == 'login')
{
    if ($realUser == $u) //Do not overwrite name in mock mode!
        $good = User::SetDataFromFB($u, $value['f'], $value['l'], $value['v']); 
}
else
{
    if(!User::SetColumn($u, $col, $value))
    {
        error('Problem on the server, this field cannot be updated. Please retry later.');
    } 
}

$timer->setMarker('query');

InsertBenchmarkDB($timer);  //Save the data on DB

echo '{"result": "OK"}';

if (isset($XML_debug))
{
    $timer->display(true); // if you want to display immidiately and to output html formated

    //$profiling = $timer->getProfiling(); // get the profiler info as an associative array    
    //echo "<br> This is the array: <br> <pre>";
    //print_r($profiling);
    //echo "</pre><br>";
}

?>