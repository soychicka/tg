/*
 *
 * Utilities
 *
 */
	
function gCookie(c){var b=document.cookie.split(";"),a="",e="",f="",d="";for(d=0;d<b.length;d++){a=b[d].split("=");e=a[0].replace(/^\s+|\s+$/g,"");if(e==c){if(a.length>1)f=unescape(a[1].replace(/^\s+|\s+$/g,""));return f}}return null}	

/*
As has been mentioned, using something like this would be the best way to do it:
window["functionName"](arguments);
That, however, will not work with a namespace'd function:
window["My.Namespace.functionName"](arguments); // fail
This is how you would do that:
window["My"]["Namespace"]["functionName"](arguments); // succeeds
In order to make that easier and provide some flexibility,use the convenience function: execFuncByName
You would call it like so:
executeFunctionByName("My.Namespace.functionName", window, arguments);
Note, you can pass in whatever context you want, so this would do the same as above:
executeFunctionByName("Namespace.functionName", My, arguments);
*/

function execFuncByName(functionName, context /*, args */) {
  var args = Array.prototype.slice.call(arguments).splice(2);
  var namespaces = functionName.split(".");
  var func = namespaces.pop();
  for(var i = 0; i < namespaces.length; i++) {	
    context = context[namespaces[i]];
  }
  return context[func].apply(this, args);
}



$(document).ready(function() {
	
	function ShowError(a)
	{
		alert(a);
	}


	function register(e) {
		e.preventDefault();
		 $.ajax({ url: "action_user",
			 type: "POST",
			 data: {
			 	action: "register",
			 	email:      $("#r_email").val(),
			 	pwd:        $("#r_pwd").val(),
			 	first_name: $("#r_fname").val(),
			 	last_name:  $("#r_lname").val(),
			 	gender:     $('input[name=gender]:checked').val()
			 	},
			 dataType: "json",
			 success: function () {
			 	$("div#panel").slideUp("slow", function(){
			 		location.reload(true);
			 	});
			 },   
			 timeout: 5000,
			 error: function(xhr, textStatus, thrownError) {
				 //$('#loading').css({"display": "none"});
				 ShowError("Hit error fn!\nresponseText: "+xhr.responseText+"\nstatusText: "+xhr.statusText+"\ntextStatus: "+textStatus+"\nthrownError: "+thrownError);
			 }
		 });
	}		
	

	function login(e) {
		 e.preventDefault();
		 $.ajax({ url: "action_user",
			 type: "POST",
			 data: {
			 	action: "login",
			 	email:      $("#l_email").val(),
			 	pwd:        $("#l_pwd").val()
			 	},
			 dataType: "json",
			 success: function () {
				$("div#panel").slideUp("slow", function(){
					location.reload(true);
				});
			 },   
			 timeout: 5000,
			 error: function(xhr, textStatus, thrownError) {
				 //$('#loading').css({"display": "none"});
				 ShowError("Hit error fn!\nresponseText: "+xhr.responseText+"\nstatusText: "+xhr.statusText+"\ntextStatus: "+textStatus+"\nthrownError: "+thrownError);
			 }
		 });
	}		

	
	
	$("#registration-form").submit(register).keyup(function(e){
		if (e.which == 13) $(this).submit(); //Need this because ezpz_hint introduce a hidden field that prevent to fire submit when ENTER is pressed
	});

	$("#login-form").submit(login).keyup(function(e){
		if (e.which == 13) $(this).submit(); //Need this because ezpz_hint introduce a hidden field that prevent to fire submit when ENTER is pressed
	});


	
	// Expand Panel
	$("#open").click(function(){
		$("div#panel").slideDown("slow");
	
	});	
	
	// Collapse Panel
	$("#close").click(function(){
		$("div#panel").slideUp("slow");	
	});		
	
	// Switch buttons from "Log In | Register" to "Close Panel" on click
	$("#toggle a").click(function () {
		$("#toggle a").toggle();
	});	
	
	$(".field").ezpz_hint();

	$("#logout").click(function () {
		 $.ajax({ url: "action_user",
			 type: "GET",
			 data: {action: "logoff"},
			 dataType: "json",
			 success: function () {
			 	location.reload(true);
			 },   
			 timeout: 5000,
			 error: function(xhr, textStatus, thrownError) {
				 //$('#loading').css({"display": "none"});
				 ShowError("Hit error fn!\nresponseText: "+xhr.responseText+"\nstatusText: "+xhr.statusText+"\ntextStatus: "+textStatus+"\nthrownError: "+thrownError);
			 }
		 });
	});		
		
});