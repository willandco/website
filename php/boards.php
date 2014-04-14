<?php
include 'main_header.php';
$DB_Wrap = new DatabaseWrapper();
$result = $DB_Wrap->getBoards();

// Page Generator requires old connection to continue 
$Page_Gen = new PageGenerator($result, $DB_Wrap->GetConnection());
$Page_Gen->genBoards();
?>