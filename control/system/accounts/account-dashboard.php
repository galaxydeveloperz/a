<?php

/* System - Accounts
----------------------------------------------------------*/

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Check log in..
if (MS_PERMISSIONS == 'guest' || !isset($LI_ACC->id)) {
  header("Location:index.php?p=login");
  exit;
}

$title = $msg_header3;
$tz    = ($LI_ACC->timezone ? $LI_ACC->timezone : $SETTINGS->timezone);

include(PATH . 'control/header.php');

// Show..
$tpl = new Savant3();
$tpl->assign('TXT', array(
  $msg_header13,
  $MSDT->mswDateTimeDisplay(strtotime(date('Y-m-d', $MSDT->mswUTC())), $SETTINGS->dateformat, $tz),
  $msg_public_dashboard1,
  $msg_public_dashboard2,
  $msg_public_dashboard3,
  $msg_public_dashboard4,
  $msg_public_dashboard5,
  str_replace('{name}', mswSH($LI_ACC->name), $msg_public_dashboard11),
  $msg_public_dashboard12,
  $msg_main2,
  $msg_public_account4,
  str_replace('{count}', $SETTINGS->popquestions, $msg_main10),
  str_replace('{count}', $SETTINGS->popquestions, $msg_public_main3),
  $msadminlangpublic[7],
  $msg_pkbase7,
  $msg_pkbase,
  mswSH($msadminlang3_1faq[4]),
  $msg_portal40,
  $msg_portal41
));
$tpl->assign('TXT2', array(
  $msg_viewticket25,
  $msg_open36,
  $msg_open37,
  $msg_showticket18
));
$tpl->assign('TICKETS', $MSTICKET->ticketList(MS_PERMISSIONS, array(
  0,
  99999
), false, 'AND `ticketStatus` NOT IN(\'close\',\'closed\')'));
$tpl->assign('DISPUTES', $MSTICKET->disputeList(MS_PERMISSIONS, $LI_ACC->id, array(
  0,
  99999
), false, 'AND `ticketStatus` NOT IN(\'close\',\'closed\')'));
$tpl->assign('TICKETS_CNT', $MSTICKET->ticketList(MS_PERMISSIONS, array(
  0,
  99999
), true, 'AND `ticketStatus` NOT IN(\'close\',\'closed\')'));
$tpl->assign('DISPUTES_CNT', $MSTICKET->disputeList(MS_PERMISSIONS, $LI_ACC->id, array(
  0,
  99999
), true, 'AND `ticketStatus` NOT IN(\'close\',\'closed\')'));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/account-dashboard.tpl.php');

include(PATH . 'control/footer.php');

?>