<?php if (!defined('PARENT')) { exit; }

/* MENU: DEPARTMENTS
========================================================*/

$deptMenuArr = array('dept','deptman');
$mR4         = array_intersect($deptMenuArr, $userAccess);
$mR4_en      = (isset($nMenu['dept']['en']) && in_array($nMenu['dept']['en'], array('yes','no')) ? $nMenu['dept']['en'] : 'yes');

if (!empty($mR4) || USER_ADMINISTRATOR == 'yes') {

  $slidePanelLeftMenu['dept']          = array($msg_adheader3, 'building', $mR4_en);
  $slidePanelLeftMenu['dept']['links'] = array();

  if ($mR4_en == 'yes') {
  
    // Add department..
    if (in_array('dept', $mR4) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['dept']['links'][] = array(
        'url' => '?p=dept',
        'name' => $msg_dept2
      );
    }

    // Manage departments..
    if (in_array('deptman', $mR4) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['dept']['links'][] = array(
        'url' => '?p=deptman',
        'name' => $msg_dept9
      );
    }
  
  }

}

?>