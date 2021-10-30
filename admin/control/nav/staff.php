<?php if (!defined('PARENT')) { exit; }

/* MENU: SUPPORT TEAM
========================================================*/

$staffMenuArr = array('team','teamman');
$mR2          = array_intersect($staffMenuArr, $userAccess);
$mR2_en       = (isset($nMenu['staff']['en']) && in_array($nMenu['staff']['en'], array('yes','no')) ? $nMenu['staff']['en'] : 'yes');

if (!empty($mR2) || USER_ADMINISTRATOR == 'yes') {

  $slidePanelLeftMenu['staff']          = array($msg_adheader4, 'group', $mR2_en);
  $slidePanelLeftMenu['staff']['links'] = array();

  if ($mR2_en == 'yes') {
  
    // Add user..
    if (in_array('team', $mR2) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['staff']['links'][] = array(
        'url' => '?p=team',
        'name' => $msg_adheader57
      );
    }

    // Manage users..
    if (in_array('teamman', $mR2) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['staff']['links'][] = array(
        'url' => '?p=teamman',
        'name' => $msg_adheader58
      );
    }
  
  }

}

?>