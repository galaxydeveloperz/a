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
  include(PATH . 'templates/system/settings/tags.php');
  exit;
}

$title = $msg_adheader18;
$loadiBox  = true;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/settings/tools.php');
include(PATH . 'templates/footer.php');

?>