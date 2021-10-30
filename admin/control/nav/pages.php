<?php if (!defined('PARENT')) { exit; }

/* MENU: OTHER PAGES
========================================================*/

$cPagesMenuArr = array('pages','pageman');
$mR11          = array_intersect($cPagesMenuArr, $userAccess);
$mR11_en       = (isset($nMenu['pages']['en']) && in_array($nMenu['pages']['en'], array('yes','no')) ? $nMenu['pages']['en'] : 'yes');

if (!empty($mR11) || USER_ADMINISTRATOR == 'yes') {

  $slidePanelLeftMenu['pages']          = array($msadminlang3_1cspages[0], 'file-text-o', $mR11_en);
  $slidePanelLeftMenu['pages']['links'] = array();

  if ($mR11_en == 'yes') {
  
    // Add custom pages..
    if (in_array('pages', $mR11) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['pages']['links'][] = array(
        'url' => '?p=pages',
        'name' => $msadminlang3_1cspages[1]
      );
    }

    // Manage custom pages..
    if (in_array('pageman', $mR11) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['pages']['links'][] = array(
        'url' => '?p=pageman',
        'name' => $msadminlang3_1cspages[2]
      );
    }
  
  }

}

?>