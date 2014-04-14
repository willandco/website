<?php
include 'main_header.php';
// currently just echoing pretty much the whole thing
$DB_wrap = new DatabaseWrapper();

echo "
<html>
<head>
<meta charset='utf-8' />
<link rel='stylesheet' type='text/css' href='https://localhost/website/css/style.css' />
<script type='text/javascript' src='../js/jquery.min.js'></script>
<script type='text/javascript' src='../js/jquery-textrange.js'></script>
<script type='text/javascript' src='../js/message_functions.js'></script>
</head>
<body>
	<h1>Create new Topic</h1>
<div class='page'>
	<div style='padding:5px;'class='space'>
	<div id='resp_area' class='warning'></div>
			<form id='newtopic'>
			<p><b>Your Topic Title</b></p>
			<p>Topic titles can be between 5 and 80 characters in length.</p>
			<input type='text' maxlength='80' id='title' name='title'/>
			<br>
			<p><b>Your Message</b></p>
			<button id='b' type='button'>Bold</button><button id='i' type='button'>Italic</button><button id='u' type='button'>Underline</button><button id='img' type='button'>Image</button>
			<br>
			<textarea id='msg' name='msg' maxlength = '7800'>".$DB_wrap->getSig()."</textarea>
			<br>
			<button id='sub' type='button' class='submit'>Submit Message</button>
			<button id='preview' type='button' class='submit'>Preview Message</button>
			</form>
	</div>
</div>
</body>
</html>
";
?>