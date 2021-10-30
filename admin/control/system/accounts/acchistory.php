<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT') || !isset($_GET['id'])) {
  $HEADERS->err403(true);
}

// Access..
if (!in_array($cmd, $userAccess) && USER_ADMINISTRATOR != 'yes') {
  $HEADERS->err403(true);
}

// Get account info..
mswVLDG($_GET['id'], true);

$ACC = mswSQL_table('portal', 'id', $_GET['id']);

// Checks..
if (!isset($ACC->id)) {
  $HEADERS->err403(true);
  exit;
}

include_once(BASE_PATH . 'control/classes/class.tickets.php');
$MSPTICKETS           = new tickets();
$MSPTICKETS->settings = $SETTINGS;
$MSPTICKETS->datetime = $MSDT;
$title                = $msg_header11;
$loadiBox             = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/accounts/history.php');
include(PATH . 'templates/footer.php');

?>