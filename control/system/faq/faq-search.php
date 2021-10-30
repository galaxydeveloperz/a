<?php

/* System - FAQ
----------------------------------------------------------*/

// Check var and parent load..
if (!defined('PARENT') || !isset($_GET['q']) || !defined('MS_PERMISSIONS') || $SETTINGS->kbase == 'no') {
  $HEADERS->err403();
}

// Load the skip words array..
include(PATH . 'control/skipwords.php');

// Variables..
$limitvalue  = $page * $SETTINGS->quePerPage - ($SETTINGS->quePerPage);
$pageNumbers = '';
$html        = '';
$title       = $msg_pkbase;
$dataCount   = 0;

// Build search query..
$SQL = '';
if ($_GET['q']) {
  $chop = array_map('trim', explode(' ', $_GET['q']));
  if (!empty($chop)) {
    foreach ($chop AS $word) {
      if (!in_array($word, $searchSkipWords)) {
        $SQL .= (!$SQL ? 'WHERE (' : 'OR (') . "`" . DB_PREFIX . "faq`.`question` LIKE '%" . mswSQL($word) . "%' OR `" . DB_PREFIX . "faq`.`answer` LIKE '%" . mswSQL($word) . "%' OR `" . DB_PREFIX . "faq`.`searchkeys` LIKE '%" . mswSQL($word) . "%')";
      }
    }
  }
  // Are we searching for anything..
  if ($SQL) {
    $html = $FAQ->questions(array(
      'id' => 0,
      'limit' => $limitvalue,
      'search' => array($SQL, 'no'),
      'l' => array($msg_pkbase8),
      'account' => (isset($LI_ACC->id) ? $LI_ACC->id : '0')
    ));
    $dataCount = $FAQ->questions(array(
      'id' => 0,
      'limit' => $limitvalue,
      'search' => array($SQL, 'yes'),
      'account' => (isset($LI_ACC->id) ? $LI_ACC->id : '0')
    ));
  }
} else {
  // If no keywords were entered, do nothing and go back to homepage..
  header("Location: index.php");
  exit;
}

// Check for category/search params..
if (isset($_GET['c']) && (int) $_GET['c'] > 0) {
  $CAT = mswSQL_table('categories', 'id', (int) $_GET['c'], 'AND `enCat` = \'yes\'', '`id`,`name`,`subcat`');
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
  }
}

// Pagination..
if ($dataCount > $SETTINGS->quePerPage) {
  define('PER_PAGE', $SETTINGS->quePerPage);
  $PTION       = new pagination(array($dataCount, $msg_script42, $page, 'q'), $SETTINGS->scriptpath . '/?q=' . urlencode($_GET['q']) . '&amp;next=');
  $pageNumbers = $PTION->display();
}

// Header..
include(PATH . 'control/header.php');

// Template initialisation..
$tpl = new Savant3();
$tpl->assign('TXT', array(
  $msg_header8,
  $msg_pkbase,
  $msg_header4,
  $msg_kbase53,
  str_replace('{count}', mswNFM($dataCount), $msadminlang3_1faq[12]),
  $msadminlang3_1faq[5],
  $msg_pkbase7,
  $msadminlang3_1faq[13]
));
$tpl->assign('PARENT', (isset($CAT->id) ? (array) $CAT : array()));
$tpl->assign('SUB', (isset($SUB->id) ? (array) $SUB : array()));
$tpl->assign('SCH_TXT', $msg_header4);
$tpl->assign('FAQ', $html);
$tpl->assign('RESULTS', $dataCount);
$tpl->assign('MSDT', $MSDT);
$tpl->assign('PAGES', $pageNumbers);

// Global vars..
include(PATH . 'control/lib/global.php');

// Global vars..
$tpl->display('content/' . MS_TEMPLATE_SET . '/faq-search.tpl.php');

// Footer..
include(PATH . 'control/footer.php');

?>