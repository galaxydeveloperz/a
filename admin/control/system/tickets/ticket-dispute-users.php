<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT') || (!isset($_GET['disputeUsers']) && !isset($_GET['changeState'])) || $SETTINGS->disputes == 'no') {
  $HEADERS->err403(true);
}

// Access..
if (!in_array($cmd, $userAccess) && USER_ADMINISTRATOR != 'yes') {
  $HEADERS->err403(true);
}

// Priority levels and statuses
include(BASE_PATH . 'control/system/loader.php');

// Check digit..
mswVLDG($_GET['disputeUsers'], true);

// Load ticket data..
$SUPTICK = mswSQL_table('tickets', 'id', $_GET['disputeUsers']);

// Checks..
if (!isset($SUPTICK->id)) {
  $HEADERS->err404(true);
  exit;
}

// Department check..
if (mswDeptPerms($SUPTICK->department, $userDeptAccess, array('assigned' => $SUPTICK->assignedto, 'team' => $MSTEAM->id)) == 'fail') {
  $HEADERS->err403(true);
}

$title = $msg_disputes8 . ' (#' . mswTicketNumber($_GET['disputeUsers'], $SETTINGS->minTickDigits, $SUPTICK->tickno) . ')';

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-dispute-users.php');
include(PATH . 'templates/footer.php');

?>