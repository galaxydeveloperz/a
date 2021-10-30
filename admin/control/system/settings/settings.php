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

if (isset($_GET['mailTest'])) {
  include(PATH . 'templates/system/settings/mail-test.php');
  exit;
}

$title    = $msg_adheader2;
$loadiBox = true;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/settings/settings.php');
include(PATH . 'templates/footer.php');

?>