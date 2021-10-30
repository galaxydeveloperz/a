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

$title = (isset($_GET['edit']) ? $msg_accounts6 : $msg_adheader39);
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/accounts/accounts.php');
include(PATH . 'templates/footer.php');

?>