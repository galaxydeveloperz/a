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

$title = (isset($_GET['edit']) ? $msg_kbasecats5 : $msg_kbase16);

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/faq/faq-cat.php');
include(PATH . 'templates/footer.php');

?>