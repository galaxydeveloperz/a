<?php

/* CLASS FILE
----------------------------------*/

class systemUsers {

  public $settings;
  public $dt;
  public $dl;
  public $ssn;

  const REPORT_FILENAME = 'staff-report-{date}.csv';

  public function staffSaveSelections($id) {
    $saved = (!empty($_POST['staffmail']) ? serialize($_POST['staffmail']) : '');
    mswSQL_query("UPDATE `" . DB_PREFIX . "users` SET
    `savedstaff`  = '" . mswSQL($saved) . "'
    WHERE `id` = '{$id}'
    ", __file__, __line__); 
  }
  
  public function report($d = array()) {
    global $msg_script17;
    if (!is_writeable(PATH . 'export')) {
      return 'err';
    }
    $sepr = ',';
    $file = PATH . 'export/' . str_replace(array(
      '{date}'
    ), array(
      $this->dt->mswDateTimeDisplay(strtotime(date('Ymd H:i:s')), 'dmY-his')
    ), systemUsers::REPORT_FILENAME);
    $string = $d['l'][0] . mswNL();
    if (!empty($d['ids'])) {
      $sfreps = mswSQL_rows('replies WHERE `replyType` = \'admin\'', false);
      $q = mswSQL_query("SELECT `id`, `ts`, `name` FROM `" . DB_PREFIX . "users`
           " . (isset($_GET['param']) ? 'WHERE `id` = \'' . (int) $_GET['param'] . '\'' : '') . "
           ORDER BY `id`
           ", __file__, __line__);
      while ($USR = mswSQL_fetchobj($q)) {
        $total  = systemUsers::stats($USR->id, 'total');
        $allrp  = (($total / $sfreps) * 100);
        $perc   = mswNFM($allrp, 2);
        $lastTS = systemUsers::last($USR->id);
        $string .= mswCleanCSV(mswCD($USR->name), $sepr) . $sepr;
        $string .= mswCleanCSV($total, $sepr) . $sepr;
        $string .= mswCleanCSV(systemUsers::stats($USR->id, 'year'), $sepr) . $sepr;
        $string .= mswCleanCSV(systemUsers::stats($USR->id, 'month'), $sepr) . $sepr;
        $string .= mswCleanCSV(systemUsers::stats($USR->id, '3month'), $sepr) . $sepr;
        $string .= mswCleanCSV(systemUsers::stats($USR->id, '6month'), $sepr) . $sepr;
        $yearspassed = date('Y', $this->dt->mswTimeStamp()) - date('Y', $USR->ts);
        $monthspassed = abs((date('Y', $this->dt->mswTimeStamp()) - date('Y', $USR->ts))*12 + (date('m', $this->dt->mswTimeStamp()) - date('m', $USR->ts)));
        $mp = mswNFM($total / $monthspassed, 2);
        $yp = mswNFM($total / $yearspassed, 2);
        $string .= mswCleanCSV(($lastTS > 0 ? $this->dt->mswDateTimeDisplay($lastTS, $this->settings->dateformat) : $msg_script17), $sepr) . $sepr;
        $string .= mswCleanCSV((substr($mp, -2) == '00' ? substr($mp, 0, -3) : $mp), $sepr) . $sepr;
        $string .= mswCleanCSV((substr($yp, -2) == '00' ? substr($yp, 0, -3) : $yp), $sepr) . $sepr;
        $string .= mswCleanCSV((substr($perc, -2) == '00' ? substr($perc, 0, -3) : $perc), $sepr) . mswNL();
      }
      if (mswSQL_numrows($q) > 0) {
        // Save file to server and download..
        $this->dl->write($file, rtrim($string));
        if (file_exists($file)) {
          return $file;
        }
      }
    }
    return 'none';
  }

  public function updateDefDays($id) {
    $_GET['dd'] = (int) $_GET['dd'];
    if ($_GET['dd'] > 999) {
      $_GET['dd'] = 45;
    }
    mswSQL_query("UPDATE `" . DB_PREFIX . "users` SET
    `defDays`  = '{$_GET['dd']}'
    WHERE `id` = '{$id}'
    ", __file__, __line__);
  }

  public function enable() {
    $_GET['id'] = (int) $_GET['id'];
    mswSQL_query("UPDATE `" . DB_PREFIX . "users` SET
    `enabled`  = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id` = '{$_GET['id']}'
    ", __file__, __line__);
  }

  public function reset($acc) {
    $changed = array();
    for ($i = 0; $i < count($_POST['id']); $i++) {
      $e  = strtolower($_POST['mail'][$i]);
      $n  = $_POST['name'][$i];
      $np = '';
      $p  = ($_POST['password'][$i] ? mswPassHash(array('type' => 'add', 'pass' => $_POST['password'][$i])) : '');
      if ($p == '' && isset($_POST['autoall'])) {
        $pg                    = $acc->ms_generate();
        $_POST['password'][$i] = $pg;
        $p                     = mswPassHash(array('type' => 'add', 'pass' => $pg));
      }
      $id = $_POST['id'][$i];
      if ($e && $p) {
        mswSQL_query("UPDATE `" . DB_PREFIX . "users` SET
        `email`     = '{$e}',
        `accpass`   = '{$p}'
        WHERE `id`  = '{$id}'
        ", __file__, __line__);
        // Was anything updated?
        if (mswSQL_affrows() > 0) {
          $changed[] = array(
            'id' => $id,
            'pass' => $_POST['password'][$i]
          );
        }
      }
    }
    return $changed;
  }

  public function log($user) {
    $defLogs = ($this->settings->defKeepLogs ? unserialize($this->settings->defKeepLogs) : array());
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "log` (
    `ts`,`userID`,`ip`,`type`
    ) VALUES (
    UNIX_TIMESTAMP(),'{$user->id}','" . mswSQL(mswIP()) . "','user'
    )", __file__, __line__);
    // Clear previous..
    if (isset($defLogs['user']) && $defLogs['user'] > 0) {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "log` WHERE `userID` = '{$user->id}' AND `id` <
	    (SELECT min(`id`) FROM
      (SELECT `id` FROM `" . DB_PREFIX . "log`
	    WHERE `userID` = '{$user->id}'
	    AND `type`     = 'user'
	    ORDER BY `id` DESC LIMIT " . $defLogs['user'] . "
	    ) AS `" . DB_PREFIX . "log`)", __file__, __line__);
    }
  }

  public function add($user) {
    $editperms = (!empty($_POST['editperms']) ? serialize($_POST['editperms']) : '');
    $isAdminUser = 'no';
    if ($user != '1') {
      $isAdminUser = 'no';
    } else {
      if (isset($_POST['admin'])) {
        $isAdminUser = 'yes';
        $editperms = serialize(array('ticket','reply'));
      }
    }
    $digestops = (!empty($_POST['digops']) ? serialize($_POST['digops']) : '');
    $digestdays = (!empty($_POST['digdays']) ? serialize($_POST['digdays']) : '');
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "users` (
    `ts`,
    `name`,
    `email`,
    `email2`,
    `accpass`,
    `signature`,
    `notify`,
    `spamnotify`,
    `pageAccess`,
    `emailSigs`,
    `notePadEnable`,
    `delPriv`,
    `nameFrom`,
    `emailFrom`,
    `assigned`,
    `timezone`,
    `ticketHistory`,
    `enableLog`,
    `mailbox`,
    `mailFolders`,
    `mailDeletion`,
    `mailScreen`,
    `mailCopy`,
    `mailPurge`,
    `addpages`,
    `mergeperms`,
    `digest`,
    `profile`,
    `helplink`,
    `editperms`,
    `close`,
    `lock`,
    `admin`,
    `timer`,
    `startwork`,
    `workedit`,
    `language`,
    `staffupnotify`,
    `faqHistory`,
    `digestops`,
    `digestdays`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '" . mswSQL($_POST['name']) . "',
    '" . strtolower($_POST['email']) . "',
    '" . mswSQL(strtolower($_POST['email2'])) . "',
    '" . mswPassHash(array('type' => 'add', 'pass' => $_POST['accpass'])) . "',
    '" . mswSQL(strip_tags($_POST['signature'])) . "',
    '" . (isset($_POST['notify']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['spamnotify']) ? 'yes' : 'no') . "',
    '" . (!empty($_POST['accessPages']) ? mswSQL(implode('|', $_POST['accessPages'])) : '') . "',
    '" . (isset($_POST['emailSigs']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['notePadEnable']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '" . (isset($_POST['delPriv']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '" . mswSQL($_POST['nameFrom']) . "',
    '" . mswSQL($_POST['emailFrom']) . "',
    '" . (isset($_POST['assigned']) ? 'yes' : 'no') . "',
    '" . mswSQL($_POST['timezone']) . "',
    '" . (isset($_POST['ticketHistory']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '" . (isset($_POST['enableLog']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '" . (isset($_POST['mailbox']) ? 'yes' : 'no') . "',
    '" . (int) $_POST['mailFolders'] . "',
    '" . (isset($_POST['mailDeletion']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '" . (isset($_POST['mailScreen']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['mailCopy']) ? 'yes' : 'no') . "',
    '" . (int) $_POST['mailPurge'] . "',
    '" . mswSQL($_POST['addpages']) . "',
    '" . (isset($_POST['mergeperms']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '" . (isset($_POST['digest']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['profile']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '" . (isset($_POST['helplink']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '" . mswSQL($editperms) . "',
    '" . (isset($_POST['close']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '" . (isset($_POST['lock']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '{$isAdminUser}',
    '" . (isset($_POST['timer']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '" . (isset($_POST['startwork']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '" . (isset($_POST['workedit']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '" . mswSQL($_POST['language']) . "',
    '" . (isset($_POST['staffupnotify']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '" . (isset($_POST['faqHistory']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    '" . mswSQL($digestops) . "',
    '" . mswSQL($digestdays) . "'
    )", __file__, __line__);
    $id = mswSQL_insert_id();
    // Add to user departments..
    if (!empty($_POST['dept']) && !isset($_POST['assigned'])) {
      foreach ($_POST['dept'] AS $dID) {
        mswSQL_query("INSERT INTO `" . DB_PREFIX . "userdepts` (
        `userID`,`deptID`
        ) VALUES (
        '{$id}','{$dID}'
        )", __file__, __line__);
      }
    } else {
      // If no departments were set, add user to all as default..
      // If ticket assign is on, no departments needed..
      if (!isset($_POST['assigned'])) {
        $d = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "departments` ORDER BY `orderBy`", __file__, __line__);
        while ($D = mswSQL_fetchobj($d)) {
          mswSQL_query("INSERT INTO `" . DB_PREFIX . "userdepts` (
          `userID`,`deptID`
          ) VALUES (
          '{$id}','{$D->id}'
          )", __file__, __line__);
        }
      }
    }
    // Determine access pages..
    if (!empty($_POST['accessPages'])) {
      foreach ($_POST['accessPages'] AS $aPage) {
        mswSQL_query("INSERT INTO `" . DB_PREFIX . "usersaccess` (
        `page`,`userID`,`type`
        ) VALUES (
        '{$aPage}','{$id}','pages'
        )", __file__, __line__);
      }
    }
    return $id;
  }

  public function profile($user) {
    $rows = 0;
    $pass = ($_POST['accpass'] ? mswPassHash(array('type' => 'add', 'pass' => $_POST['accpass'])) : $user->accpass);
    // This is a security check. Make sure details don`t match someone else`s account..
    if (mswSQL_rows('users WHERE LOWER(`email`) = \'' . mswSQL(strtolower($_POST['email'])) . '\' AND `id` != \'' . $user->id . '\'') == 0) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "users` SET
      `name`           = '" . mswSQL($_POST['name']) . "',
      `email`          = '" . mswSQL(strtolower($_POST['email'])) . "',
      `email2`         = '" . mswSQL(strtolower($_POST['email2'])) . "',
      `accpass`        = '{$pass}',
      `signature`      = '" . mswSQL(strip_tags($_POST['signature'])) . "',
      `notify`         = '" . (isset($_POST['notify']) ? 'yes' : 'no') . "',
      `spamnotify`     = '" . (isset($_POST['spamnotify']) ? 'yes' : 'no') . "',
      `emailSigs`      = '" . (isset($_POST['emailSigs']) ? 'yes' : 'no') . "',
      `nameFrom`       = '" . mswSQL($_POST['nameFrom']) . "',
      `emailFrom`      = '" . mswSQL(strtolower($_POST['emailFrom'])) . "',
      `timezone`       = '" . mswSQL($_POST['timezone']) . "',
      `language`       = '" . mswSQL($_POST['language']) . "'
      WHERE `id`       = '{$user->id}'
      ", __file__, __line__);
      $rows = mswSQL_affrows();
      // Update session vars..
      $this->ssn->set(array('_ms_mail' => strtolower($_POST['email'])));
      if ($_POST['accpass']) {
        $this->ssn->set(array('_ms_key' => $pass));
      }
      // Clear cookies..
      if ($this->ssn->active_c('_msc_mail') == 'yes') {
        $this->ssn->delete_c(array('_msc_mail', '_msc_key'));
      }
    }
    return $rows;
  }

  public function update($user) {
    $_GET['edit']  = (int) $_POST['update'];
    $pass          = ($_POST['accpass'] ? mswPassHash(array('type' => 'add', 'pass' => $_POST['accpass'])) : $_POST['old_pass']);
    $editperms     = (!empty($_POST['editperms']) ? serialize($_POST['editperms']) : '');
    $isAdminUser = 'no';
    if ($user != '1') {
      // If previously set as admin, this remains..
      $isAdminUser = (isset($_POST['admin']) ? 'yes' : 'no');
    } else {
      if (isset($_POST['admin']) || $_GET['edit'] == '1') {
        $isAdminUser = 'yes';
        $editperms = serialize(array('ticket','reply'));
      }
    }
    $digestops = (!empty($_POST['digops']) ? serialize($_POST['digops']) : '');
    $digestdays = (!empty($_POST['digdays']) ? serialize($_POST['digdays']) : '');
    mswSQL_query("UPDATE `" . DB_PREFIX . "users` SET
    `name`           = '" . mswSQL($_POST['name']) . "',
    `email`          = '" . strtolower($_POST['email']) . "',
    `email2`         = '" . mswSQL(strtolower($_POST['email2'])) . "',
    `accpass`        = '{$pass}',
    `signature`      = '" . mswSQL(strip_tags($_POST['signature'])) . "',
    `notify`         = '" . (isset($_POST['notify']) ? 'yes' : 'no') . "',
    `spamnotify`     = '" . (isset($_POST['spamnotify']) ? 'yes' : 'no') . "',
    `pageAccess`     = '" . (!empty($_POST['accessPages']) ? mswSQL(implode('|', $_POST['accessPages'])) : '') . "',
    `emailSigs`      = '" . (isset($_POST['emailSigs']) ? 'yes' : 'no') . "',
    `notePadEnable`  = '" . (isset($_POST['notePadEnable']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `delPriv`        = '" . (isset($_POST['delPriv']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `nameFrom`       = '" . mswSQL($_POST['nameFrom']) . "',
    `emailFrom`      = '" . mswSQL(strtolower($_POST['emailFrom'])) . "',
    `assigned`       = '" . (isset($_POST['assigned']) ? 'yes' : 'no') . "',
    `timezone`       = '" . mswSQL($_POST['timezone']) . "',
    `enabled`        = '" . (isset($_POST['enabled']) ? 'yes' : 'no') . "',
    `ticketHistory`  = '" . (isset($_POST['ticketHistory']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `enableLog`      = '" . (isset($_POST['enableLog']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `mailbox`        = '" . (isset($_POST['mailbox']) ? 'yes' : 'no') . "',
    `mailFolders`    = '" . (int) $_POST['mailFolders'] . "',
    `mailDeletion`   = '" . (isset($_POST['mailDeletion']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `mailScreen`     = '" . (isset($_POST['mailScreen']) ? 'yes' : 'no') . "',
    `mailCopy`       = '" . (isset($_POST['mailCopy']) ? 'yes' : 'no') . "',
    `mailPurge`      = '" . (int) $_POST['mailPurge'] . "',
    `addpages`       = '" . (isset($_POST['addpages']) ? mswSQL($_POST['addpages']) : '') . "',
    `mergeperms`     = '" . (isset($_POST['mergeperms']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `digest`         = '" . (isset($_POST['digest']) ? 'yes' : 'no') . "',
    `profile`        = '" . (isset($_POST['profile']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `helplink`       = '" . (isset($_POST['helplink']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `editperms`      = '" . mswSQL($editperms) . "',
    `close`          = '" . (isset($_POST['close']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `lock`           = '" . (isset($_POST['lock']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `admin`          = '{$isAdminUser}',
    `timer`          = '" . (isset($_POST['timer']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `startwork`      = '" . (isset($_POST['startwork']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `workedit`       = '" . (isset($_POST['workedit']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `language`       = '" . mswSQL($_POST['language']) . "',
    `staffupnotify`  = '" . (isset($_POST['staffupnotify']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `faqHistory`     = '" . (isset($_POST['faqHistory']) || $isAdminUser == 'yes' ? 'yes' : 'no') . "',
    `digestops`      = '" . mswSQL($digestops) . "',
    `digestdays`     = '" . mswSQL($digestdays) . "'
    WHERE `id`       = '{$_POST['update']}'
    ", __file__, __line__);
    // Add to user departments..
    if (!empty($_POST['dept']) && !isset($_POST['assigned']) && $_POST['update'] > 1) {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "userdepts`
      WHERE `userID` = '{$_GET['edit']}'
      ", __file__, __line__);
      mswSQL_truncate(array('userdepts'));
      foreach ($_POST['dept'] AS $dID) {
        mswSQL_query("INSERT INTO `" . DB_PREFIX . "userdepts` (
        `userID`,`deptID`
        ) VALUES (
        '{$_GET['edit']}','{$dID}'
        )", __file__, __line__);
      }
    } else {
      // If not global user, add to all departments if none set..
      if ($_GET['edit'] > 1) {
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "userdepts`
        WHERE `userID` = '{$_GET['edit']}'
        ", __file__, __line__);
        // If no departments were set, add user to all as default..
        // Not needed if assignment is on..
        if (!isset($_POST['assigned'])) {
          $d = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "departments` ORDER BY `orderBy`", __file__, __line__);
          while ($D = mswSQL_fetchobj($d)) {
            mswSQL_query("INSERT INTO `" . DB_PREFIX . "userdepts` (
            `userID`,`deptID`
            ) VALUES (
            '{$_GET['edit']}','{$D->id}'
            )", __file__, __line__);
          }
        }
      }
    }
    // Determine access pages..
    if (!empty($_POST['accessPages']) && $_GET['edit'] > 1) {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "usersaccess`
      WHERE `userID` = '{$_GET['edit']}'
      ", __file__, __line__);
      mswSQL_truncate(array('usersaccess'));
      foreach ($_POST['accessPages'] AS $aPage) {
        mswSQL_query("INSERT INTO `" . DB_PREFIX . "usersaccess` (
      `page`,`userID`,`type`
      ) VALUES (
      '{$aPage}','{$_GET['edit']}','pages'
      )", __file__, __line__);
      }
    }
    // If password was set and the person logged in has changed their details, change session vars..
    // We`ll update password and email session vars and reset cookies..
    if ($user == $_GET['edit']) {
      $this->ssn->set(array('_ms_mail' => strtolower($_POST['email'])));
      if ($_POST['accpass']) {
        $this->ssn->set(array('_ms_key' => $pass));
      }
      // Clear cookies..
      if ($this->ssn->active_c('_msc_mail') == 'yes') {
        $this->ssn->delete_c(array('_msc_mail', '_msc_key'));
      }
    }
  }

  public function delete() {
    if (!empty($_POST['del'])) {
      $uID = mswSQL(implode(',', $_POST['del']));
      // Users info..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "users`
      WHERE `id` IN({$uID})
      ", __file__, __line__);
      $rows = mswSQL_affrows();
      // Departments assigned..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "userdepts`
      WHERE `userID` IN({$uID})
      ", __file__, __line__);
      // Access assigned..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "usersaccess`
      WHERE `userID` IN({$uID})
      ", __file__, __line__);
      // Log entries..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "log`
      WHERE `userID` IN({$uID})
	    AND `type`      = 'user'
      ", __file__, __line__);
      mswSQL_truncate(array('users','userdepts','usersaccess','log'));
      return $rows;
    }
  }

  // Does email exist..
  public function check($entered = '') {
    $SQL = '';
    if ($entered) {
      $_POST['checkEntered'] = $entered;
    }
    if (isset($_POST['currID']) && (int) $_POST['currID'] > 0) {
      $_POST['currID'] = (int) $_POST['currID'];
      $SQL             = "AND `id` != '{$_POST['currID']}'";
    }
    $q = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "users`
         WHERE LOWER(`email`) = '" . mswSQL(strtolower($_POST['checkEntered'])) . "'
	       $SQL
         LIMIT 1
         ", __file__, __line__);
    $P = mswSQL_fetchobj($q);
    return (isset($P->id) ? 'exists' : 'accept');
  }

  // Reset password..
  public function password($id, $password) {
    mswSQL_query("UPDATE `" . DB_PREFIX . "users` SET
    `accpass`  = '" . mswPassHash(array('type' => 'add', 'pass' => $password)) . "'
    WHERE `id` = '{$id}'
    ", __file__, __line__);
    return $password;
  }

  // Stats
  public function stats($id, $time) {
    switch($time) {
      case 'total':
        $SQL = 'WHERE `replyType` = \'admin\' AND `replyUser` = \'' . $id . '\'';
        break;
      case 'year':
        $SQL = 'WHERE `replyType` = \'admin\' AND `replyUser` = \'' . $id . '\' AND YEAR(FROM_UNIXTIME(`ts`)) = \'' . date('Y', $this->dt->mswTimeStamp())  . '\'';
        break;
      case 'month':
        $SQL = 'WHERE `replyType` = \'admin\' AND `replyUser` = \'' . $id . '\' AND MONTH(FROM_UNIXTIME(`ts`)) = \'' . date('m', $this->dt->mswTimeStamp())  . '\' AND YEAR(FROM_UNIXTIME(`ts`)) = \'' . date('Y', $this->dt->mswTimeStamp())  . '\'';
        break;
      case '3month':
        $SQL = 'WHERE `replyType` = \'admin\' AND `replyUser` = \'' . $id . '\' AND (`ts` BETWEEN \'' . strtotime(date('Y-m-d H:i:s', strtotime('-3 months'))) . '\' AND \'' . strtotime(date('Y-m-d H:i:s', $this->dt->mswTimeStamp()))  . '\')';
        break;
      case '6month':
        $SQL = 'WHERE `replyType` = \'admin\' AND `replyUser` = \'' . $id . '\' AND (`ts` BETWEEN \'' . strtotime(date('Y-m-d H:i:s', strtotime('-6 months'))) . '\' AND \'' . strtotime(date('Y-m-d H:i:s', $this->dt->mswTimeStamp()))  . '\')';
        break;
    }
    $q = mswSQL_query("SELECT count(*) AS `rC`
         FROM `" . DB_PREFIX . "replies`
         $SQL
         GROUP BY `replyUser`
         ", __file__, __line__);
    $C = mswSQL_fetchobj($q);
    return (isset($C->rC) ? mswNFM($C->rC) : '0');
  }

  // Last reply..
  public function last($id) {
    $q = mswSQL_query("SELECT `ts`
         FROM `" . DB_PREFIX . "replies`
         WHERE `replyType` = 'admin'
         AND `replyUser` = '{$id}'
         ORDER BY `id` DESC LIMIT 1
         ", __file__, __line__);
    $L = mswSQL_fetchobj($q);
    return (isset($L->ts) ? $L->ts : '0');
  }
  
  // Search accounts..
  public function searchTeamPages($v) {
    $ar  = array();
    $q   = mswSQL_query("SELECT `id`,`name`,`email` FROM `" . DB_PREFIX . "users`
           WHERE (LOWER(`name`) LIKE '%" . strtolower(mswSQL($v)) . "%'
            OR LOWER(`email`) LIKE '%" . strtolower(mswSQL($v)) . "%')
           AND `enabled` = 'yes'
           ORDER BY `name`, `email`
           ", __file__, __line__);
    while ($A = mswSQL_fetchobj($q)) {
      $ar[] = array(
        'value' => $A->id,
        'label' => mswSH($A->name) . ' (' . $A->email . ')',
        'name' => mswSH($A->name),
        'email' => mswSH($A->email)
      );
    }
    return $ar;
  }

}

?>