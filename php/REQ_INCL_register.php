<?php

$file = fopen("regkey.txt", "r");
$regkey = fread($file, filesize("regkey.txt"));
$get_key = (!empty($_GET['regkey']))? $_GET['regkey'] : 0;

while($regkey = fread($file, filesize("regkey.txt"))){
	if($regkey == $get_key){
		exit;
	}
}
fclose($file);

echo "
<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8' />
<link rel='stylesheet' type='text/css' href='style.css' />
</head>
<body>
<h2>Register</h2>
<form action='newuser.php' method='post'>
Username: <input type='text' name='username' required><br>
Password: <input type='password' name='password' required><br>
<input type='submit' value='Create Account'>
<a href='login.html'><u>Login</u></a>
</form>
</body>
</html> 
";
?>
