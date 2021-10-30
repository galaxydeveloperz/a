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

$title = (isset($_GET['edit']) ? $msg_customfields11 : $msg_customfields2);
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/fields/fields.php');
include(PATH . 'templates/footer.php');

?>