<?php
include 'main_header.php';
$DB_wrap = new DatabaseWrapper();

if(isset($_GET["id"])){
	$get = $_GET["id"];
} else {
	$get = 1;
}
$result = $DB_wrap->getUserlist($get);
$Page_Gen = new PageGenerator($result, $DB_wrap->GetConnection());
$Page_Gen->genUserlist($get,'topic');
?>