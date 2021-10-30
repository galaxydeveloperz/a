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
  include(PATH.'templates/system/pages/page-window.php');
  exit;
}

$title     = $msadminlang3_1cspages[2];
$loadiBox  = true;
$loadBBCSS = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/pages/pageman.php');
include(PATH . 'templates/footer.php');

?>