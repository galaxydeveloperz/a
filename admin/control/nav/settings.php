<?php if (!defined('PARENT')) { exit; }

/* MENU: SETTINGS & TOOLS
========================================================*/

$setMenuArr = array('settings','apages','tools','reports','log','backup');
$mR9        = array_intersect($setMenuArr, $userAccess);
$mR9_en     = (isset($nMenu['settings']['en']) && in_array($nMenu['settings']['en'], array('yes','no')) ? $nMenu['settings']['en'] : 'yes');

if (!empty($mR9) || USER_ADMINISTRATOR == 'yes') {

  $slidePanelLeftMenu['settings']          = array($msg_adheader37, 'cog', $mR9_en);
  $slidePanelLeftMenu['settings']['links'] = array();

  if ($mR9_en == 'yes') {
  
    // Settings..
    if (in_array('settings', $mR9) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['settings']['links'][] = array(
        'url' => '?p=settings',
        'name' => $msg_adheader2
      );
    }
  
    // Admin Pages..
    if (in_array('apages', $mR9) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['settings']['links'][] = array(
        'url' => '?p=apages',
        'name' => $msadminpages4_3[0]
      );
    }

    // Tools..
    if (in_array('tools', $mR9) || USER_ADMINISTRATOR == 'yes') {
      if (USER_DEL_PRIV == 'yes') {
        $slidePanelLeftMenu['settings']['links'][] = array(
          'url' => '?p=tools',
          'name' => $msg_adheader15
        );
      }
    }

    // Reports..
    if (in_array('reports', $mR9) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['settings']['links'][] = array(
        'url' => '?p=reports',
        'name' => $msg_adheader34
      );
    }

    // Entry log..
    if (in_array('log', $mR9) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['settings']['links'][] = array(
        'url' => '?p=log',
        'name' => $msg_adheader20
      );
    }

    // Database backup..
    if (in_array('backup', $mR9) || USER_ADMINISTRATOR == 'yes') {
      $slidePanelLeftMenu['settings']['links'][] = array(
        'url' => '?p=backup',
        'name' => $msg_adheader30
      );
    }
  
  }

}

?>