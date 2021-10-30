<?php

/* System - Main
----------------------------------------------------------*/

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

if ($SSN->active('vis_menu_panel') == 'yes') {
  $SSN->delete(array('vis_menu_panel'));
}

// Show BBCode help..
if (isset($_GET['bbcode'])) {

  $tpl = new Savant3();
  $tpl->assign('CHARSET', $msg_charset);
  $tpl->assign('LANG', $html_lang);
  $tpl->assign('DIR', $lang_dir);
  $tpl->assign('TITLE', ($title ? $title . ': ' : '') . $msg_bbcode . ': ' . str_replace('{website}', mswCD($SETTINGS->website), $msg_header));
  $tpl->assign('TOP_BAR_TITLE', str_replace('{website}', mswCD($SETTINGS->website), $msg_header));

  // Global vars..
  include(PATH . 'control/lib/global.php');

  // Load template..
  $tpl->display('content/' . MS_TEMPLATE_SET . '/bb-code-help.tpl.php');

} else {

  include(PATH . 'control/header.php');

  $tpl = new Savant3();
  $tpl->assign('TXT', array(
    $msg_public_main,
    str_replace('{name}', mswCD($SETTINGS->website), $msg_public_main2),
    str_replace('{count}', $SETTINGS->popquestions, $msg_main10),
    str_replace('{count}', $SETTINGS->popquestions, $msg_public_main3),
    $msadminlangpublic[7],
    $msg_pkbase7,
    $msg_pkbase,
    mswSH($mspubliclang3_7[5] . ' - ' . $msg_pkbase2)
  ));
  $tpl->assign('FEATURED', $FAQ->questions(array(
    'id' => 0,
    'limit' => 0,
    'search' => array(),
    'orderor' => '`' . DB_PREFIX . 'faq`.`orderBy`',
    'queryadd' => 'GROUP BY `' . DB_PREFIX . 'faq`.`id`',
    'flag' => 'AND `' . DB_PREFIX . 'faq`.`featured` = \'yes\'',
    'l' => array($msg_pkbase8),
    'account' => (isset($LI_ACC->id) ? $LI_ACC->id : '0')
  )));
  $tpl->assign('POPULAR', $FAQ->questions(array(
    'id' => 0,
    'limit' => 0,
    'search' => array(),
    'show_limit' => $SETTINGS->popquestions,
    'orderor' => '`' . DB_PREFIX . 'faq`.`kviews` DESC',
    'queryadd' => 'GROUP BY `' . DB_PREFIX . 'faq`.`id`',
    'flag' => 'AND `' . DB_PREFIX . 'faq`.`featured` = \'no\'',
    'l' => array($msg_pkbase8),
    'account' => (isset($LI_ACC->id) ? $LI_ACC->id : '0')
  )));
  $tpl->assign('LATEST', $FAQ->questions(array(
    'id' => 0,
    'limit' => 0,
    'show_limit' => $SETTINGS->popquestions,
    'search' => array(),
    'orderor' => '`' . DB_PREFIX . 'faq`.`ts` DESC, `' . DB_PREFIX . 'faq`.`id` DESC',
    'queryadd' => 'GROUP BY `' . DB_PREFIX . 'faq`.`id`',
    'flag' => 'AND `' . DB_PREFIX . 'faq`.`featured` = \'no\'',
    'l' => array($msg_pkbase8),
    'account' => (isset($LI_ACC->id) ? $LI_ACC->id : '0')
  )));

  // Global vars..
  include(PATH . 'control/lib/global.php');

  // Load template..
  $tpl->display('content/' . MS_TEMPLATE_SET . '/main.tpl.php');

  include(PATH . 'control/footer.php');

}

?>