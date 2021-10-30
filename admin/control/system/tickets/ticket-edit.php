<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT') || (!in_array('add', $userAccess) && USER_ADMINISTRATOR == 'no') || USER_EDIT_T_PRIV == 'no') {
  $HEADERS->err403(true);
}

// Check digit..
mswVLDG($_GET['id'], true);

// Get ticket data..
$SUPTICK = mswSQL_table('tickets', 'id', $_GET['id']);

// Checks..
if (!isset($SUPTICK->id)) {
  $HEADERS->err404(true);
  exit;
}

// Priority levels and statuses
include(BASE_PATH . 'control/system/loader.php');

// Department check..
if (mswDeptPerms($SUPTICK->department, $userDeptAccess, array('assigned' => $SUPTICK->assignedto, 'team' => $MSTEAM->id)) == 'fail') {
  $HEADERS->err403(true);
}

$title      = str_replace('{ticket}', mswTicketNumber($SUPTICK->id, $SETTINGS->minTickDigits, $SUPTICK->tickno), $msg_viewticket20);
$loadBBCSS  = true;
$loadiBox   = true;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-edit.php');
include(PATH . 'templates/footer.php');

?>