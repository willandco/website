<?php
echo "
<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8' />
<link rel='stylesheet' type='text/css' href='style.css' />
</head>
<body>
<h2>Test like</h2>
<form action='./like.php' method='post'>
LikeVal: <input type='text' name='like_val' required><br>
MSGID: <input type='password' name='msg_id' required><br>
<input type='submit' value='Test'>
</form>
</body>
</html> 
";
?>