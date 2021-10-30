<?php

/* System - FAQ
----------------------------------------------------------*/

// Check var and parent load..
if (!defined('PARENT') || !defined('MS_PERMISSIONS') || $SETTINGS->kbase == 'no') {
  $HEADERS->err403();
}

// Check var and parent load..
if (!isset($_GET['c']) || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Security check..
mswVLDG($_GET['c']);

// Load category..
$CAT = mswSQL_table('categories', 'id', (int) $_GET['c'], 'AND `enCat` = \'yes\'');

// 404 if not found..
if (!isset($CAT->name)) {
  $HEADERS->err404();
}

// Is this private?
if ($CAT->private == 'yes' && !isset($LI_ACC->id)) {
  $HEADERS->err403();
}

// Is this private account based category?
if ($CAT->private == 'yes' && !in_array($CAT->accounts, array(null, '', 'all')) && !in_array($LI_ACC->id, explode(',', $CAT->accounts))) {
  $HEADERS->err403();
}

// Variables..
$limitvalue  = $page * $SETTINGS->quePerPage - ($SETTINGS->quePerPage);
$pageNumbers = '';
$title       = $CAT->name . ' - ' . $msg_adheader17;
$dataCount   = $FAQ->questions(array(
  'id' => $CAT->id,
  'limit' => $limitvalue,
  'l' => array($msg_pkbase8),
  'count' => 'yes',
  'account' => (isset($LI_ACC->id) ? $LI_ACC->id : '0')
));

// Check if sub category..
if ($CAT->subcat > 0) {
  $SUB = mswSQL_table('categories', 'id', $CAT->subcat);
  if (isset($SUB->name)) {
    $title = mswCD($CAT->name) . ' (' . mswCD($SUB->name) . ') - ' . $msg_adheader17;
  }
}

// Pagination..
if ($dataCount > $SETTINGS->quePerPage) {
  define('PER_PAGE', $SETTINGS->quePerPage);
  $PTION       = new pagination(array($dataCount, $msg_script42, $page, 'c'), $SETTINGS->scriptpath . '/?c=' . (int) $_GET['c'] . '&amp;next=');
  $pageNumbers = $PTION->display();
}

// Header..
include(PATH . 'control/header.php');

// Template initialisation..
$tpl = new Savant3();
$tpl->assign('TXT', array(
  $msg_header8,
  $msg_header4,
  $msg_pkbase,
  mswSH($mspubliclang3_7[5] . ' - ' . $msg_pkbase2),
  $msadminlang3_1faq[5],
  $msg_pkbase7,
  $msadminlang3_1faq[6],
  $msadminlang3_1faq[7],
  $mspubliclang3_7[3],
  $mspubliclang3_7[4]
));
$cats = $FAQ->menu(array(
  'account' => (isset($LI_ACC->id) ? $LI_ACC->id : 0),
  'parent' => $CAT->id,
  'private_cat' => $CAT->private
));
$tpl->assign('RELATED_CATEGORIES', $cats['array']);
$tpl->assign('SCH_TXT', $msg_header4);
$tpl->assign('FAQ', $FAQ->questions(array(
  'id' => $CAT->id,
  'limit' => $limitvalue,
  'l' => array($msg_pkbase8),
  'account' => (isset($LI_ACC->id) ? $LI_ACC->id : '0'),
  'private_cat' => $CAT->private
)));
$tpl->assign('PARENT', (array) $CAT);
$tpl->assign('SUB', (isset($SUB->id) ? (array) $SUB : array()));
$tpl->assign('MSDT', $MSDT);
$tpl->assign('PAGES', $pageNumbers);
$tpl->assign('COUNT', $dataCount);

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/faq-cat.tpl.php');

// Footer..
include(PATH . 'control/footer.php');

?>