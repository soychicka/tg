<?php

require_once 'benchmarks.php';

/* Just remember: 
 * $mysqldate = date( 'Y-m-d H:i:s', $phpdate );
 *  $phpdate = strtotime( $mysqldate );
 */

class User
{        
    static public function getById($id) 
    {       
        
            $db = DB::get()->getConnection(); 
            $sql = "SELECT * FROM user WHERE uid = :p_id";
            $stmt = $db->prepare($sql);
            myPDOexecute($stmt, array(':p_id' => $id));
            
            $r = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$r) 
            {
                return false;
            }
                                
        return $r;              
    }

    static public function getByEmail($email) 
    {       
        
            $db = DB::get()->getConnection(); 
            $sql = "SELECT * FROM user WHERE email = :p_email";
            $stmt = $db->prepare($sql);
            myPDOexecute($stmt, array(':p_email' => $email));
            
            $r = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$r) 
            {
                return false;
            }
                                
        return $r;              
    }


    
    static public function getName($id)
    {
        $u = self::getById($id);
        if (!u) return array('Unknow');
        else return array($u['f_name'], $u['l_name']);
    }
    
    
    //Check the login and return the user id if the pwd is correct
    static public function CheckUser($email, $password)
    {
        global $fp;        
        
        $fp->log("CheckUser($email, $password)");
        $r = self::getByEmail($email);
        if ($r['pwd'] == md5($password)) return $r;
        else return false;
    }
    
    
    static public function RegisterUser($f_name, $l_name, $email, $password, $gender)
    {        
        $db = DB::get()->getConnection();
        $stmt = $db->prepare("INSERT INTO user (f_name, l_name, email, pwd, gender) VALUES  (:f, :l, :e, :p, :g) ON DUPLICATE KEY UPDATE f_name = :f, l_name = :l, email = :e, pwd = :p, gender = :g");
        if (!$stmt->execute(array(':f' =>$f_name, ':l' => $l_name, ':e' => $email, ':g' => $gender , ':p' => md5($password))))
        {
            error_log("ERROR(RegisterUser): Update on the server failed!->".join (", ",$stmt->errorInfo()));
            return false;     
        }

                
        return true;        
    }   

    static public function setLocationByText($uid, $loc_text)
    {     
        global $fp;
        
        $loc= getLocationByText($loc_text);
        $fp->log($loc);

        $db = DB::get()->getConnection();
        $stmt = $db->prepare("UPDATE user SET lon = :lon, lat = :lat, place = :p WHERE uid = :u");
        if (!$stmt->execute(array(':u' =>$uid, ':lon' => $loc->lon, ':lat' => $loc->lat, ':p' => $loc->text )))
        {
            error_log("ERROR(setLocationByText): Update on the server failed!->".join (", ",$stmt->errorInfo()));
            return false;     
        }

                
        return true;        
    } 

    //Trust that the caller is callaing with a valid $col and $value
    static public function SetColumn($id, $col, $value)
    {
        $db = DB::get()->getConnection();
        $stmt = $db->prepare("INSERT INTO user (uid, $col) VALUES  (:u, :v) ON DUPLICATE KEY UPDATE $col=:v");
        if (!$stmt->execute(array(':u' => $id, ':v' => $value)))
        {
            error_log("ERROR(SetUserColumn): Update on the server failed!->".join (", ",$stmt->errorInfo()));
            return false;           
        }
        
        return true;        
    }       
}

?>