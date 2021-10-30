<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT')) {
  $HEADERS->err403(true);
}

// Access..
if (!in_array($cmd, $userAccess) && USER_ADMINISTRATOR != 'yes') {
  $HEADERS->err403(true);
}

// Check global user..
if (isset($_GET['edit']) && $_GET['edit'] == '1' && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

$title    = (isset($_GET['edit']) ? $msg_user14 : $msg_adheader57);
$loadiBox = true;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/team/team.php');
include(PATH . 'templates/footer.php');

?>