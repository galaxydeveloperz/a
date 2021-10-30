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

if (isset($_GET['pr_acc'])) {
  include(PATH . 'templates/system/faq/faq-cat-accounts.php');
  exit;
}

$title = $msg_adheader45;
$loadiBox  = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/faq/faq-catman.php');
include(PATH . 'templates/footer.php');

?>