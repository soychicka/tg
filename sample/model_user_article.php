<?php

require_once ('benchmarks.php');

/* Just remember: 
 * $mysqldate = date( 'Y-m-d H:i:s', $phpdate );
 *  $phpdate = strtotime( $mysqldate );
 */

class UserArticle
{
	//private static $useMC = false; //Use memcahed (default true)
	
	//Return the a string of a comma separated articles or (true putting the array in  $ids_array is $ids_array is not NULL)
	static public function getArticlesIds($UserId, $where = NULL, &$ids_array = NULL)	
	{		
		global $fp;
		
		$db = DB::get()->getConnection();

		$sql = "SELECT GROUP_CONCAT(aid SEPARATOR ',') AS a FROM user_article WHERE uid = :u";
		if (isset($where)) $sql.= " AND $where";
		
		$stmt = $db->prepare($sql);
		
		if(!$stmt->execute(array(':u' => $UserId)))
			return false;		
		
		$r = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$r) 
		{
			return false;
		}

//		$fp->log("getArticlesIds($UserId, $where = NULL, xxx): r[a]=".$r['a']);
						
		if (!isset($ids_array))
		{
			return $r['a'];
		} else {
			if (strlen($r['a'])>0)
				$ids_array = explode(',', $r['a']);
			else // If the string is empty set an empty array. The explode will set a 1 element array
				$ids_array = array();
			return true;
		}				
	}

	static public function setColumn($u, $a, $col, $value)	
	{		
		$db = DB::get()->getConnection();

		$stmt = $db->prepare("INSERT INTO user_article (tti, uid, aid, $col, {$col}_t) VALUES  (NOW(), :u, :a, :v, NOW()) ON DUPLICATE KEY UPDATE $col=:v, {$col}_t=NOW()");
		return !!$stmt->execute(array(':u' => $u, ':a' => $a, ':v' => $value));		
	}	

	//Get number of followers of a few article contained in a comma separated string $ids_text and return them in $flwrs
	static public function getArticleFollowers($ids_text, &$flwrs)	
	{		
		$db = DB::get()->getConnection();
		
		/*
		   Note: ###
		
		   The result is truncated to the maximum length that is given by the "group_concat_max_len" system variable, which has a default value of 1024.
		   The value can be set higher, although the effective maximum length of the return value is constrained by the value of "max_allowed_packet" (default 1048576). 
		   The syntax to change the value of group_concat_max_len at runtime is as follows, where val is an unsigned integer: 
		   SET [GLOBAL | SESSION] group_concat_max_len = val;
		*/
		
		//Followers for each article
		$sql = "SELECT aid, count(uid) AS num, GROUP_CONCAT(uid SEPARATOR ',')  AS ids FROM user_article WHERE aid in ($ids_text) AND followed = 1 GROUP BY aid";		
		$stmt = $db->prepare($sql);  
		
		if(!$stmt->execute())
			return false;								
		
		$flwrs = Array();     //This array contains the info about the followers
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
		{
		  $aid = $row['aid'];
		  $flwrs[$aid] = $row;  
		}
		
		return true;  		
	}
	
	//Get number of comments of a few article contained in a comma separated string $ids_text and return them in comts
	static public function getArticleComments($ids_text, &$cmnts)	
	{		
		$db = DB::get()->getConnection();
		
		$sql = "SELECT aid, sum(tot_comments) AS num, GROUP_CONCAT(uid SEPARATOR ',') AS ids FROM user_article WHERE aid in ($ids_text) AND (tot_comments >= 1 OR deleted_comments >= 1) GROUP BY aid";
		$stmt = $db->prepare($sql);

		if(!$stmt->execute())
			return false;								
						
		$cmnts = Array();     //This array contains the info about the followers
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
		{
		  $aid = $row['aid'];
		  $cmnts[$aid] = $row;  
		}  		
		
		return true;
	}

	//Get number of xxx of a few article contained in a comma separated string $ids_text and return them in $articles
	//###Generalize getArticleComments and getArticleFollowers by passing the "where" statement
	static public function getArticleWith($ids_text, &$articles, $where_cond)	
	{		
		//### To be TESTED
		$db = DB::get()->getConnection();
		
		$sql = "SELECT aid, count(uid) AS num, GROUP_CONCAT(uid SEPARATOR ',') AS ids FROM user_article WHERE aid in ($ids_text) AND ($where_cond) GROUP BY aid";
		$stmt = $db->prepare($sql);

		if(!$stmt->execute())
			return false;								
						
		$articles = Array();     //This array contains the info about the followers
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
		{
		  $aid = $row['aid'];
		  $articles[$aid] = $row;  
		}  		
		
		return true;		
	}
	
	
	//Get augmented article data for a few article contained in a comma separated string $ids_text associated to user $u
	static public function getArticleData($u, $ids_text, &$ua)	
	{	
		global $fp;
		
		$db = DB::get()->getConnection();
		//Augmented data for each article
		
		$ignore_deleted_sql = !$ignore_deleted ? "" : 'and deleted=0'; 
		
		$sql = "SELECT aid, its_mine, deleted, read_later, followed, pdfread, bookmarked, personal_note FROM user_article WHERE uid = :u $ignore_deleted_sql and aid IN ($ids_text)";
		
		$fp->log("getArticleData SQL: $sql");
		$stmt = $db->prepare($sql);
		if(!$stmt->execute(array(':u' => $u)))
			return false;								
								
		$fp->log("ua = Array();");			
		$ua = Array();     //This array contains the whole row index by article number
//		$toDel = Array();  //This array contain the article to delete 
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
		{		  
		  $aid = $row['aid'];
//		  $fp->log("while: $aid");
		  $ua[$aid] = $row;
//		  if ($row['deleted']) $toDel[] = $aid;
		}  
		
		return true;
	}
	
	static public function getArticleTitle($aid)
	{
		return redisLink()->get("aid:$aid:ArticleTitle");
	}
	
	//Return the recommended article for user $u
	static public function getRecommendedArticles($u, &$res)	
	{		
		//This is just a n experiment so far
		$db = DB::get()->getConnection();

		$stmt = $db->prepare('SELECT aid FROM user_article_recommendation WHERE uid = :u ORDER BY neighbor_score DESC LIMIT 10');
		if(!$stmt->execute(array(':u' => $u)))
			return false;		
		
		$res = array();
	    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
	    {
	        $res[] = $row['aid']; 
	    }    
	    return true;
		
	}	
	
	
}

?>