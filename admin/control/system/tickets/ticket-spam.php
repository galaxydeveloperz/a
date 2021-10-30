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

// Auto spam clear..
if ($SETTINGS->autospam > 0) {
  $spamCleared = $MSTICKET->autoClearSpam();
}

// Call relevant classes..
include_once(BASE_PATH . 'control/classes/class.tickets.php');
$MSPTICKETS           = new tickets();
$MSPTICKETS->settings = $SETTINGS;
$MSPTICKETS->datetime = $MSDT;
$title                = $msg_adheader63;
$loadiBox             = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-spam.php');
include(PATH . 'templates/footer.php');

?>