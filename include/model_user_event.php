<?php

require_once 'benchmarks.php';
require_once ('include/model_user.php');

/* Just remember: 
 * $mysqldate = date( 'Y-m-d H:i:s', $phpdate );
 *  $phpdate = strtotime( $mysqldate );
 */

class UserEvent
{                
    
    static public function rsvp($uid, $eid, $ans)
    {        
        global $fp;
    
        
        $db = DB::get()->getConnection();
        $stmt = $db->prepare("INSERT INTO user_event (uid, eid, answer) VALUES  (:uid, :eid, :ans) ON DUPLICATE KEY UPDATE answer = :ans");
        if (!$stmt->execute(array(':uid' => $uid, ':eid' => $eid,':ans' => $ans)))
        {
           $err = "ERROR(UserEvent::rsvp): Update on the server failed!->".join (", ",$stmt->errorInfo());
            error_log($err);
            $fp->log($err);
            return false;     
        }

                
        return true;        
    }  


    static public function getYes($eid)
    {     
        global $fp;
        
        $fp->log("eid: $eid");
        
        $db = DB::get()->getConnection();
                      
        $sql = "SELECT count(answer) as yes FROM user_event WHERE eid = :eid AND anwser = 1";
        $stmt = $db->prepare($sql);
        
        if (!$stmt->execute(array(':eid' =>$eid)))
        {   
            $err = "ERROR(UserEvent::getYes): Update on the server failed!->".join (", ",$stmt->errorInfo());
            error_log($err);
            $fp->log($err);

            return false;
        }
                                        
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$r) 
        {
            $err = "ERROR(UserEvent::getYes): Fetch returned no value";
            error_log($err);
            $fp->log($err);

            return 0;
        } else {
            return $r['yes'];
        }
    } 
    


    static public function getYesUsers($eid)
    {     
        global $fp;
        
        $fp->log("eid: $eid");
        
        $db = DB::get()->getConnection();
                      
        $sql = "SELECT user.*, event.quota as quota FROM user_event, user, event WHERE event.eid =:eid AND user.uid = user_event.uid AND user_event.eid =:eid AND answer = 1";
        $stmt = $db->prepare($sql);
        
        if (!$stmt->execute(array(':eid' =>$eid)))
        {   
            $err = "ERROR(UserEvent::getYes): Update on the server failed!->".join (", ",$stmt->errorInfo());
            error_log($err);
            $fp->log($err);

            return false;
        }                                        
                                    
        $out = Array();

        $i = 0;
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) 
        {         
            if ($i == 0) $o->quota = $r['quota'];
            $out[$r['uid']] = $r;

            ++$i;
        } 
                

        $o->num = count($out); 
        if(!isset($o->quota)) $o->quota = 1000000;
        $o->users = $out;
        return $o;
    } 
    

    
    
}

?>