<?php

/* System - Accounts
----------------------------------------------------------*/

if (!defined('PARENT') || !defined('MS_PERMISSIONS') || $SETTINGS->disputes == 'no') {
  $HEADERS->err403();
}

include(PATH . 'control/classes/class.upload.php');
$MSUPL  = new msUpload();

// Upload dropzone..
$mSize = $MSUPL->getMaxSize();
if ($SETTINGS->attachment == 'yes' && $SETTINGS->attachboxes > 0) {
  $ms_js_css_loader['uploader'] = 'yes';
  $aMax   = (LICENCE_VER == 'locked' && $SETTINGS->attachboxes > RESTR_ATTACH ? RESTR_ATTACH : $SETTINGS->attachboxes);
  $mswUploadDropzone = array(
    'ajax' => 'create-ticket',
    'multiple' => ($SETTINGS->attachboxes > 1 && $aMax > 1 ? 'true' : 'false'),
    'max-files' => $aMax,
    'max-size' => ($SETTINGS->maxsize > 0 ? ($SETTINGS->maxsize > $mSize ? $mSize : $SETTINGS->maxsize) : $mSize),
    'allowed' => ($SETTINGS->filetypes ? str_replace(array('|','.'),array(',',''),strtolower($SETTINGS->filetypes)) : '*'),
    'drag' => 'false',
    'txt' => mswJSClean($msadminlang3_1uploads[5]),
    'div' => 'three'
  );
}

// For redirection..
if (MS_PERMISSIONS == 'guest' && isset($_GET['d']) && $_GET['d']) {
  if ($SETTINGS->rantick == 'yes' && strpos($_GET['d'], '-') !== false) {
    $SSN->set(array('disputeAccessID' => $_GET['d']));
  } else {
    $SSN->set(array('disputeAccessID' => (int) $_GET['d']));
  }
}

// Load account globals..
include(PATH . 'control/system/accounts/account-global.php');

// Check log in..
if (MS_PERMISSIONS == 'guest' || !isset($_GET['d'])) {
  header("Location:index.php?p=login");
  exit;
}

// Get ticket information and check permissions..
$checkID = '';
if (preg_match("[[0-9a-zA-Z\-]{1,20}]", $_GET['d'], $regs)) {
  if ($SETTINGS->rantick == 'yes' && strpos($regs[0], '-') !== false) {
    $T = mswSQL_table('tickets', 'tickno', mswSQL($regs[0]), 'AND `visitorID` = \'' . $LI_ACC->id . '\' AND `spamFlag` = \'no\' AND `isDisputed` = \'yes\'');
    if (!isset($T->id)) {
      $T = mswSQL_table('tickets', 'tickno', mswSQL($regs[0]), 'AND `spamFlag` = \'no\' AND `isDisputed` = \'yes\'');
    }
    $checkID = (isset($T->id) ? $T->id : '');
  } else {
    $T = mswSQL_table('tickets', 'id', (int) $regs[0], 'AND `visitorID` = \'' . $LI_ACC->id . '\' AND `spamFlag` = \'no\' AND `isDisputed` = \'yes\'');
    $checkID = (int) $regs[0];
  }
}

if ($checkID == '') {
  $HEADERS->err403();
}

if (!isset($T->id)) {
  // Check if this user is in the dispute list...
  $PRIV = mswSQL_table('disputes', 'visitorID', $LI_ACC->id, 'AND `ticketID` = \'' . $checkID . '\'');
  // If privileges allow viewing of dispute, requery without email..
  if (isset($PRIV->id)) {
    $T = mswSQL_table('tickets', 'id', $checkID);
    // Get person who started ticket..
    $ORGL = mswSQL_table('portal', 'id', $T->visitorID);
  } else {
    $HEADERS->err403();
  }
}

// Assign get var here for other ops..
$_GET['d'] = $T->id;

// Users in dispute..
$usersInDispute = $MSTICKET->disputeUserNames($T, (isset($ORGL->name) ? mswSH($ORGL->name) : mswSH($LI_ACC->name)));

// Post privileges..
$userPostPriv = (isset($PRIV->id) ? $PRIV->postPrivileges : $T->disPostPriv);

// Check admin restriction of not allowing any more posts until admin has replied..
$getLastReplyInfo = $MSTICKET->getLastReply($T->id);
if (in_array($getLastReplyInfo[2], array('visitor')) && $SETTINGS->disputeAdminStop == 'yes') {
  $userPostPriv = 'no';
}

// Re-open..can only be re-opened by original user..
if ($T->ticketStatus == 'close' && isset($_GET['lk']) && $T->visitorID == $LI_ACC->id) {
  $rows = $MSTICKET->openclose($T->id);
  // History if affected rows..
  if ($rows > 0) {
    $MSTICKET->historyLog($T->id, str_replace('{user}', mswSH($LI_ACC->name), $msg_ticket_history['vis-ticket-open']));
    $T               = mswSQL_table('tickets', 'id', $T->id);
    $ticketSystemMsg = $msg_public_ticket14;
  }
}

// Close..can only be re-opened by original user..
if ($T->ticketStatus != 'close' && isset($_GET['cl']) && $T->visitorID == $LI_ACC->id) {
  $rows = $MSTICKET->openclose($T->id, 'close');
  // History if affected rows..
  if ($rows > 0) {
    $MSTICKET->historyLog($T->id, str_replace('{user}', mswSH($LI_ACC->name), $msg_ticket_history['vis-ticket-close']));
    $T               = mswSQL_table('tickets', 'id', $T->id);
    $ticketSystemMsg = $msg_public_ticket13;
  }
}

// Add reply..
if (isset($_POST['process'])) {
  define('T_PERMS', 'd');
  include(PATH . 'control/system/accounts/account-ticket-reply.php');
}

// Is IP blank?
if ($T->ipAddresses == '' && $T->visitorID == $LI_ACC->id) {
  $MSTICKET->updateIP($T->id);
  $T->ipAddresses = mswIP();
}

// Variables..
$title = str_replace('{ticket}', mswTicketNumber($T->id, $SETTINGS->minTickDigits, $T->tickno), $msg_showticket32);

include(PATH . 'control/header.php');

$tpl = new Savant3();
$tpl->assign('TXT', array(
  $title,
  $msg_header16,
  $msg_header3,
  $msg_main11,
  $MSYS->levels($T->priority),
  $MSDT->mswDateTimeDisplay($T->ts, $SETTINGS->dateformat),
  $MSDT->mswDateTimeDisplay($T->ts, $SETTINGS->timeformat),
  $msg_viewticket75,
  $MSYS->department($T->department, $msg_script30),
  str_replace('{url}', 'index.php?d=' . $T->id . '&amp;lk=yes', $msg_viewticket45),
  $msg_public_ticket,
  $msg_open19,
  $msg_newticket43,
  $msg_viewticket101,
  $msg_showticket5,
  $msg_viewticket78,
  $msg_newticket37,
  $msg_newticket38,
  $attachRestrictions,
  $bb_code_buttons,
  str_replace('{count}', count($usersInDispute), $msg_showticket30),
  $msg_public_ticket4,
  $msg_public_ticket9,
  $msg_viewticket27,
  $msg_public_ticket10,
  $msg_public_ticket3,
  $msg_public_ticket11,
  $msg_public_ticket15,
  $msg_script43,
  $msadminlang3_1adminviewticket[8],
  $msg_viewticket40,
  $msg_add2
));
$tpl->assign('TXT2', array(
  $mspubliclang4_3
));
$tpl->assign('REPTXT', array(
  $msadminlang3_1adminviewticket[14],
  $msg_add2,
  $msg_attachments,
  $msg_accounts8,
  str_replace(
    array('{max}','{files}','{types}'),
    array(
      ($SETTINGS->maxsize > 0 ? ($SETTINGS->maxsize > $mSize ? mswFSC($mSize) : mswFSC($SETTINGS->maxsize)) : mswFSC($mSize)),
      (LICENCE_VER == 'locked' && $SETTINGS->attachboxes > RESTR_ATTACH ? RESTR_ATTACH : $SETTINGS->attachboxes),
      ($SETTINGS->filetypes ? str_replace(array('|','.'),array(', ',''), $SETTINGS->filetypes) : $msadminlang3_1uploads[4])
    ),
    $msadminlang3_1uploads[3]
  )
));
$tpl->assign('LAST_REPLY_INFO', $getLastReplyInfo);
$tpl->assign('COMMENTS', $MSPARSER->mswTxtParsingEngine($T->comments));
$tpl->assign('USERS_IN_DISPUTE', $usersInDispute);
$tpl->assign('ORG_USER', (isset($ORGL->name) ? $ORGL : ''));
$tpl->assign('USERS_IN_DISPUTE_COUNT', count($usersInDispute));
$tpl->assign('CUSTOM_FIELD_DATA', $MSFIELDS->display($T->id));
$tpl->assign('CUSTOM_FIELD_DATA_COUNT', $MSFIELDS->display($T->id, 0, 1));
$tpl->assign('ATTACHMENTS', $MSTICKET->attachments($T->id));
$tpl->assign('ATTACHMENTS_COUNT', $MSTICKET->attachments($T->id, 0, 1));
$tpl->assign('TICKET_REPLIES', $MSTICKET->replies($T->id, mswSH($LI_ACC->name), $LI_ACC->id, array($msg_showticket21, $msg_viewticket39, $msg_viewticket40, $msg_add2)));
$tpl->assign('ENTRY_CUSTOM_FIELDS', $MSFIELDS->build('reply', $T->department, $LI_ACC->id));
$tpl->assign('REPLY_PERMISSIONS', $userPostPriv);
$tpl->assign('SYSTEM_MESSAGE', $ticketSystemMsg);
$tpl->assign('TICKET_CLOSE_PERMS', ($T->visitorID == $LI_ACC->id ? 'yes' : 'no'));
$tpl->assign('STATUS_TITLE', (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_script17));

// Check status and send ticket data to template
if (!in_array($T->ticketStatus, array('close','closed','open')) &&
    isset($ticketStatusSel[$T->ticketStatus][1]) && $ticketStatusSel[$T->ticketStatus][1] == 'yes') {
  $T->ticketStatus = 'status-lock';
}
$tpl->assign('TICKET', $T);

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/account-view-dispute.tpl.php');

// Load js triggers..
if (file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/reply.htm')) {
  $jsHTML = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/reply.htm', 'ok');
}

include(PATH . 'control/footer.php');

?>