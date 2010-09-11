<?php
    require_once 'basic.php';
    require_once 'MyAuth.php';

   class FbAuth extends MyAuth
   {
       const expiration = 3600; //1 hour in seconds
       

       static function requireAuthenticate($is_ajax = true, $extra_secure = true, &$realUser = null)
       {        
            global $api_key, $api_key_secret, $adminUserIDs;            
            $u = MyAuth::checkAuthentication($extra_secure);

            if (!$u)
            {
                if($is_ajax) return 0;
                
                $facebook = new Facebook($api_key, $api_key_secret);
                $user = $facebook->require_login();
                if ($user)
                {
                    MyAuth::setLoginAuthenticate($user);
                }
                $u = $user;
            }
            
            if(isset($realUser)) $realUser = $u;

            //Return the mock_user if the real user is an administrator and mockuser is set
            $mu = GetAdminDebug('mock_user');
            if ($mu != 'NONE' && in_array(intval($u), $adminUserIDs))            
            {    
                return $mu;
            } else {
                return $u;
            }
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
    else if ($c == 4)
    {         
        //perform the check auth
        $e = FbAuth::requireAuthenticate(false);
        $n = MyAuth::checkAuthentication(false);
        
        echo '<pre>';
        print_r($_COOKIE);
        
        echo "FbAuth (normal) user is $e\n";
        echo "MyAuth (normal) user is $n";
        echo '</pre>';
    }      

}
?>