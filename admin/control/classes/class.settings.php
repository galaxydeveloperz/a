<?php

/* CLASS FILE
----------------------------------*/

class systemSettings {

  public $datetime;
  public $settings;
  public $upload;

  const ENTRY_LOG_FILENAME = 'log-{date}.csv';
  const REPORT_LOG_FILENAME = 'reports-{date}.csv';

  public function cleanUpOps() {
    // Dead logs that don't belong to anyone..
    mswSQL_query("DELETE FROM `" . DB_PREFIX . "log` WHERE
    (SELECT count(*) FROM `" . DB_PREFIX . "users` WHERE
    `" . DB_PREFIX . "log`.`userID` = `" . DB_PREFIX . "users`.`id`) = 0
    and (SELECT count(*) FROM `" . DB_PREFIX . "portal` WHERE
    `" . DB_PREFIX . "log`.`userID` = `" . DB_PREFIX . "portal`.`id`) = 0
    ", __file__, __line__);
    // Dead dispute users..
    mswSQL_query("DELETE FROM `" . DB_PREFIX . "disputes` WHERE
    (SELECT count(*) FROM `" . DB_PREFIX . "portal` WHERE
    `" . DB_PREFIX . "disputes`.`visitorID` = `" . DB_PREFIX . "portal`.`id`) = 0
    OR (select count(*) FROM `" . DB_PREFIX . "tickets` WHERE
    `" . DB_PREFIX . "disputes`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`) = 0
    ", __file__, __line__);
  }
  
  public function batchEnableDisable($fields) {
    $opt = ($_POST['action'] == 'enable' ? 'yes' : 'no');
    foreach (array_keys($fields) AS $k) {
      if (in_array($k, $_POST['tbls'])) {
        switch ($k) {
          case 'users':
            $tbl   = 'users';
            $field = 'enabled';
            break;
          case 'portal':
            $tbl   = 'portal';
            $field = 'enabled';
            break;
          case 'fields':
            $tbl   = 'cusfields';
            $field = 'enField';
            break;
          case 'responses':
            $tbl   = 'responses';
            $field = 'enResponse';
            break;
          case 'imap':
            $tbl   = 'imap';
            $field = 'im_piping';
            break;
          case 'faq-cat':
            $tbl   = 'categories';
            $field = 'enCat';
            break;
          case 'faq-que':
            $tbl   = 'faq';
            $field = 'enFaq';
            break;
        }
        // For users, we skip ID 1..
        if ($k == 'users') {
          mswSQL_query("UPDATE `" . DB_PREFIX . $tbl . "` SET
	        `" . $field . "` = '{$opt}'
		      WHERE `id`  != '1'
	        ", __file__, __line__);
        } else {
          mswSQL_query("UPDATE `" . DB_PREFIX . $tbl . "` SET
	        `" . $field . "` = '{$opt}'
	        ", __file__, __line__);
        }
      }
    }
  }

  public function exportReportCSV($dl) {
    if (!is_writeable(PATH . 'export')) {
      return 'err';
    }
    global $msg_reports7, $msg_reports8, $msg_reports9, $msg_reports10, $msg_reports11, $msg_script21, $msadminlang_reports_3_7;
    $sep  = ',';
    $file = PATH . 'export/' . str_replace('{date}', date('dmY-his'), systemSettings::REPORT_LOG_FILENAME);
    if ($this->settings->disputes == 'yes') {
      $data = $msg_reports7 . $sep . $msg_reports8 . $sep . $msg_reports9 . $sep . $msg_reports10 . $sep . $msg_reports11;
    } else {
      $data = $msg_reports7 . $sep . $msg_reports8 . $sep . $msg_reports9;
    }
    if ($this->settings->timetrack == 'yes') {
      $data .= $sep . $msadminlang_reports_3_7[0] . mswNL();
    } else {
      $data .= mswNL();
    }
    $from  = (isset($_POST['from']) && $this->datetime->mswDatePickerFormat($_POST['from']) != '0000-00-00' ? $_POST['from'] : $this->datetime->mswConvertMySQLDate(date('Y-m-d', strtotime('-6 months', $this->datetime->mswTimeStamp()))));
    $to    = (isset($_POST['to']) && $this->datetime->mswDatePickerFormat($_POST['to']) != '0000-00-00' ? $_POST['to'] : $this->datetime->mswConvertMySQLDate(date('Y-m-d', $this->datetime->mswTimeStamp())));
    $view  = (isset($_POST['view']) && in_array($_POST['view'], array(
      'month',
      'day'
    )) ? $_POST['view'] : 'month');
    $dept  = (isset($_POST['dept']) ? $_POST['dept'] : '0');
    // Get data..
    $where = 'WHERE DATE(FROM_UNIXTIME(`ts`)) BETWEEN \'' . $this->datetime->mswDatePickerFormat($from) . '\' AND \'' . $this->datetime->mswDatePickerFormat($to) . '\'';
    if (substr($dept, 0, 1) == 'u') {
      $where .= mswNL() . 'AND FIND_IN_SET(\'' . substr($dept, 1) . '\',`assignedto`) > 0';
    } else {
      if ($dept > 0) {
        $where .= mswNL() . 'AND `department` = \'' . $dept . '\'';
      }
    }
    $where .= mswNL() . 'AND `assignedto` != \'waiting\'';
    switch ($view) {
      case 'month':
        $qRE = mswSQL_query("SELECT *,MONTH(FROM_UNIXTIME(`ts`)) AS `m`,YEAR(FROM_UNIXTIME(`ts`)) AS `y` FROM `" . DB_PREFIX . "tickets`
               $where
		           AND `spamFlag` = 'no'
               GROUP BY MONTH(FROM_UNIXTIME(`ts`)),YEAR(FROM_UNIXTIME(`ts`))
               ORDER BY `ts`
               ", __file__, __line__);
        break;
      case 'day':
        $qRE = mswSQL_query("SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `d` FROM `" . DB_PREFIX . "tickets`
               $where
               AND `spamFlag` = 'no'
               GROUP BY DATE(FROM_UNIXTIME(`ts`))
               ORDER BY `ts`
               ", __file__, __line__);
        break;
    }
    while ($REP = mswSQL_fetchobj($qRE)) {
      switch ($view) {
        case 'month':
          // Total work time..
          $TWT = mswSQL_fetchobj(
                 mswSQL_query("SELECT SUM(TIME_TO_SEC(`worktime`)) AS `twt` FROM `" . DB_PREFIX . "tickets`
                 $where
                 AND `ticketStatus`             NOT IN('closed')
                 AND MONTH(FROM_UNIXTIME(`ts`)) = '{$REP->m}'
                 AND YEAR(FROM_UNIXTIME(`ts`))  = '{$REP->y}'
                 ", __file__, __line__)
                 );
          // Open tickets..
          $C1 = mswSQL_fetchobj(mswSQL_query("SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                $where
                AND `ticketStatus`             NOT IN('close','closed')
                AND `isDisputed`               = 'no'
                AND `spamFlag`                 = 'no'
                AND MONTH(FROM_UNIXTIME(`ts`)) = '{$REP->m}'
                AND YEAR(FROM_UNIXTIME(`ts`))  = '{$REP->y}'
                ", __file__, __line__));
          // Closed tickets..
          $C2 = mswSQL_fetchobj(mswSQL_query("SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                $where
                AND `ticketStatus`             = 'close'
                AND `isDisputed`               = 'no'
                AND `spamFlag`                 = 'no'
                AND MONTH(FROM_UNIXTIME(`ts`)) = '{$REP->m}'
                AND YEAR(FROM_UNIXTIME(`ts`))  = '{$REP->y}'
                ", __file__, __line__));
          if ($this->settings->disputes == 'yes') {
            // Open disputes..
            $C3 = mswSQL_fetchobj(mswSQL_query("SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                  $where
                  AND `ticketStatus`             NOT IN('close','closed')
                  AND `isDisputed`               = 'yes'
                  AND `spamFlag`                 = 'no'
                  AND MONTH(FROM_UNIXTIME(`ts`)) = '{$REP->m}'
                  AND YEAR(FROM_UNIXTIME(`ts`))  = '{$REP->y}'
                  ", __file__, __line__));
            // Closed disputes..
            $C4 = mswSQL_fetchobj(mswSQL_query("SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                  $where
                  AND `ticketStatus`             = 'close'
                  AND `isDisputed`               = 'yes'
                  AND `spamFlag`                 = 'no'
                  AND MONTH(FROM_UNIXTIME(`ts`)) = '{$REP->m}'
                  AND YEAR(FROM_UNIXTIME(`ts`))  = '{$REP->y}'
                  ", __file__, __line__));
          }
          break;
        case 'day':
          // Total work time..
          $TWT = mswSQL_fetchobj(
                 mswSQL_query("SELECT SUM(TIME_TO_SEC(`worktime`)) AS `twt` FROM `" . DB_PREFIX . "tickets`
                 $where
                 AND `ticketStatus`             NOT IN('closed')
                 AND DATE(FROM_UNIXTIME(`ts`))  = '{$REP->d}'
                 ", __file__, __line__)
                 );
          // Open tickets..
          $C1 = mswSQL_fetchobj(mswSQL_query("SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                $where
                AND `ticketStatus`             NOT IN('close','closed')
                AND `isDisputed`               = 'no'
                AND `spamFlag`                 = 'no'
                AND DATE(FROM_UNIXTIME(`ts`))  = '{$REP->d}'
                ", __file__, __line__));
          // Closed tickets..
          $C2 = mswSQL_fetchobj(mswSQL_query("SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                $where
                AND `ticketStatus`             = 'close'
                AND `isDisputed`               = 'no'
                AND `spamFlag`                 = 'no'
                AND DATE(FROM_UNIXTIME(`ts`))  = '{$REP->d}'
                ", __file__, __line__));
          if ($this->settings->disputes == 'yes') {
            // Open disputes..
            $C3 = mswSQL_fetchobj(mswSQL_query("SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                  $where
                  AND `ticketStatus`             NOT IN('close','closed')
                  AND `isDisputed`               = 'yes'
                  AND `spamFlag`                 = 'no'
                  AND DATE(FROM_UNIXTIME(`ts`))  = '{$REP->d}'
                  ", __file__, __line__));
            // Closed disputes..
            $C4 = mswSQL_fetchobj(mswSQL_query("SELECT COUNT(*) AS `c` FROM `" . DB_PREFIX . "tickets`
                  $where
                  AND `ticketStatus`             = 'close'
                  AND `isDisputed`               = 'yes'
                  AND `spamFlag`                 = 'no'
                  AND DATE(FROM_UNIXTIME(`ts`))  = '{$REP->d}'
                  ", __file__, __line__));
          }
          break;
      }
      $cnt1 = (isset($C1->c) ? $C1->c : '0');
      $cnt2 = (isset($C2->c) ? $C2->c : '0');
      $cnt3 = (isset($C3->c) ? $C3->c : '0');
      $cnt4 = (isset($C4->c) ? $C4->c : '0');
      $twt  = (isset($TWT->twt) ? $TWT->twt : '0');
      if ($this->settings->disputes == 'yes') {
        $data .= ($view == 'day' ? date($this->settings->dateformat, strtotime($REP->d)) : $msg_script21[($REP->m - 1)] . ' ' . $REP->y) . $sep;
        $data .= mswNFM($cnt1) . $sep;
        $data .= mswNFM($cnt2) . $sep;
        $data .= mswNFM($cnt3) . $sep;
        $data .= mswNFM($cnt4);
      } else {
        $data .= ($view == 'day' ? date($this->settings->dateformat, strtotime($REP->d)) : $msg_script21[($REP->m - 1)] . ' ' . $REP->y) . $sep;
        $data .= mswNFM($cnt1) . $sep;
        $data .= mswNFM($cnt2);
      }
      if ($this->settings->timetrack == 'yes') {
        $data .= $sep . $this->datetime->secToTime($twt) . mswNL();
      } else {
        $data .= mswNL();
      }
    }
    if ($data) {
      // Save file to server and download..
      $dl->write($file, rtrim($data));
      return $file;
    }
    return 'none';
  }

  public function exportLogFile($dl) {
    global $msg_log15, $msg_log14;
    if (!is_writeable(PATH . 'export')) {
      return 'err';
    } else {
      $file  = PATH . 'export/' . str_replace('{date}', date('dmY-his'), systemSettings::ENTRY_LOG_FILENAME);
      $data  = '';
      $sepr  = ',';
      $from  = (isset($_POST['from']) && $this->datetime->mswDatePickerFormat($_POST['from']) != '0000-00-00' ? $_POST['from'] : '');
      $to    = (isset($_POST['to']) && $this->datetime->mswDatePickerFormat($_POST['to']) != '0000-00-00' ? $_POST['to'] : '');
      $keys  = '';
      $where = array();
      if (isset($_POST['keys']) && $_POST['keys']) {
        $chop  = explode(' ', $_POST['keys']);
        $words = '';
        for ($i = 0; $i < count($chop); $i++) {
          $words .= ($i ? 'OR ' : 'WHERE (') . "`" . DB_PREFIX . "portal`.`name` LIKE '%" . mswSQL($chop[$i]) . "%' OR `" . DB_PREFIX . "users`.`name` LIKE '%" . mswSQL($chop[$i]) . "%' ";
        }
        if ($words) {
          $where[] = $words . ')';
        }
      }
      if ($from && $to) {
        $where[] = (!empty($where) ? 'AND ' : 'WHERE ') . 'DATE(FROM_UNIXTIME(`' . DB_PREFIX . 'log`.`ts`)) BETWEEN \'' . $this->datetime->mswDatePickerFormat($from) . '\' AND \'' . $this->datetime->mswDatePickerFormat($to) . '\'';
      }
      $q_log = mswSQL_query("SELECT *,
               `" . DB_PREFIX . "log`.`ts` AS `lts`,
               `" . DB_PREFIX . "log`.`userID` AS `personID`,
               `" . DB_PREFIX . "portal`.`name` AS `portalName`,
               `" . DB_PREFIX . "log`.`ip` AS `entryLogIP`,
               `" . DB_PREFIX . "users`.`name` AS `userName`
               FROM `" . DB_PREFIX . "log`
               LEFT JOIN `" . DB_PREFIX . "users`
               ON `" . DB_PREFIX . "log`.`userID` = `" . DB_PREFIX . "users`.`id`
               LEFT JOIN `" . DB_PREFIX . "portal`
               ON `" . DB_PREFIX . "log`.`userID` = `" . DB_PREFIX . "portal`.`id`
               " . (!empty($where) ? mswSQL(implode(mswNL(), $where)) : '') . "
               ORDER BY `" . DB_PREFIX . "log`.`id` DESC
               ", __file__, __line__);
      while ($LOG = mswSQL_fetchobj($q_log)) {
        $data .= mswCleanCSV(($LOG->type == 'acc' ? $LOG->portalName : $LOG->userName), $sepr) . $sepr . ($LOG->type == 'user' ? $msg_log15 : $msg_log14) . $sepr . mswCleanCSV($LOG->entryLogIP, $sepr) . $sepr . mswCleanCSV($this->datetime->mswDateTimeDisplay($LOG->lts, $this->settings->dateformat), $sepr) . $sepr . mswCleanCSV($this->datetime->mswDateTimeDisplay($LOG->lts, $this->settings->timeformat), $sepr) . mswNL();
      }
      // Save file to server and download..
      $dl->write($file, rtrim($data));
      if (file_exists($file)) {
        return $file;
      }
      return 'none';
    }
  }

  public function clearLogFile() {
    mswSQL_truncate(array('log'));
  }

  public function deleteLogs() {
    if (!empty($_POST['del'])) {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "log`
      WHERE `id` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
	    ", __file__, __line__);
      return mswSQL_affrows();
    }
  }

  public function updateBackupEmails() {
    $_POST = mswMDAM('mswSQL', $_POST);
    mswSQL_query("UPDATE `" . DB_PREFIX . "settings` SET
    `backupEmails` = '{$_POST['emails']}'
    ", __file__, __line__);
  }

  public function updateSettings() {
    $_POST                     = mswMDAM('mswSQL', $_POST);
    // Defaults if not set..
    $_POST['attachment']       = (isset($_POST['attachment']) ? 'yes' : 'no');
    $_POST['rename']           = (isset($_POST['rename']) ? 'yes' : 'no');
    $_POST['weekStart']        = (isset($_POST['weekStart']) && in_array($_POST['weekStart'], array(
      'sun',
      'mon'
    )) ? $_POST['weekStart'] : 'sun');
    $_POST['enableBBCode']     = (isset($_POST['enableBBCode']) ? 'yes' : 'no');
    $_POST['disputes']         = (isset($_POST['disputes']) ? 'yes' : 'no');
    $_POST['multiplevotes']    = (isset($_POST['multiplevotes']) ? 'yes' : 'no');
    $_POST['enableVotes']      = (isset($_POST['enableVotes']) ? 'yes' : 'no');
    $_POST['sysstatus']        = (isset($_POST['sysstatus']) ? 'yes' : 'no');
    $_POST['autoenable']       = ($_POST['autoenable'] ? $this->datetime->mswDatePickerFormat($_POST['autoenable']) : '1000-01-01');
    $_POST['kbase']            = (isset($_POST['kbase']) ? 'yes' : 'no');
    $_POST['faqHistory']       = (isset($_POST['faqHistory']) ? 'yes' : 'no');
    $_POST['scriptpath']       = systemSettings::filterInstallationPath($_POST['scriptpath']);
    $_POST['protocol']         = (isset($_POST['protocol']) && in_array($_POST['protocol'], array(
      'http',
      'https'
    )) ? $_POST['protocol'] : 'http');
    $_POST['attachpath']       = systemSettings::filterInstallationPath($_POST['attachpath']);
    $_POST['attachhref']       = systemSettings::filterInstallationPath($_POST['attachhref']);
    $_POST['aprotocol']        = (isset($_POST['aprotocol']) && in_array($_POST['aprotocol'], array(
      'http',
      'https'
    )) ? $_POST['aprotocol'] : 'http');
    $_POST['attachpathfaq']    = systemSettings::filterInstallationPath($_POST['attachpathfaq']);
    $_POST['attachhreffaq']    = systemSettings::filterInstallationPath($_POST['attachhreffaq']);
    $_POST['fprotocol']        = (isset($_POST['fprotocol']) && in_array($_POST['fprotocol'], array(
      'http',
      'https'
    )) ? $_POST['fprotocol'] : 'http');
    $_POST['imap_param']       = ($_POST['imap_param'] ? $_POST['imap_param'] : 'pipe');
    $_POST['renamefaq']        = (isset($_POST['renamefaq']) ? 'yes' : 'no');
    $_POST['smtp_debug']       = (isset($_POST['smtp_debug']) ? 'yes' : 'no');
    $_POST['smtp_html']        = (isset($_POST['smtp_html']) ? 'yes' : 'no');
    $_POST['createPref']       = (isset($_POST['createPref']) ? 'yes' : 'no');
    $_POST['createAcc']        = (isset($_POST['createAcc']) ? 'yes' : 'no');
    $_POST['ticketHistory']    = (isset($_POST['ticketHistory']) ? 'yes' : 'no');
    $_POST['closenotify']      = (isset($_POST['closenotify']) ? 'yes' : 'no');
    $_POST['accProfNotify']    = (isset($_POST['accProfNotify']) ? 'yes' : 'no');
    $_POST['newAccNotify']     = (isset($_POST['newAccNotify']) ? 'yes' : 'no');
    $_POST['enableLog']        = (isset($_POST['enableLog']) ? 'yes' : 'no');
    $_POST['enableMail']       = (isset($_POST['enableMail']) ? 'yes' : 'no');
    $_POST['imap_debug']       = (isset($_POST['imap_debug']) ? 'yes' : 'no');
    $_POST['imap_attach']      = (isset($_POST['imap_attach']) ? 'yes' : 'no');
    $_POST['imap_notify']      = (isset($_POST['imap_notify']) ? 'yes' : 'no');
    $_POST['imap_open']        = (isset($_POST['imap_open']) ? 'yes' : 'no');
    $_POST['apiLog']           = (isset($_POST['apiLog']) ? 'yes' : 'no');
    $_POST['disputeAdminStop'] = (isset($_POST['disputeAdminStop']) ? 'yes' : 'no');
    $_POST['faqcounts']        = (isset($_POST['faqcounts']) ? 'yes' : 'no');
    $_POST['closeadmin']       = (isset($_POST['closeadmin']) ? 'yes' : 'no');
    $_POST['adminlock']        = (isset($_POST['adminlock']) ? 'yes' : 'no');
    $_POST['imap_clean']       = (isset($_POST['imap_clean']) ? 'yes' : 'no');
    $_POST['tawk_home']        = (isset($_POST['tawk_home']) ? 'yes' : 'no');
    $_POST['rantick']          = (isset($_POST['rantick']) ? 'yes' : 'no');
    $_POST['timetrack']        = (isset($_POST['timetrack']) ? 'yes' : 'no');
    $_POST['selfsign']         = (isset($_POST['selfsign']) ? 'yes' : 'no');
    $_POST['openlimit']        = (isset($_POST['openlimit']) ? 'yes' : 'no');
    $_POST['visclose']         = (isset($_POST['visclose']) ? 'yes' : 'no');
    $_POST['imapspamcloseacc'] = (isset($_POST['imapspamcloseacc']) ? 'yes' : 'no');
    // Check max size against server limit..
    if ($_POST['maxsize'] > $this->upload->getMaxSize()) {
      $_POST['maxsize'] = $this->upload->getMaxSize();
    }
    $_POST['maxsize']          = (isset($_POST['maxsize']) ? (int) $_POST['maxsize'] : '0');
    $_POST['popquestions']     = (isset($_POST['popquestions']) ? (int) $_POST['popquestions'] : '10');
    $_POST['quePerPage']       = (isset($_POST['quePerPage']) ? (int) $_POST['quePerPage'] : '10');
    $_POST['cookiedays']       = (isset($_POST['cookiedays']) ? (int) $_POST['cookiedays'] : '60');
    $_POST['attachboxes']      = (isset($_POST['attachboxes']) ? (int) $_POST['attachboxes'] : '1');
    $_POST['autoClose']        = (isset($_POST['autoClose']) ? (int) $_POST['autoClose'] : '0');
    $_POST['smtp_port']        = (isset($_POST['smtp_port']) ? (int) $_POST['smtp_port'] : '25');
    $_POST['loginLimit']       = (isset($_POST['loginLimit']) ? (int) $_POST['loginLimit'] : '0');
    $_POST['banTime']          = (isset($_POST['banTime']) ? (int) $_POST['banTime'] : '25');
    $_POST['minPassValue']     = (isset($_POST['minPassValue']) ? (int) $_POST['minPassValue'] : '8');
    $_POST['minTickDigits']    = (isset($_POST['minTickDigits']) ? (int) $_POST['minTickDigits'] : '5');
    $_POST['imap_timeout']     = (isset($_POST['imap_timeout']) ? (int) $_POST['imap_timeout'] : '0');
    $_POST['imap_memory']      = (isset($_POST['imap_memory']) ? (int) $_POST['imap_memory'] : '0');
    $_POST['defdept']          = (isset($_POST['defdept']) ? (int) $_POST['defdept'] : '0');
    $_POST['defprty']          = (isset($_POST['defprty']) ? $_POST['defprty'] : '');
    $_POST['autospam']         = (isset($_POST['autospam']) ? (int) $_POST['autospam'] : '0');
    $_POST['accautodel']       = (isset($_POST['accautodel']) ? (int) $_POST['accautodel'] : '0');
    $_POST['mail']             = (isset($_POST['mail']) && in_array($_POST['mail'], array(
      'smtp',
      'mail'
    )) ? $_POST['mail'] : 'smtp');
    if (!isset($_POST['afolder']) || $_POST['afolder'] == '') {
      $_POST['afolder'] = 'admin';
    }
    // Restrictions..
    if (LICENCE_VER == 'locked') {
      $_POST['attachboxes']  = RESTR_ATTACH;
      $_POST['adminFooter']  = 'To add your own footer code, go to &quot;Settings &amp; Tools > Other Options > Edit Footers&quot;';
      $_POST['publicFooter'] = 'To add your own footer code, go to &quot;Settings &amp; Tools > Other Options > Edit Footers&quot;';
    }
    // Serialized data..
    $langSets = (!empty($_POST['templateSet']) ? serialize($_POST['templateSet']) : '');
    if ($_POST['defKeepLogs']['user'] == '') {
      $_POST['defKeepLogs']['user'] = '0';
    }
    if ($_POST['defKeepLogs']['acc'] == '') {
      $_POST['defKeepLogs']['acc'] = '0';
    }
    $defLog   = (!empty($_POST['defKeepLogs']) ? serialize($_POST['defKeepLogs']) : '');
    $handlers = (!empty($_POST['apiHandlers']) ? mswSQL(implode(',', $_POST['apiHandlers'])) : '');
    $wwrap    = (!empty($_POST['wordwrap']) ? serialize($_POST['wordwrap']) : '');
    $menu     = array();
    if (isset($_POST['applyMenuChanges']) && !empty($_POST['navkey'])) {
      // Determine the order..
      $menuord = array();
      foreach($_POST['navkey'] AS $nav_l) {
        $menuord[$_POST['navorder'][$nav_l]] = $nav_l;
      }
      if (!empty($menuord)) {
        ksort($menuord);
        foreach($menuord AS $k => $v) {
          $menu[$v]['en'] = (isset($_POST['navstate'][$v]) ? $_POST['navstate'][$v] : 'yes');
        }
      }
    }
    mswSQL_query("UPDATE IGNORE `" . DB_PREFIX . "settings` SET
    `website`              = '{$_POST['website']}',
    `email`                = '{$_POST['email']}',
    `replyto`              = '{$_POST['replyto']}',
    `scriptpath`           = '" . $_POST['protocol'] . '://' . $_POST['scriptpath'] . "',
    `attachpath`           = '{$_POST['attachpath']}',
    `attachhref`           = '" . $_POST['aprotocol'] . '://' . $_POST['attachhref'] . "',
    `attachpathfaq`        = '{$_POST['attachpathfaq']}',
    `attachhreffaq`        = '" . $_POST['fprotocol'] . '://' . $_POST['attachhreffaq'] . "',
    `language`             = '{$_POST['language']}',
    `langSets`             = '" . mswSQL($langSets) . "',
    `dateformat`           = '{$_POST['dateformat']}',
    `timeformat`           = '{$_POST['timeformat']}',
    `timezone`             = '{$_POST['timezone']}',
    `weekStart`            = '{$_POST['weekStart']}',
    `jsDateFormat`         = '{$_POST['jsDateFormat']}',
    `kbase`                = '{$_POST['kbase']}',
    `faqHistory`           = '{$_POST['faqHistory']}',
    `enableVotes`          = '{$_POST['enableVotes']}',
    `multiplevotes`        = '{$_POST['multiplevotes']}',
    `popquestions`         = '{$_POST['popquestions']}',
    `quePerPage`           = '{$_POST['quePerPage']}',
    `cookiedays`           = '{$_POST['cookiedays']}',
    `renamefaq`            = '{$_POST['renamefaq']}',
    `attachment`           = '{$_POST['attachment']}',
    `rename`               = '{$_POST['rename']}',
    `attachboxes`          = '{$_POST['attachboxes']}',
    `filetypes`            = '{$_POST['filetypes']}',
    `maxsize`              = '{$_POST['maxsize']}',
    `enableBBCode`         = '{$_POST['enableBBCode']}',
    `afolder`              = '{$_POST['afolder']}',
    `autoClose`            = '{$_POST['autoClose']}',
    `smtp_host`            = '{$_POST['smtp_host']}',
    `smtp_user`            = '{$_POST['smtp_user']}',
    `smtp_pass`            = '{$_POST['smtp_pass']}',
    `smtp_port`            = '{$_POST['smtp_port']}',
    `smtp_security`        = '{$_POST['smtp_security']}',
    `smtp_debug`           = '{$_POST['smtp_debug']}',
    `smtp_html`            = '{$_POST['smtp_html']}',
    `adminFooter`          = '{$_POST['adminFooter']}',
    `publicFooter`         = '{$_POST['publicFooter']}',
    `apiKey`               = '{$_POST['apiKey']}',
    `apiLog`               = '{$_POST['apiLog']}',
    `apiHandlers`          = '{$handlers}',
    `sysstatus`            = '{$_POST['sysstatus']}',
    `autoenable`           = '{$_POST['autoenable']}',
    `disputes`             = '{$_POST['disputes']}',
    `offlineReason`        = '{$_POST['offlineReason']}',
    `createPref`           = '{$_POST['createPref']}',
    `createAcc`            = '{$_POST['createAcc']}',
    `loginLimit`           = '{$_POST['loginLimit']}',
    `banTime`              = '{$_POST['banTime']}',
    `ticketHistory`        = '{$_POST['ticketHistory']}',
    `closenotify`          = '{$_POST['closenotify']}',
    `accProfNotify`        = '{$_POST['accProfNotify']}',
    `minPassValue`         = '{$_POST['minPassValue']}',
    `newAccNotify`         = '{$_POST['newAccNotify']}',
    `enableLog`            = '{$_POST['enableLog']}',
    `defKeepLogs`          = '" . mswSQL($defLog) . "',
    `minTickDigits`        = '{$_POST['minTickDigits']}',
    `enableMail`           = '{$_POST['enableMail']}',
    `imap_debug`           = '{$_POST['imap_debug']}',
    `imap_param`           = '{$_POST['imap_param']}',
    `imap_memory`          = '{$_POST['imap_memory']}',
    `imap_timeout`         = '{$_POST['imap_timeout']}',
    `imap_attach`          = '{$_POST['imap_attach']}',
    `imap_notify`          = '{$_POST['imap_notify']}',
    `imap_open`            = '{$_POST['imap_open']}',
    `disputeAdminStop`     = '{$_POST['disputeAdminStop']}',
    `faqcounts`            = '{$_POST['faqcounts']}',
    `closeadmin`           = '{$_POST['closeadmin']}',
    `adminlock`            = '{$_POST['adminlock']}',
    `imap_clean`           = '{$_POST['imap_clean']}',
    `tawk`                 = '{$_POST['tawk']}',
    `tawk_home`            = '{$_POST['tawk_home']}',
    `defdept`              = '{$_POST['defdept']}',
    `defprty`              = '{$_POST['defprty']}',
    `rantick`              = '{$_POST['rantick']}',
    `autospam`             = '{$_POST['autospam']}',
    `wordwrap`             = '" . mswSQL($wwrap) . "',
    `timetrack`            = '{$_POST['timetrack']}',
    `selfsign`             = '{$_POST['selfsign']}',
    `openlimit`            = '{$_POST['openlimit']}',
    `mail`                 = '{$_POST['mail']}',
    `accautodel`           = '{$_POST['accautodel']}',
    `visclose`             = '{$_POST['visclose']}',
    `imapspamcloseacc`     = '{$_POST['imapspamcloseacc']}',
    `navmenu`              = '" . (!empty($menu) ? mswSQL(serialize($menu)) : '') . "',
    `spam_score_header`    = '{$_POST['spam_score_header']}',
    `spam_score_value`     = '{$_POST['spam_score_value']}'
    ", __file__, __line__);
    $apiKeys = array_keys($_POST['api']);
    $added = array();
    foreach ($apiKeys AS $k) {
      foreach ($_POST['api'][$k] AS $apiK => $apiV) {
        $added[] = "'" . $apiK . "'";
        $Q = mswSQL_query("SELECT `id`
             FROM `" . DB_PREFIX . "social`
             WHERE `desc` = '{$k}'
             AND `param` = '{$apiK}'
             LIMIT 1
             ", __file__, __line__);
        $PAR = mswSQL_fetchobj($Q);
        if (isset($PAR->id)) {
          mswSQL_query("UPDATE `" . DB_PREFIX . "social` SET
          `value`    = '" . mswSQL($apiV) . "'
          WHERE `id` = '{$PAR->id}'
          ");
        } else {
          mswSQL_query("INSERT INTO `" . DB_PREFIX . "social` (
          `desc`,
          `param`,
          `value`
          ) VALUES (
          '" . mswSQL($k) . "',
          '" . mswSQL($apiK) . "',
          '" . mswSQL($apiV) . "'
          )", __file__, __line__);
        }
      }
    }
    // Clear bans..
    if ($_POST['loginLimit'] == 0) {
      mswSQL_truncate(array('ban'), true);
    }
    if (!empty($added)) {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "social` WHERE `param` NOT IN(" . implode(',', $added) . ")", __file__, __line__);
    }
  }

  private function filterInstallationPath($path) {
    if (substr($path, -1) == '/') {
      $path = substr_replace($path, '', -1);
    }
    return $path;
  }
  
  // Reset menu
  public function resetMenu() {
    mswSQL_query("UPDATE `" . DB_PREFIX . "settings` SET
    `navmenu` = ''
    ", __file__, __line__);
  }

  // Check for new version..
  public function mswSoftwareVersionCheck() {
    $url = 'https://www.maianscriptworld.co.uk/version-check.php?id=' . SCRIPT_ID;
    $str = '';
    if (function_exists('curl_init')) {
      $ch = @curl_init();
      @curl_setopt($ch, CURLOPT_URL, $url);
      @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $result = @curl_exec($ch);
      @curl_close($ch);
      if ($result) {
        if ($result != $this->settings->softwareVersion) {
          $str = 'Installed Version: <b>' . $this->settings->softwareVersion . '</b>' . mswNL();
          $str .= 'Current Version: <b>' . $result . '</b>';
          $str .= '<hr><i class="fa fa-warning fa-fw ms_red"></i> Your version is out of date.<hr>';
          $str .= 'Download new version at:' . mswNL();
          $str .= '<a href="https://www.' . SCRIPT_URL . '/download.html" onclick="window.open(this);return false">www.' . SCRIPT_URL . '</a>';
        } else {
          $str = 'Current Version: ' . $this->settings->softwareVersion . mswNL() . mswNL() . '<i class="fa fa-check fa-fw"></i> You are currently using the latest version';
        }
      }
    } else {
      if (@ini_get('allow_url_fopen') == '1') {
        $result = @mswTmp($url, 'ok');
        if ($result) {
          if ($result != $this->settings->softwareVersion) {
            $str = 'Installed Version: <b>' . $this->settings->softwareVersion . '</b>' . mswNL();
            $str .= 'Current Version: <b>' . $result . '</b>';
            $str .= '<hr><i class="fa fa-warning fa-fw ms_red"></i> Your version is out of date.<hr>';
            $str .= 'Download new version at:' . mswNL();
            $str .= '<a href="https://www.' . SCRIPT_URL . '/download.html" onclick="window.open(this);return false">www.' . SCRIPT_URL . '</a>';
          } else {
            $str = 'Current Version: ' . $this->settings->softwareVersion . mswNL() . mswNL() . '<i class="fa fa-check fa-fw"></i> You are currently using the latest version';
          }
        }
      }
    }
    // Nothing?
    if ($str == '') {
      $str = 'Server check functions not available.' . mswNL() . mswNL();
      $str .= 'Please visit <a href="https://www.' . SCRIPT_URL . '/download.html" onclick="window.open(this);return false">www.' . SCRIPT_URL . '</a> to check for updates';
    }
    return $str;
  }

}

?>