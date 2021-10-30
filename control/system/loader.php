<?php

if (!defined('PARENT')) { exit; }

/* PRIORITY LEVELS & STATUSES
---------------------------------------------------------*/

$ticketLevelSel  = $MSYS->levels('', true);
$ticketStatusSel = $MSYS->statuses('', true);
$levelPrKeys     = array_keys($ticketLevelSel);
$statusPrKeys    = array_keys($ticketStatusSel);

// Admin or frontend
if (defined('ADMIN_PANEL') && isset($MSTICKET) && method_exists($MSTICKET, 'locker')) {
  $MSTICKET->tk_levels    = $ticketLevelSel;
  $MSTICKET->tk_statuses  = $ticketStatusSel;
} else {
  if (isset($MSTICKET) && method_exists($MSTICKET, 'isTicketOpen')) {
    $MSTICKET->tk_levels    = $ticketLevelSel;
    $MSTICKET->tk_statuses  = $ticketStatusSel;
  }
}

?>
