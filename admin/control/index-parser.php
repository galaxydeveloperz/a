<?php

/* Admin Index Parser
----------------------------------------------------------*/

if (!defined('PARENT')) {
  $HEADERS->err403(true);
}

// For login, must NOT be changed..
$li_team = (isset($MSTEAM->name) ? $MSTEAM : '');

// Check for status view
if (isset($_GET['t_status'])) {
  $cmd = 'status';
}

switch ($cmd) {
  // System..
  case 'home':
  case 'purchase':
  case 'bbCode':
    mswIsLoggedIn($li_team, $SSN);
    $MSTICKET->blankStatuses();
    include(PATH . 'control/system/' . $cmd . '.php');
    break;
  // Statuses..
  case 'status':
  case 'statusman':
    mswIsLoggedIn($li_team, $SSN);
    $MSTICKET->blankStatuses();
    include(PATH . 'control/system/status/' . $cmd . '.php');
    break;
  // Priority levels..
  case 'levels':
  case 'levelsman':
    mswIsLoggedIn($li_team, $SSN);
    include(PATH . 'control/system/levels/' . $cmd . '.php');
    break;
  // Custom Pages..
  case 'pages':
  case 'pageman':
    mswIsLoggedIn($li_team, $SSN);
    include(PATH . 'control/system/pages/' . $cmd . '.php');
    break;
  // Custom Fields..
  case 'fields':
  case 'fieldsman':
    mswIsLoggedIn($li_team, $SSN);
    include(PATH . 'control/system/fields/' . $cmd . '.php');
    break;
  // Imap..
  case 'imap':
  case 'imapman':
  case 'imapban':
    mswIsLoggedIn($li_team, $SSN);
    include(PATH . 'control/system/imap/' . $cmd . '.php');
    break;
  // Departments..
  case 'dept':
  case 'deptman':
    mswIsLoggedIn($li_team, $SSN);
    include(PATH . 'control/system/dept/' . $cmd . '.php');
    break;
  // Support Team..
  case 'team':
  case 'teamman':
  case 'graph':
  case 'responses':
  case 'team-profile':
    mswIsLoggedIn($li_team, $SSN);
    include(PATH . 'control/system/team/' . $cmd . '.php');
    break;
  // Accounts..
  case 'accounts':
  case 'accountman':
  case 'acchistory':
  case 'acc-import':
    mswIsLoggedIn($li_team, $SSN);
    include(PATH . 'control/system/accounts/' . $cmd . '.php');
    break;
  // FAQ..
  case 'faq-cat':
  case 'faq':
  case 'attachments':
  case 'faq-catman':
  case 'faqman':
  case 'faq-import':
  case 'attachman':
    mswIsLoggedIn($li_team, $SSN);
    include(PATH . 'control/system/faq/' . $cmd . '.php');
    break;
  // Tools..
  case 'tools':
  case 'log':
  case 'backup':
  case 'reports':
  case 'settings':
  case 'apages':
    mswIsLoggedIn($li_team, $SSN);
    include(PATH . 'control/system/settings/' . $cmd . '.php');
    break;
  // Ticket Management..
  case 'view-ticket':
  case 'view-dispute':
  case 'merge-ticket':
    mswIsLoggedIn($li_team, $SSN);
    if ($cmd == 'view-dispute' && (isset($_GET['disputeUsers']) || isset($_GET['changeState']))) {
      include(PATH . 'control/system/tickets/ticket-dispute-users.php');
    } else {
      include(PATH . 'control/system/tickets/ticket-view.php');
    }
    break;
  case 'edit-ticket':
    mswIsLoggedIn($li_team, $SSN);
    include(PATH . 'control/system/tickets/ticket-edit.php');
    break;
  case 'edit-reply':
    mswIsLoggedIn($li_team, $SSN);
    include(PATH . 'control/system/tickets/ticket-edit-reply.php');
    break;
  // Standard Responses..
  case 'standard-responses':
  case 'standard-responses-import':
  case 'responseman':
    mswIsLoggedIn($li_team, $SSN);
    include(PATH . 'control/system/responses/' . $cmd . '.php');
    break;
  // Tickets..
  case 'assign':
  case 'open':
  case 'search':
  case 'search-fields':
  case 'close':
  case 'disputes':
  case 'cdisputes':
  case 'add':
  case 'spam':
    mswIsLoggedIn($li_team, $SSN);
    $MSTICKET->blankStatuses();
    include(PATH . 'control/system/tickets/ticket-' . $cmd . '.php');
    break;
  // Mailbox..
  case 'mailbox':
    mswIsLoggedIn($li_team, $SSN);
    include(PATH . 'control/system/mailbox/' . $cmd . '.php');
    break;
  // Login events..
  case 'login':
  case 'logout':
    include(PATH . 'control/system/access.php');
    break;
  // Password reset..
  case 'reset':
    include(PATH . 'control/system/' . $cmd . '.php');
    break;
  // Version check..
  case 'vc':
    mswIsLoggedIn($li_team, $SSN);
    include(PATH . 'control/system/version-check.php');
    break;
  // Ajax handler..
  case 'ajax':
    if (isset($_GET['ajax']) && in_array($_GET['ajax'], array('login','pass-reset'))) {
      $skipLogin = true;
    }
    if (!isset($skipLogin)) {
      mswIsLoggedIn($li_team, $SSN);
    }
    include(PATH . 'control/system/_ajax.php');
    break;
  // Default..
  default:
    $HEADERS->err403(true);
    break;
}

// Clear locks..
if (!defined('TICKET_VIEW_LOADED') && isset($MSTEAM->id)) {
  $MSTICKET->locker(array(
    'action' => 'unlock',
    'team' => $MSTEAM->id
  ));
}

?>