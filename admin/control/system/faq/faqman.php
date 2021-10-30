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

if (isset($_GET['view'])) {
  include(PATH . 'templates/system/faq/faq-window.php');
  exit;
}

$title     = $msg_adheader47;
$loadiBox  = true;
$loadBBCSS = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/faq/faqman.php');
include(PATH . 'templates/footer.php');

?>