<?php

require_once 'benchmarks.php';

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
    
    
    
    static public function CreateEvent($name, $description, $time, $owner, $phone_number, $hide_lastname, $quota, $location)
    {        
                
        return true;        
    }   

    static public function getEvents($uid, $radius, $is_mine, $gender)
    {     
        global $fp;
        
        
        $db = DB::get()->getConnection();
        
        $u = User::getById($uid);       
        
        $sql = "SELECT event.*, truncate((degrees(acos(sin(radians(loc_lat)) * sin(radians(:lat)) + cos(radians(loc_lat)) * cos(radians(:lat)) * cos(radians(loc_lon - :log)))) * 69.09),1) as distance FROM event WHERE 1=1 HAVING distance < :r";
        $stmt = $db->prepare($sql);
        
        if (!$stmt->execute(array(':lon' =>$u['lon'],':lat' =>$u['lat'], ':r' => radius)))//, ':mine' => $mine, ':gender' => $gender )))
            return false;                               
                                
        $out->status = "OK";
        $out->results = Array();

        $i = 0;
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) 
        {         
            $out->results[$]->eid         = $r['eid'];
            $out->results[$]->name        = $r['name'];
            $out->results[$]->description = $r['description'];
            $out->results[$]->time        = $r['time'];
            $out->results[$]->location->lon =  $r['loc_lon'];
            $out->results[$]->location->lat =  $r['loc_lat'];
            $out->results[$]->location->name = $r['loc_text'];
            $out->results[$]->location->owner_id = $r['owner_id'];
            $out->results[$]->quota       = $r['quota'];
            
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
            error_log("ERROR(SetUserColumn): Update on the server failed!->".join (", ",$stmt->errorInfo()));
            return false;           
        }
        
        return true;        
    }       
}

?>