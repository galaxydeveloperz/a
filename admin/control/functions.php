<?php

/* Admin Functions
----------------------------------------------------------*/

function mswPRMarker($level, $clrs, $nme = '') {
  $c = ($clrs ? @unserialize($clrs) : array());
  if (!empty($c) && isset($c['fg'], $c['bg']) && $c['fg'] && $c['bg']) {
    return '<span class="level_marker" style="background:#' . mswSH($c['bg']) . ';color:#' . mswSH($c['fg']) . '">' . ($nme ? mswCD($nme) : $level) . '</span>';
  }
  return ($nme ? mswCD($nme) : $level);
}

function loadIPAddresses($ip) {
  $t = '&nbsp;';
  if ($ip) {
    if (strpos($ip, ',') !== false) {
      $t = array();
      foreach (explode(',', $ip) AS $ipp) {
        $ipt = trim($ipp);
        if ($ipt) {
          $t[] = '<a href="' . str_replace('{ip}', $ipt, IP_LOOKUP) . '" onclick="window.open(this);return false">' . $ipt . '</a>';
        }
      }
      return implode(', ', $t);
    } else {
      return '<a href="' . str_replace('{ip}', $ip, IP_LOOKUP) . '" onclick="window.open(this);return false">' . $ip . '</a>';
    }
  }
  return $t;
}

function mswUnreadMailbox($id) {
  return mswSQL_rows('mailassoc WHERE `staffID` = \'' . $id . '\' AND `folder` = \'inbox\' AND `status` = \'unread\'');
}

function mswClearExportFiles() {
  if (is_dir(PATH . 'export')) {
    $dir = opendir(PATH . 'export');
    while (false!==($read=readdir($dir))) {
      if (substr($read, -4) == '.csv') {
        @unlink(PATH . 'export/' . $read);
      }
    }
    closedir($dir);
  }
}

function getTicketLink($d = array()) {
  if ($d['t']->ticketStatus == 'open' && $d['t']->isDisputed == 'no' && $d['t']->assignedto != 'waiting' && $d['t']->spamFlag == 'no') {
    return array('?p=open', $d['l'][0]);
  }
  if (in_array($d['t']->ticketStatus, array('close','closed')) && $d['t']->isDisputed == 'no' && $d['t']->assignedto != 'waiting' && $d['t']->spamFlag == 'no') {
    return array('?p=close', $d['l'][1]);
  }
  if ($d['t']->ticketStatus == 'open' && $d['t']->isDisputed == 'yes' && $d['t']->assignedto != 'waiting' && $d['t']->spamFlag == 'no') {
    return array('?p=disputes', $d['l'][2]);
  }
  if (in_array($d['t']->ticketStatus, array('close','closed')) && $d['t']->isDisputed == 'yes' && $d['t']->assignedto != 'waiting' && $d['t']->spamFlag == 'no') {
    return array('?p=cdisputes', $d['l'][3]);
  }
  if (!in_array($d['t']->ticketStatus, array('open','close','closed')) && isset($d['s'][$d['t']->ticketStatus][0]) && $d['t']->assignedto != 'waiting' && $d['t']->spamFlag == 'no') {
    return array('?t_status=' . $d['t']->ticketStatus, $d['s'][$d['t']->ticketStatus][0]);
  }
  if ($d['t']->spamFlag == 'yes') {
    return array('?p=spam', $d['l'][4]);
  }
  if ($d['t']->isDisputed == 'no' && $d['t']->assignedto == 'waiting' && $d['t']->spamFlag == 'no') {
    return array('?p=assign', $d['l'][5]);
  }
  return array('','');
}

function helpPageLoader($page) {
  switch ($page) {
    case 'view-dispute':
      if (isset($_GET['disputeUsers'])) {
        return 'view-dispute-users';
      }
      break;
  }
  return $page;
}

function mswUserPageAccess($t) {
  $a = ($t->pageAccess ? explode('|', $t->pageAccess) : array());
  if ($t->addpages) {
    $b = explode(',', $t->addpages);
    return array_merge($a, $b);
  }
  return $a;
}

function mswDeptPerms($dept, $arr, $assigned = array()) {
  if (USER_ADMINISTRATOR == 'yes') {
    return 'ok';
  } elseif (!empty($dept) && in_array($dept, $arr)) {
    return 'ok';
  } elseif (isset($assigned['assigned'], $assigned['team'])) {
    $chop = explode(',', $assigned['assigned']);
    if (in_array($assigned['team'], $chop)) {
      return 'ok';
    } else {
      return 'fail';
    }
  } else {
    return 'fail';
  }
}

function mswSQL_deptfilter($code, $query = 'AND') {
  return ($code ? $query . ' ' . $code : '');
}

function userAccessPages($id) {
  $p = array();
  $q = mswSQL_query("SELECT `page` FROM `" . DB_PREFIX . "usersaccess`
       WHERE `userID`  = '{$id}'
       AND `type`      = 'pages'
       ORDER BY `page`
       ", __file__, __line__);
  while ($AP = mswSQL_fetchobj($q)) {
    $p[] = $AP->page;
  }
  if (!empty($p)) {
    mswSQL_query("UPDATE `" . DB_PREFIX . "users` SET
    `pageAccess`  = '" . implode('|', $p) . "'
    WHERE `id`    = '{$id}'
	  ", __file__, __line__);
    return implode('|', $p);
  }
  return '';
}

function mswDeptFilterAccess($MSTEAM, $userDeptAccess, $table) {
  $f = '';
  if ($MSTEAM->id != '1' && $MSTEAM->admin == 'no') {
    switch ($MSTEAM->assigned) {
      // Can view assigned tickets ONLY..
      case 'yes':
        switch ($table) {
          case 'department':
            $f = '`id` > 0 AND `manual_assign` = \'yes\'';
            break;
          case 'tickets':
            $f = 'FIND_IN_SET(\'' . $MSTEAM->id . '\',`assignedto`) > 0';
            break;
        }
        break;
      // Can view tickets by department..
      case 'no':
        switch ($table) {
          case 'department':
            if (!empty($userDeptAccess)) {
              $f = '`id` IN(' . implode(',', $userDeptAccess) . ')';
            } else {
              $f = '`id` = \'0\'';
            }
            break;
          case 'tickets':
            if (!empty($userDeptAccess)) {
              $f = '(`department` IN(' . implode(',', $userDeptAccess) . ') OR FIND_IN_SET(\'' . $MSTEAM->id . '\',`assignedto`) > 0)';
            } else {
              $f = '`department` = \'0\'';
            }
            break;
        }
        break;
    }
  }
  return $f;
}

function mswCallBackUrls($cmd) {
  if (isset($_GET['dla'])) {
    $cmd = 'ajax';
  }
  if (isset($_GET['response'])) {
    $cmd = 'view-ticket';
  }
  if (isset($_GET['fattachment'])) {
    $_GET['ajax'] = 'dl';
    $cmd = 'ajax';
  }
  if (isset($_GET['p']) && $_GET['p'] == 'cp') {
    $cmd = 'team-profile';
  }
  if (isset($_GET['ajax'])) {
    $cmd = 'ajax';
  }
  return $cmd;
}

// Field display information..
function mswFieldDisplayInformation($d = array()) {
  $chop = explode(',', $d['loc']);
  $dis  = array();
  if (in_array('ticket', $chop)) {
    $dis[] = $d['l'][0];
  }
  if (in_array('reply', $chop)) {
    $dis[] = $d['l'][1];
  }
  if (in_array('admin', $chop)) {
    $dis[] = $d['l'][2];
  }
  return implode(', ', $dis);
}

// Clear settings footers..
function mswClearSettingsFooters() {
  mswSQL_query("UPDATE `" . DB_PREFIX . "settings` SET
  `adminFooter`   = '',
  `publicFooter`  = ''
  ", __file__, __line__);
}

// Log in checker..
function mswIsLoggedIn($t, $ss) {
  if (($ss->active('_ms_mail') == 'yes' && $ss->active('_ms_key') == 'yes' && mswIsValidEmail($ss->get('_ms_mail'))) || ($ss->active_c('_msc_mail') == 'yes' && $ss->active_c('_msc_key') == 'yes' && mswIsValidEmail($ss->get_c('_msc_mail')))) {
    if (!isset($t->name)) {
      header("Location: index.php?p=login");
      exit;
    }
  } else {
    header("Location: index.php?p=login");
    exit;
  }
}

// Cleans CSV..adds quotes if data contains delimiter..
function mswCleanCSV($data, $del) {
  if (strpos($data, $del) !== false) {
    return '"' . mswCD($data) . '"';
  } else {
    return mswCD($data);
  }
}

// Get page access for user..
function mswGetUserPageAccess($id) {
  $q     = mswSQL_query("SELECT `pageAccess`,`addpages` FROM `" . DB_PREFIX . "users` WHERE `id` = '{$id}'", __file__, __line__);
  $U     = mswSQL_fetchobj($q);
  $pages = explode('|', $U->pageAccess);
  // Additional page rules..
  if ($U->addpages) {
    $add = array_map('trim', explode(',', $U->addpages));
    return array_merge($add, $pages);
  }
  return $pages;
}

// Get department access for user..
function mswGetDepartmentAccess($id) {
  $dept = array();
  $q = mswSQL_query("SELECT `deptID` FROM `" . DB_PREFIX . "userdepts` WHERE `userID` = '{$id}'", __file__, __line__);
  while ($row = mswSQL_fetchobj($q)) {
    $dept[] = $row->deptID;
  }
  // Are there any tickets assigned to this user NOT in the department array..?
  // If there are, add department to allowed array..
  $q2 = mswSQL_query("SELECT `department` FROM `" . DB_PREFIX . "tickets`
        WHERE `department` NOT IN(" . implode(',', (!empty($dept) ? $dept : array(
        '0'
        ))) . ")
        AND FIND_IN_SET('{$id}',`assignedto`) > 0
        GROUP BY `department`
        ", __file__, __line__);
  while ($DP = mswSQL_fetchobj($q2)) {
    //$dept[] = $DP->department;
  }
  if (!empty($dept)) {
    sort($dept);
  }
  return $dept;
}

// Standard response department..
function mswSrCat($depts) {
  $dep = array();
  if ($depts == '') {
    $depts = 0;
  }
  $q = mswSQL_query("SELECT `name` FROM `" . DB_PREFIX . "departments`
         WHERE `id` IN({$depts})
         ORDER BY `name`
	     ", __file__, __line__);
  while ($DP = mswSQL_fetchobj($q)) {
    $dep[] = mswSH($DP->name);
  }
  return (!empty($dep) ? implode(', ', $dep) : '');
}

?>