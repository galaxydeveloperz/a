<?php

/* System - Account Close
----------------------------------------------------------*/

if (!defined('PARENT') || !defined('MS_PERMISSIONS') || $SETTINGS->visclose == 'no') {
  $HEADERS->err403();
}

// Check log in..
if (MS_PERMISSIONS == 'guest' || !isset($LI_ACC->id)) {
  header("Location:index.php?p=login");
  exit;
}

// Variables..
$title  = $mspubliclang4_2[0];

include(PATH . 'control/header.php');

// Show..
$tpl = new Savant3();
$tpl->assign('TXT', array(
 $msg_header3,
 $mspubliclang4_2[0],
 $mspubliclang4_2
));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/account-close.tpl.php');

include(PATH . 'control/footer.php');

?>