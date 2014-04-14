<?php
include 'main_header.php';
$DB_wrap = new DatabaseWrapper();
$DB_wrap->NewMessage($_POST['msg']);
?>