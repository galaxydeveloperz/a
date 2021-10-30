<?php

/* CLASS FILE
----------------------------------*/

class accountSystem {

  public $settings;
  public $ssn;

  public function updateIP($id) {
    mswSQL_query("UPDATE `" . DB_PREFIX . "portal` SET
    `ip`       = '" . mswSQL(mswIP()) . "',
    `system1`  = '',
    `system2`  = ''
    WHERE `id` = '{$id}'
    ", __file__, __line__);
    mswSQL_query("UPDATE `" . DB_PREFIX . "replies` SET
    `ipAddresses` = '" . mswSQL(mswIP()) . "'
    WHERE `replyUser` = '{$id}'
    ", __file__, __line__);
  }

  public function clearSystemFlags($id) {
    mswSQL_query("UPDATE `" . DB_PREFIX . "portal` SET
    `system1`  = '',
    `system2`  = ''
    WHERE `id` = '{$id}'
    ", __file__, __line__);
  }

  public function log($user) {
    $defLogs = ($this->settings->defKeepLogs ? unserialize($this->settings->defKeepLogs) : array());
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "log` (
    `ts`,`userID`,`ip`,`type`
    ) VALUES (
    UNIX_TIMESTAMP(),'{$user}','" . mswSQL(mswIP()) . "','acc'
    )", __file__, __line__);
    // Clear previous..
    if (isset($defLogs['acc']) && $defLogs['acc'] > 0) {
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "log` WHERE `userID` = '{$user}' AND `id` <
	    (SELECT min(`id`) FROM
      (SELECT `id` FROM `" . DB_PREFIX . "log`
	    WHERE `userID` = '{$user}'
	    AND `type`     = 'acc'
	    ORDER BY `id` DESC LIMIT " . $defLogs['acc'] . "
	    ) AS `" . DB_PREFIX . "log`)", __file__, __line__);
    }
  }

  public function activate($data = array()) {
    mswSQL_query("UPDATE `" . DB_PREFIX . "portal` SET
    `userPass` = '" . mswPassHash(array('type' => 'add', 'pass' => $data['pass'])) . "',
    `verified` = 'yes',
    `enabled`  = 'yes'
    WHERE `id` = '{$data['id']}'
    ", __file__, __line__);
    return mswSQL_affrows();
  }

  public function add($add = array()) {
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "portal` (
    `name`,
    `ts`,
    `email`,
    `userPass`,
    `enabled`,
    `verified`,
    `timezone`,
    `ip`,
    `notes`,
    `system1`,
    `system2`,
    `language`,
    `enableLog`
    ) VALUES (
    '" . mswSQL($add['name']) . "',
    UNIX_TIMESTAMP(),
    '" . mswSQL(strtolower($add['email'])) . "',
    '" . mswPassHash(array('type' => 'add', 'pass' => $add['pass'])) . "',
    '{$add['enabled']}',
    '{$add['verified']}',
    '" . mswSQL($add['timezone']) . "',
    '" . mswSQL($add['ip']) . "',
    '" . mswSQL($add['notes']) . "',
    '" . (isset($add['system1']) ? mswSQL($add['system1']) : '') . "',
    '" . (isset($add['system2']) ? mswSQL($add['system2']) : '') . "',
    '" . mswSQL($add['language']) . "',
    '{$this->settings->enableLog}'
    )", __file__, __line__);
    $id = mswSQL_insert_id();
    return $id;
  }

  public function ban() {
    $q = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "ban`
         WHERE `type` = 'login'
	       AND `ip`     = '" . mswSQL(mswIP()) . "'
         LIMIT 1
         ", __file__, __line__);
    $B = mswSQL_fetchobj($q);
    // If entry found, increment count, else create new entry..
    if (isset($B->id)) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "ban` SET
      `count`      = (`count`+1)
      WHERE `type` = 'login'
      AND `ip`     = '" . mswSQL(mswIP()) . "'
      LIMIT 1
      ", __file__, __line__);
    } else {
      mswSQL_query("INSERT INTO `" . DB_PREFIX . "ban` (
      `type`,
      `ip`,
      `count`,
      `banstamp`
      ) VALUES (
      'login',
      '" . mswSQL(mswIP()) . "',
      '1',
      UNIX_TIMESTAMP()
      )", __file__, __line__);
    }
  }

  public function clearban() {
    mswSQL_query("DELETE FROM `" . DB_PREFIX . "ban`
    WHERE `type` = 'login'
    AND `ip`     = '" . mswSQL(mswIP()) . "'
    ", __file__, __line__);
  }

  public function checkban($s, $dt) {
    $q = mswSQL_query("SELECT `id`,`banstamp` FROM `" . DB_PREFIX . "ban`
         WHERE `type` = 'login'
	       AND `ip`     = '" . mswSQL(mswIP()) . "'
	       AND `count`  = '{$s->loginLimit}'
         LIMIT 1
         ", __file__, __line__);
    $B = mswSQL_fetchobj($q);
    // If found, check ban time against current timestamp..
    if (isset($B->id)) {
      $now     = $dt->mswUTC();
      $bantime = $B->banstamp;
      $elapsed = (int) ($now - $bantime) / 60;
      if ($s->banTime > 0 && $elapsed >= $s->banTime) {
        // Remove..
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "ban`
        WHERE `type` = 'login'
	      AND `ip`     = '" . mswSQL(mswIP()) . "'
	      ", __file__, __line__);
        return 'ok';
      }
      return 'fail';
    }
    return 'ok';
  }

  public function ms_generate() {
    $pass = '';
    // Check min password isn`t zero by mistake..
    // If it is, set a default..
    if ($this->settings->minPassValue == 0) {
      $this->settings->minPassValue = 8;
    }
    $arr1 = range('A','Z');
    $arr2 = range('a','z');
    $arr3 = range(0, 9);
    $arr4 = array('[',']','&','*','(',')','#','!','%');
    $sec = array_merge($arr1, $arr2, $arr3, $arr4);
    for ($i = 0; $i < count($sec); $i++) {
      $rand = rand(0, (count($sec) - 1));
      $char = $sec[$rand];
      $pass .= $char;
      if ($this->settings->minPassValue == ($i + 1)) {
        return $pass;
      }
    }
    return $pass;
  }

  public function ms_user() {
    $q = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "portal`
         WHERE LOWER(`email`)  = '" . strtolower(MS_PERMISSIONS) . "'
	       AND `verified` = 'yes'
         LIMIT 1
         ", __file__, __line__);
    $P = mswSQL_fetchobj($q);
    return $P;
  }

  public function ms_update($data = array()) {
    // Update portal..
    $ID = (int) $data['id'];
    mswSQL_query("UPDATE `" . DB_PREFIX . "portal` SET
    `name`      = '" . mswSQL($data['name']) . "',
    `email`     = '" . mswSQL(strtolower($data['email'])) . "',
    `userPass`  = '{$data['pass']}',
    `timezone`  = '" . mswSQL($data['timezone']) . "',
    `language`  = '" . mswSQL($data['language']) . "',
    `system2`   = ''
    WHERE `id`  = '{$ID}'
    ", __file__, __line__);
    // Update login so we don`t log visitor out..
    if (!isset($data['nologin'])) {
      $this->ssn->delete(array('_msw_support'));
      $this->ssn->set(array('_msw_support' => strtolower($data['email'])));
    }
    return mswSQL_affrows();
  }

  public function ms_password($email, $password = '') {
    $pass = ($password ? $password : accountSystem::ms_generate());
    mswSQL_query("UPDATE `" . DB_PREFIX . "portal` SET
    `userPass` = '" . mswPassHash(array('type' => 'add', 'pass' => $pass)) . "',
    `system2` = 'forcepasschange'
    WHERE LOWER(`email`)  = '" . strtolower($email) . "'
    ", __file__, __line__);
    return $pass;
  }

  public function close($userID) {
    // Get all tickets related to the users that are going to be deleted..
      $tickets = array();
      $q       = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "tickets`
                 WHERE `visitorID` = '{$userID}'
		             ORDER BY `id`
		             ", __file__, __line__);
      while ($T = mswSQL_fetchobj($q)) {
        $tickets[] = $T->id;
      }
      // If there are tickets, delete all information..
      if (!empty($tickets)) {
        $tickDel = implode(',', $tickets);
        $qA = mswSQL_query("SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
	            WHERE `ticketID` IN({$tickDel})
			        ", __file__, __line__);
        while ($A = mswSQL_fetchobj($qA)) {
          $split  = explode('-', $A->addDate);
          $folder = '';
          // Check for newer folder structure..
          if (@file_exists($this->settings->attachpath . '/' . $split[0] . '/' . $split[1] . '/' . $A->fileName)) {
            $folder = $split[0] . '/' . $split[1] . '/';
          }
          if (is_writeable($this->settings->attachpath) && @file_exists($this->settings->attachpath . '/' . $folder . $A->fileName)) {
            @unlink($this->settings->attachpath . '/' . $folder . $A->fileName);
          }
        }
        // Delete all attachment data..
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "attachments` WHERE `ticketID` IN({$tickDel})", __file__, __line__);
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "replies` WHERE `ticketID` IN({$tickDel})", __file__, __line__);
        $r = mswSQL_affrows();
        // Delete all tickets..
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "tickets` WHERE `id` IN({$tickDel})", __file__, __line__);
        // Delete all custom data..
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "ticketfields` WHERE `ticketID` IN({$tickDel})", __file__, __line__);
        // Delete disputes..
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "disputes` WHERE `ticketID` IN({$tickDel})", __file__, __line__);
        // Delete history..
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "tickethistory` WHERE `ticketID` IN({$tickDel})", __file__, __line__);
      }
      // Users info..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "portal`
      WHERE `id` = '{$userID}'
      ", __file__, __line__);
      // Delete disputes..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "disputes` WHERE `visitorID` = '{$userID}'", __file__, __line__);
      // Log entries..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "log`
      WHERE `userID` = '{$userID}'
	    AND `type`      = 'acc'
      ", __file__, __line__);
      mswSQL_truncate(array('tickets','attachments','replies','cusfields','ticketfields','disputes','tickethistory','portal'));
  }

}

?>