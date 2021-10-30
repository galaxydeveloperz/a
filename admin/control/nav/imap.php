<?php if (!defined('PARENT')) { exit; }

/* MENU: IMAP ACCOUNTS
========================================================*/

$imapMenuArr = array('imap','imapman','imapfilter');
$mR8         = array_intersect($imapMenuArr, $userAccess);
$mR8_en      = (isset($nMenu['imap']['en']) && in_array($nMenu['imap']['en'], array('yes','no')) ? $nMenu['imap']['en'] : 'yes');

if (!empty($mR8) || USER_ADMINISTRATOR == 'yes') {

  $slidePanelLeftMenu['imap']          = array($msg_adheader24, 'envelope-o', $mR8_en);
  $slidePanelLeftMenu['imap']['links'] = array();

  if ($mR8_en == 'yes') {
  
    // Add imap account..
    if (in_array('imap', $mR8) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['imap']['links'][] = array(
        'url' => '?p=imap',
        'name' => $msadminlang3_7[11]
      );
    }

    // Manage imap accounts..
    if (in_array('imapman', $mR8) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['imap']['links'][] = array(
        'url' => '?p=imapman',
        'name' => $msg_adheader40
      );
    }

    // Imap ban filters..
    if (in_array('imapban', $mR8) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['imap']['links'][] = array(
        'url' => '?p=imapban',
        'name' => $msadminlang_imap_3_7[0]
      );
    }
  
  }

}


?>