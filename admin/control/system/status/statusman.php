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

$title = $msticketstatuses4_3[2];

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/status/statusman.php');
include(PATH . 'templates/footer.php');

?>