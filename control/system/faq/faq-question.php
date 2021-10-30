<?php

/* System - FAQ
----------------------------------------------------------*/

// Check var and parent load..
if (!defined('PARENT') || !isset($_GET['a']) || !defined('MS_PERMISSIONS') || $SETTINGS->kbase == 'no') {
  $HEADERS->err403();
}

// We load custom fields for this page..
$ms_js_css_loader['bbcode'] = 'yes';

// Security check..
mswVLDG($_GET['a']);

$QUE = mswSQL_table('faq', 'id', (int) $_GET['a'], 'AND `enFaq` = \'yes\'', '*');

if (!isset($QUE->question)) {
  $HEADERS->err404();
}

// Is this private?
if ($QUE->private == 'yes' && !isset($LI_ACC->id)) {
  $HEADERS->err403();
}

// Variables..
$title = $QUE->question . ' - ' . $msg_adheader17;
$subt  = $msg_header8;
$cky   = array();

// Check for category/search params..
if (isset($_GET['c']) && (int) $_GET['c'] > 0) {
  $CAT = mswSQL_table('categories', 'id', (int) $_GET['c'], 'AND `enCat` = \'yes\'', '`id`,`name`,`subcat`,`private`,`accounts`');
  if (isset($CAT->name)) {
    if (isset($CAT->subcat) && $CAT->subcat > 0) {
      $SUB = mswSQL_table('categories', 'id', $CAT->subcat);
    }
    // Is this private account based category?
    if ($CAT->private == 'yes' && !isset($LI_ACC->id)) {
      $HEADERS->err403();
    }
    if ($CAT->private == 'yes' && !in_array($CAT->accounts, array(null, '', 'all')) && !in_array($LI_ACC->id, explode(',', $CAT->accounts))) {
      $HEADERS->err403();
    }
  } else {
    $HEADERS->err403();
  }
} else {
  // If category isn`t set in the url, get category from question..
  $CAT = mswSQL_table('categories', 'id', $QUE->cat, 'AND `enCat` = \'yes\'', '`name`,`subcat`,`private`,`accounts`');
  if (isset($CAT->name)) {
    if (isset($CAT->subcat) && $CAT->subcat > 0) {
      $SUB = mswSQL_table('categories', 'id', $CAT->subcat);
    }
    if ($CAT->private == 'yes' && !isset($LI_ACC->id)) {
      $HEADERS->err403();
    }
    // Is this private account based category?
    if ($CAT->private == 'yes' && !in_array($CAT->accounts, array(null, '', 'all')) && !in_array($LI_ACC->id, explode(',', $CAT->accounts))) {
      $HEADERS->err403();
    }
  } else {
    $HEADERS->err403();
  }
}

// Header..
include(PATH . 'control/header.php');

// Cookie set..
if ($SSN->active_c(COOKIE_NAME) == 'yes') {
  $cky = unserialize($SSN->get_c(COOKIE_NAME));
}

// Template initialisation..
$tpl = new Savant3();
$tpl->assign('TXT', array(
  $msg_kbase52,
  $msg_kbase54,
  $msg_pkbase18,
  $msg_pkbase,
  mswSH($mspubliclang3_7[5] . ' - ' . $msg_pkbase2),
  $msadminlang3_1faq[5],
  $msg_pkbase7,
  $msadminlang3_1faq[6],
  $msadminlang3_1faq[7],
  $msg_kbase51,
  $msg_header8,
  $msadminlang3_1faq[8],
  $msadminlang3_1faq[9],
  str_replace('{date}', $MSDT->mswDateTimeDisplay($QUE->ts, $SETTINGS->dateformat), $msg_pkbase11),
  $msadminlang3_1faq[11],
  $mspubliclang3_7[3],
  $mspubliclang3_7[4]
));
$tpl->assign('PARENT', (isset($CAT->id) ? (array) $CAT : array()));
$tpl->assign('SUB', (isset($SUB->id) ? (array) $SUB : array()));
$tpl->assign('SCH_TXT', $msg_header4);
$tpl->assign('ANSWER', (array) $QUE);
$tpl->assign('ANSWER_TXT', $MSPARSER->mswTxtParsingEngine($QUE->answer));
$tpl->assign('MSDT', $MSDT);
$tpl->assign('ATTACHMENTS', $FAQ->attachments());
$tpl->assign('FAQ_COOKIE_SET', (in_array($_GET['a'], $cky) ? 'yes' : 'no'));
$tpl->assign('STATS', $FAQ->stats($QUE->id));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
if ($QUE->tmp && file_exists('content/' . MS_TEMPLATE_SET . '/custom-templates/' . $QUE->tmp)) {
  $tpl->display('content/' . MS_TEMPLATE_SET . '/custom-templates/' . $QUE->tmp);
} else {
  $tpl->display('content/' . MS_TEMPLATE_SET . '/faq-question.tpl.php');
}

// Load js triggers..
if (file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/print-friendly.htm')) {
  $jsHTML = str_replace(array(
    '{website}'
  ), array(
      mswJSClean($SETTINGS->website)
  ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/print-friendly.htm', 'ok'));
}

// Footer..
include(PATH . 'control/footer.php');

?>