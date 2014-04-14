$(document).ready(function(){

old_sig = $('#sig').val();

$('button').bind('click',function( event ){ ChangeUserSettings( event );});
});

function ChangeUserSettings( event ){
	
	// Find out what will be updated

	var cur_password = $('#cpw').val();
	var new_password = $('#npw').val();
	var chk_password = $('#vpw').val();
	var sig = $('#sig').val();
	var warn_str = "";
	
	if(cur_password.length != 0 && new_password.length != 0){
	
	warn_str = (new_password.length < 3) ? warn_str + "Password must be at least 3 characters!\n" : warn_str + "";
	warn_str = (chk_password.length > 20) ? warn_str + "Password must be less than 20 characters!\n" : warn_str + "";
	warn_str = (new_password != chk_password) ? warn_str + "Passwords do not match!\n" : warn_str + "";
	}
	
	if(old_sig != sig){
		warn_str = (sig.length > 120) ? warn_str + "Signature must be less than 120 characters!\n" : warn_str + "";
	} else {
		sig = "";
	}
	
	$('.warning').text(warn_str);
	
	if(warn_str.length == 0){
		$('.warning').load('../php/useroptions_change.php',{cpw: cur_password, npw: new_password, sig: sig});
		old_sig = $('#sig').val();
	}
}