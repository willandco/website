<?php
$str = "

https://i.imgur.com/0oDGmYU.jpg

<img src=\"https://mail.google.com/mail/e/B0E\"></img>

<img src=\"http://www.skincarephysicians.net/img/icon-google-plus.png\"></img>


---
other";

$str_next = htmlspecialchars($str);


// Find the img tag location in the string and replace it approprately
// Then find the next &gt bracket and replace it with >
// although &lt and &gt could be used in the string directly, this is more descriptive
$find_img = true;
$str_find = '<img src=';
$next_close = '>';
$offset = 0;
$array_correct = array();
$array_fix = array(); // avoid using regular expressions for now

while($find_img){

	// Find the first occurrance of <img src=
	$startpos = strpos($str, $str_find, $offset);
	if($startpos !== false){
		// Find the next occurrance of >
		$offset = strpos($str, $next_close, $startpos);		
		// Substring between the two, +1 required to offset length.
		$conv_substr = substr($str, $startpos, $offset-$startpos+1);
		$array_correct[] = $conv_substr;
		$array_fix[] = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a target=\"_blank\" href=\"$1\">$1</A>",$conv_substr);
		$encode_substr = htmlspecialchars($conv_substr);
		//QuickCheck($conv_substr);
		// replace the converted substring with the decoded one
		$str_next = str_replace($encode_substr, $conv_substr, $str_next);
		$offset = $offset;
	} else {
		$find_img = false;
	}
}

$str = $str_next;
/*** make sure there is an http:// on all URLs ***/
$str = preg_replace("/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i", "$1http://$2",$str);
/*** make all URLs links ***/
$str = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a target=\"_blank\" href=\"$1\">$1</A>",$str);

$i = 0;
for($i=0; $i < count($array_correct); $i++){
	$str = str_replace($array_fix[$i], $array_correct[$i], $str);
}

QuickCheck($str);

function QuickCheck($var){
	echo $var . "<br>";
}
?>