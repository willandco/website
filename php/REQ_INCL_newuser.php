<?php
// validate username and password
// check to ensure the username is three characters
include 'varinit_header.php';
$con=wrap_sqli_connect();

// store passed values for stripping
$username = $_POST['username'];
$password = $_POST['password'];
$username = addcslashes(mysqli_real_escape_string($con, $username), '%_');
$password = addcslashes(mysqli_real_escape_string($con, $password), '%_');
$encrypt_password = md5($password);

// ensure proper formatting
if(!test_user_input($username, $con) || !test_pass_input($password)){
echo "<br>" . "Account not created.";
mysqli_close($con);
exit;
}

// Insert into the table.
$sql="INSERT INTO $tbl_user ($col_user_name, $col_user_pass, $col_user_joindate)
VALUES
('$username','$encrypt_password',CURDATE())";

echo "Account created";

mysqli_close($con);

function test_user_input($name, $con)
{
if(empty($name)){
echo "username field left empty.";
return false;
}	
if (!preg_match("/^[a-zA-Z1-9 ]*$/",$name)){
echo "Only letters, numbers and white space allowed";
return false;
}
if (24 < strlen($name) || 3 > strlen($name)){
echo "Username must be between 3 and 24 characters long";
return false;
}
/* $result = mysqli_query($con,"SELECT * FROM $GLOBALS[tbl_user]
WHERE $GLOBALS[col_user_name] LIKE $name");	 */// TODO: replace LIKE with FULLTEXT
$result = mysqli_query($con,"SELECT * FROM users
WHERE username LIKE '$name'");
CheckError($con, $result);
if(mysqli_num_rows($result) == 1){
echo "<br>The username $name  has already been taken";
return false;
}
return true;
}

function test_pass_input($pass)
{
if(empty($pass)){
echo "Password field left empty.";
return false;
}
if (20 < strlen($pass) || 3 > strlen($pass)){
echo "Password must be between 3 and 20 characters long.";
return false;
}
return true;
}

?>