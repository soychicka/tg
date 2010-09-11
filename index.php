<?php

/* TODO:
     
 */ 


    require_once 'include/basic.php';
    require_once 'include/MyAuth.php';
    require_once 'include/model_user.php';
    require_once 'include/model_event.php';
    
    if(isset($_REQUEST['logoff']))
        MyAuth::logoff();
    
    $user = MyAuth::checkAuthentication(false);
    $fp -> log ($user);
    if ($user)
    {
        $u = User::getById($user);
    }
    else
    {
        $u = false;
    }
    $fp -> log ($u);
    
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="Content-Style-Type" content="text/css">
        <meta http-equiv="Content-Script-Type" content="text/javascript">

        <link rel="stylesheet" type="text/css" href="static/css/main.css<?= cache_burner() ?>" />
        <link rel="stylesheet" type="text/css" href="fa_b/jquery.fancybox-1.3.1.css" media="all" />
                

        <title>Together.us: Find your group activity!</title>
        
    </head>

    <body> 



<!-- Panel -->
<div id="toppanel">
    <div id="panel">
        <div class="content clearfix">
            <div class="left">
                <h1>Welcome to Together Us</h1>
                <h2>Bringing People Together</h2>       
                <p class="grey">You can put anything you want in this sliding panel: videos, audio, images, forms... The only limit is your imagination!</p>
                <h2>Download</h2>
                <p class="grey">To download this script go back to <a href="http://web-kreation.com/index.php/tutorials/nice-clean-sliding-login-panel-built-with-jquery" title="Download">article &raquo;</a></p>
            </div>
            <div class="left">
                <!-- Login Form -->
                <form id="login-form" class="clearfix" action="#" method="post">
                    <h1>Member Login</h1>
                    <label class="grey" for="log">Username:</label>
                    <input class="field" type="text" name="log" id="l_email" value="" size="23" />
                    <label class="grey" for="pwd">Password:</label>
                    <input class="field" type="password" name="l_pwd" id="l_pwd" size="23" />
<!--                    <label><input name="rememberme" id="rememberme" type="checkbox" checked="checked" value="forever" /> &nbsp;Remember me</label> -->
                    <div class="clear"></div>
                    <input type="submit" name="submit" value="Login" class="bt_login" />
                    <a class="lost-pwd" href="#">Lost your password?</a>
                </form>
            </div>
            <div class="left right">            
                <!-- Register Form -->
                <form id="registration-form" action="#" method="post">
                
                    <h1>Not a member yet? Sign Up!</h1>             
                    
                    <p><input class="field" type="text" name="email" id="r_email" size="23" title="Your email address"/> </p>
                    <p><input class="field" type="password" name="password" id="r_pwd" title="Password" size="23"/></p>
                    <p><input class="field" type="text" name="first_name" id="r_fname" title="First Name" size="23"/></p>
                    <p><input class="field" type="text" name="last_name" id="r_lname" title="Last Name" size="23"/></p>
                    <p><label>Gender:</label>
                       Male:<input type="radio" value="M" name="gender" checked />
                       Female:<input type="radio" value="F" name="gender" /></p>
                    
                    
                    <input type="submit" name="submit" value="Register" class="bt_register" />
                </form>
            </div>
        </div>
</div> <!-- /login -->  

    <!-- The tab on top --> 
    <div class="tab">
        <ul class="login">
            <li class="left">&nbsp;</li>
            <li>Hello <?php echo !$u ? "Guest" : $u['f_name'].' '.$u['l_name'] ?>!</li>
            <li class="sep">|</li>
<?php if ($u): ?>
            <li id="logout">
                <a class="open" href="#">Logout</a>
            </li>
<?php else: ?>
            <li id="toggle">
                <a id="open" class="open" href="#">Log In | Register</a>
                <a id="close" style="display: none;" class="close" href="#">Close Panel</a>         
            </li>
<?php endif; ?>
            <li class="right">&nbsp;</li>
        </ul> 
    </div> <!-- / top --   
    
</div> <!--panel -->

    <div id="container">
        <div id="content" style="padding-top:5px;">
            <div id="left_pane">
                <div class="day-header">
                    Tomorrow
                </div>

<?php 

$events = Event::getNearbyEvents($user, 50000, "all");

foreach($events->results as $e)
{
    

?>
                <div class="feed">
                    <div class="desc">
                        <h3><?= $e->name ?></h3>
                        <p>Saturday Aug 14, 6:00 PM somewhere in <?= $e->location->name ?></p>
                    </div>
                    <div class="answer">
                        <div class="answer_buttons">
                            <ul>
                                <li style="background-color:#9E9E9E">min <?= $e->quota ?></li>
                                <li style="background-color:#009900">yes <?= $e->yes ?></li>
                                <li style="background-color:#FF0000">no <?= $e->no ?></li>
                            </ul>
                        </div>
                        <div class="rsvp">
                            <span style="font-weight: bolder">RSVP:</span> <a href="#">yes</a> <a href="#">no</a>
                            </ul>

                        </div>
                    </div>
                </div>
<?php

}

?>
            
            </div>
            <div id="map">
                a<br />
                a<br />
                a<br />
                a<br />

            </div>

        </div><!-- / content -->        
    </div><!-- / container -->





    <script type="text/javascript" src="static/js/jquery-1.4_2.js"></script>    
    
<?php 
    echo "<!-- DEBUG: $debugOn -->";
    if ($debugOn): 
?>
        <style type="text/css">
            #debug {            
                position: fixed;
                top:  10px;
                left: 770px;
                width: 850px;
                height: 400px;
                
                padding: 5px;
                
                background:#000; 
                color:#fff;
                opacity: 0.6;
            }
            
            #debug table, #debug th, #debug tr,  #debug td{
                border: 1px solid #fff;
                border-collapse: collapse;
                padding: 2px 8px;
            }
        </style>

        <script type="text/javascript">
            $(document).ready(function()
             {
                  //Graphical debugger
                $(document).mousemove(function(e)
                 {
                  $('#debug').html("curs: (" + e.pageX + ", " + e.pageY + ")");
                })


                $("*", document.body).live('click', function (e) {
                  e.stopPropagation();

                  function getClassAndId(o){
                      return '<td>' + o.get(0).tagName + '</td><td>'+o.attr("id")+'</td><td>'+o.attr("class")+'</td>';
                  }

                  function getPosition(o){
                      var offset = o.offset();
                      var pos = o.position();

                      return "<td>"+o.css("position")+'</td><td>('+offset.left+", "+offset.top+')</td><td>('+pos.left+", "+pos.top+")</td><td>("+o.width()+", "+o.height()+")</td><td>"+o.css("marginTop")+","+o.css("marginRight")+", "+o.css("marginBottom")+","+o.css("marginLeft")+"</td><td>"+o.css("paddingTop")+","+o.css("paddingRight")+", "+o.css("paddingBottom")+","+o.css("paddingLeft")+"</td>";
                  }          

                  function getParents(o, n) {              
                      var pre = '';
                      var theParent = o.parent();              

                      if (n>=1 && theParent.length > 0 ) {                  
                        return  getParents(theParent, n-1) + "<tr>" + getClassAndId(o) + getPosition(o) + "</tr>";
                      } else 
                          return '';
                  }

                 var sz = '<table><thead><tr><th>Tag</th><th>Id</th><th>Class</th><th>Pos</th><th>Offset</th><th>Position</th><th>(w ,h)</th><th>margin (T,R,B,L)</th><th>padding(T,R,B,L)</th></tr></thead>'+
                          getParents($(this), 100)+'</table>' ;

                 //console.log (sz);

                 $('#debug').html("curs: (" + e.pageX + ", " + e.pageY + ')<br>' + sz);
                });            
            });
        </script>

        <div id="debug">Debug initialiazed</div>     
<?php endif; ?>


          
         <script type="text/javascript" src="static/js/jquery.bgiframe.js"></script>
         <script type="text/javascript" src="static/js/jquery.bt.js"></script>
         <script type="text/javascript" src="static/js/StatusBar.js<?= cache_burner() ?>"></script>
         <script type="text/javascript" src="fa_b/jquery.fancybox-1.3.1.js"></script>
         
         <script type="text/javascript" src="static/js/jquery.ezpz_hint.js"></script>

        <script type="text/javascript" src="static/js/main.js<?= cache_burner() ?>"></script>

        <script type="text/javascript">             
        </script>
        
    </body>
</html>
