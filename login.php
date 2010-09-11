<?php

/* TODO:
     
 */ 


    require_once 'include/basic.php';
    require_once 'include/MyAuth.php';
    require_once 'include/model_user.php';
    $fp->log("login.php");
    if (isset($_REQUEST['email']))
    {
        if (isset($_REQUEST['login']))
        {//login
            $fp->log("login!");
            $u = User::CheckUser($_REQUEST['email'], $_REQUEST['password']);
            $fp->log($u);
            if ($u)
            {
                MyAuth::setLoginAuthenticate($u['uid']);
                header("Location: $appAfterLoginUrl");
                die();
            } else {
                echo '<span class="error_message">Login not valid!</span>';
            }
        }
        else
        {//signup
            $fp->log("Signup!");
            if (!User::RegisterUser($_REQUEST['first_name'], $_REQUEST['last_name'], $_REQUEST['email'], $_REQUEST['password'],$_REQUEST['gender']))
            {
                echo '<span class="error_message">Probaly the email has been already used! Plase try wih a different one</span>';
            }else{
                
                $u = User::getByEmail($_REQUEST['email']);
                MyAuth::setLoginAuthenticate($u['uid']);
                
                header("Location: $appAfterLoginUrl");
                die();                
            }
        }
    }
        
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
    
    
<h1>Registration/Login</h1> 
<fieldset>
    <legend>Registration Form</legend> 
    <form action="login" method="post"> 
        <p><label for="username">email:</label> <input type="text" name="email" id="email" /> 
        <p><label for="password">Password:</label> <input type="password" name="password" id="password"/></p> 
        <p><label for="first_name">First name:</label> <input type="text" name="first_name" id="first_name"/></p> 
        <p><label for="last_name">Last name:</label> <input type="text" name="last_name" id="last_name"/></p>
        <p><label for="gender">Gender:</label>
           Male:<input type="radio" value="M" name="gender">
           Female:<input type="radio" value="F" name="gender"><br />
        <p><input type="submit" name="submit" value="Sign Up!" /></p> 
    </form> 
</fieldset>    

<fieldset>
    <legend>Login Form</legend> 
    <form action="login" method="post"> 
        <p><label for="username">email:</label> <input type="text" name="email" id="email" /> 
        <p><label for="password">Password:</label> <input type="password" name="password" id="password"/></p> 
        <input type="hidden" name="login" value=1 />
        <p><input type="submit" name="submit" value="Login In" /></p> 
    </form> 
</fieldset>    


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
