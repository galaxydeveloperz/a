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

$title = (isset($_GET['edit']) ? $msg_levels5 : $msg_adheader50);

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/levels/levels.php');
include(PATH . 'templates/footer.php');

?>