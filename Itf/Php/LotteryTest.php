<?php
require_once('CmnMis/IncludeFiles.php');
require_once('CmnActivity/Lottery.php');

$_userID="1";
$_prizePoolIDs = array();
array_push($_prizePoolIDs,"1");

$_lottery = new Lottery($_userID,$_prizePoolIDs);
$_prizeInfo = $_lottery->Execute();
echo json_encode($_prizeInfo);

?>