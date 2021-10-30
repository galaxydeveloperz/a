<?php

/* CLASS FILE
----------------------------------*/

class supportTickets {

  public $settings;
  public $team;
  public $dt;
  public $tk_levels;
  public $tk_statuses;

  const TICKET_HISTORY_FILENAME = 'history-{ticket}-{date}.csv';
  const TICKET_EXPORT_FILENAME = 'ticket-stats-{date}.csv';

  public function deleteCF($id) {
    mswSQL_query("DELETE FROM `" . DB_PREFIX . "ticketfields` WHERE `id` = '{$id}'", __file__, __line__);
    $rows = mswSQL_affrows();
    mswSQL_truncate(array('ticketfields'));
    return $rows;
  }
  
  public function blankStatuses() {
    mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
    `ticketstatus` = 'open'
    WHERE `ticketstatus` = ''
    ", __file__, __line__);
  }

  public function locker($d = array()) {
    switch($d['action']) {
      case 'lock':
        if ($this->settings->adminlock == 'yes') {
          mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
          `lockteam` = '{$d['team']}'
          WHERE `id` = '{$d['ticket']}'
          AND `lockteam` = 0
          AND `assignedto` != 'waiting'
          AND `spamFlag` = 'no'
          ", __file__, __line__);
        } else {
          mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
          `lockteam` = '0',
          `lockrelease` = '0'
          ", __file__, __line__);
        }
        break;
      case 'unlock':
        mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
        `lockteam` = '0',
        `lockrelease` = '0'
        WHERE (`lockteam` = '{$d['team']}') OR (`assignedto` = 'waiting' OR `spamFlag` = 'yes' OR `ticketStatus` IN('close','closed')
        )", __file__, __line__);
        break;
      case 'unlock-all':
        mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
        `lockteam` = '0',
        `lockrelease` = '0'
        ", __file__, __line__);
        break;
      case 'release':
        mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
        `lockteam` = '0',
        `lockrelease` = '0'
        WHERE `id` = '{$d['id']}'
        ", __file__, __line__);
        break;
    }
  }

  public function exportTicketStats($dt, $dl) {
    global $msg_search26, $msg_search27, $msg_script4, $msg_script5, $msadminlang_reports_3_7;
    if (!is_writeable(PATH . 'export')) {
      return 'err';
    }
    $sepr = ',';
    $file = PATH . 'export/' . str_replace(array(
      '{date}'
    ), array(
      $dt->mswDateTimeDisplay(strtotime(date('Ymd H:i:s')), 'dmY-his')
    ), supportTickets::TICKET_EXPORT_FILENAME);
    if ($this->settings->disputes == 'no') {
      unset($msg_search26[15]);
    }
    $string = implode(',', $msg_search26);
    if ($this->settings->timetrack == 'yes') {
      $string .= $sepr . $msadminlang_reports_3_7[0] . mswNL();
    } else {
      $string .= mswNL();
    }
    if (!empty($_POST['del'])) {
      $q = mswSQL_query("SELECT *,
           `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
           `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
           `" . DB_PREFIX . "portal`.`email` AS `ticketMail`,
           `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
           `" . DB_PREFIX . "departments`.`name` AS `deptName`
           FROM `" . DB_PREFIX . "tickets`
           LEFT JOIN `" . DB_PREFIX . "departments`
           ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
           LEFT JOIN `" . DB_PREFIX . "portal`
           ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
           WHERE `" . DB_PREFIX . "tickets`.`id` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
           " . (isset($_POST['orderbyexp']) ? mswSQL($_POST['orderbyexp']) : 'ORDER BY `' . DB_PREFIX . 'tickets`.`id`'), __file__, __line__);
      while ($T = mswSQL_fetchobj($q)) {
        // Ticket No..
        $string .= mswCleanCSV(mswTicketNumber($T->ticketID, $this->settings->minTickDigits, $T->tickno), $sepr) . $sepr;
        // Created By..
        $string .= mswCleanCSV(mswCD($T->ticketName), $sepr) . $sepr;
        // Email..
        $string .= mswCleanCSV($T->ticketMail, $sepr) . $sepr;
        // Created On..
        $string .= mswCleanCSV($dt->mswDateTimeDisplay($T->ticketStamp, $this->settings->dateformat) . ' ' . $dt->mswDateTimeDisplay($T->ticketStamp, $this->settings->timeformat), $sepr) . $sepr;
        // First Reply On..
        $first = supportTickets::getLastReplyExportInfo($T->ticketID, 'first');
        $last  = supportTickets::getLastReplyExportInfo($T->ticketID, 'last');
        $string .= mswCleanCSV(($first[2] > 0 ? $dt->mswDateTimeDisplay($last[2], $this->settings->dateformat) . ' ' . $dt->mswDateTimeDisplay($last[2], $this->settings->timeformat) : ''), $sepr) . $sepr;
        // First Reply By..
        $string .= mswCleanCSV($first[0], $sepr) . $sepr;
        // Last Reply On..
        $string .= mswCleanCSV(($last[2] > 0 ? $dt->mswDateTimeDisplay($last[2], $this->settings->dateformat) . ' ' . $dt->mswDateTimeDisplay($last[2], $this->settings->timeformat) : ''), $sepr) . $sepr;
        // Last Reply By..
        $string .= mswCleanCSV($first[0], $sepr) . $sepr;
        // Agents Assigned..
        $assgd = supportTickets::assignedTeam($T->assignedto);
        $string .= mswCleanCSV($assgd, $sepr) . $sepr;
        // Subject..
        $string .= mswCleanCSV(mswCD($T->subject), $sepr) . $sepr;
        // Department..
        $string .= mswCleanCSV(mswCD($T->deptName), $sepr) . $sepr;
        // Ticket Status..
        $string .= mswCleanCSV((isset($this->tk_statuses[$T->ticketStatus][0]) ? $this->tk_statuses[$T->ticketStatus][0] : $T->ticketStatus), $sepr) . $sepr;
        // Priority..
        $string .= mswCleanCSV(mswCD((isset($this->tk_levels[$T->priority]) ? $this->tk_levels[$T->priority] : $T->priority)), $sepr) . $sepr;
        // Via..
        $string .= mswCleanCSV((isset($msg_search27[$T->source]) ? $msg_search27[$T->source] : 'Undefined'), $sepr) . $sepr;
        // Is Dispute..
        if ($this->settings->disputes == 'yes') {
          $string .= mswCleanCSV(($T->isDisputed == 'yes' ? $msg_script4 : $msg_script5), $sepr) . $sepr;
        }
        // Total Replies..
        $string .= mswCleanCSV(mswSQL_rows('replies WHERE `ticketID` = \'' . $T->ticketID . '\''), $sepr) . $sepr;
        // Total History Actions..
        $string .= mswCleanCSV(mswSQL_rows('tickethistory WHERE `ticketID` = \'' . $T->ticketID . '\''), $sepr);
        if ($this->settings->timetrack == 'yes') {
          $string .= $sepr . $T->worktime . mswNL();
        } else {
          $string .= mswNL();
        }
      }
      if (mswSQL_numrows($q) > 0) {
        // Save file to server and download..
        $dl->write($file, rtrim($string));
        if (file_exists($file)) {
          return $file;
        }
      }
    }
    return 'none';
  }

  public function exportStatus($t, $type) {
    global $msg_search28, $msg_search29, $msg_script17;
    if ($t->assignedto == 'waiting') {
      return $msg_public_history8;
    }
    return (isset($this->tk_statuses[$t->ticketStatus][0]) ? $this->tk_statuses[$t->ticketStatus][0] : $msg_script17);
  }

  public function getLastReplyExportInfo($id, $type) {
    global $msg_script17;
    switch ($type) {
      case 'first':
        $q = mswSQL_query("SELECT `ts`,`replyType`,`replyUser`,`disputeUser` FROM `" . DB_PREFIX . "replies`
             WHERE `ticketID` = '{$id}'
		         ORDER BY `id`
		         LIMIT 1
		         ", __file__, __line__);
        break;
      case 'last':
        $q = mswSQL_query("SELECT `ts`,`replyType`,`replyUser`,`disputeUser` FROM `" . DB_PREFIX . "replies`
             WHERE `ticketID` = '{$id}'
		         ORDER BY `id` DESC
		         LIMIT 1
		         ", __file__, __line__);
        break;
    }
    $R = mswSQL_fetchobj($q);
    if (isset($R->ts)) {
      switch ($R->replyType) {
        case 'admin':
          $A    = mswSQL_table('users', 'id', $R->replyUser);
          $info = array(
            (isset($A->name) ? mswSH($A->name) : $msg_script17),
            (isset($A->email) ? mswSH($A->email) : $msg_script17),
            $R->ts
          );
          break;
        case 'visitor':
          if ($R->disputeUser > 0) {
            $U    = mswSQL_table('portal', 'id', $R->disputeUser, '', '`name`');
            $info = array(
              (isset($U->name) ? mswSH($U->name) : $msg_script17),
              (isset($U->email) ? mswSH($U->email) : $msg_script17),
              $R->ts
            );
          } else {
            $U    = mswSQL_table('portal', 'id', $R->replyUser, '', '`name`');
            $info = array(
              (isset($U->name) ? mswSH($U->name) : $msg_script17),
              (isset($U->email) ? mswSH($U->email) : $msg_script17),
              $R->ts
            );
          }
          break;
      }
      return $info;
    }
    return array(
      '',
      '',
      0
    );
  }

  public function attachList($id) {
    $s = array();
    $q = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "attachments`
	       WHERE `ticketID` = '{$id}'
	       ORDER BY `id`
	       ", __file__, __line__);
    while ($A = mswSQL_fetchobj($q)) {
      $s[] = $this->settings->scriptpath . '/' . $this->settings->afolder . '/?dla=' . $A->id;
    }
    return (!empty($s) ? implode(mswNL(), $s) : '');
  }

  public function notSpam() {
    mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
    `spamFlag` = 'no'
    WHERE `id` IN(" . mswSQL(implode(',', $_POST['del'])) . ")
    ", __file__, __line__);
    return mswSQL_affrows();
  }

  public function searchDisputeUsers() {
    $f     = (isset($_GET['field']) && in_array($_GET['field'], array(
      'name',
      'email'
    )) ? $_GET['field'] : 'name');
    $ID    = (int) $_GET['id'];
    $acc   = array();
    $users = array();
    // Get all users currently in dispute..
    $qDU = mswSQL_query("SELECT `visitorID` FROM `" . DB_PREFIX . "disputes`
           WHERE `ticketID` = '{$ID}'
           ", __file__, __line__);
    while ($DU = mswSQL_fetchobj($qDU)) {
      $users[] = $DU->visitorID;
    }
    // Get ID of person who started ticket
    $TK = mswSQL_table('tickets', 'id', $ID);
    if (isset($TK->visitorID)) {
      $users[] = $TK->visitorID;
    }
    $q = mswSQL_query("SELECT `name`,`email` FROM `" . DB_PREFIX . "portal`
         WHERE `" . $f . "` LIKE '%" . mswSQL($_GET['term']) . "%'
		     " . (!empty($users) ? 'AND `id` NOT IN(' . mswSQL(implode(',', $users)) . ')' : '') . "
         AND `enabled` = 'yes'
		     GROUP BY `email`
	       ORDER BY `name`,`email`
		     ", __file__, __line__);
    if (mswSQL_numrows($q) > 0) {
      while ($A = mswSQL_fetchobj($q)) {
        $n          = array();
        $n['name']  = mswSH($A->name);
        $n['email'] = mswSH($A->email);
        $acc[]      = $n;
      }
    }
    return $acc;
  }

  public function deleteTicketHistory($id, $ticket) {
    // All or single entry..
    if ($id == 'all') {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "tickethistory` WHERE `ticketID` = '{$ticket}'", __file__, __line__);
    } else {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "tickethistory` WHERE `id` = '{$id}'", __file__, __line__);
    }
    mswSQL_truncate(array('tickethistory'));
  }

  public function historyLog($ticket, $action, $staff = 0) {
    if ($this->settings->ticketHistory == 'yes') {
      mswSQL_query("INSERT INTO `" . DB_PREFIX . "tickethistory` (
      `ts`,
      `ticketID`,
      `action`,
      `ip`,
      `staff`
      ) VALUES (
      UNIX_TIMESTAMP(),
      '{$ticket}',
      '" . mswSQL($action) . "',
      '" . mswSQL(mswIP()) . "',
      '{$staff}'
      )", __file__, __line__);
      $id = mswSQL_insert_id();
      return $id;
    }
    return 0;
  }

  public function exportTicketHistory($dl, $dt) {
    global $msg_viewticket113;
    if (!is_writeable(PATH . 'export')) {
      return 'err';
    }
    $id   = (isset($_GET['param']) ? (int) $_GET['param'] : '0');
    $TK   = mswSQL_table('tickets', 'id', $id);
    $sepr = ',';
    $file = PATH . 'export/' . str_replace(array(
      '{ticket}',
      '{date}'
    ), array(
      mswTicketNumber($id, $this->settings->minTickDigits, $TK->tickno),
      $dt->mswDateTimeDisplay(strtotime(date('Ymd H:i:s')), 'dmY-his')
    ), supportTickets::TICKET_HISTORY_FILENAME);
    $csv = array($msg_viewticket113);
    $qTH = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "tickethistory`
           WHERE `ticketID` = '{$id}'
           ORDER BY `ts` DESC
           ", __file__, __line__);
    while ($HIS = mswSQL_fetchobj($qTH)) {
      $csv[] = mswCleanCSV($dt->mswDateTimeDisplay($HIS->ts, $this->settings->dateformat), $sepr) . $sepr . mswCleanCSV($dt->mswDateTimeDisplay($HIS->ts, $this->settings->timeformat), $sepr) . $sepr . mswCleanCSV($HIS->action, $sepr) . $sepr . mswCleanCSV($HIS->ip, $sepr);
    }
    if (mswSQL_numrows($qTH) > 0) {
      // Save file to server and download..
      $dl->write($file, implode(mswNL(), $csv));
      if (file_exists($file)) {
        return $file;
      }
    }
    return 'none';
  }

  public function searchBatchUpdate() {
    $cnt = 0;
    $bd  = array();
    $act = array();
    if (!empty($_POST['ticket'])) {
      if ($_POST['department'] != 'no-change' || $_POST['priority'] != 'no-change' || $_POST['status'] != 'no-change') {
        // Department update..
        if ((int) $_POST['department'] > 0) {
          $bd[] = '`department` = \'' . (int) $_POST['department'] . '\'';
          mswSQL_query("UPDATE `" . DB_PREFIX . "attachments` SET
          `department`      = '{$_POST['department']}'
          WHERE `ticketID` IN(" . mswSQL(implode(',', $_POST['ticket'])) . ")
          ", __file__, __line__);
          $act[] = 'dept';
        }
        // Priority update..
        if ($_POST['priority'] != 'no-change') {
          $bd[]  = '`priority` = \'' . mswSQL($_POST['priority']) . '\'';
          $act[] = 'priority';
        }
        // Status update..
        $status = (isset($_POST['status']) && in_array($_POST['status'], array_keys($this->tk_statuses)) ? $_POST['status'] : 'xx');
        if ($status != 'xx') {
          $bd[]  = '`ticketStatus` = \'' . mswSQL($status) . '\'';
          $act[] = 'status';
        }
        // Is anything changing?
        if (!empty($bd)) {
          mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
          " . mswSQL(implode(',', $bd)) . "
          WHERE `id` IN(" . mswSQL(implode(',', $_POST['ticket'])) . ")
          ", __file__, __line__);
          $rows = mswSQL_affrows();
          // Update timestamp if something actually changed..
          if ($rows > 0) {
            mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
            `lastrevision` = UNIX_TIMESTAMP()
            WHERE `id` IN(" . mswSQL(implode(',', $_POST['ticket'])) . ")
            ", __file__, __line__);
            return array(
              $rows,
              $act
            );
          }
        }
      }
    }
    return array(
      $cnt,
      array()
    );
  }

  public function assignedTeam($assigned, $glue = ', ') {
    global $msg_script17;
    if ($assigned == 'waiting' || $assigned == '') {  
      return $msg_script17;
    }
    $u = array();
    $q = mswSQL_query("SELECT `name` FROM `" . DB_PREFIX . "users` WHERE `id` IN({$assigned}) ORDER BY `name`", __file__, __line__);
    while ($TM = mswSQL_fetchobj($q)) {
      $u[] = mswCD($TM->name);
    }
    return (!empty($u) ? implode($glue, $u) : $msg_script17);
  }

  public function ticketUserAssign($id, $users, $action) {
    $string = '';
    mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
    `assignedto` = '{$users}'
    WHERE `id`   = '{$id}'
    ", __file__, __line__);
    // Write log if there are affected rows..
    if (mswSQL_affrows() > 0) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
      `lastrevision` = UNIX_TIMESTAMP()
      WHERE `id`     = '{$id}'
      ", __file__, __line__);
      supportTickets::historyLog($id, str_replace(array(
        '{admin}',
        '{users}'
      ), array(
        $this->team->name,
        supportTickets::assignedTeam($users)
      ), $action));
    }
  }

  public function updateNotes($id) {
    mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
    `ticketNotes`  = '" . (isset($_POST['notes']) ? mswSQL($_POST['notes']) : '') . "'
    WHERE `id`     = '{$id}'
    ", __file__, __line__);
    $rows = mswSQL_affrows();
    if ($rows > 0) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
      `lastrevision` = UNIX_TIMESTAMP()
      WHERE `id`     = '{$id}'
      ", __file__, __line__);
    }
    return $rows;
  }

  public function addDisputeUser($ticket, $visitor, $priv) {
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "disputes` (
    `ticketID`,
    `visitorID`,
    `postPrivileges`
    ) VALUES (
    '{$ticket}',
    '{$visitor}',
    '{$priv}'
    )", __file__, __line__);
  }

  public function updateDisputePrivileges($id, $tk, $type, $priv) {
    switch ($type) {
      case 'ticket':
        mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
        `disPostPriv` = '{$priv}'
        WHERE `id`    = '{$tk}'
        ", __file__, __line__);
        break;
      default:
        mswSQL_query("UPDATE `" . DB_PREFIX . "disputes` SET
        `postPrivileges` = '{$priv}'
        WHERE `ticketID` = '{$tk}'
        AND `visitorID`  = '{$id}'
        ", __file__, __line__);
        break;
    }
  }

  public function removeDisputeUsersFromTicket($rem) {
    if (!empty($rem)) {
      $disID = implode(',', $rem);
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "disputes`
      WHERE `id` IN({$disID})
      ", __file__, __line__);
      mswSQL_truncate(array('disputes'));
    }
  }

  public function deleteAttachments($ids = array()) {
    if (!empty($ids)) {
      $q = mswSQL_query("SELECT `fileName`,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
	         WHERE `id` IN(" . mswSQL(implode(',', $ids)) . ")
		       ", __file__, __line__);
      while ($A = mswSQL_fetchobj($q)) {
        supportTickets::deleteAttachmentData($A);
      }
      // Delete all attachment data..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "attachments` WHERE `id` IN(" . mswSQL(implode(',', $ids)) . ")", __file__, __line__);
      mswSQL_truncate(array('attachments'));
    }
    return $ids;
  }

  public function purgeTickets() {
    $t       = 0;
    $r       = 0;
    $a       = 0;
    $sql     = '';
    $tickets = array();
    if ((int) $_POST['days1'] > 0) {
      $days = (int) $_POST['days1'];
      // Departments..
      if (!empty($_POST['dept1'])) {
        $sql = "WHERE `department` IN(" . mswSQL(implode(',', $_POST['dept1'])) . ")";
      }
      $sql .= ($sql ? ' AND ' : 'WHERE ') . 'DATEDIFF(NOW(),DATE(FROM_UNIXTIME(`ts`))) >= ' . $days;
      // Get tickets applicable for deletion..
      $q_t = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "tickets` $sql AND `ticketStatus` IN('close','closed') AND `assignedto` != 'waiting' AND `spamFlag` = 'no'", __file__, __line__);
      while ($TK = mswSQL_fetchobj($q_t)) {
        $tickets[] = $TK->id;
      }
      // Anything to delete..
      if (!empty($tickets)) {
        $_POST['ticket'] = $tickets;
        $ret             = supportTickets::deleteTickets((isset($_POST['clear']) ? 'yes' : 'no'), 'yes', true);
        return $ret;
      }
    }
    return array(
      $t,
      $r,
      $a
    );
  }

  public function purgeAttachments() {
    $count = 0;
    $sql   = '';
    if ((int) $_POST['days2'] > 0) {
      $days = (int) $_POST['days2'];
      // Departments..
      if (!empty($_POST['dept1'])) {
        $sql = "WHERE `department` IN(" . mswSQL(implode(',', $_POST['dept2'])) . ")";
      }
      $sql .= ($sql ? ' AND ' : 'WHERE ') . 'DATEDIFF(NOW(),DATE(FROM_UNIXTIME(`ts`))) >= ' . $days;
      // Delete attachment files..
      $qA = mswSQL_query("SELECT `fileName`,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments` $sql", __file__, __line__);
      while ($A = mswSQL_fetchobj($qA)) {
        supportTickets::deleteAttachmentData($A);
      }
      // Delete all attachment data..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "attachments` $sql", __file__, __line__);
      $count = mswSQL_affrows();
      mswSQL_truncate(array('attachments'));
    }
    return $count;
  }

  private function deleteAttachmentData($A) {
    $split  = explode('-', $A->addDate);
    $folder = '';
    // Check for newer folder structure..
    if (@file_exists($this->settings->attachpath . '/' . $split[0] . '/' . $split[1] . '/' . $A->fileName)) {
      $folder = $split[0] . '/' . $split[1] . '/';
    }
    if (@is_writeable($this->settings->attachpath) && @file_exists($this->settings->attachpath . '/' . $folder . $A->fileName)) {
      @unlink($this->settings->attachpath . '/' . $folder . $A->fileName);
    }
  }

  public function deleteTickets($attachments = 'yes', $ticketData = 'yes', $purgeCounts = false) {
    $pcnt = array();
    $t    = 0;
    $r    = 0;
    $a    = 0;
    if (!empty($_POST['del'])) {
      $tIDs = mswSQL(implode(',', $_POST['del']));
      // Delete attachment files..
      if ($attachments == 'yes') {
        $qA = mswSQL_query("SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
	            WHERE `ticketID` IN({$tIDs})
			        ", __file__, __line__);
        while ($A = mswSQL_fetchobj($qA)) {
          supportTickets::deleteAttachmentData($A);
        }
        // Delete all attachment data..
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "attachments` WHERE `ticketID` IN({$tIDs})", __file__, __line__);
        $a = mswSQL_affrows();
      }
      if ($ticketData == 'yes') {
        // Delete all replies..
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "replies` WHERE `ticketID` IN({$tIDs})", __file__, __line__);
        $r = mswSQL_affrows();
        // Delete all tickets..
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "tickets` WHERE `id` IN({$tIDs})", __file__, __line__);
        $t = mswSQL_affrows();
        // Delete all custom data..
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "ticketfields` WHERE `ticketID` IN({$tIDs})", __file__, __line__);
        // Delete disputes..
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "disputes` WHERE `ticketID` IN({$tIDs})", __file__, __line__);
        // Delete history..
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "tickethistory` WHERE `ticketID` IN({$tIDs})", __file__, __line__);
        // Truncate tables..
        mswSQL_truncate(array('tickets','attachments','replies','cusfields','ticketfields','disputes','tickethistory'));
      }
      if ($purgeCounts) {
        return array(
          mswNFM($t),
          mswNFM($r),
          mswNFM($a)
        );
      }
    }
    return array(
      0,
      0,
      0
    );
  }

  public function reOpenTicket() {
    $rows = 0;
    if (!empty($_POST['ticket'])) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
      `lastrevision`  = UNIX_TIMESTAMP(),
      `ticketStatus`  = 'open'
      WHERE `id`     IN(" . mswSQL(implode(',', $_POST['ticket'])) . ")
      ", __file__, __line__);
      $rows = mswSQL_affrows();
    }
    return $rows;
  }

  public function addTicketReply() {
    $tID     = (int) $_POST['ticketID'];
    $array   = array(
      'merged' => 'no',
      'ticketID' => $tID,
      'mergeID' => 0,
      'subject' => '',
      'oldrantick' => ''
    );
    $mergeID = (isset($_POST['mergeid']) ? (int) mswReverseTicketNumber($_POST['mergeid']) : '0');
    $newID   = ($mergeID > 0 ? $mergeID : $tID);
    $mergeTickNo = '';
    // Are we merging this ticket..
    if ($mergeID > 0) {
      if (mswSQL_rows('tickets WHERE `id` = \'' . $mergeID . '\' AND `isDisputed` = \'no\'') > 0) {
        // Get original ticket and convert it to a reply..
        $OTICKET = mswSQL_table('tickets', 'id', $tID);
        $mergeTickNoOld = $OTICKET->tickno;
        // Get new parent data for department..
        $MERGER  = mswSQL_table('tickets', 'id', $mergeID);
        // Check accounts are the same..
        if ($OTICKET->isDisputed == 'no' && ($OTICKET->visitorID == $MERGER->visitorID)) {
          $mergeTickNo = $MERGER->tickno;
          // Account information..
          $PORTAL  = mswSQL_table('portal', 'id', $MERGER->visitorID);
          // Add original ticket as reply..
          mswSQL_query("INSERT INTO `" . DB_PREFIX . "replies` (
          `ts`,
          `ticketID`,
          `comments`,
          `replyType`,
          `replyUser`,
          `isMerged`,
          `ipAddresses`
          ) VALUES (
          UNIX_TIMESTAMP(),
          '{$mergeID}',
          '" . mswSQL($OTICKET->comments) . "',
          'visitor',
          '{$OTICKET->visitorID}',
          'yes',
          '{$OTICKET->ipAddresses}'
          )", __file__, __line__);
          // Now remove original ticket
          mswSQL_query("DELETE FROM `" . DB_PREFIX . "tickets` WHERE `id` = '{$tID}'", __file__, __line__);
          // Move any replies attached to original ticket to new parent..
          // Update timestamp so they fall in line..
          mswSQL_query("UPDATE `" . DB_PREFIX . "replies` SET
          `ts`              = UNIX_TIMESTAMP(),
          `ticketID`        = '{$mergeID}',
          `isMerged`        = 'yes'
          WHERE `ticketID`  = '{$tID}'
          ", __file__, __line__);
          // Move attachments to new ticket id..
          mswSQL_query("UPDATE `" . DB_PREFIX . "attachments` SET
          `ticketID`        = '{$mergeID}',
          `department`      = '{$MERGER->department}'
          WHERE `ticketID`  = '{$tID}'
          ", __file__, __line__);
          // Move custom field data to new ticket..
          mswSQL_query("UPDATE `" . DB_PREFIX . "ticketfields` SET
          `ticketID`        = '{$mergeID}'
          WHERE `ticketID`  = '{$tID}'
          ", __file__, __line__);
          // Remove history for old ticket..
          mswSQL_query("DELETE FROM `" . DB_PREFIX . "tickethistory` WHERE `ticketID` = '{$tID}'", __file__, __line__);
          // Move any dispute user data to new ticket..
          mswSQL_query("UPDATE `" . DB_PREFIX . "disputes` SET
          `ticketID`        = '{$mergeID}'
          WHERE `ticketID`  = '{$tID}'
          ", __file__, __line__);
          // Overwrite array..
          $array   = array(
            'merged' => 'yes',
            'ticketID' => $mergeID,
            'subject' => $OTICKET->subject,
            'oldrantick' => $mergeTickNoOld
          );
        }
      }
    }
    // Add new reply..
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "replies` (
    `ts`,
    `ticketID`,
    `comments`,
    `replyType`,
    `replyUser`,
    `isMerged`,
    `ipAddresses`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '{$newID}',
    '" . mswSQL($_POST['comments']) . "',
    'admin',
    '{$this->team->id}',
    'no',
    '" . mswSQL(mswIP()) . "'
    )", __file__, __line__);
    $newReply = mswSQL_insert_id();
    // Custom field data..
    if (!empty($_POST['customField'])) {
      // Check to see if any checkboxes arrays are now blank..
      // If there are, create empty array to prevent ommission in loop..
      if (!empty($_POST['hiddenBoxes'])) {
        foreach ($_POST['hiddenBoxes'] AS $hb) {
          if (!isset($_POST['customField'][$hb])) {
            $_POST['customField'][$hb] = array();
          }
        }
      }
      foreach ($_POST['customField'] AS $k => $v) {
        $data = '';
        // If value is array, its checkboxes..
        if (is_array($v)) {
          if (!empty($v)) {
            $data = implode('#####', $v);
          }
        } else {
          $data = $v;
        }
        $k = (int) $k;
        // If data exists, update or add entry..
        // If blank or 'nothing-selected', delete if exists..
        if ($data != '' && $data != 'nothing-selected') {
          if (mswSQL_rows('ticketfields WHERE `ticketID`  = \'' . $newID . '\' AND `fieldID` = \'' . $k . '\' AND `replyID` = \'' . $newReply . '\'') > 0) {
            mswSQL_query("UPDATE `" . DB_PREFIX . "ticketfields` SET
            `fieldData`       = '" . mswSQL($data) . "'
            WHERE `ticketID`  = '{$newID}'
            AND `fieldID`     = '{$k}'
            AND `replyID`     = '{$newReply}'
            ", __file__, __line__);
          } else {
              mswSQL_query("INSERT INTO `" . DB_PREFIX . "ticketfields` (
            `fieldData`,`ticketID`,`fieldID`,`replyID`
            ) VALUES (
            '" . mswSQL($data) . "','{$newID}','{$k}','{$newReply}'
            )", __file__, __line__);
          }
        } else {
          mswSQL_query("DELETE FROM `" . DB_PREFIX . "ticketfields`
          WHERE `ticketID`  = '{$newID}'
          AND `fieldID`     = '{$k}'
          AND `replyID`     = '{$newReply}'
          ", __file__, __line__);
          mswSQL_truncate(array('ticketfields'));
        }
      }
    }
    // Worktime..
    $worktime = supportTickets::worktime($_POST['worktime']);
    // Update ticket status..
    $status = (isset($_POST['status']) && in_array($_POST['status'], array_keys($this->tk_statuses)) ? $_POST['status'] : 'open');
    $priority = (isset($_POST['priority']) && in_array($_POST['priority'], array_keys($this->tk_levels)) ? $_POST['priority'] : $_POST['cur_priority']);
    mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
    `lastrevision`  = UNIX_TIMESTAMP(),
    `ticketStatus`  = '{$status}',
    `priority`      = '{$priority}',
    `worktime`      = ADDTIME(`worktime`, '" . mswSQL($worktime) . "')
    WHERE `id`      = '{$newID}'
    ", __file__, __line__);
    // If specified, add reply as standard response..
    if (isset($_POST['response']) && $_POST['response']) {
      // Add response..
      $dept = (empty($_POST['dept']) ? implode(',', $_POST['deptall']) : implode(',', $_POST['dept']));
      mswSQL_query("INSERT INTO `" . DB_PREFIX . "responses` (
      `ts`,
      `title`,
      `answer`,
      `departments`
      ) VALUES (
      UNIX_TIMESTAMP(),
      '" . mswSQL($_POST['response']) . "',
      '" . mswSQL($_POST['comments']) . "',
      '" . mswSQL($dept) . "'
      )", __file__, __line__);
      // Rebuild sequence..
      include_once(PATH . 'control/classes/class.responses.php');
      $MSSTR = new standardResponses();
      $MSSTR->rebuildSequence();
    }
    $array['newreply'] = $newReply;
    return $array;
  }

  private function worktime($time) {
    if (preg_match('/^([0-9]{2,3}):([0-5][0-9]):([0-5][0-9])$/', $time)) {
    	return $time;
    }
    $t = array_map('trim', explode(':', $time));
    if (isset($t[0], $t[1], $t[2])) {
      $h = (int) $t[0];
      $m = (int) $t[1];
      $s = (int) $t[2];
      if ($s > 59) {
    	  $m = floor($s / 60) + $m;
        $s = intval($s % 60);
      }
      if ($m > 59) {
    	  $h = floor($m / 60) + $h;
        $m = intval($m % 60);
      }
      if ($h > 838) {
    	  return '838:59:59';
      }
      return $h . ':' . $m . ':' . $s;
    } else {
      return '00:00:00';
    }
  }

  public function deleteReply($RP, $TK, $ID) {
    $rows = 0;
    if (isset($RP->ticketID)) {
      if (isset($TK->id)) {
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "replies` WHERE `id` = '{$ID}'");
        $rows = mswSQL_affrows();
        // Delete attachments..
        $q    = mswSQL_query("SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
                WHERE `ticketID`  = '{$TK->id}'
                AND `replyID`     = '{$ID}'
                ORDER BY `id`
                ", __file__, __line__);
        while ($ATT = mswSQL_fetchobj($q)) {
          supportTickets::deleteAttachmentData($ATT);
        }
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "attachments` WHERE `replyID` = '{$ID}'", __file__, __line__);
        // If all replies have been deleted. ticket should be set back to start..
        if (mswSQL_rows('replies WHERE `ticketID` = \'' . $TK->id . '\'') == 0) {
          mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
          `lastrevision` = UNIX_TIMESTAMP()
          WHERE `id`     = '{$TK->id}'
          ", __file__, __line__);
        }
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "ticketfields` WHERE `replyID` = '{$ID}'", __file__, __line__);
        // Truncate tables to start at 1..
        mswSQL_truncate(array('attachments','replies','ticketfields'));
      }
    }
    return $rows;
  }

  public function updateTicketReply($action) {
    $_GET['id']        = (isset($_POST['replyID']) ? (int) $_POST['replyID'] : '0');
    $_POST['ticketID'] = (isset($_POST['ticketID']) ? (int) $_POST['ticketID'] : '0');
    mswSQL_query("UPDATE `" . DB_PREFIX . "replies` SET
    `comments`  = '" . mswSQL($_POST['comments']) . "'
    WHERE `id`  = '{$_GET['id']}'
    ", __file__, __line__);
    // Only write log if there are affected rows and something was changed..
    if (mswSQL_affrows() > 0) {
      supportTickets::historyLog($_POST['ticketID'], str_replace(array(
        '{id}',
        '{user}'
      ), array(
        $_GET['id'],
        $this->team->name
      ), $action));
    }
    // Custom field data..
    if (!empty($_POST['customField'])) {
      // Check to see if any checkboxes arrays are now blank..
      // If there are, create empty array to prevent ommission in loop..
      if (!empty($_POST['hiddenBoxes'])) {
        foreach ($_POST['hiddenBoxes'] AS $hb) {
          if (!isset($_POST['customField'][$hb])) {
            $_POST['customField'][$hb] = array();
          }
        }
      }
      foreach ($_POST['customField'] AS $k => $v) {
        $data = '';
        // If value is array, its checkboxes..
        if (is_array($v)) {
          if (!empty($v)) {
            $data = implode('#####', $v);
          }
        } else {
          $data = $v;
        }
        $k = (int) $k;
        // If data exists, update or add entry..
        // If blank or 'nothing-selected', delete if exists..
        if ($data != '' && $data != 'nothing-selected') {
          if (mswSQL_rows('ticketfields WHERE `ticketID`  = \'' . $_POST['ticketID'] . '\' AND `fieldID` = \'' . $k . '\' AND `replyID` = \'' . $_GET['id'] . '\'') > 0) {
            mswSQL_query("UPDATE `" . DB_PREFIX . "ticketfields` SET
            `fieldData`       = '" . mswSQL($data) . "'
            WHERE `ticketID`  = '{$_POST['ticketID']}'
            AND `fieldID`     = '{$k}'
            AND `replyID`     = '{$_GET['id']}'
            ", __file__, __line__);
          } else {
            mswSQL_query("INSERT INTO `" . DB_PREFIX . "ticketfields` (
            `fieldData`,`ticketID`,`fieldID`,`replyID`
            ) VALUES (
            '" . mswSQL($data) . "','{$_POST['ticketID']}','{$k}','{$_GET['id']}'
            )", __file__, __line__);
          }
        } else {
          mswSQL_query("DELETE FROM `" . DB_PREFIX . "ticketfields`
          WHERE `ticketID`  = '{$_POST['ticketID']}'
          AND `fieldID`     = '{$k}'
          AND `replyID`     = '{$_GET['id']}'
          ", __file__, __line__);
          mswSQL_truncate(array('ticketfields'));
        }
      }
    }
  }

  public function updateTicketDisputeStatus() {
    $status = (isset($_GET['odis']) ? 'yes' : 'no');
    if ((int) $_GET['id'] > 0) {
      $_GET['id'] = (int) $_GET['id'];
      mswSQL_query("UPDATE " . DB_PREFIX . "tickets SET
      `lastrevision` = UNIX_TIMESTAMP(),
      `isDisputed`   = '{$status}'
      WHERE `id`     = '{$_GET['id']}'
      ", __file__, __line__);
    }
  }

  public function batchReOpenTickets() {
    mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
    `ticketStatus` = 'open',
    `lastrevision` = UNIX_TIMESTAMP()
    WHERE `id`    IN(" . mswSQL(implode(',', $_POST['del'])) . ")
    ", __file__, __line__);
    $rows = mswSQL_affrows();
    return $rows;
  }

  public function updateTicketStatus() {
    $ID   = (int) $_GET['id'];
    $rows = 0;
    switch ($_GET['act']) {
      // Open/close/lock ticket and status change..
      case 'open':
      case 'reopen':
      case 'status-change':
        mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
        `ticketStatus` = '" . mswSQL(($_GET['act'] == 'status-change' ? $_GET['status-change-id'] : 'open')) . "',
        `spamFlag`     = 'no'
        WHERE `id`     = '{$ID}'
        ", __file__, __line__);
        $rows = mswSQL_affrows();
        break;
      case 'close':
      case 'lock':
      case 'reopen':
        $status = ($_GET['act'] == 'lock' ? 'closed' : $_GET['act']);
        mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
        `ticketStatus` = '{$status}'
        WHERE `id`     = '{$ID}'
        ", __file__, __line__);
        $rows = mswSQL_affrows();
        break;
      case 'ticket':
        mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
        `isDisputed`  = 'no',
	      `disPostPriv` = 'yes'
        WHERE `id`    = '{$ID}'
        ", __file__, __line__);
        $rows = mswSQL_affrows();
        // Remove users in this dispute..
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "disputes`
        WHERE `ticketID` = '{$ID}'
        ", __file__, __line__);
        mswSQL_truncate(array('disputes'));
        break;
      // Convert to dispute..
      case 'dispute':
        mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
        `isDisputed` = 'yes'
        WHERE `id`   = '{$ID}'
        ", __file__, __line__);
        $rows = mswSQL_affrows();
        break;
      // Move to spam..
      case 'spam':
      case 'to-spam':
        // Is the option to disable account enabled?
        if ($this->settings->imapspamcloseacc == 'yes') {
          $qA = mswSQL_query("SELECT `visitorID` FROM `" . DB_PREFIX . "tickets`
	              WHERE `id`   = '{$ID}'
	              ", __file__, __line__);
          $A = mswSQL_fetchobj($qA);
          if (isset($A->visitorID)) {
            mswSQL_query("UPDATE `" . DB_PREFIX . "portal` SET
            `enabled` = 'no'
	          WHERE `id` = '{$A->visitorID}'
	          ", __file__, __line__);
          }
        }
        mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
        `spamFlag` = 'yes'
        WHERE `id`   = '{$ID}'
        ", __file__, __line__);
        $rows = mswSQL_affrows();
        break;
    }
    // If something happened, update the timestamp..
    if ($rows > 0) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
      `lastrevision` = UNIX_TIMESTAMP()
      WHERE `id`     = '{$ID}'
      ", __file__, __line__);
    }
    return $rows;
  }

  public function updateTicket() {
    $tickID = (isset($_POST['id']) ? (int) $_POST['id'] : '0');
    $deptID = (isset($_POST['dept']) ? (int) $_POST['dept'] : '0');
    if ($tickID == '0') {
      return 0;
    }
    $isAssg = mswSQL_rows('departments WHERE `id` = \'' . $deptID . '\' AND `manual_assign` = \'yes\'');
    if (isset($_POST['worktime'])) {
      $worktime = supportTickets::worktime($_POST['worktime']);
    } else {
      $worktime = $_POST['wtime'];
    }
    $status = (isset($_POST['status']) && in_array($_POST['status'], array_keys($this->tk_statuses)) ? $_POST['status'] : 'open');
    mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
    `lastrevision` = UNIX_TIMESTAMP(),
    `department`   = '{$deptID}',
    `assignedto`   = '" . ($isAssg > 0 && !empty($_POST['assigned']) ? implode(',',$_POST['assigned']) : '') . "',
    `subject`      = '" . mswSQL($_POST['subject']) . "',
    `comments`     = '" . mswSQL($_POST['comments']) . "',
    `priority`     = '" . mswSQL($_POST['priority']) . "',
    `ticketStatus` = '" . mswSQL($status) . "',
    `worktime`     = '" . ($worktime ? mswSQL($worktime) : '00:00:00') . "'
    WHERE `id`     = '{$tickID}'
    ", __file__, __line__);
    $rows = mswSQL_affrows();
    // Custom field data..
    if (!empty($_POST['customField'])) {
      // Check to see if any checkboxes arrays are now blank..
      // If there are, create empty array to prevent ommission in loop..
      if (!empty($_POST['hiddenBoxes'])) {
        foreach ($_POST['hiddenBoxes'] AS $hb) {
          if (!isset($_POST['customField'][$hb])) {
            $_POST['customField'][$hb] = array();
          }
        }
      }
      foreach ($_POST['customField'] AS $k => $v) {
        $data = '';
        // If value is array, its checkboxes..
        if (is_array($v)) {
          if (!empty($v)) {
            $data = implode('#####', $v);
          }
        } else {
          $data = $v;
        }
        $k = (int) $k;
        // If data exists, update or add entry..
        // If blank or 'nothing-selected', delete if exists..
        if ($data != '' && $data != 'nothing-selected') {
          if (mswSQL_rows('ticketfields WHERE `ticketID`  = \'' . $tickID . '\' AND `fieldID` = \'' . $k . '\' AND `replyID` = \'0\'') > 0) {
            mswSQL_query("UPDATE `" . DB_PREFIX . "ticketfields` SET
            `fieldData`       = '" . mswSQL($data) . "'
            WHERE `ticketID`  = '{$tickID}'
            AND `fieldID`     = '{$k}'
            AND `replyID`     = '0'
            ", __file__, __line__);
            $rows = $rows + mswSQL_affrows();
          } else {
            mswSQL_query("INSERT INTO `" . DB_PREFIX . "ticketfields` (
            `fieldData`,`ticketID`,`fieldID`,`replyID`
            ) VALUES (
            '" . mswSQL($data) . "','{$tickID}','{$k}','0'
            )", __file__, __line__);
            $rows = $rows + mswSQL_affrows();
          }
        } else {
          mswSQL_query("DELETE FROM `" . DB_PREFIX . "ticketfields`
          WHERE `ticketID`  = '{$tickID}'
          AND `fieldID`     = '{$k}'
          AND `replyID`     = '0'
          ", __file__, __line__);
          $rows = $rows + mswSQL_affrows();
          mswSQL_truncate(array('ticketfields'));
        }
      }
    }
    // Do we need to delete any attachments?
    if (!empty($_POST['attachment'])) {
      $aIDs = implode(',', $_POST['attachment']);
      $qA   = mswSQL_query("SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
	            WHERE `id` IN({$aIDs})
			        ", __file__, __line__);
      while ($A = mswSQL_fetchobj($qA)) {
        supportTickets::deleteAttachmentData($A);
      }
      // Delete all attachment data..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "attachments` WHERE `id` IN({$aIDs})", __file__, __line__);
    }
    // If department was changed, update attachments..
    if ($deptID != $_POST['odeptid']) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "attachments` SET
      `department`      = '{$deptID}'
      WHERE `ticketID`  = '{$tickID}'
      ", __file__, __line__);
      // Check assignment..If department has assign disabled, we need to clear assigned values from ticket..
      if ($isAssg > 0) {
        mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
        `assignedto` = ''
        WHERE `id`   = '{$tickID}'
        ", __file__, __line__);
      }
    }
    return $rows;
  }

  public function mergeSearch($access,$txt) {
    $vs  = (isset($_GET['visitor']) ? (int) $_GET['visitor'] : '0');
    $id  = (isset($_GET['id']) ? (int) $_GET['id'] : '0');
    $fl  = mswSQL_deptfilter($access, 'WHERE');
    $ar  = array();
    $q   = mswSQL_query("SELECT `id`,`subject`,`tickno` FROM `" . DB_PREFIX . "tickets`
           $fl
		       " . ($fl ? 'AND' : 'WHERE') . " `visitorID`    = '{$vs}'
           AND `id`          != '{$id}'
           AND `assignedto`  != 'waiting'
		       AND `ticketStatus` NOT IN('close','closed')
		       AND `isDisputed`   = 'no'
		       AND `spamFlag`     = 'no'
           AND (`subject` LIKE '%" . mswSQL($_GET['term']) . "%' OR `id` = '" . (int) $_GET['term'] . "' OR `tickno` LIKE '%" . mswSQL($_GET['term']) . "%')
           ORDER BY `id`
           ", __file__, __line__);
    while ($TK = mswSQL_fetchobj($q)) {
      $ar[] = array(
        'value' => $TK->id,
        'label' => '[#' . mswTicketNumber($TK->id, $this->settings->minTickDigits, $TK->tickno) . '] ' . mswSH($TK->subject),
        'ticket' => mswTicketNumber($TK->id, $this->settings->minTickDigits, $TK->tickno),
        'txt' => $txt
      );
    }
    return $ar;
  }

  public function buttonRebuild($d = array()) {
    $tmp1 = mswTmp(PATH . 'templates/system/html/tickets/close-lock-button.htm');
    $tmp2 = mswTmp(PATH . 'templates/system/html/tickets/open-button.htm');
    $tmp3 = mswTmp(PATH . 'templates/system/html/tickets/other-button.htm');
    $f_r = array(
      '{id}' => $d['id'],
      '{action_txt}' => $d['action_txt'],
      '{txt1}' => $d['txt1'],
      '{txt2}' => $d['txt2'],
      '{txt}' => $d['txt']
    );
    $f_r2 = array(
      '{id}' => $d['id'],
      '{txt3}' => $d['txt3'],
      '{width}' => IBOX_STATUSES_WIDTH,
      '{height}' => IBOX_STATUSES_HEIGHT,
      '{type}' => $d['type']
    );
    $other_btn = strtr($tmp3, $f_r2);
    return strtr(($d['act'] == 'open' ? $tmp1 : $tmp2), $f_r) . ($d['act'] == 'open' ? $other_btn : '');
  }

  public function autoClearSpam() {
    $cleared = 0;
    if ($this->settings->autospam > 0) {
      $_POST['del'] = array();
      $q = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "tickets`
           WHERE `spamFlag` = 'yes'
           AND DATE(FROM_UNIXTIME(`ts`)) <= DATE_SUB(DATE(UTC_TIMESTAMP),INTERVAL " . (int) $this->settings->autospam . " DAY)
           ORDER BY `id`
           ", __file__, __line__);
      while ($TK = mswSQL_fetchobj($q)) {
        $_POST['del'][] = $TK->id;
      }
      if (!empty($_POST['del'])) {
        $del = supportTickets::deleteTickets('yes', 'yes', true);
        $cleared = (isset($del[0]) ? $del[0] : '0');
      }
    }
    return $cleared;
  }

  public function ticket($id) {
    if ($this->settings->rantick == 'yes') {
      $gen = mswRandTicket($id);
      mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
      `tickno` = '" . mswSQL($gen) . "'
      WHERE `id` = '{$id}'
      ", __file__, __line__);
      return $gen;
    }
    return $id;
  }

}

?>