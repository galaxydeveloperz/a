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

$title           = (isset($_GET['edit']) ? $msg_response13 : $msg_adheader53);
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/responses/responses.php');
include(PATH . 'templates/footer.php');

?>