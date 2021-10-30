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

// Priority levels and statuses
include(BASE_PATH . 'control/system/loader.php');

// Department check for filter..
if (isset($_GET['dept'])) {
  // Are we viewing assigned department?
  if (substr($_GET['dept'], 0, 1) == 'u') {
    if (USER_ADMINISTRATOR == 'no' && $MSTEAM->id != substr($_GET['dept'], 1)) {
      $HEADERS->err403(true);
    }
  } else{
    if (mswDeptPerms($_GET['dept'], $userDeptAccess) == 'fail') {
      $HEADERS->err403(true);
    }
  }
}

// Call relevant classes..
include_once(BASE_PATH . 'control/classes/class.tickets.php');
$MSPTICKETS           = new tickets();
$MSPTICKETS->settings = $SETTINGS;
$MSPTICKETS->datetime = $MSDT;
$title                = (isset($_GET['keys']) ? $msg_search6 : $msg_header18);
$loadiBox             = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-search-fields.php');
include(PATH . 'templates/footer.php');

?>