<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT') || USER_EDIT_R_PRIV == 'no') {
  $HEADERS->err403(true);
}

// Priority levels and statuses
include(BASE_PATH . 'control/system/loader.php');

// Check digit..
mswVLDG($_GET['id'], true);

// Get reply..
$REPLY = mswSQL_table('replies', 'id', $_GET['id']);

// Checks..
if (!isset($REPLY->id)) {
  $HEADERS->err404(true);
}

// Get ticket data..
$SUPTICK = mswSQL_table('tickets', 'id', $REPLY->ticketID);

// Checks..
if (!isset($SUPTICK->id)) {
  $HEADERS->err403(true);
}

// Department check..
if (mswDeptPerms($SUPTICK->department, $userDeptAccess, array('assigned' => $SUPTICK->assignedto, 'team' => $MSTEAM->id)) == 'fail') {
  $HEADERS->err403(true);
}

$title      = $msg_viewticket36;
$loadBBCSS  = true;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-edit-reply.php');
include(PATH . 'templates/footer.php');

?>