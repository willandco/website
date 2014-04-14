<?php
include 'main_header.php';
$DB_wrap = new DatabaseWrapper();

$result = $DB_wrap->getProfile($_GET["user"]);
$Page_Gen = new PageGenerator($result, $DB_wrap->GetConnection());
$Page_Gen->genProfile();
?>