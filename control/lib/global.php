<?php

/* GLOBAL TEMPLATE CONSTANTS
   Available in ALL .tpl.php files
-------------------------------------------------------------------------------------------*/

if (!defined('PARENT') || !defined('MS_PERMISSIONS') || !defined('MSW_LOGGED_IN') || !isset($SETTINGS)) {
  $HEADERS->err403();
}

$tpl->assign('SETTINGS', (isset($SETTINGS) && property_exists($SETTINGS, 'id') ? $SETTINGS : new stdclass()));
$tpl->assign('LOGGED_IN', MSW_LOGGED_IN);
$tpl->assign('USER_DATA', (MSW_LOGGED_IN == 'yes' && isset($LI_ACC) && property_exists($LI_ACC, 'id') ? $LI_ACC : ''));
$tpl->assign('SYS_BASE_HREF', (isset($SETTINGS) && property_exists($SETTINGS, 'scriptpath') ? $SETTINGS->scriptpath . '/content/' . MS_TEMPLATE_SET . '/' : ''));
$tpl->assign('FILE_LOADER', (isset($ms_js_css_loader) ? $ms_js_css_loader : ''));
$tpl->assign('DROPZONE', (isset($mswUploadDropzone) ? $mswUploadDropzone : array()));
$cats = (isset($FAQ) && method_exists($FAQ, 'menu') ? $FAQ->menu(array(
  'account' => (MSW_LOGGED_IN == 'yes' && isset($LI_ACC->id) ? $LI_ACC->id : 0)
)) : '');
$tpl->assign('CATEGORIES', (isset($cats['string']) ? $cats['string'] : ''));
$tpl->assign('CATEGORIES_MENU', (isset($cats['array']) ? $cats['array'] : array()));
// Private categories only seen on login..
if (MSW_LOGGED_IN == 'yes' && isset($LI_ACC) && property_exists($LI_ACC, 'id')) {
  $pcats = (isset($FAQ) && method_exists($FAQ, 'menu') ? $FAQ->menu(array(
    'account' => $LI_ACC->id,
    'private_cats' => 'yes'
  )) : '');
  $tpl->assign('PRIVATE_CATEGORIES', (isset($pcats['string']) ? $pcats['string'] : ''));
  $tpl->assign('PRIVATE_CATEGORIES_MENU', (isset($pcats['array']) ? $pcats['array'] : array()));
}

// Custom page loader..
$cs_html = array('', array(), array());
$pcs_html = array('', array(), array());
if (isset($MSYS) && method_exists($MSYS, 'customPages')) {
  $cs_html = $MSYS->customPages(0, $msadminlangpublic);
}
if (isset($LI_ACC->id) && isset($MSYS) && method_exists($MSYS, 'customPages')) {
  $pcs_html = $MSYS->customPages($LI_ACC->id, $msadminlangpublic);
}
if (MSW_LOGGED_IN == 'yes' && isset($LI_ACC->id)) {
  $tpl->assign('PRIVATE_PAGES_MENU', $pcs_html[1]);
}
$tpl->assign('OTHER_PAGES', $cs_html[0]);
$tpl->assign('OTHER_PAGES_MENU', $cs_html[2]);

$tpl->assign('T_STATUSES', (!empty($ticketStatusSel) ? $ticketStatusSel : array()));
$tpl->assign('T_LEVELS', (!empty($ticketLevelSel) ? $ticketLevelSel : array()));

// Load off canvas menu?
$tpl->assign('LOAD_OFF_CANVAS_MENU', (isset($SETTINGS) && property_exists($SETTINGS, 'kbase') == 'yes' || !empty($cs_html[1]) || !empty($cs_html[2]) || MSW_LOGGED_IN == 'yes' ? 'yes' : 'no'));
$tpl->assign('OFF_CANVAS_PANEL_STATE', (isset($SSN) && method_exists($SSN, 'active') && $SSN->active('vis_menu_panel') == 'yes' ? $SSN->get('vis_menu_panel') : (MSW_LOGGED_IN == 'yes' ? 'mn1' : (isset($cats) && $cats ? 'ct1' : (isset($pcats) && $pcats ? 'pct1' : 'pg1')))));
$tpl->assign('PB_LNG', array(
  (isset($mspubliclang3_7) ? $mspubliclang3_7 : array()),
  (isset($msadminlang3_1cspages[0]) ? $msadminlang3_1cspages[0] : ''),
  (isset($msg_pkbase7) ? $msg_pkbase7 : '')
));
$tpl->assign('PB_TXT_LNG', array(
  (isset($msg_main2) ? $msg_main2 : ''),
  (isset($msg_header11) ? $msg_header11 : ''),
  (isset($msg_header2) ? $msg_header2 : ''),
  (isset($msg_header15) ? $msg_header15 : ''),
  (isset($msg_header16) ? $msg_header16 : ''),
  (isset($mspubliclang4_2[0]) ? $mspubliclang4_2[0] : '')
));

?>