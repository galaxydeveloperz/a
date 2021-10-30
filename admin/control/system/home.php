<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT')) {
  $HEADERS->err403(true);
}

mswClearExportFiles();

$SSN->delete(array('adm_menu_panel'));

if (isset($_GET['sys_overview'])) {
  // Priority levels and statuses
  include(BASE_PATH . 'control/system/loader.php');
  include(PATH . 'templates/system/home/overview.php');
  exit;
}

if (isset($_GET['ticket_locks'])) {
  include(PATH . 'templates/system/home/ticket-locks.php');
  exit;
}

// Priority levels and statuses
include(BASE_PATH . 'control/system/loader.php');

// Call relevant classes..
include_once(BASE_PATH . 'control/classes/class.tickets.php');
$MSPTICKETS           = new tickets();
$MSPTICKETS->settings = $SETTINGS;
$MSPTICKETS->datetime = $MSDT;

$title      = $msg_adheader11;
$loadJQAPI  = true;
$loadGraph = true;
$loadiBox  = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/dashboard.php');
include(PATH . 'templates/footer.php');

?>