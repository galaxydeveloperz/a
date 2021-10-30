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

$title = $msg_adheader40;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/accounts/accountman.php');
include(PATH . 'templates/footer.php');

?>