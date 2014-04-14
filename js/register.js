$(document).ready(function(){

$('button').bind('click',function( event ){ NewUser( event );});
});

function NewUser( event ){

	var username = $('#un').val();
	var password = $('#pw').val();
	var passcheck = $('#pw2').val();
	var warn_str = "";
	
	warn_str = (username.length < 3) ? warn_str + "Username must be at least 3 characters!\n" : warn_str + "";
	warn_str = (username.length > 24) ? warn_str + "Username must be less than 24 characters!\n" : warn_str + "";
	warn_str = (password.length < 3) ? warn_str + "Password must be at least 3 characters!\n" : warn_str + "";
	warn_str = (password.length > 20) ? warn_str + "Password must be less than 20 characters!\n" : warn_str + "";
	warn_str = (password != passcheck) ? warn_str + "Passwords do not match!\n" : warn_str + "";
	
	$('.warning').text(warn_str);
	
	if(warn_str.length == 0){
		$('form').attr({
			method:'post',
			action:'register_main.php'
		});
		$('form').submit();
	}
}