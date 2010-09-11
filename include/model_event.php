<?php

require_once 'benchmarks.php';
require_once ('include/model_user.php');

/* Just remember: 
 * $mysqldate = date( 'Y-m-d H:i:s', $phpdate );
 *  $phpdate = strtotime( $mysqldate );
 */

class Event
{        
    static public function getById($id) 
    {       
        
            $db = DB::get()->getConnection(); 
            $sql = "SELECT * FROM event WHERE uid = :p_id";
            $stmt = $db->prepare($sql);
            myPDOexecute($stmt, array(':p_id' => $id));
            
            $r = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$r) 
            {
                return false;
            }
                                
        return $r;              
    }
    
    
    
    static public function createEvent($owner, $name, $description, $location, $time,  $phone_number, $hide_lastname, $quota, $gender)
    {        
        global $fp;
    
        $fp->log($location);
        $loc = getLocationByText($location);
        $fp->log($loc);
        
        $db = DB::get()->getConnection();
        $stmt = $db->prepare("INSERT INTO event (owner_uid, name, description, loc_text, loc_lon, loc_lat, time, phone, hide_last_name, quota, gender) VALUES  (:uid, :name, :desc, :p, :lon, :lat, :t, :ph, :hide, :q, :g) ON DUPLICATE KEY UPDATE name=:name, description=:desc, loc_text=:p, loc_lat=:lat, loc_lon= :lon, phone= :ph, hide_last_name = :hide, quota = :q, gender = :g");
        if (!$stmt->execute(array(':uid' => $owner, 
                                  ':name' =>$name, 
                                  ':desc' => $description, 
                                  ':p' => $loc->text,
                                  ':lon' => $loc->lon, 
                                  ':lat' => $loc->lat, 
                                  ':t' => gmdate("Y-m-d H:i:s", $time), 
                                  ':ph' => $phone_number, 
                                  ':hide' => $hide_lastname, 
                                  ':q' => $quota,
                                  ':g' => $gender)))
        {
           $err = "ERROR(CreateEvent): Update on the server failed!->".join (", ",$stmt->errorInfo());           
            error_log($err);
            $fp->log($err);
            return false;     
        }

                
        return true;        
    }  

    static public function getNearbyEvents($uid, $radius, $what_gender)
    {     
        global $fp;
        
        /*
         * $what_gender = "all" or whatever => no filter
         * $what_gender = "same" => user.gender='M'  -> no event.gender = 'F', user.gender='F' -> no event.gender = 'M'
         */
        
        $db = DB::get()->getConnection();
        
        $u = User::getById($uid);       
        $radius = isset($radius)? $radius : 30;
        
        
        $filter_gender = '';
        
        if ($what_gender == 'same') 
        {
            $filter_gender = "event.gender == '".$u['gender']."'";
        } 
        else 
        {
            if ($u['gender'] == 'M')
                $filter_gender = "event.gender != 'F'";
            else if ($u['gender'] == 'F')
                $filter_gender = "event.gender != 'M'";
            else 
                $filter_gender = "1=1";
        }
        
        
        $sql = "SELECT event.*, f_name, l_name,  (select count(answer) FROM user_event WHERE user_event.eid = event.eid AND answer = 1) as yes, (select count(answer) FROM user_event WHERE user_event.eid = event.eid AND answer = 1) as no, truncate((degrees(acos(sin(radians(loc_lat)) * sin(radians(:lat)) + cos(radians(loc_lat)) * cos(radians(:lat)) * cos(radians(loc_lon - :lon)))) * 69.09),1) as distance 
                FROM event, user 
                WHERE $filter_gender 
                HAVING distance < :r";
        $stmt = $db->prepare($sql);
        
        if (!$stmt->execute(array(':lon' =>$u['lon'],':lat' =>$u['lat'], ':r' => $radius)))//, ':mine' => $mine, ':gender' => $gender )))
        {   
            $err = "ERROR(getNearbyEvents($uid, $radius, $what_gender)/filter_gender:$filter_gender): Update on the server failed!->".join (", ",$stmt->errorInfo());
            error_log($err);
            $fp->log($err);

            return false;
        }
                                
        $out->status = "OK";
        $out->results = Array();

        $i = 0;
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) 
        {         
            $out->results[$i]->eid         = $r['eid'];
            $out->results[$i]->name        = $r['name'];
            $out->results[$i]->description = $r['description'];
            $out->results[$i]->time        = $r['time'];
            $out->results[$i]->distance        = $r['distance'];
            $out->results[$i]->location->lon   = $r['loc_lon'];
            $out->results[$i]->location->lat   = $r['loc_lat'];
            $out->results[$i]->location->name  = $r['loc_text'];
            $out->results[$i]->owner->name     = $r['hide_last_name'] ? $r['f_name'].' '.$r['l_name'] : $r['f_name'];
            $out->results[$i]->owner->uid      = $r['owner_id'];
            $out->results[$i]->quota           = $r['quota'];
            $out->results[$i]->gender          = $r['gender'];
            $out->results[$i]->yes             = $r['yes'];
            $out->results[$i]->no              = $r['no'];
            
            //{eid: 5, name: "Event name", description: "This is a great event", time: 43424234, location: {name: "", lon: 34343, lat:34343}, owner: {name: "Chris Cinelli, uid: 5}, quota: 4, n_yes: 4, n_no: 45},...
            ++$i;
        }  
        
        return $out;                                    
    } 

    static public function getUserEvents($uid)
    {     
        global $fp;
        
        $fp->log("uid: $uid");
        /*
         * $what_gender = "all" or whatever => no filter
         * $what_gender = "same" => user.gender='M'  -> no event.gender = 'F', user.gender='F' -> no event.gender = 'M'
         */
        
        $db = DB::get()->getConnection();
                      
        $sql = "(SELECT event.*,  (select count(answer) FROM user_event WHERE user_event.eid = event.eid AND answer = 1) as yes, (select count(answer) FROM user_event WHERE user_event.eid = event.eid AND answer = 1) as no  FROM event WHERE owner_uid=:u) UNION (SELECT event.*,  (select count(answer) FROM user_event WHERE user_event.eid = event.eid AND answer = 1) as yes, (select count(answer) FROM user_event WHERE user_event.eid = event.eid AND answer = -1) as no  FROM event, user_event WHERE user_event.eid = event.eid AND answer = 1 AND uid = :u)";
        $stmt = $db->prepare($sql);
        
        if (!$stmt->execute(array(':u' =>$uid)))
        {   
            $err = "ERROR(getUserEvents): Update on the server failed!->".join (", ",$stmt->errorInfo());
            error_log($err);
            $fp->log($err);

            return false;
        }
                                
        $u = User::getById($uid); 
        
        $out->status = "OK";
        $out->results = Array();

        $fp->log("uid: $uid");
        $i = 0;
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) 
        {         
            $out->results[$i]->eid         = $r['eid'];
            $out->results[$i]->name        = $r['name'];
            $out->results[$i]->description = $r['description'];
            $out->results[$i]->gender      = $r['gender'];
            $out->results[$i]->time        = $r['time'];
            $out->results[$i]->location->lon   = $r['loc_lon'];
            $out->results[$i]->location->lat   = $r['loc_lat'];
            $out->results[$i]->location->name  = $r['loc_text'];
            $out->results[$i]->owner->name     = $r['hide_last_name'] ? $u['f_name'].' '.$u['l_name'] : $u['f_name'];
            $out->results[$i]->owner->uid      = $uid;
            $out->results[$i]->quota           = $r['quota'];            
            $out->results[$i]->yes             = $r['yes'];
            $out->results[$i]->no              = $r['no'];
            
            //{eid: 5, name: "Event name", description: "This is a great event", time: 43424234, location: {name: "", lon: 34343, lat:34343}, owner: {name: "Chris Cinelli, uid: 5}, quota: 4, n_yes: 4, n_no: 45},...
            ++$i;
        }  
        
        return $out;                                    
    } 

    static public function getLastEvents($lon, $lat, $radius = 20000, $start = 0, $n = 20)
    {     
        global $fp;
        
        
        $db = DB::get()->getConnection();
        
        
        
        $sql = "SELECT event.*, f_name, l_name, (select count(answer) FROM user_event WHERE user_event.eid = event.eid AND answer = 1) as yes, (select count(answer) FROM user_event WHERE user_event.eid = event.eid AND answer = 1) as no, truncate((degrees(acos(sin(radians(loc_lat)) * sin(radians(:lat)) + cos(radians(loc_lat)) * cos(radians(:lat)) * cos(radians(loc_lon - :lon)))) * 69.09),1) as distance 
                FROM event, user 
                WHERE eid > :start
                HAVING distance < :r
                ORDER BY time_created
                LIMIT :lim"; //time_created;
        $stmt = $db->prepare($sql);
        
        if (!$stmt->execute(array(':lon' =>$lon,':lat' =>$lat, ':r' => $radius, ':start'=>$start, ':lim'=> $n )))//, ':mine' => $mine, ':gender' => $gender )))
        {   
            $err = "ERROR(getNearbyEvents($uid, $radius, $what_gender)/filter_gender=$filter_gender): Update on the server failed!->".join (", ",$stmt->errorInfo());
            error_log($err);
            $fp->log($err);

            return false;
        }
                                
        $out->status = "OK";        
        $out->results->last = 0;
        $out->results->events = Array();

        $i = 0;
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) 
        {         
            if ($i == 0) $out->results->last = $r['eid'];
            
            $out->results->events[$i]->eid         = $r['eid'];
            $out->results->events[$i]->name        = $r['name'];
            $out->results->events[$i]->description = $r['description'];
            $out->results->events[$i]->time        = $r['time'];
            $out->results->events[$i]->distance        = $r['distance'];
            $out->results->events[$i]->location->lon   = $r['loc_lon'];
            $out->results->events[$i]->location->lat   = $r['loc_lat'];
            $out->results->events[$i]->location->name  = $r['loc_text'];
            $out->results->events[$i]->owner->name     = $r['hide_last_name'] ? $r['f_name'].' '.$r['l_name'] : $r['f_name'];
            $out->results->events[$i]->owner->uid      = $r['owner_id'];
            $out->results->events[$i]->quota           = $r['quota'];
            $out->results->events[$i]->gender          = $r['gender'];
            $out->results->events[$i]->yes             = $r['yes'];
            $out->results->events[$i]->no              = $r['no'];
            
            //{eid: 5, name: "Event name", description: "This is a great event", time: 43424234, location: {name: "", lon: 34343, lat:34343}, owner: {name: "Chris Cinelli, uid: 5}, quota: 4, n_yes: 4, n_no: 45},...
            ++$i;
        }  
        
        return $out;                                    
    } 



    //Trust that the caller is callaing with a valid $col and $value
    static public function SetColumn($id, $col, $value)
    {
        $db = DB::get()->getConnection();
        $stmt = $db->prepare("INSERT INTO event (eid, $col) VALUES  (:eid, :v) ON DUPLICATE KEY UPDATE $col=:v");
        if (!$stmt->execute(array(':eid' => $id, ':v' => $value)))
        {
            $err = "ERROR(SetUserColumn): Update on the server failed!->".join (", ",$stmt->errorInfo());
            error_log($err);
            $fp-log($err);
            return false;           
        }
        
        return true;        
    }       
}

?>