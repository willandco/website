<?php

/* stopped at newmessages, need front end */

class BasicDatabase
{
	protected static $host="localhost"; 		// Host name
	protected static $usr="root"; 			// Mysql username
	protected static $pw="mega64"; 			// Mysql password
	protected static $db="forum"; 			// Database name
	
	// connection
	protected $con, $redir;

	public function __construct(){
		// All basic functions to occur in every extending class
		@session_start();
		$this->redir = new Redir();
		$this->connect();
		
	}
	
	public function __destruct(){
		mysqli_close($this->con);
	}
	
	protected function connect(){
		// Connects to SQL server
		
		try {
			$this->con = mysqli_connect(BasicDatabase::$host,BasicDatabase::$usr,BasicDatabase::$pw,BasicDatabase::$db);
			Return True;
		} catch (mysqli_sql_exception $e) {
			throw $e;
			Return False;
		}
		// TODO : Move this somewhere better
		$this->UpdateUserTopicLocation();
	}
	
	// Update database field generically
	protected function UpdateField($tbl_name, $update_col_to, $new_val, $where_col, $is_val){
	
		$sql = 		"UPDATE $tbl_name\n"
				. 	"SET 	$update_col_to = $new_val\n"
				. 	"WHERE 	$where_col = $is_val;";
		
		return mysqli_query($this->con, $sql);

	}
	
	// Increment database field
	protected function IncrField($tbl_name, $col_update, $where_col, $where_val, $add_val = 1){
	
		$sql = 		"UPDATE $tbl_name\n"
				. 	"SET 	$col_update = $col_update + $add_val\n"
				. 	"WHERE 	$where_col = $where_val;";
		$result=mysqli_query($this->con, $sql);
	}
	
	// Set date field to now 
	protected function NowDate($tbl_name, $col_update, $where_col, $where_val){

		$sql = 	"UPDATE $tbl_name\n"
			. 	"SET 	$col_update = NOW()\n"
			. 	"WHERE 	$where_col = $where_val;";
			
		$result=mysqli_query($this->con, $sql);
	}
	
	// Inserts a new row into table the table
	protected function InsertIntoTable($tbl_name, $col_to_insert_str, $val_to_insert_str){
		$sql="INSERT INTO $tbl_name ($col_to_insert_str)
		VALUES ($val_to_insert_str)";
		
		$result = mysqli_query($this->con, $sql);
		return $result;
	}
	
	protected function getUserField($get_col){
	
		// Depreciated: Use getField instead
		// assumes the session has been set for no real reason

		$sql ="	SELECT $get_col
				FROM ".DBV::$users."
				WHERE ".DBV::$user_id." = ".$_SESSION[DBV::$ses_id].";";
		
		$result = mysqli_query($this->con, $sql);
		
		if(isset($result)){$row = mysqli_fetch_array($result);}
		
		if(isset($row)){
			return $row[$get_col];
		} else {
			return '';
		}
	}
	
	protected function countFieldWhere($get_tbl, $get_col, $is_val){
		
		// Returns the number of values that meet a certain criteria
		
		$sql ="	SELECT 	$get_col
				FROM 	$get_tbl
				WHERE 	$get_col = $is_val";
		
		$res = mysqli_query($this->con, $sql);
		
		return mysqli_num_rows($res);
	}
	
	protected function getField($get_tbl, $get_col, $where_str){
		
		// Depreciated: Use getField instead
		// Returns a single field entry value;
		$sql ="	SELECT 	$get_col
				FROM 	$get_tbl
				$where_str";
		$res = mysqli_query($this->con, $sql);
		$row = mysqli_fetch_array($res);
		if(isset($row)){
			return $row["$get_col"];
		} else {
			return false;
		}
	}
	
	/* PATH SENSITIVE FUNCTION */
	
	protected function UpdateUserTopicLocation(){
		
		// Check if the topic the user topic location needs to be updated
		$parsed_url = parse_url(getCurrentUrl());
		$strcmp_res = strcasecmp($parsed_url['path'],"./showmessages.php");
		
		// Check if the currently in a topic or if has just left a topic
		if(isset($_GET["topic"]) && $strcmp_res == 0){
			$cur_topic = $_GET["topic"];
				
			// UpdateField(tbl_name, $update_col_to, $new_val, $where_col, $is_val)
			$this->UpdateField(DBV::$users, DBV::$user_ctopic, $cur_topic, DBV::$user_id, $_SESSION[DBV::$ses_id]);
		}
	}
	
	protected function publicTags($str, $img_okay = true){
		// Removes all tags
		// < = &lt;	> = &gt;
		//echo $str;
		//exit;
		$str = str_replace('</img>','',$str);
		$str_next = htmlspecialchars($str, ENT_QUOTES);
		$str_next = str_replace(htmlspecialchars('</img>'),'',$str_next);

		// Image tags must be replaced with more finess as they do not simply wrap the tag name
		// Images may not always be alright to insert (sig, passwords, titles.. etc)		
		// This does the same thing with links as well so links are only allowed in the same spots as images I guess
		if($img_okay){
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
				$startpos = strpos($str, $str_find, 0);
	
				if($startpos !== false){
		
					// Find the next occurrance of >
					$offset = strpos($str, $next_close, $startpos);		
					// Substring between the two, +1 required to offset length.
					$conv_substr = substr($str, $startpos, $offset-$startpos+1);
					$array_correct[] = $conv_substr;
					$array_fix[] = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a target=\"_blank\" href=\"$1\">$1</A>",preg_replace("/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i", "$1http://$2",$conv_substr));
					$encode_substr = htmlspecialchars($conv_substr);
					//QuickCheck($conv_substr);
					// replace the converted substring with the decoded one
					$str_next = str_replace($encode_substr, $conv_substr, $str_next);
					$str = substr($str, $offset);
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
		}
		
		$public_tags = array(1 => 'b', 'i', 'pre','u');
		// insert the publicly allowed tags into the message
		foreach ($public_tags as $tag){
			
			$ftag = "<".$tag.">";
			$btag = "</".$tag.">";
			
			// Convert into the special version of the tag, then replace with the normal
			$conv_tag = htmlspecialchars($ftag, ENT_NOQUOTES);
			$str = str_replace($conv_tag, $ftag, $str);
			
			// Do it again with the </~>
			$conv_tag = htmlspecialchars($btag, ENT_NOQUOTES);
			$str = str_replace($conv_tag, $btag, $str);
		}

		return $str;
	}
	
	protected function countRows($tbl, $where_str=''){
		
		$sql = "SELECT COUNT(*) FROM $tbl $where_str";
		$result = mysqli_query($this->con, $sql);
		$row = mysqli_fetch_array($result);
		$rtn = (isset($row['COUNT(*)']))? $row['COUNT(*)']:0;
		return $rtn;
	}
	
	public function GetConnection(){
		return $this->con;
	}
}

class ChangeUser extends BasicDatabase
{
	
	public function __construct(){
		parent::__construct();
		$this->redir->login();
	}
	
	protected function test_user_input($pass)
	{
		// A shitload of shitty checks
		
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
	
	public function ChangePassword($new_pass, $old_pass){
	
		if($this->test_user_input($new_pass)){
		
			$cur_pass = $this->getUserField(DBV::$user_pass);
			$cmp_pass = md5(mysqli_real_escape_string($this->con,$old_pass));
			if(strcmp($cur_pass, $cmp_pass) != 0){
				echo "Current password incorrect!";
				return false;
			}
			
			$new_pass = md5(mysqli_real_escape_string($this->con, $new_pass));
			
			$sql = 		"UPDATE ".DBV::$users." "
			. 	"SET 	".DBV::$user_pass." = '".$new_pass."' "
			. 	"WHERE 	".DBV::$user_id." = ".$_SESSION[DBV::$ses_id].";";
			
			$result = mysqli_query($this->con, $sql);
			
			echo "Password updated. ";
		}
	}
	
	public function ChangeSig($new_sig){
	
		$new_sig = $this->publicTags($new_sig, false);
	
		mysqli_real_escape_string($this->con, $new_sig);
	
		$sql = 		"UPDATE ".DBV::$users." "
				. 	"SET 	".DBV::$user_sig." = '".$new_sig."' "
				. 	"WHERE 	".DBV::$user_id." = ".$_SESSION[DBV::$ses_id].";";
				
		$result = mysqli_query($this->con, $sql);
				
		echo "Signature updated. ";
	}
}

class Like extends BasicDatabase
{
	public function __construct(){
		parent::__construct();
		$this->redir->login();
	}
	
	public function Like(){
		
	}
}

class LoginHandler extends BasicDatabase
{
	// Login page given its own class to handle it's unique features
	// Forced to do this to handle redirecting issue
	
	public function __construct(){
		parent::__construct();
	}

	private function RSS(){
		// Address of the RSS feed
		$xml_wnem = ("http://www.wnem.com/category/13544/strange-news?clienttype=rss");
		$xml_huff = ("http://www.huffingtonpost.com/feeds/verticals/weird-news/index.xml");
		$xml_nsbc = ("http://feeds.nbcnews.com/feeds/weird");
		
		
		$xml_wnemDoc = new DOMDocument();
		$xml_huffDoc = new DOMDocument();
		$xml_nsbcDoc = new DOMDocument();
		
		$xml_wnemDoc->load($xml_wnem);
		$xml_huffDoc->load($xml_huff);
		$xml_nsbcDoc->load($xml_nsbc);
		
		// Attempt to output from the stream
		$item_w = $xml_wnemDoc->getElementsByTagName('item');
		$item_h = $xml_huffDoc->getElementsByTagName('item');
		$item_n = $xml_nsbcDoc->getElementsByTagName('item');
		
		//$item = new Array();
		$item[0] = $item_w;
		$item[1] = $item_h;
		$item[2] = $item_n;
		
		for ($i=0; $i <= 6; $i++){
			for($j=0; $j <= 2; $j++){
				$item_title=$item[$j]->item($i)->getElementsByTagName('title')
				->item(0)->childNodes->item(0)->nodeValue;
				$item_link=$item[$j]->item($i)->getElementsByTagName('link')
				->item(0)->childNodes->item(0)->nodeValue;
				
				if((3*$i+$j)%2 == 0){
					$alt = "even";
				} else {
					$alt = "odd";
				}
				
				echo ("<tr><td id='".$alt."'><a href='" . $item_link . "'>" . $item_title . "</a></td></tr>");
			
			}
		}
	}
	// Generates the login page; Kept seperate from other gen classes as it has it's own style
	// Obviously the login should have it's own class but I don't want to implement that at the moment
	
	public function genLogin(){
		
		// Redirect to the boards if appropriate
		$this->redir->boards();
		
		// Generate the header HTML
		echo "
		<!DOCTYPE html>
		<html>
		<head>
		<meta charset='utf-8' />
		<link rel='stylesheet' type='text/css' href='../css/login_style.css' />
		<script src='../js/script.js'></script>
		</head>
		<body onkeyup='fun(event)'>
		<div id='unused'></div>
		<div id='content'><table><tr><th>Just Another RSS Feed</th></tr>";
		
		// Otherwise get the RSS feed and generate/update the login page
		$this->RSS();
		
		echo "</table></div></body></html>";
	}
	
	// Called with username and password to actually log users into the website
	public function Login($username, $password){
	
		$this->redir->boards();
	
		if(strcmp($username,'&register') == 0){ header("location:register.php");}
	
		// To protect MySQL injection 
		$username = addcslashes(mysqli_real_escape_string($this->con, $username), '%_');
		$password = addcslashes(mysqli_real_escape_string($this->con, $password), '%_');
		$encrypt_password = md5($password);

		$sql="	SELECT * FROM ".DBV::$users."
				WHERE ".DBV::$user_name."='$username' and ".DBV::$user_pass."='$encrypt_password'";
				
		$result=mysqli_query($this->con, $sql);
		
		// Mysql_num_row is counting table row
		$count=mysqli_num_rows($result);

		// If result matched $username and $password, table row must be 1 row
		if($count==1){
			$row = mysqli_fetch_array($result);
			// Register $username and $user_id and redirect to file "login_success.php"
			session_start();
			$_SESSION[DBV::$ses_id] = $row[DBV::$user_id];
			$_SESSION[DBV::$ses_un] = $row[DBV::$user_name];
			$_SESSION[DBV::$ses_ut] = False;
			header("location:boards.php");
		} else {
		/* TODO: Add JS return to update HTML login page on failure to login */
			echo "Wrong Username or Password";
			return FALSE;
		}
	}
	
	public function Logout() {
		// Unset all of the session variables.
		$_SESSION = array();

		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
				);
		}
		// Finally, destroy the session.
		session_destroy();
		$this->redir->login();
	}
}

class Timer extends BasicDatabase
{
	// Time Between actions
	private $timer = 15;
	
	public function __construct(){
		parent::__construct();
		$this->redir->login();
		$this->UpdateUserTopicLocation();
		date_default_timezone_set('America/Halifax');
	}
	
	private function TimeDiffNowThen($time_field){
	// Returns the difference from
		$prev_time = strtotime($time_field);
		$time_now = strtotime(date('Y-m-d H:i:s'));
		return $time_now - $prev_time;
	}
	
	public function PostTimer(){
		$time_diff = $this->TimeDiffNowThen($this->getUserField(DBV::$user_lastpost));
		$rtn = ($time_diff >= $this->timer) ? 0 : ($this->timer - $time_diff);
		return $rtn;
	}
}

class Register extends BasicDatabase
{
	// Start all the crap session
	public function __construct(){
		parent::__construct();
		$this->redir->boards();
	}
	
	public function genRegister(){
		echo "
			<!DOCTYPE html>
			<html>
			<head>
			<meta charset='utf-8' />
			<link rel='stylesheet' type='text/css' href='../css/style.css' />
			<script type='text/javascript' src='../js/jquery.min.js'></script>
			<script type='text/javascript' src='../js/register.js'></script>
			</head>
			<body>
			<h1>Register</h1>
			<div>
			<div class ='warning'></div>
			<form>
			Username: <input type='text' id='un' name='username'><br>
			Password: <input type='password' id='pw' name='password'><br>
			Repeat Password: <input type='password' id='pw2'>
			<br><br><button type='button'>Create Account</button>
			<br>
			<br>
			<a href='login.php'><u>Return to Login</u></a>
			</form>
			</div>
			</body>
			</html> 
			";
	}
	
	protected function test_user_input($name, $pass)
	{
		// A shitload of shitty checks
		
		if(empty($name)){
			echo "username field left empty.";
			return false;
		}
		if(empty($pass)){
			echo "Password field left empty.";
			return false;
		}
		if (20 < strlen($pass) || 3 > strlen($pass)){
			echo "Password must be between 3 and 20 characters long.";
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
		
		$result = mysqli_query($this->con,"SELECT * FROM users WHERE username = '$name'");
		if(mysqli_num_rows($result) == 1){
			echo "<br>The username $name  has already been taken";
			return false;
		}
		return true;
	}
	
	public function Register(){
		// store passed values for stripping
		$username = (!empty($_POST['username'])) ? $_POST['username'] : "";
		$password = (!empty($_POST['password'])) ? $_POST['password'] : "";
		
		$username = mysqli_real_escape_string($this->con, $username);
		$password = mysqli_real_escape_string($this->con, $password);
		$encrypt_password = md5($password);
		
		if(!$this->test_user_input($username, $password)){
			echo "<br>Account not created.";
			echo "<br><a href='./register.php'>Return to register page</a><br><a href='./login.php'>Return to login page</a>";
			exit;
		}
		
		/* use a function to insert for some reason */
		$col_str = DBV::$user_name.', '.DBV::$user_pass.', '.DBV::$user_joindate.', '.DBV::$user_actdate.', '.DBV::$user_sig;
		$val_str = "'".$username."', '".$encrypt_password."', NOW(), NOW(), 'a sig'";
		$res = $this->InsertIntoTable(DBV::$users, $col_str, $val_str);
		if(isset($res)){
			echo "Account created<br><a href='./login.php'>Return to login page</a>";
		} else {
			echo "Error creating account<br><a href='./register.php'>Return to register page</a>";
		}
	}
}

class DatabaseWrapper extends BasicDatabase
{
	// Property Declaration
	
	public function __construct(){
		parent::__construct();
		$this->redir->login();
		$this->UpdateUserTopicLocation();
	}
	
	public function ShowMessages($topic){
		
		$topic = mysqli_real_escape_string($this->con, $topic);
		
		$sql = "SELECT * 
				FROM ".DBV::$msgs."
				WHERE ".DBV::$msg_topicid."=$topic;";

		$result = mysqli_query($this->con, $sql);		
		
		// Confirm the topic exists
		if(mysqli_num_rows($result) == 0){
			// TODO: JS handler aquires this instead of echo		
			return false;
		}
		
		// Limit by index
		$limit_low = (!empty($_GET['index']))? $_GET['index'] : 0;
		$limit_high = $limit_low + DBV::$query_limit;
		
		// Find all messages with topic id and sort by message id
		$msgs_userid = DBV::$msgs . "." . DBV::$msg_userid;
		$msgs_likes = DBV::$msgs . "." . DBV::$msg_likes;
		$users_id = DBV::$users . "." . DBV::$user_id;
		$sql = "SELECT ".DBV::$msg_id.", ".DBV::$msg_topicid.", $msgs_userid, ".DBV::$msg_date.", ".DBV::$msg_content.", $msgs_likes, ".DBV::$msg_who_likes.", ".DBV::$user_name."
				FROM ".DBV::$users."
				JOIN ".DBV::$msgs."
				ON $msgs_userid = $users_id
				WHERE topic_id=$topic
				GROUP BY ".DBV::$msg_id ." ASC
				LIMIT $limit_low, $limit_high";

		// Result is returned to allow for another class to project new page
		return mysqli_query($this->con, $sql);
	}
	
	public function NewTopic($title, $msgbody){
	
		// TODO : Generic 'okay' message checking
		if(!$msgbody){
			echo "Enter valid message";
			return FALSE;
		}

		$title = htmlspecialchars(mysqli_real_escape_string($this->con, $title), ENT_NOQUOTES);
		
		// Create new entry for topic
		$sql="	INSERT INTO ".DBV::$topics." (".DBV::$topic_name.", ".DBV::$topic_userid.", ".DBV::$topic_timecreated.", 
										".DBV::$topic_pinned.", ".DBV::$topic_lastpost.", ".DBV::$topic_msgno.")
				VALUES 
				('$title', ".$_SESSION[DBV::$ses_id].", NOW(), FALSE, NOW(), 0)";
		$result=mysqli_query($this->con, $sql);
		$topic_id = mysqli_insert_id($this->con);
		
		// Manually update the users topic location for use in NewMessage.
		$this->UpdateField(DBV::$users, DBV::$user_ctopic, $topic_id, DBV::$user_id, $_SESSION[DBV::$ses_id]);
		
		$this->NewMessage($msgbody);
	}
	
	public function NewMessage($msgbody){
		
		$msgbody = $this->publicTags($msgbody);
		$msgbody = mysqli_real_escape_string($this->con, $msgbody);
		$cur_topic = $this->getUserField(DBV::$user_ctopic);
		
		// TODO : Create JS to handle this 'error' instead of redirecting

		// Create new entry for message with topic id.
		$sql="INSERT INTO ".DBV::$msgs." (".DBV::$msg_topicid.", ".DBV::$msg_userid.", ".DBV::$msg_date.", ".DBV::$msg_content.")
		VALUES
		('$cur_topic','".$_SESSION[DBV::$ses_id]."',NOW(),'$msgbody')";
		$result=mysqli_query($this->con, $sql);
		
		// Update the number of posts in the topic
		$this->IncrField(DBV::$topics, DBV::$topic_msgno, DBV::$topic_id, $cur_topic);
		
		// Update the time of last post for the user
		$this->NowDate(DBV::$users, DBV::$user_lastpost, DBV::$user_id, $_SESSION[DBV::$ses_id]);
	
		header("location:showmessages.php?topic=$cur_topic");
	}
	
	// Generate userlist
	public function getUserlist($start_id = 1){
	
		// Limit by index
		$limit_low = (!empty($_GET['index']))? $_GET['index'] : 0;
		$limit_high = $limit_low + DBV::$query_limit;
	
		$sql = "SELECT * FROM ".DBV::$users."
				WHERE ".DBV::$user_id." >= $start_id
				GROUP BY ".DBV::$user_id ." ASC
				LIMIT $limit_low, $limit_high";
		return $result = mysqli_query($this->con, $sql);
	}
	
	// Generate boards
	public function getBoards(){
	
		// Remove Ambiguity from SQL query
		$user_userid = DBV::$users . "." . DBV::$user_id;
		$topic_userid = DBV::$topics . "." .DBV::$topic_userid;
		$topic_lastpost = DBV::$topics . "." .DBV::$topic_lastpost;
		
		// Limit by index
		$limit_low = (!empty($_GET['index']))? $_GET['index'] : 0;
		$limit_high = $limit_low + DBV::$query_limit;
		
		$sql = "SELECT ".DBV::$topic_id.",". DBV::$topic_name.", 
					$user_userid,$topic_lastpost,". DBV::$topic_pinned.",". 
					DBV::$user_name.",". DBV::$topic_msgno ."
				FROM ".DBV::$users."
				JOIN ".DBV::$topics."
				ON $topic_userid = $user_userid
				GROUP BY ".DBV::$topic_pinned." DESC, $topic_lastpost DESC
				LIMIT $limit_low, $limit_high";

		return mysqli_query($this->con, $sql);
	}
	
	public function getProfile($user_id){
		
		// A quick query is all that is required
		$sql = "SELECT * 
				FROM ".DBV::$users."
				WHERE ".DBV::$user_id." = $user_id;";
		
		return mysqli_query($this->con, $sql);
	}
	
	public function getSig(){
		
		// Get the sig for a message box
		return "\n\n---\n".$this->getUserField(DBV::$user_sig);
	}
}

class PageGenerator extends BasicDatabase
{

	// Collection of page generation functions. Takes $result, produces page.
	private $header;
	private $result;
	
	/********************/
	/*	The Basic Stuff	*/
	/********************/
	
	public function __construct($result){
		// Session_start has implicitly been called at this point. 
		// Connection already has been made at this point.
		parent::__construct();
		$this->result = $result;
		$this->header = "	<a href='userlist.php'>Userlist</a> | 
		<a href='boards.php'>Boards</a> | <a href='newtopic.php'>New Topic</a> | 
		 <a href='profile.php?user=".$_SESSION[DBV::$ses_id]."'>".$_SESSION[DBV::$ses_un]."</a> |	
		<a href='logout.php'>Logout</a>";
		if($this->checkInvalid()){ exit;}
	}
	
	// TODO : Current error handling sucks shit to the max
	// do it better somehow.
	private function checkInvalid(){
		
		$error_text = "<br>page not found<br>";
		
		// Create the template page
		// TODO : appropriate error text for each page somehow
		if(@mysqli_num_rows($this->result) == 0 && $this->result != true) {
			$this->genPageTop("The Dusty Desert", 1);
			echo $error_text . "<br>";
			$this->genPageBottom();
			exit;
		}
	}
	
	private function genPageTop($page_title){
		
		// Generate the template page that every page is based off
		echo "
		<!DOCTYPE html>
		<html>
		<head>
		<meta charset='utf-8' />
		<link rel='stylesheet' type='text/css' href='../css/style.css' />
		</head>
		<body>	<div><h1>".$page_title."</h1></div>
		<div class='menuheader'>$this->header</div>
		";
	}
	
	private function genPageBottom(){
		
		// Currently not really in use, could be a way to display page stats or something later on
		echo "</body></html>";
	}
	
	private function nextPage($col_span, $tbl, $where_string = ""){

		$new_index = (empty($_GET['index'])) ? DBV::$query_limit : $_GET['index']+DBV::$query_limit;
		
		/* Store URL query variables in array */
		$full_url = parse_url(getCurrentURL());
		if(array_key_exists('query',$full_url)) {parse_str($full_url['query'],$output);}
		
		/* adjust index */
		$output['index'] = $new_index;
		/* Recreate query variable string */
		$i = 0;
		$query_str = '?';
		while(!(current($output) === FALSE)){
			$get_var = current($output);
			$query_str = $query_str . key($output) . '=' . $get_var;
			if(!(next($output) === FALSE)){ $query_str = $query_str . '&';}
		}
		
		/* Rebuild URL now that is complete */
		$str_url = 'https://' . $full_url['host'] . $full_url['path'] . $query_str;
		
		$rows = $this->countRows($tbl, $where_string);
		
		$current_page = (empty($_GET['index'])) ? 1 : ceil($_GET['index']/DBV::$query_limit);
		$max_page = ceil($rows/DBV::$query_limit);
		
		$next_page = ($current_page == $max_page) ? "" :  " | <a href='$str_url'>Next Page</a>";
		
		echo "<tr><th colspan='". $col_span ."'>Page ".$current_page." of ".$max_page . $next_page . "</th></tr>";
	}
	
	/******************/
	/* The Main Stuff */
	/******************/
	
	public function genUserOptions(){
		
		$col_span = 2;
		$page_name = $_SESSION[DBV::$ses_un] . '\'s Options Page';
		$this->genPageTop($page_name);
		
		// Get sig
		$sig = $this->getUserField(DBV::$user_sig);
		
		// include scripts 
		echo "	<script type='text/javascript' src='../js/jquery.min.js'></script>
				<script type='text/javascript' src='../js/changeusersettings.js'></script>";
		
		// Create the password table
		echo "	<table>
				
				</table>
				<table>
				<tr><td class='rowhead'>Change Password</td><td class='warning'></td></tr>
				<tr><td style='width:150px'>Enter current password</td><td><input class='c_pass' id='cpw' type='password' name='cpw'></td></tr>
				<tr><td>Enter new password</td><td><input class='c_pass' id='npw' type='password' name='npw'></td></tr>
				<tr><td>Repeat new password</td><td><input class='c_pass' id='vpw' type='password'></td></tr>
				<tr><td class='rowhead'>Change Signature</td><td><textarea class='sig' id='sig' name='sig'>$sig</textarea></td></tr>
				</table>
				<button type='button' style='margin-left:160px;'>Submit Changes</button>
				";
		
		echo "</table>";
		
		
		
		$this->genPageBottom();
	}
	
	public function genBoards(){
		
		
		// Generates the boards.php html given the result containing up to 50 topics.
		// Does not do much other than show these 50.
		
		// Board page 'constants'
		$col_span = 4;
		$board_name = "Boards";
		$this->genPageTop($board_name);
		
		// Create the table header
		echo "<table><tr><th>Topic Name</th><th class='name'>Created By</th><th class='msgno'>Msgs</th><th class='date'>Last Post</th></tr>";
		
		// Create the table rows for each result
		while($row = mysqli_fetch_array($this->result)){
			// Bold pinned topics
			$topic_name = (($row[DBV::$topic_pinned]) == 1) ? '<b>'.$row[DBV::$topic_name].'</b>' : $row[DBV::$topic_name];
			
			echo "<tr><td><a href='showmessages.php?topic=". $row[DBV::$topic_id] ."'>". $topic_name ."</a></td><td><a href='profile.php?user=".$row[DBV::$user_id]."'>". $row[DBV::$user_name] .
			"</a></td><td>". $row[DBV::$topic_msgno] ."</td><td>". date(DBV::$date, strtotime($row[DBV::$topic_lastpost])) ."</td></tr>";
		}
		
		// Next page, Users browsing and end the table 
		
		// TODO : Adjust so that it includes people not in topics
		
		$this->nextPage($col_span, DBV::$topics);
		
		// TODO : Make some kind of 'users reading this page' system
		
		$sql ="	SELECT 	".DBV::$user_ctopic."
				FROM 	".DBV::$users."
				WHERE 	".DBV::$user_ctopic." <> NULL";
		$count = mysqli_num_rows(mysqli_query($this->con, $sql)) + 1;
		echo "<tr><th class='menu' colspan='". $col_span ."'><a>".$count." users currently reading this board</a></th></tr>";
		
		// This closes out all remaining tags, may be used for supplying additional information later on
		$this->genPageBottom();
		
	}
	
	public function genShowMessages($topic_id_val){
		
		// Result fields : msg_id, msg_topicid, $msgs_userid, $msg_date, $msg_content, $user_name
		// Board page 'constants' and generate page header
		
		if($this->checkInvalid()){ return false;}

		$this->UpdateField(DBV::$users, DBV::$user_ctopic, $topic_id_val, DBV::$user_id, $_SESSION[DBV::$ses_id]);
		
		$col_span = 2;
		$topic_name = $this->getField(DBV::$topics, DBV::$topic_name, "WHERE ".DBV::$topic_id." = ".$topic_id_val);
		$this->genPageTop($topic_name, $col_span);
		
		// Open the table
		echo '<table>';
		
		// TODO : Randomly generate the 'looks like' to give more results
		// TODO : Userpics; Signature built into message; 'Titles'
		// Store index so page refreshing does not change the string
		while($row = mysqli_fetch_array($this->result)){
			
			// Like string
			$like_str = ($row[DBV::$msg_likes] == 0) ? "" : " | <a class='like' id='who_like' href='javascript:;'>".$row[DBV::$msg_likes]." ".(($row[DBV::$msg_likes] == 1) ? "person":"people")."</a> like this post";
			
			echo "
			<tr><th colspan='".$col_span."' class='msgh'><a href='profile.php?id=".$row[DBV::$user_id]."'>
			".$row[DBV::$user_name]."</a> posted this message. 
			When? ".date(DBV::$date, strtotime($row[DBV::$msg_date]))." <a class='like' id='".$row[DBV::$msg_id]."' href='javascript:;'>Like</a>".$like_str."</th></tr>
			<tr>
				<td class='msg'><pre>".$row[DBV::$msg_content]."</pre></td>
				<td class='avatar'><img class='profile' src='../img/ava/ava (".$row[DBV::$user_id].").jpg'/></td>
			</tr>
			";
		}
		
		$this->nextPage($col_span, DBV::$msgs, 'WHERE '.DBV::$topic_id.'='.$topic_id_val);
		
		// Bottom of the page 
		$people_reading = $this->countFieldWhere(DBV::$users, DBV::$user_ctopic, $topic_id_val);
		$people_reading = ($people_reading == 1) ? 'is currently '.$people_reading.' person' : 'are currently '.$people_reading.' people';
		echo "<tr><th class='menu' colspan='4'>There ".$people_reading." reading this topic.</th></tr>";

		// Close the table
		echo '</table>';
		
		// Hamfisted way of avoiding updating every time any action is taken
		$_SESSION[DBV::$ses_ut] = True;
		
		// Message box
		genMessageBox();
		
		// Currently not in much use
		$this->genPageBottom();
	}
	
	public function genUserList($first_user = 1){
		
		if($this->checkInvalid()){ return false;}
		
		$col_span = 4;
		$page_name = "Userlist";
		$this->genPageTop($page_name, $col_span);
		
		// Open table
		echo "<table>";
		
		// Output the table header
		echo "
			<tr><th>User ID</th><th>Username</th>
			<th>Account Created</th><th>Last Online</th></tr>
		";
		
		while($row = mysqli_fetch_array($this->result)){
		echo "
			<tr>
				<td id='id'>". $row[DBV::$user_id] ."</td>
				<td><a href='profile.php?user=" . $row[DBV::$user_id] . "'>" . $row[DBV::$user_name] . "</a></td>
				<td id='date'>". date(DBV::$date, strtotime($row[DBV::$user_joindate])) ."</td>
				<td id='date'>".date(DBV::$date, strtotime($row[DBV::$user_actdate]))."</td>
			</tr>				
			";
		}
		
		// Next page field
		$this->nextPage($col_span, DBV::$users);
		
		// Close table
		echo "</table>";
		
		$this->genPageBottom();
	}
	
	public function genProfile(){
		
		if($this->checkInvalid()){ return false;}
		
		// All required information is in this fetch
		$row = mysqli_fetch_array($this->result);
		
		$col_span = 2;
		$page_name = $row[DBV::$user_name];	
		$this->genPageTop($page_name, $col_span);
		
		// Open table
		echo "<table>";
		
		// Output Table Stats
		echo "
			<tr><th colspan='".$col_span."'>Stats on ".$row[DBV::$user_name]."</th></tr>
			<tr><td width='1px'>Username</td>		<td>".$row[DBV::$user_name]." (gt-bt count)</td></tr>
			<tr><td>User ID</td>		<td>".$row[DBV::$user_id]."</td></tr>
			<tr><td>???</td>		<td>???</td></tr>
			<tr><td>???</td>		<td>???</td></tr>
			<tr><td class='wrap'>Account Created</td><td>".date(DBV::$date, strtotime($row[DBV::$user_joindate]))."</td></tr>
			<tr><td>Last Active</td>	<td>".date(DBV::$date, strtotime($row[DBV::$user_actdate]))."</td></tr>
			<tr><td>???</td>		<td>???</td>
			";
			
		// Output Options
		echo "
			<tr><th colspan='".$col_span."'>Options						</th></tr>
			<tr><td colspan='".$col_span."'><a>[Currently Disabled]</td></tr>
			<tr><td colspan='".$col_span."'><a>[Currently Disabled]</td></tr>
			";
			
		// Output Personal Options if this is your userpage
		if($row[DBV::$user_id] == $_SESSION[DBV::$ses_id]){
		echo "
			<tr><th colspan='".$col_span."'>Personal Options							</th></tr>
			<tr><td colspan='".$col_span."'><a href='useroptions.php'>Change Password or Signature</a></th></tr>
			<tr><td colspan='".$col_span."'><a>[Currently Disabled]</a>					</th></tr>
			";
		}
		
		
		// Close table
		echo "<tr><th colspan='".$col_span." 'style='padding-top:16px'></th></tr><tr><th colspan='".$col_span."' class='menu' style='padding-top:16px'></th></tr>";
		echo "</table>";
		$this->genPageBottom();
	}
	

}

class Redir 
{
	// Redirect to login page 
	public function login(){
		if(!isset($_SESSION[DBV::$ses_id])){
			header("location:login.php");
		}
	}
	
	// Redirect to main page
	public function boards(){
		if(isset($_SESSION[DBV::$ses_id])){
			header("location:boards.php");
		}
	}
}

/**
 * Get the current Url taking into account Https and Port
 * @link http://css-tricks.com/snippets/php/get-current-page-url/
 * @version Refactored by @AlexParraSilva
 */
function getCurrentUrl() {
	$url  = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
	$url .= '://' . $_SERVER['SERVER_NAME'];
	$url .= in_array( $_SERVER['SERVER_PORT'], array('80', '443') ) ? '' : ':' . $_SERVER['SERVER_PORT'];
	$url .= $_SERVER['REQUEST_URI'];
	return $url;
}

class DBV
{
		// A collection of names to avoid needing to change too much code
	public static $msgs="msgs"; 		// Message table name 
	public static $topics="topics"; 	// Topic table name
	public static $users="users"; 		// User table name

	public static $date="d/m/y g:i:s A";
	
	// topic row names
	public static $topic_id = "topic_id";
	public static $topic_name = "topicname";
	public static $topic_userid = "user_id";
	public static $topic_timecreated = "time_created";
	public static $topic_pinned = "pinned";
	public static $topic_lastpost = "time_lastpost";
	public static $topic_msgno = "msgs";

	// message row names
	public static $msg_id = "msgs_id";
	public static $msg_userid = "user_id";
	public static $msg_topicid = "topic_id";
	public static $msg_date = "date";
	public static $msg_content = "content";
	public static $msg_likes = "likes";
	public static $msg_who_likes = "who_likes";

	// user row names
	public static $user_name="username";
	public static $user_id="user_id";
	public static $user_pass = "pass";
	public static $user_joindate = "jdate";
		/* currently not in use */
	public static $user_actdate = "adate";
	public static $user_sig = "sig";
	public static $user_email = "email";
	public static $user_ctopic = "cur_topic";
	public static $user_lastpost = "time_lastpost";
		/* very not in use */
	public static $user_likes = "likes";
	public static $user_tokens = "tokens";
	public static $user_spenttokens = "spent_tokens";
	public static $user_gtokens = "good_tokens";
	public static $user_btokens = "bad_tokens";

	public static $ses_id = "id";
	public static $ses_un = "username";
	public static $ses_ut = "update_topic";
	
	public static $query_limit = 50;
}

/* MISC FUNCTIONS */
function genMessageBox(){
$DB_wrap = new DatabaseWrapper();
	echo "
<script type='text/javascript' src='../js/jquery.min.js'></script>
<script type='text/javascript' src='../js/jquery-textrange.js'></script>
<script type='text/javascript' src='../js/message_functions.js'></script>
<script type='text/javascript' src='../js/showmessages.js'></script>
<div class='space' id='absolute' style='padding:5px;'>
	<div id='append'>
		<div id='resp_area' class='warning'></div>
		<form id='newmessage'>
		<p><b>Your Message</b></p>
		<button id='b' type='button'>Bold</button>
		<button id='i' type='button'>Italic</button>
		<button id='u' type='button'>Underline</button>
		<button id='quote' type='button'>Quote</button>
		<button id='img' type='button'>Image</button>
		<br>
		<textarea id='msg' name='msg' maxlength = '7800'>".$DB_wrap->getSig()."</textarea>
		<br>
		<button id='sub' type='button' class='submit'>Submit Message</button>
		<button id='preview' type='button' class='submit'>Preview Message</button>
		</form>
	</div>
</div>
<img id='display' src='../img/plus.png' class='plus'/>
<div id='fixed' class='fixed'>
</div>";
	
}
?>