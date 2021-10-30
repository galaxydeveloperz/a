<?php

/* System - Create Ticket Ops
----------------------------------------------------------*/

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

define('TICKET_CREATION', 1);
$ms_js_css_loader['textarea'] = 'yes';

include(PATH . 'control/classes/class.upload.php');
$MSUPL  = new msUpload();

// Upload dropzone..
$mSize = 0;
if ($SETTINGS->attachment == 'yes' && $SETTINGS->attachboxes > 0) {
  $ms_js_css_loader['uploader'] = 'yes';
  $mSize  = $MSUPL->getMaxSize();
  $aMax   = (LICENCE_VER == 'locked' && $SETTINGS->attachboxes > RESTR_ATTACH ? RESTR_ATTACH : $SETTINGS->attachboxes);
  $mswUploadDropzone = array(
    'ajax' => 'create-ticket',
    'multiple' => ($SETTINGS->attachboxes > 1 && $aMax > 1 ? 'true' : 'false'),
    'max-files' => $aMax,
    'max-size' => ($SETTINGS->maxsize > 0 ? ($SETTINGS->maxsize > $mSize ? $mSize : $SETTINGS->maxsize) : $mSize),
    'allowed' => ($SETTINGS->filetypes ? str_replace(array('|','.'),array(',',''),strtolower($SETTINGS->filetypes)) : '*'),
    'drag' => 'false',
    'txt' => mswJSClean($msadminlang3_1uploads[5]),
    'div' => 'two'
  );
}

// Check log in..
if ($SETTINGS->createPref == 'yes' && MS_PERMISSIONS == 'guest') {
  $SSN->set(array('redirectPage' => 'open'));
  header("Location:index.php?p=login");
  exit;
}

// Load account globals..
include(PATH . 'control/system/accounts/account-global.php');

$title = $msg_main2;

include(PATH . 'control/header.php');

$tpl = new Savant3();
$tpl->assign('TXT', array(
  $msg_main2,
  $msg_main17,
  $msg_newticket3,
  $msg_newticket4,
  $msg_newticket15,
  $msg_newticket6,
  $msg_newticket8,
  $msg_newticket5,
  $msg_viewticket78,
  $msg_newticket37,
  $msg_newticket38,
  $attachRestrictions,
  $msg_main2,
  $msg_newticket43,
  $msg_viewticket101,
  $msg_public_ticket4,
  $msg_public_ticket5,
  $msg_public_ticket9,
  $msg_public_ticket10,
  $bb_code_buttons,
  '',
  $msg_header3,
  $msg_add3,
  $msg_add2,
  $msg_add,
  str_replace(
    array('{max}','{files}','{types}'),
    array(
      ($SETTINGS->maxsize > 0 ? ($SETTINGS->maxsize > $mSize ? mswFSC($mSize) : mswFSC($SETTINGS->maxsize)) : mswFSC($mSize)),
      (LICENCE_VER == 'locked' && $SETTINGS->attachboxes > RESTR_ATTACH ? RESTR_ATTACH : $SETTINGS->attachboxes),
      ($SETTINGS->filetypes ? str_replace(array('|','.'),array(', ',''), $SETTINGS->filetypes) : $msadminlang3_1uploads[4])
    ),
    $msadminlang3_1uploads[3]
  ),
  $msadminlang3_1createticket[0]
));
$tpl->assign('DEPARTMENTS', $MSYS->ticketDepartments());
$tpl->assign('PRIORITY_LEVELS', $ticketLevelSel);
$tpl->assign('CUS_FIELDS_COUNT', mswSQL_rows('cusfields WHERE `enField` = \'yes\''));
$tpl->assign('LOGGED_IN', (MS_PERMISSIONS != 'guest' && isset($LI_ACC->name) ? 'yes' : 'no'));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/account-create-ticket.tpl.php');

// Load js triggers..
if (file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/create.htm')) {
  $jsHTML = str_replace(array(
    '{id}'
  ), array(
    (!isset($_GET['set_dept']) && $SETTINGS->defdept > 0 ? $SETTINGS->defdept : (isset($_GET['set_dept']) ? (int) $_GET['set_dept'] : 'void'))
  ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/create.htm', 'ok'));
}

// Draft
if (SAVE_DRAFTS && file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/draft.htm')) {
  $jsHTML .= str_replace(array(
    '{draft_msg_timeout}', '{draft_timeout}', '{id}', '{post_id}'
  ), array(
    DRAFT_MSG_TIMEOUT, DRAFT_TIMEOUT, 'add', '\'add\''
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