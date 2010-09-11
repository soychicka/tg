<?php
   class MyAuth
   {
       const expiration = 86400; //3600*24; //1 day in seconds
       
       //### Salts - Remember to update them in case the code is realeased to the public!
       private static $salt1 = 'gflkjLpw8dw8Hq9qQH88h89A3K23';
       private static $salt2 = 'lh378H8Ho7g7GOII7o7boB7O7Bo6';
       private static $salt3 = 'dFS5093Jld75fgdg4054R3RwKSfs';
              

       static protected function get_signature($u, $t)
       {           
           return md5(self::$salt1.$u.$t.self::$salt2);
       }

       static protected function get_signature_post($s)
       {
           return md5($s.self::$salt3);
       }

    
       //Set autentification cookies on the browser
       //!!!Call it only when we are sure who the user is.
       static function setLoginAuthenticate($u)
       {
           $t = time();
           $s = self::get_signature($u, $t);
           setcookie('tjc_lo',  $u.','.$t.','.$s, $t + self::expiration);    //Login cookie
           setcookie('tjc_th',  self::get_signature_post($s), $t + self::expiration); //Cookie that must be sent back for authorization to prevent cross-domain attacks
       }
       
       //Require autentification and return the user id. If it is not authenticated it redirect the browser to $login_url
       static function requireAuthentication($login_url, $is_ajax = true, $extra_secure = true)
       {
           $u = self::checkAuthentication($extra_secure);
           
           if (!$u)
           {   
               if($is_ajax) return 0;
               header("Location: $login_url");
               die();
           }
           return $u;
           
       }

        //Set autentification cookies on the browser
        //if extra_secure is true check for the presence for _POST['sig'] or _GET['sig'] with the special cookie.
        //Return the User ID if everything is fine. Otherwise return 0.
       static function checkAuthentication($extra_secure = true)
       {
            $u = 0;
            
            
            if ($_COOKIE['tjc_lo']) ###HAching to make it work on android
            {
                list($c_u,$t,$sig) = explode (',',$_COOKIE['tjc_lo']); 
                if (self::get_signature($c_u, $t) == $sig &&  //lo cookie is allright
                    (!$extra_secure || (isset($_GET['sig'])?$_GET['sig']:$_POST['sig']) == self::get_signature_post($sig)) //if extra_secure, look for _GET or _POST [sig]
                   ) 
                {//The cookie is good
                    $u = $c_u;
                    
                    $curr_t = time();
                    if ($curr_t-$t > self::expiration/2) //if the cookie is "half expired"
                        {//Set back the cookie moving forward the expiration time
                            self::setLoginAuthenticate($u);
                        }
                } else {
                    ; //### Should we return a 404 and die or leave the script the option on what to do?
                }
            }
            return $u;
       }

       static function logoff()
       {
           $t = time() - 1000000;
           setcookie('tjc_lo', '', $t );    //Login cookie
           setcookie('tjc_th', '', $t );    //Cookie that must be sent back for authorization to prevent cross-domain attacks
       }


    }

//Unit test of the script
//Setup: alter expiration to 3600 -> 60
//T01: http://thejournalclub.stanford.edu/tjc/auth_h.php?T3stThisScript=1&cmd=1 //Check the cookies
//T02: http://thejournalclub.stanford.edu/tjc/auth_h.php?T3stThisScript=1&cmd=0 //Logoff
//T03: http://thejournalclub.stanford.edu/tjc/auth_h.php?T3stThisScript=1&cmd=1 //Check the cookies: Be sure there is no _lo and _th cookies
//T04: http://thejournalclub.stanford.edu/tjc/auth_h.php?T3stThisScript=1&cmd=2 //Login
//T05: http://thejournalclub.stanford.edu/tjc/auth_h.php?T3stThisScript=1&cmd=1 //Check the cookies: Be sure there is  _lo and _th cookies
//T06: http://thejournalclub.stanford.edu/tjc/auth_h.php?T3stThisScript=1&cmd=1 //Check after 60 sec and be sure there is no _lo and _th cookies
//T07: http://thejournalclub.stanford.edu/tjc/auth_h.php?T3stThisScript=1&cmd=2 //Login
//T08: http://thejournalclub.stanford.edu/tjc/auth_h.php?T3stThisScript=1&cmd=3 //Check that the Auth (normal) user is NOT 0 and Auth (extra secure) user IS 0
//T09: http://thejournalclub.stanford.edu/tjc/auth_h.php?T3stThisScript=1&cmd=3$sig=... //Check that the Auth (normal) user is NOT 0 and Auth (extra secure) user is NOT 0
//T10: Be sure that after 1/2 exipration time the cookie is refreshed.

if (isset($_GET['T3stThisScript']))
{
    $c = $_GET['cmd'];
    
    if ($c == 0)
    {  //logoff
        MyAuth::logoff();
        echo "MyAuth::logoff() executed";
    }  
    else if ($c == 1)
    {  //show cookies
        echo '<pre>';
        print_r($_COOKIE);
        if (isset($_COOKIE['tjc_lo']))
        {
            $a = explode (',',$_COOKIE['tjc_lo']);
            $t = $a[1];
            $curr_t = time();
            $exp_time = $curr_t - $t;
            echo "\nCookie 'lo' was set at ", date('l jS \of F Y h:i:s A', $t),' (', intval($exp_time / 60) ,' min and ', $exp_time % 60, ' sec  ago)';            
        }
        echo '</pre>';
        
    }
    else if ($c == 2)
    {  //Auth as user 10101010
        $u = '10101010';
        MyAuth::setLoginAuthenticate($u);
        echo "MyAuth::setLoginAuthenticate($u) executed";
    }
    else if ($c == 3)
    {         
        //perform the check auth
        $n = MyAuth::checkAuthentication(false);
        $e = MyAuth::checkAuthentication();
        
        echo '<pre>';
        print_r($_COOKIE);
        
        echo "Auth (normal) user is $n\n";
        echo "Auth (extra secure) user is $e";
        echo '</pre>';
    }      
}
?>