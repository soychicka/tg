<?php      

$NameParts = explode('/', $_SERVER["SCRIPT_NAME"]);
$page = $NameParts[count($NameParts) - 1];

require_once 'include/basic.php';
require_once 'include/MyAuth.php';


$fp->log($_GET, '_GET');
$fp->log($_COOKIE, '_COOKIE');

/*
$fp->group('_COOKIE[]:');
ob_start();
print_r( $_COOKIE );
$out = ob_get_clean();
TRACE(TRACE_DEBUG, $out);
$fp->groupEnd();
*/


$user = MyAuth::requireAuthentication($appLoginUrl, false, false);


if (!in_array($user, $adminUserIDs)) 
{
    echo '<h1>You are not the admin!</h1>';
    die();
}

if (isset($_GET['obstart_debug']))
{    
    if ($_GET['admin_obstart'] == '1') {
        SetAdminDebug('obstart','1');
    } else { 
        SetAdminDebug('obstart','0');
    }
    
    
    header( "Refresh: 0;url=admin.php" );
    echo 'You\'ll be redirected in about 1 sec. If not, click <a href="admin.php">here</a>.';
    die();        
} 


if (isset($_GET['mock_user_debug']))
{    
    $mu = $_GET['mock_user_debug'] ;
    SetAdminDebug('mock_user',$mu);
    
    header( "Refresh: 0;url=admin.php" );
    echo 'You\'ll be redirected in about 1 sec. If not, click <a href="admin.php">here</a>.';
    die();        
} 


if (isset($_GET['fb_debug']))
{    
    if ($_GET['fb_debug'] == '1') {
        SetAdminDebug('fb_debug','1');
    } else { 
        SetAdminDebug('fb_debug','0');
    }
    
    
    header( "Refresh: 0;url=admin.php" );
    echo 'You\'ll be redirected in about 1 sec. If not, click <a href="admin.php">here</a>.';
    die();        
} 



if (isset($_GET['admin_debug']))
{    
    if ($_GET['admin_debug'] == 'Y') {
        SetAdminDebug('Y');
        SetAdminDebug('obstart','1');
    } else { 
        SetAdminDebug('N');
    }
    
    
    header( "Refresh: 0;url=admin.php" );
    echo 'You\'ll be redirected in about 1 sec. If not, click <a href="admin.php">here</a>.';
    die();
        
} 

if (isset($_GET['expire_session']))
{    
    $facebook->expire_session();    
}


$d_mode = GetAdminDebug();
$new_debug_mode = ($d_mode  != 'Y') ? 'Y' : 'N';
$new_debug_mode_button = ($d_mode  != 'Y') ? 'ON' : 'OFF';
$obs = intval(GetAdminDebug('obstart'));
$fb_debug = intval(GetAdminDebug('fb_debug'));
$mu = intval(GetAdminDebug('mock_user'));


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
    <head>
    </head>
    
    <body>

        <div class="container" >   
            <!--    CONTENT  -->
            <div class="appContent">    


                <div class="main">

                    <h2>Welcome Admin (id = <?= $user ?>) </h2>

                    <HR WIDTH="90%" COLOR="#6699FF" SIZE="6">
                    <h3>Debug mode for Admin is "<?= $d_mode ?>"</h3>
                    <form action="<?=$baseCallbackURL?>admin" method="GET" >
                        <input type="hidden" name="admin_debug" value="<?= $new_debug_mode ?>" /> <br />
                        <input type="submit" value="Turn Admin Debug <?= $new_debug_mode_button ?>" />
                    </form>
                    <HR WIDTH="90%" COLOR="#6699FF" SIZE="6">
                    <h3>ob_start is "<?= $obs ?>"</h3>
                    <form action="<?=$baseCallbackURL?>admin" method="GET" >
                        <input type="hidden" name="obstart_debug" value="<?= $obs ? 0 : 1  ?>" /> <br />
                        <input type="submit" value="Turn obstart in Debug <?= $obs ? 'OFF' : 'ON' ?>" />
                    </form>
                    <HR WIDTH="90%" COLOR="#6699FF" SIZE="6">
                    <h3>mock_user is "<?= $mu ?>"</h3>
                    <form action="<?=$baseCallbackURL?>admin" method="GET" >
                        <input name="mock_user_debug" value="" /> <br />
                        <input type="submit" value="Set Mock User" />
                    </form>
                    <HR WIDTH="90%" COLOR="#6699FF" SIZE="6">
                    <h3>fb_debug is "<?= $fb_debug ?>"</h3>
                    <form action="<?=$baseCallbackURL?>admin" method="GET" >
                        <input type="hidden" name="fb_debug" value="<?= $fb_debug ? 0 : 1  ?>" /> <br />
                        <input type="submit" value="Turn fb_debug in Debug <?= $fb_debug ? 'OFF' : 'ON' ?>" />
                    </form>
                    <HR WIDTH="90%" COLOR="#6699FF" SIZE="6">
                    <h3>Expire Session</h3>
                    <form action="<?=$baseCallbackURL?>admin" method="GET" >
                        <input type="hidden" name="expire_session" value="1" /> <br />
                        <input type="submit" value="Expire!" />
                    </form>

                </div>
            </div>            
        </div>

    </body>
</html>