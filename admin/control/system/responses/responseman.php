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

// View preview..
if (isset($_GET['view'])) {
  include(PATH.'templates/system/responses/responses-window.php');
  exit;
}

$title     = $msg_adheader54;
$loadiBox  = true;
$loadBBCSS = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/responses/responses-man.php');
include(PATH . 'templates/footer.php');

?>