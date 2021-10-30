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

$title = $msg_adheader43;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/fields/fieldsman.php');
include(PATH . 'templates/footer.php');

?>