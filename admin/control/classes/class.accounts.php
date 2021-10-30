<?php

/* CLASS FILE
----------------------------------*/

class accounts {

  public $settings;
  public $timezones;
  public $ssn;

  const ACC_EXP_FILENAME = 'accounts-{date}.csv';

  public function purgeAccounts() {
    $days = (isset($_POST['days3']) ? (int) $_POST['days3'] : '0');
    if ($days > 0) {
      $acc = array();
      $q   = mswSQL_query("SELECT `" . DB_PREFIX . "portal`.`id` AS `accID`,`" . DB_PREFIX . "portal`.`language` AS `lang`,`name`,`email` FROM `" . DB_PREFIX . "portal`
             WHERE DATEDIFF(NOW(),DATE(FROM_UNIXTIME(`ts`))) >= " . $days . "
             HAVING(SELECT count(*) FROM `" . DB_PREFIX . "tickets` WHERE `" . DB_PREFIX . "portal`.`id` = `" . DB_PREFIX . "tickets`.`visitorID` AND `spamFlag` = 'no') = 0
             ", __file__, __line__);
      while ($A = mswSQL_fetchobj($q)) {
        $acc[$A->accID] = array(
          'name' => $A->name,
          'email' => $A->email,
          'lang' => $A->lang
        );
      }
      // Delete..
      if (!empty($acc)) {
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "portal` WHERE `id` IN(" . mswSQL(implode(',', array_keys($acc))) . ")", __file__, __line__);
      }
    }
    return $acc;
  }

  public function export($head, $head2, $dl) {
    if (!is_writeable(PATH . 'export')) {
      return 'err';
    }
    $file         = PATH . 'export/' . str_replace('{date}', date('dmY-his'), accounts::ACC_EXP_FILENAME);
    $sep          = ',';
    $csv          = array();
    $SQL          = '';
    if (!isset($_POST['orderby'])) {
      $_POST['orderby'] = 'order_asc';
    }
    $orderBy = 'ORDER BY `name`';
    if (isset($_POST['orderby'])) {
      switch ($_POST['orderby']) {
        // Name (ascending)..
        case 'name_asc':
          $orderBy = 'ORDER BY `name`';
          break;
        // Name (descending)..
        case 'name_desc':
          $orderBy = 'ORDER BY `name` desc';
          break;
        // Email Address (ascending)..
        case 'email_asc':
          $orderBy = 'ORDER BY `email`';
          break;
        // Email Address (descending)..
        case 'email_desc':
          $orderBy = 'ORDER BY `email` desc';
          break;
        // Most tickets..
        case 'tickets_asc':
          $orderBy = 'ORDER BY `tickCount` desc';
          break;
        // Least tickets..
        case 'tickets_desc':
          $orderBy = 'ORDER BY `tickCount`';
          break;
      }
    }
    if (isset($_POST['filter'])) {
      switch ($_POST['filter']) {
        case 'disabled':
          $SQL = 'WHERE `enabled` = \'no\' AND `verified` = \'yes\'';
          break;
        case 'verified':
          $SQL = 'WHERE `verified` = \'no\'';
          break;
      }
    } else {
      $SQL = 'WHERE `enabled` = \'yes\'';
    }
    // Filters..
    if (isset($_POST['keys']) && $_POST['keys']) {
      $_POST['keys'] = mswSQL(strtolower($_POST['keys']));
      $filters[]    = "LOWER(`" . DB_PREFIX . "portal`.`name`) LIKE '%" . $_POST['keys'] . "%' OR LOWER(`" . DB_PREFIX . "portal`.`email`) LIKE '%" . $_POST['keys'] . "%' OR LOWER(`" . DB_PREFIX . "portal`.`notes`) LIKE '%" . $_POST['keys'] . "%'";
    }
    if (isset($_POST['from'], $_POST['to']) && $_POST['from'] && $_POST['to']) {
      $from      = $MSDT->mswDatePickerFormat($_POST['from']);
      $to        = $MSDT->mswDatePickerFormat($_POST['to']);
      $filters[] = "DATE(FROM_UNIXTIME(`ts`)) BETWEEN '{$from}' AND '{$to}'";
    }
    // Build search string..
    if (!empty($filters)) {
      for ($i = 0; $i < count($filters); $i++) {
        $SQL .= 'AND (' . $filters[$i] . ') ';
      }
    }
    // Disputes
    $sqlDisputes = '';
    if ($this->settings->disputes == 'yes') {
      $sqlDisputes = ',
       (SELECT count(*) FROM `' . DB_PREFIX . 'disputes`
        WHERE `' . DB_PREFIX . 'portal`.`id` = `' . DB_PREFIX . 'disputes`.`visitorID`
       ) AS `dispCount`';
      $head = $head2;
    }
    $q = mswSQL_query("SELECT `name`,`email`,`ip`,`timezone`,
         (SELECT count(*) FROM `" . DB_PREFIX . "tickets`
          WHERE `" . DB_PREFIX . "portal`.`id` = `" . DB_PREFIX . "tickets`.`visitorID`
          AND `spamFlag`   = 'no'
          AND `isDisputed` = 'no'
         ) AS `tickCount`
         $sqlDisputes
         FROM `" . DB_PREFIX . "portal`
         $SQL
         $orderBy
		     ", __file__, __line__);
    if (mswSQL_numrows($q) > 0) {
      while ($ACC = mswSQL_fetchobj($q)) {
        $csv[] = mswCleanCSV($ACC->name, $sep) . $sep . mswCleanCSV($ACC->email, $sep) . $sep . mswCleanCSV($ACC->ip, $sep) . $sep . mswCleanCSV($ACC->timezone, $sep) . $sep . mswCleanCSV($ACC->tickCount, $sep) . ($this->settings->disputes == 'yes' ? $sep . mswCleanCSV($ACC->dispCount, $sep) : '');
      }
      // Download...
      if (!empty($csv)) {
        // Save file to server and download..
        $dl->write($file, $head . mswNL() . implode(mswNL(), $csv));
        if (file_exists($file)) {
          return $file;
        }
      }
    }
    return 'none';
  }

  public function import() {
    $data  = array();
    // Upload CSV file..
    if ($this->ssn->active('upload_file') == 'yes' && file_exists($this->ssn->get('upload_file'))) {
      $handle = fopen($this->ssn->get('upload_file'), 'r');
      if ($handle) {
        while (($CSV = fgetcsv($handle, CSV_MAX_LINES_TO_READ, CSV_IMPORT_DELIMITER, CSV_IMPORT_ENCLOSURE)) !== false) {
          // Add account..
          $_POST['name']     = (isset($CSV[0]) && $CSV[0] ? trim($CSV[0]) : '');
          $_POST['email']    = (isset($CSV[1]) && mswIsValidEmail($CSV[1]) ? trim($CSV[1]) : '');
          $_POST['userPass'] = (isset($CSV[2]) && $CSV[2] ? trim($CSV[2]) : substr(md5(uniqid(rand(), 1)), 0, $this->settings->minPassValue));
          $_POST['enabled']  = 'yes';
          $_POST['timezone'] = (isset($CSV[3]) && in_array($CSV[3], array_keys($this->timezones)) ? trim($CSV[3]) : $this->settings->timezone);
          $_POST['ip']       = '';
          // If name and email are ok and email doesn`t exist, we can add user..
          if (trim($_POST['name']) && trim($_POST['email']) && accounts::check($_POST['email']) == 'accept') {
            // Add to db..
            accounts::add(array(
              'name' => $_POST['name'],
              'email' => $_POST['email'],
              'userPass' => $_POST['userPass'],
              'enabled' => 'yes',
              'timezone' => $_POST['timezone'],
              'ip' => $_POST['ip'],
              'notes' => '',
              'language' => $this->settings->language,
              'enableLog' => $this->settings->enableLog
            ));
            // Add to array..
            $data[] = array(
              $_POST['name'],
              $_POST['email'],
              $_POST['userPass']
            );
          }
        }
        fclose($handle);
      }
      // Clear session file..
      $this->ssn->delete(array('upload_file'));
    }
    return $data;
  }

  public function search() {
    $f   = (isset($_GET['field']) && in_array($_GET['field'], array(
      'name',
      'email',
      'dest_email'
    )) ? $_GET['field'] : 'name');
    $acc = array();
    if ($f == 'dest_email') {
      $q = mswSQL_query("SELECT `name`,`email` FROM `" . DB_PREFIX . "portal`
           WHERE (`name` LIKE '%" . mswSQL($_GET['term']) . "%' OR
           LOWER(`email`) LIKE '%" . mswSQL(strtolower($_GET['term'])) . "%')
           AND `enabled`  = 'yes'
           AND `verified` = 'yes'
           " . ((int) $_GET['id'] > 0 ? 'AND `id` != \'' . (int) $_GET['id'] . '\'' : '') . "
           GROUP BY `email`
	         ORDER BY `name`,`email`
		       ", __file__, __line__);
    } else {
      $q = mswSQL_query("SELECT `name`,`email` FROM `" . DB_PREFIX . "portal`
           WHERE `" . $f . "` LIKE '%" . mswSQL($_GET['term']) . "%'
           AND `enabled` = 'yes'
		       AND `verified` = 'yes'
		       " . ((int) $_GET['id'] > 0 ? 'AND `id` != \'' . (int) $_GET['id'] . '\'' : '') . "
		       GROUP BY `email`
	         ORDER BY `name`,`email`
		       ", __file__, __line__);
    }
    while ($A = mswSQL_fetchobj($q)) {
      $n          = array();
      $n['name']  = mswCD($A->name);
      $n['email'] = mswCD($A->email);
      $acc[]      = $n;
    }
    return $acc;
  }

  public function enable() {
    $_GET['id'] = (int) $_GET['id'];
    mswSQL_query("UPDATE `" . DB_PREFIX . "portal` SET
    `enabled`  = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id` = '{$_GET['id']}'
    ", __file__, __line__);
  }

  public function add($add = array()) {
    // Add override..
    if (!empty($add)) {
      foreach ($add AS $k => $v) {
        $_POST[$k] = $v;
      }
    }
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
    `reason`,
    `language`,
    `enableLog`
    ) VALUES (
    '" . mswSQL($_POST['name']) . "',
    UNIX_TIMESTAMP(),
    '" . mswSQL(strtolower($_POST['email'])) . "',
    '" . mswPassHash(array('type' => 'add', 'pass' => $_POST['userPass'])) . "',
    '" . (isset($_POST['enabled']) ? 'yes' : 'no') . "',
    'yes',
    '" . mswSQL($_POST['timezone']) . "',
    '" . mswSQL($_POST['ip']) . "',
    '" . mswSQL($_POST['notes']) . "',
    '" . (isset($_POST['reason']) ? mswSQL($_POST['reason']) : '') . "',
    '" . (isset($_POST['language']) ? mswSQL($_POST['language']) : 'english') . "',
    '" . (isset($_POST['enableLog']) ? 'yes' : 'no') . "'
    )", __file__, __line__);
    $id = mswSQL_insert_id();
    return $id;
  }

  public function update() {
    $_POST['update'] = (int) $_POST['update'];
    mswSQL_query("UPDATE `" . DB_PREFIX . "portal` SET
    `name`      = '" . mswSQL($_POST['name']) . "',
    `email`     = '" . mswSQL(strtolower($_POST['email'])) . "',
    `userPass`  = '" . ($_POST['userPass'] ? mswPassHash(array('type' => 'add', 'pass' => $_POST['userPass'])) : $_POST['old_pass']) . "',
    `enabled`   = '" . (isset($_POST['enabled']) ? 'yes' : 'no') . "',
    `verified`  = IF(`verified` = 'no', '" . (isset($_POST['enabled']) ? 'yes' : 'no') . "', 'yes'),
    `timezone`  = '" . mswSQL($_POST['timezone']) . "',
    `ip`        = '" . mswSQL($_POST['ip']) . "',
    `notes`     = '" . mswSQL($_POST['notes']) . "',
    `reason`    = '" . mswSQL($_POST['reason']) . "',
    `language`  = '" . mswSQL($_POST['language']) . "',
    `enableLog` = '" . (isset($_POST['enableLog']) ? 'yes' : 'no') . "'
    WHERE `id`  = '{$_POST['update']}'
    ", __file__, __line__);
    // If now verified, clear system fields..
    if (isset($_POST['enabled'])) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "portal` SET
      `system1` = '',
      `system2` = ''
      WHERE `id`  = '{$_POST['update']}'
      ", __file__, __line__);
    }
  }

  public function move($from, $to) {
    $rows    = 0;
    $toID    = mswSQL_table('portal', 'email', mswSQL(strtolower($to)));
    $fromID  = mswSQL_table('portal', 'email', mswSQL(strtolower($from)));
    if (isset($toID->id, $fromID->id)) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
      `lastrevision`     = UNIX_TIMESTAMP(),
      `visitorID`        = '{$toID->id}'
      WHERE `visitorID`  = '{$fromID->id}'
      ", __file__, __line__);
      $rows = mswSQL_affrows();
    }
    return $rows;
  }

  public function delete($t_class) {
    if (!empty($_POST['del'])) {
      $uIDs    = implode(',', $_POST['del']);
      // Get all tickets related to the users that are going to be deleted..
      $tickets = array();
      $q       = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "tickets`
                 WHERE `visitorID` IN({$uIDs})
		             ORDER BY `id`
		             ", __file__, __line__);
      while ($T = mswSQL_fetchobj($q)) {
        $tickets[] = $T->id;
      }
      // If there are tickets, delete all information..
      // We can use the delete operation from the ticket class..
      if (!empty($tickets)) {
        $_POST['ticket'] = $tickets;
        $t_class->deleteTickets();
      }
      // Users info..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "portal`
      WHERE `id` IN({$uIDs})
      ", __file__, __line__);
      // Delete disputes..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "disputes` WHERE `visitorID` IN({$uIDs})", __file__, __line__);
      // Log entries..
      mswSQL_query("DELETE FROM `" . DB_PREFIX . "log`
      WHERE `userID` IN({$uIDs})
	    AND `type`      = 'acc'
      ", __file__, __line__);
      mswSQL_truncate(array('tickets','attachments','replies','cusfields','ticketfields','disputes','tickethistory','portal'));
      return count($_POST['del']);
    }
    return '0';
  }

  // Does data exist..
  public function check($data = '', $field = 'email') {
    $SQL = '';
    if (isset($_POST['currID']) && (int) $_POST['currID'] > 0) {
      $_POST['currID'] = (int) $_POST['currID'];
      $SQL             = "AND `id` != '{$_POST['currID']}'";
    }
    $q = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "portal`
         WHERE `" . $field . "` = '" . mswSQL(($data ? $data : $_POST['checkEntered'])) . "'
	       $SQL
         LIMIT 1
         ", __file__, __line__);
    $P = mswSQL_fetchobj($q);
    return (isset($P->id) ? 'exists' : 'accept');
  }

  // Search accounts..
  public function searchAccounts($f, $v, $e) {
    $ar  = array();
    $q   = mswSQL_query("SELECT `id`,`name`,`email` FROM `" . DB_PREFIX . "portal`
           WHERE (LOWER(`name`) LIKE '%" . strtolower(mswSQL($v)) . "%'
            OR LOWER(`email`) LIKE '%" . strtolower(mswSQL($v)) . "%')
           AND `enabled` = 'yes'
           AND `verified` = 'yes'
           ORDER BY `name`, `email`
           ", __file__, __line__);
    while ($A = mswSQL_fetchobj($q)) {
      $ar[] = array(
        'name' => mswSH($A->name),
        'email' => mswSH($A->email)
      );
    }
    return $ar;
  }

  // Search accounts..
  public function searchAccountsPages($v) {
    $ar  = array();
    $q   = mswSQL_query("SELECT `id`,`name`,`email` FROM `" . DB_PREFIX . "portal`
           WHERE (LOWER(`name`) LIKE '%" . strtolower(mswSQL($v)) . "%'
            OR LOWER(`email`) LIKE '%" . strtolower(mswSQL($v)) . "%')
           AND `enabled` = 'yes'
           AND `verified` = 'yes'
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

  // Search..
  public function autoSearch($access) {
    $ds  = (isset($_GET['dispute']) ? (int) $_GET['dispute'] : '0');
    $s   = array();
    // All users in current dispute..
    $tk  = mswSQL_table('tickets', 'id', $ds);
    $s[] = $tk->visitorID;
    $q   = mswSQL_query("SELECT `visitorID` FROM `" . DB_PREFIX . "disputes`
           WHERE `ticketID` = '{$ds}'
           ", __file__, __line__);
    while ($DU = mswSQL_fetchobj($q)) {
      $s[] = $DU->visitorID;
    }
    $ar  = array();
    $q   = mswSQL_query("SELECT `id`,`name`,`email` FROM `" . DB_PREFIX . "portal`
           WHERE (LOWER(`name`) LIKE '%" . strtolower(mswSQL($_GET['term'])) . "%'
            OR LOWER(`email`) LIKE '%" . strtolower(mswSQL($_GET['term'])) . "%'
            OR LOWER(`notes`) LIKE '%" . strtolower(mswSQL($_GET['term'])) . "%'
           AND `enabled` = 'yes'
           AND `verified` = 'yes')
           AND (`id` NOT IN(" . (!empty($s) ? mswSQL(implode(',', $s)) : '0') . "))
           ORDER BY `name`, `email`
           ", __file__, __line__);
    while ($A = mswSQL_fetchobj($q)) {
      $ar[] = array(
        'value' => $A->id,
        'label' => mswSH($A->name) . ' (' . $A->email . ')',
        'name' => mswSH($A->name),
        'email' => mswSH($A->email),
        'access' => $access
      );
    }
    return $ar;
  }

  public function autoDel() {
    $acc = array();
    if ($this->settings->accautodel > 0) {
      $q   = mswSQL_query("SELECT `id` FROM `" . DB_PREFIX . "portal`
             WHERE DATEDIFF(NOW(),DATE(FROM_UNIXTIME(`ts`))) >= " . $this->settings->accautodel . "
             AND `enabled` = 'no'
             AND `verified` = 'no'
             ", __file__, __line__);
      while ($A = mswSQL_fetchobj($q)) {
        $acc[] = $A->id;
      }
      // Delete..
      if (!empty($acc)) {
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "portal` WHERE `id` IN(" . mswSQL(implode(',', $acc)) . ")", __file__, __line__);
      }
    }
    return (!empty($acc) ? count($acc) : '0');
  }

}

?>