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

if (isset($_GET['mtags'])) {
  include(PATH . 'templates/system/dept/tags.php');
  exit;
}

// Priority levels and statuses
include(BASE_PATH . 'control/system/loader.php');

$title = (isset($_GET['edit']) ? $msg_dept5 : $msg_dept2);
$loadiBox  = true;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/dept/dept.php');
include(PATH . 'templates/footer.php');

?>