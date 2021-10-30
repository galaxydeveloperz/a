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

$title           = (isset($_GET['edit']) ? $msg_kbase13 : $msg_adheader46);
$loadBBCSS       = true;
$textareaFullScr = true;
$loadiBox        = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/faq/faq.php');
include(PATH . 'templates/footer.php');

?>