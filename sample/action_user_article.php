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
 require_once ('model_user_article.php');
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

$u = FbAuth::requireAuthenticate(true, true); //### This must change later to true and take vcare to pass the cookie
if (!$u)
{
    error('Not logged in: Try to refresh the page');
}


//In order to debug XML add "?T3ST=1" in the URL 
if(isset($_REQUEST[$debug_parameter]))
    $XML_debug = true;


$a = isset($_REQUEST['a']) ? intval($_REQUEST['a']) : '0';
//$u = isset($_REQUEST['u']) ? $_REQUEST['u'] : '0';


if (isset($_REQUEST['mine']))
{
    $col = 'its_mine';
    $value  = !!$_REQUEST['mine'];
    //###Should claiming an article means also automatically follow it? 
    Controller::a_claim($u, $a);
}
elseif (isset($_REQUEST['delete']))
{
    $col = 'deleted';
    $value  = !!$_REQUEST['delete'];
}
elseif (isset($_REQUEST['readlater']))
{
    $col = 'read_later';
    $value  = !!$_REQUEST['readlater'];
}
elseif (isset($_REQUEST['follow']))
{
    $col = 'followed';
    $value  = !!$_REQUEST['follow'];
    Controller::a_follow($u, $a);
}
elseif (isset($_REQUEST['read']))
{
    $col = 'pdfread';
    //### it may be necessary to sanitize the input.. but prepare/execute may not need it
    //### it may be necessary to decide if adding just one to the field.
    $value  = $_REQUEST['read'];
}
elseif (isset($_REQUEST['bookmark']))
{
    $col = 'bookmarked';
    //### it may be necessary to sanitize the input.. but prepare/execute may not need it
    $value  = $_REQUEST['bookmark'];
}
elseif (isset($_REQUEST['comments']))
{
    //### need to be decide how it works
    $col = 'comments';
    $value  = $_REQUEST['comments'];
}
elseif (isset($_REQUEST['persnote']))
{
    $col = 'personal_note';
    //### it may be necessary to sanitize the input.. but prepare/execute may not need it
    $value  = $_REQUEST['persnote'];
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


if (!UserArticle::setColumn($u, $a, $col, $value))
{//Probably it doesn't exist
    error('Update on the server failed!');
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