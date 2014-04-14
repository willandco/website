<?php
include 'main_header.php';
$DB_Wrap = new DatabaseWrapper();
$title = $_POST['title'];
$msgbody = $_POST['msg'];
$DB_Wrap->NewTopic($title, $msgbody);
?>