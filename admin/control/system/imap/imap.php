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

// Priority levels and statuses
include(BASE_PATH . 'control/system/loader.php');

$title = (isset($_GET['edit']) ? $msg_imap25 : $msadminlang3_7[11]);

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/imap/imap.php');
include(PATH . 'templates/footer.php');

?>