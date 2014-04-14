<?php
include 'main_header.php';
$page_gen = new ChangeUser();

if(strlen($_POST['cpw'])!=0 && strlen($_POST['npw'])!=0) { $pck = $page_gen->ChangePassword($_POST['npw'],$_POST['cpw']); }
if(strlen($_POST['sig'])!=0) {$page_gen->ChangeSig($_POST['sig']); }
?>