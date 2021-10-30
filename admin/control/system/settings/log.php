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

$title = $msg_adheader20;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/settings/log.php');
include(PATH . 'templates/footer.php');

?>