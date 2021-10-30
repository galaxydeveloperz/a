<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT')) {
  $HEADERS->err403(true);
}

define('TICKET_VIEW_LOADED', 1);
define('DROPZONE_LOADER', 1);

// Priority levels and statuses
include(BASE_PATH . 'control/system/loader.php');

// Print mode?
if (isset($_GET['print'])) {
  $_GET['id'] = (int) $_GET['print'];
  define('PRINT_MODE_ENABLED', 1);
}

// Access..
if (!in_array('assign', $userAccess) && !in_array('open', $userAccess) &&
    !in_array('close', $userAccess) && !in_array('search', $userAccess) &&
    !in_array('odis', $userAccess) && !in_array('cdis', $userAccess) && USER_ADMINISTRATOR != 'yes') {
  $HEADERS->err403(true);
}

// Merge redirect?
if (isset($_GET['merged'])) {
  $title      = $msadminlang3_1adminviewticket[19];
  $ID         = (int) $_GET['merged'];
  $SUPTICK    = mswSQL_table('tickets', 'id', $ID);
  $metaReload = '<meta http-equiv="refresh" content="' . TICK_MERGE_RDR_TIME . ';url=index.php?p=view-ticket&id=' . $ID . '">';
  include(PATH . 'templates/header.php');
  include(PATH . 'templates/system/tickets/tickets-merge-msg.php');
  include(PATH . 'templates/footer.php');
  exit;
}

// Add to history..
if (isset($_GET['addHis']) && $SETTINGS->ticketHistory == 'yes' && (USER_ADMINISTRATOR == 'yes' || $MSTEAM->ticketHistory == 'yes')) {
  mswVLDG($_GET['addHis'], true);
  // Does ticket exists..
  $SUPTICK = mswSQL_table('tickets', 'id', $_GET['addHis']);
  if (!isset($SUPTICK->id)) {
    $HEADERS->err404(true);
  }
  include(PATH . 'templates/system/tickets/tickets-history-add.php');
  exit;
}

// Export history..
if (isset($_GET['exportHistory']) && $SETTINGS->ticketHistory == 'yes' && (USER_ADMINISTRATOR == 'yes' || $MSTEAM->ticketHistory == 'yes')) {
  mswVLDG($_GET['exportHistory'], true);
  // Does ticket exists..
  $SUPTICK = mswSQL_table('tickets', 'id', $_GET['exportHistory']);
  if (!isset($SUPTICK->id)) {
    $HEADERS->err404(true);
  }
  // Check permissions for this log..
  if (mswDeptPerms($SUPTICK->department, $userDeptAccess, array('assigned' => $SUPTICK->assignedto, 'team' => $MSTEAM->id)) == 'fail') {
    $HEADERS->err403(true);
  }
  include_once(BASE_PATH . 'control/classes/system/class.download.php');
  $MSDL = new msDownload();
  $MSTICKET->exportTicketHistory($MSDL, $MSDT);
}

// At this point id should exist..
if (!isset($_GET['id'])) {
  $HEADERS->err403(true);
}

// Check digit..
mswVLDG($_GET['id'], true);

// Enable lock mechanism..
if (!isset($_GET['quickView'])) {
  $MSTICKET->locker(array(
    'action' => 'lock',
    'ticket' => (int) $_GET['id'],
    'team' => $MSTEAM->id
  ));
}

// Load ticket data..
$SUPTICK = mswSQL_table('tickets', 'id', (int) $_GET['id']);

// Checks..
if (!isset($SUPTICK->id)) {
  $HEADERS->err404(true);
}

// Is ticket locked?
if ($SETTINGS->adminlock == 'yes' && $SUPTICK->lockteam > 0 && $SUPTICK->lockteam != $MSTEAM->id) {
  define('TICKET_TEAM_LOCK', 1);
}

// Edit notes..
if (isset($_GET['editNotes']) && ($MSTEAM->notePadEnable == 'yes' || USER_ADMINISTRATOR == 'yes')) {
  include(PATH . 'templates/system/tickets/tickets-notes.php');
  exit;
}

// Show statuses..
if (isset($_GET['showStatuses'])) {
  include(PATH . 'templates/system/tickets/tickets-other-statuses.php');
  exit;
}

// Users in dispute..
if (isset($_GET['dis_users'])) {
  include(PATH . 'templates/system/tickets/tickets-dispute-users-list.php');
  exit;
}

// Assigned staff..
if (isset($_GET['as_staff'])) {
  include(PATH . 'templates/system/tickets/tickets-assigned-staff.php');
  exit;
}

// Quick view..
if (isset($_GET['quickView'])) {
  include(PATH . 'templates/system/tickets/tickets-quick-view.php');
  exit;
}

// Department check..
if (mswDeptPerms($SUPTICK->department, $userDeptAccess, array('assigned' => $SUPTICK->assignedto, 'team' => $MSTEAM->id)) == 'fail') {
  $HEADERS->err403(true);
}

// Add reply..
if (isset($_POST['process'])) {
  define('TICKET_REPLY', 1);
  include(PATH . 'control/system/tickets/ticket-reply.php');
}

// Assign visitor name/email..
$VIS            = mswSQL_table('portal', 'id', $SUPTICK->visitorID);
$SUPTICK->name  = (isset($VIS->name) ? $VIS->name : $msg_script17);
$SUPTICK->email = (isset($VIS->email) ? $VIS->email : $msg_script17);

// Check for custom ticket status action..
if (isset($_GET['act']) && substr($_GET['act'], 0, 7) == 'status-') {
  $cstStatus = (int) substr($_GET['act'], 7);
  if (isset($ticketStatusSel[$cstStatus][0]) && $cstStatus > 3) {
    $_GET['act'] = 'status-change';
    $_GET['status-change-id'] = $cstStatus;
  }
}
// Update status..
if (!defined('TICKET_TEAM_LOCK') && isset($_GET['act']) && in_array($_GET['act'], array(
  'open',
  'close',
  'lock',
  'ticket',
  'dispute',
  'reopen',
  'spam-del',
  'to-spam',
  'status-change'
))) {
  switch($_GET['act']) {
    case 'spam-del':
      $_POST['del'] = array($_GET['id']);
      if (USER_DEL_PRIV == 'yes') {
        $MSTICKET->deleteTickets();
      }
      header("Location: index.php?p=spam");
      exit;
      break;
    default:
      switch($_GET['act']) {
        case 'to-spam':
          $action = str_replace('{user}', $MSTEAM->name, $msg_ticket_history['ticket-status-spam']);
          break;
        case 'status-change':
          $action = str_replace(array('{status}','{user}'), array($ticketStatusSel[$cstStatus][0],$MSTEAM->name), $msg_ticket_history['admin-custom-status-change']);
          break;
        default:
          $action = str_replace('{user}', $MSTEAM->name, $msg_ticket_history['ticket-status-' . $_GET['act']]);
          break;
      }
      if ($_GET['act'] == 'close' && $MSTEAM->close == 'no' && USER_ADMINISTRATOR == 'no') {
        $HEADERS->err403(true);
      }
      if ($_GET['act'] == 'lock' && $MSTEAM->lock == 'no' && USER_ADMINISTRATOR == 'no') {
        $HEADERS->err403(true);
      }
      if ($_GET['act'] == 'to-spam' && USER_ADMINISTRATOR == 'no' && !in_array('spam',$userAccess)) {
        $HEADERS->err403(true);
      }
      $rows = $MSTICKET->updateTicketStatus();
      // History if affected rows..
      if ($rows > 0) {
        $MSTICKET->historyLog($_GET['id'], str_replace(array(
          '{user}'
        ), array(
          $MSTEAM->name
        ), $action));
        $SUPTICK        = mswSQL_table('tickets', 'id', (int) $_GET['id']);
        $SUPTICK->name  = (isset($VIS->name) ? $VIS->name : $msg_script17);
        $SUPTICK->email = (isset($VIS->email) ? $VIS->email : $msg_script17);
        switch($_GET['act']) {
          case 'dispute':
            $actionMsg = str_replace('{id}', $_GET['id'], $msg_ticket_actioned[$_GET['act']]);
            break;
          case 'to-spam':
            $actionMsg = str_replace('{id}', $_GET['id'], $msg_ticket_actioned['spam']);
            break;
          case 'status-change':
            $actionMsg = str_replace('{status}', mswSH($ticketStatusSel[$cstStatus][0]), $msg_ticket_actioned['status-changed']);
            break;
          default:
            switch($_GET['act']) {
              case 'open':
              case 'reopen':
              case 'close':
              case 'lock':
                $cstStatus = ($_GET['act'] == 'lock' ? 'closed' : ($_GET['act'] == 'reopen' ? 'open' : $_GET['act']));
                if (isset($ticketStatusSel[$cstStatus][0])) {
                  $actionMsg = str_replace(array('{status}','{user}'), array($ticketStatusSel[$cstStatus][0],$MSTEAM->name), $msg_ticket_actioned['status-changed']);
                } else {
                  $actionMsg = $msg_ticket_actioned[$_GET['act']];
                }
                break;
              default:
                $actionMsg = $msg_ticket_actioned[$_GET['act']];
                break;
            }
            break;
        }
      }
      break;
  }
}

$title        = str_replace('{ticket}', mswTicketNumber($_GET['id'], $SETTINGS->minTickDigits, $SUPTICK->tickno), ($SUPTICK->isDisputed == 'yes' ? $msg_viewticket80 : $msg_viewticket));
$loadBBCSS    = true;
$loadiBox     = true;
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-view' . ($SUPTICK->isDisputed == 'yes' ? '-disputed' : '') . '.php');
include(PATH . 'templates/footer.php');

?>