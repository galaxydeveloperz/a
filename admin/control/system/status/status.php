<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT')) {
  $HEADERS->err403(true);
}

// Status view
if (isset($_GET['t_status'])) {
  if (in_array($_GET['t_status'], array(1,2,3,'open','close','closed'))) {
    header("Location: index.php?p=" . (in_array($_GET['t_status'], array(1,'open')) ? 'open' : 'close'));
  } else {
    $statusID = (int) $_GET['t_status'];
    if (in_array('status=' . $statusID, $userAccess) || USER_ADMINISTRATOR == 'yes') {
      $C_STAT = mswSQL_table('statuses', 'id', $statusID);
      if (isset($C_STAT->id)) {
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
        $title                = $msg_viewticket7 . ' > ' . mswSH($C_STAT->name);
        $loadiBox             = true;
        include(PATH . 'templates/header.php');
        include(PATH . 'templates/system/tickets/tickets-status.php');
        include(PATH . 'templates/footer.php');
      } else {
        $HEADERS->err403(true);
      }
    } else {
      $HEADERS->err403(true);
    }
  }
  exit;
}

// Access..
if (!in_array($cmd, $userAccess) && USER_ADMINISTRATOR != 'yes') {
  $HEADERS->err403(true);
}

$title = (isset($_GET['edit']) ? $msticketstatuses4_3[3] : $msticketstatuses4_3[1]);

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/status/status.php');
include(PATH . 'templates/footer.php');

?>