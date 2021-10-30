<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT')) {
  $HEADERS->err403(true);
}

if ($cmd == 'logout') {
  @session_unset();
  @session_destroy();
  $SSN->delete(array('_ms_mail', '_ms_key', 'autoPurgeRan'));
  // Clear cookies..
  if ($SSN->active_c('_msc_mail') == 'yes') {
    $SSN->delete_c(array('_msc_mail', '_msc_key'));
  }
  header("Location: index.php?p=login");
  exit;
}

// Are we already logged in via cookie..
if (isset($MSTEAM->name)) {
  header("Location: index.php");
  exit;
}

include(PATH . 'templates/system/login.php');

?>