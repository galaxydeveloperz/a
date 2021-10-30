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

$title = $msg_dept9;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/dept/deptman.php');
include(PATH . 'templates/footer.php');

?>