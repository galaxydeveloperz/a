<?php

/* System - Accounts
----------------------------------------------------------*/

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

$ms_js_css_loader['textarea'] = 'yes';
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
if (MS_PERMISSIONS == 'guest' && isset($_GET['t']) && $_GET['t']) {
  if ($SETTINGS->rantick == 'yes' && strpos($_GET['t'], '-') !== false) {
    $SSN->set(array('ticketAccessID' => $_GET['t']));
  } else {
    $SSN->set(array('ticketAccessID' => (int) $_GET['t']));
  }
}

// Load account globals..
include(PATH . 'control/system/accounts/account-global.php');

// Check log in..
if (MS_PERMISSIONS == 'guest' || !isset($_GET['t'])) {
  header("Location:index.php?p=login");
  exit;
}

// Get ticket information and check permissions..
if (preg_match("[[0-9a-zA-Z\-]{1,20}]", $_GET['t'], $regs)) {
  if ($SETTINGS->rantick == 'yes' && strpos($regs[0], '-') !== false) {
    $T = mswSQL_table('tickets', 'tickno', mswSQL($regs[0]), 'AND `visitorID` = \'' . $LI_ACC->id . '\' AND `spamFlag` = \'no\'');
  } else {
    $T = mswSQL_table('tickets', 'id', (int) $regs[0], 'AND `visitorID` = \'' . $LI_ACC->id . '\' AND `spamFlag` = \'no\'');
  }
}

// Permissions..
if (!isset($T->id)) {
  $HEADERS->err403();
}

// Assign get var here for other ops..
$_GET['t'] = $T->id;

// Re-open..
if ($T->ticketStatus == 'close' && isset($_GET['lk'])) {
  $rows = $MSTICKET->openclose($T->id);
  // History if affected rows..
  if ($rows > 0) {
    $MSTICKET->historyLog($T->id, str_replace('{user}', mswSH($LI_ACC->name), $msg_ticket_history['vis-ticket-open']));
    $T               = mswSQL_table('tickets', 'id', $T->id);
    $ticketSystemMsg = $msg_public_ticket14;
  }
}

// Close..
if ($T->ticketStatus != 'close' && isset($_GET['cl'])) {
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
  define('T_PERMS', 't');
  include(PATH . 'control/system/accounts/account-ticket-reply.php');
}

// Is IP blank?
if ($T->ipAddresses == '' && $T->visitorID == $LI_ACC->id) {
  $MSTICKET->updateIP($T->id);
  $T->ipAddresses = mswIP();
}

// Variables..
$title = str_replace('{ticket}', mswTicketNumber($T->id, $SETTINGS->minTickDigits, $T->tickno), $msg_viewticket);

include(PATH . 'control/header.php');

$tpl = new Savant3();
$tpl->assign('TXT', array(
  $title,
  $msg_header11,
  $msg_header3,
  $msg_main11,
  $MSYS->levels($T->priority),
  $MSDT->mswDateTimeDisplay($T->ts, $SETTINGS->dateformat),
  $MSDT->mswDateTimeDisplay($T->ts, $SETTINGS->timeformat),
  $msg_viewticket75,
  $MSYS->department($T->department, $msg_script30),
  str_replace('{url}', 'index.php?t=' . $T->id . '&amp;lk=yes', $msg_viewticket45),
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
  $msg_public_ticket3,
  $msg_public_ticket4,
  $msg_public_ticket9,
  $msg_viewticket27,
  $msg_public_ticket10,
  $msg_script43,
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
$tpl->assign('LAST_REPLY_INFO', $MSTICKET->getLastReply($T->id));
$tpl->assign('COMMENTS', $MSPARSER->mswTxtParsingEngine($T->comments));
$tpl->assign('CUSTOM_FIELD_DATA', $MSFIELDS->display($T->id));
$tpl->assign('CUSTOM_FIELD_DATA_COUNT', $MSFIELDS->display($T->id, 0, 1));
$tpl->assign('ATTACHMENTS', $MSTICKET->attachments($T->id));
$tpl->assign('ATTACHMENTS_COUNT', $MSTICKET->attachments($T->id, 0, 1));
$tpl->assign('TICKET_REPLIES', $MSTICKET->replies($T->id, mswSH($LI_ACC->name), $LI_ACC->id, array($msg_showticket21, $msg_viewticket39, $msg_viewticket40, $msg_add2)));
$tpl->assign('ENTRY_CUSTOM_FIELDS', $MSFIELDS->build('reply', $T->department, $LI_ACC->id));
$tpl->assign('SYSTEM_MESSAGE', $ticketSystemMsg);
$tpl->assign('TICKET_CLOSE_PERMS', 'yes');
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
$tpl->display('content/' . MS_TEMPLATE_SET . '/account-view-ticket.tpl.php');

// Load js triggers..
if (file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/reply.htm')) {
  $jsHTML = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/reply.htm', 'ok');
}
// Draft
if (SAVE_DRAFTS && file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/draft.htm')) {
  $jsHTML .= str_replace(array(
    '{draft_msg_timeout}', '{draft_timeout}', '{id}', '{post_id}'
  ), array(
    DRAFT_MSG_TIMEOUT, DRAFT_TIMEOUT, $T->id, $T->id
  ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/draft.htm', 'ok'));
}

// Date picker
if (file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/date-picker.htm')) {
  $jsHTML .= str_replace(array(
    '{cal_short}','{cal_daysmin}','{cal_firstday}','{cal_format}','{cal_rtl}'
  ), array(
    trim($msg_cal), trim($msg_cal2), ($SETTINGS->weekStart=='sun' ? '0' : '1'),
    $MSDT->mswDatePickerFormat(), $msg_cal3
  ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/date-picker.htm', 'ok'));
}

include(PATH . 'control/footer.php');

?>