<?php if (!defined('PARENT')) { exit; }

/* MENU: ACCOUNTS
========================================================*/

$accMenuArr = array('accounts','accountman','accountsearch','acc-import');
$mR3        = array_intersect($accMenuArr, $userAccess);
$mR3_en     = (isset($nMenu['accounts']['en']) && in_array($nMenu['accounts']['en'], array('yes','no')) ? $nMenu['accounts']['en'] : 'yes');

if (!empty($mR3) || USER_ADMINISTRATOR == 'yes') {

  $slidePanelLeftMenu['accounts']          = array($msg_adheader38, 'user', $mR3_en);
  $slidePanelLeftMenu['accounts']['links'] = array();

  if ($mR3_en == 'yes') {
  
    // Add account..
    if (in_array('accounts', $mR3) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['accounts']['links'][] = array(
        'url' => '?p=accounts',
        'name' => $msg_adheader39
      );
    }

    // Manage accounts..
    if (in_array('accountman', $mR3) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['accounts']['links'][] = array(
        'url' => '?p=accountman',
        'name' => $msg_adheader40
      );
    }

    // Import accounts..
    if (in_array('acc-import', $mR3) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['accounts']['links'][] = array(
        'url' => '?p=acc-import',
        'name' => $msg_adheader59
      );
    }
  
  }

}

?>
