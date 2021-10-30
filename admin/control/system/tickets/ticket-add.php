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

define('DROPZONE_LOADER', 1);

$title           = $msg_open;
$loadBBCSS       = true;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-add.php');
include(PATH . 'templates/footer.php');

?>