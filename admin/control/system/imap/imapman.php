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

$title = $msadminlang3_1[4];
$loadiBox = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/imap/imapman.php');
include(PATH . 'templates/footer.php');

?>