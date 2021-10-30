<?php

/* System - Disabled
----------------------------------------------------------*/

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Auto enable..
$now = $MSDT->mswDateTimeDisplay(strtotime(date('Y-m-d', $MSDT->mswUTC())), 'Y-m-d', $SETTINGS->timezone);
if (!in_array($SETTINGS->autoenable, array('0000-00-00','1000-01-01')) && $SETTINGS->autoenable <= $now) {
  mswSQL_query("UPDATE `" . DB_PREFIX . "settings` SET
  `sysstatus`   = 'yes',
  `autoenable`  = '1000-01-01'
  ", __file__, __line__);
  header("Location: index.php");
  exit;
}

$title = $msg_offline;

include(PATH . 'control/header.php');

$tpl = new Savant3();
$tpl->assign('CHARSET', $msg_charset);
$tpl->assign('TITLE', ($title ? $title . ': ' : '') . str_replace('{website}', mswCD($SETTINGS->website), $msg_header) . (LICENCE_VER != 'unlocked' ? ' (' . $msg_script18 . ' ' . $msg_script . ')' : '') . (LICENCE_VER != 'unlocked' ? ' - Free Version' : ''));
$tpl->assign('TXT', array(
  $msg_offline,
  mswCD($SETTINGS->offlineReason)
));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/system-disabled.tpl.php');

include(PATH . 'control/footer.php');

?>