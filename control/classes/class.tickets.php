<?php

/* CLASS FILE
----------------------------------*/

class tickets extends msSystem {

  public $parser;
  public $settings;
  public $datetime;
  public $fields;
  public $system;
  public $upload;
  public $tk_levels;
  public $tk_statuses;

  const ATTACH_FILE_NAME_TRUNCATION = 30;

  public $internal = array(
    'chmod' => 0777,
    'chmod-after' => 0644
  );

  public function isTicketOpen($d = array()) {
    if ($this->settings->openlimit == 'no') {
      return 'no';
    }
    $q = mswSQL_query("SELECT count(*) AS `c` FROM `" . DB_PREFIX . "tickets`
         LEFT JOIN `" . DB_PREFIX . "portal`
         ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
         WHERE " . (isset($d['acc']) && $d['acc'] > 0 ? '`' . DB_PREFIX . 'tickets`.`visitorID` = \'' . (int) $d['acc'] . '\'' : '`' . DB_PREFIX . 'portal`.`email` = \'' . mswSQL($d['email']) . '\'') . "
         AND `ticketStatus` NOT IN('close','closed')
         AND `spamFlag` = 'no'
         LIMIT 1
         ", __file__, __line__);
    $C = mswSQL_fetchobj($q);
    return (isset($C->c) && $C->c > 0 ? 'yes' : 'no');
  }

  public function updateIP($id, $type = 'ticket') {
    switch ($type) {
      case 'ticket':
        mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
        `ipAddresses` = '" . mswSQL(mswIP()) . "'
        WHERE `id`    = '{$id}'
        ", __file__, __line__);
        break;
      case 'reply':
        break;
    }
  }

  public function size($size) {
    if ($this->settings->maxsize == 0 || $this->settings->maxsize == '') {
      return true;
    }
    return ($size <= $this->settings->maxsize ? true : false);
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
    }
  }

  public function type($file) {
    if ($this->settings->filetypes == '') {
      return true;
    }
    $types = array_map('trim', explode('|', strtolower($this->settings->filetypes)));
    $ext   = strrchr(strtolower($file), '.');
    return (in_array($ext, $types) ? true : false);
  }

  public function preFill($id) {
    $html = array();
    $q = mswSQL_query("SELECT `dept_subject`,`dept_comments`,`dept_priority` FROM `" . DB_PREFIX . "departments`
         WHERE `showDept` = 'yes'
         AND `id`         = '{$id}'
         ORDER BY `name`
         ", __file__, __line__);
    $DEPT        = mswSQL_fetchobj($q);
    $html['sub'] = (isset($DEPT->dept_subject) && $DEPT->dept_subject ? mswSH($DEPT->dept_subject) : '');
    $html['msg'] = (isset($DEPT->dept_comments) && $DEPT->dept_comments ? mswSH($DEPT->dept_comments) : '');
    $html['prt'] = (isset($DEPT->dept_priority) && !in_array($DEPT->dept_priority, array('0','')) ? mswSH($DEPT->dept_priority) : $this->settings->defprty);
    return $html;
  }

  public function disputeUserNames($t, $name) {
    $html  = '';
    $users = array(
      mswSH($name)
    );
    $q = mswSQL_query("SELECT `name`,`email`,`" . DB_PREFIX . "portal`.`id` AS `pID` FROM `" . DB_PREFIX . "disputes`
         LEFT JOIN `" . DB_PREFIX . "portal`
		     ON `" . DB_PREFIX . "disputes`.`visitorID`  = `" . DB_PREFIX . "portal`.`id`
         WHERE `" . DB_PREFIX . "disputes`.`ticketID` = '{$t->id}'
		     ORDER BY `" . DB_PREFIX . "portal`.`name`
         ", __file__, __line__);
    while ($U = mswSQL_fetchobj($q)) {
      if ($U->pID > 0) {
        $users[$U->pID] = mswSH($U->name);
      }
    }
    return $users;
  }

  public function disputeUsers($ticket) {
    $u = array();
    $q = mswSQL_query("SELECT `visitorID` FROM `" . DB_PREFIX . "disputes`
         WHERE `ticketID` = '{$ticket}'
		     GROUP BY `visitorID`
		     ORDER BY `id`
         ", __file__, __line__);
    while ($U = mswSQL_fetchobj($q)) {
      $u[] = $U->visitorID;
    }
    return $u;
  }

  public function openclose($id, $action = 'open') {
    mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
    `lastrevision`  = UNIX_TIMESTAMP(),
    `ticketStatus`  = '{$action}'
    WHERE `id`      = '{$id}'
    ", __file__, __line__);
    return mswSQL_affrows();
  }

  public function replies($id, $name, $userid, $lng) {
    global $msg_script17;
    $data = '';
    $none = str_replace('{text}', $lng[1], mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-message.htm'));
    $sig  = str_replace('{text}', $lng[1], mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-signature.htm'));
    $sub  = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-reply-sublink.htm');
    $flk  = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-reply-field-link.htm');
    $alk  = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-reply-attachment-link.htm');
    $reps = 0;
    $q = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "replies`
         WHERE `ticketID` = '{$id}'
         ORDER BY `id`
         ", __file__, __line__);
    if (mswSQL_numrows($q) > 0) {
      while ($R = mswSQL_fetchobj($q)) {
        $siggie   = '';
        $sublinks = array();
        if ($R->disputeUser > 0) {
          $R->replyType = 'dispute';
        }
        switch ($R->replyType) {
          // Reply by admin..
          case 'admin':
            $USER      = mswSQL_table('users', 'id', $R->replyUser);
            $replyName = (isset($USER->name) ? mswSH($USER->name) : $msg_viewticket43);
            $label     = 'panel panel-default';
            $icon      = 'users';
            // Does this user have a siggie..
            if ($USER->signature) {
              $siggie = str_replace('{signature}', mswNL2BR($this->parser->mswAutoLinkParser(mswSH($USER->signature))), $sig);
            }
            break;
          // Reply by original ticket creator..
          case 'visitor':
            if ($userid == $R->replyUser) {
              $replyName = $name;
            } else {
              $USER       = mswSQL_table('portal', 'id', $R->replyUser, '', '`name`');
              $replyName  = (isset($USER->name) ? mswSH($USER->name) : $msg_viewticket43);
            }
            $label     = 'panel panel-default';
            $icon      = 'user';
            break;
          // Reply by other user viewing same ticket..
          case 'dispute':
            $D            = mswSQL_table('portal', 'id', $R->disputeUser);
            $replyName    = (isset($D->name) ? mswSH($D->name) : $msg_script17);
            $R->replyType = 'visitor';
            $label        = 'panel panel-default';
            $icon         = 'user';
            break;
        }
        // Custom field data..
        $fields   = $this->fields->display($id, $R->id, 0, $label);
        $fields_c = $this->fields->display($id, $R->id, 1);
        if ($fields_c > 0) {
          $sublinks[] = str_replace(array(
            '{id}',
            '{text}',
            '{count}'
          ),
          array(
            $R->id,
            $lng[3],
            $fields_c
          ), $flk);
        }
        // Attachments..
        $attach   = tickets::attachments($id, $R->id);
        $attach_c = tickets::attachments($id, $R->id, 1);
        if ($attach_c > 0) {
          $sublinks[] = str_replace(array(
            '{id}',
            '{text}',
            '{count}'
          ),
          array(
            $R->id,
            $lng[2],
            $attach_c
          ), $alk);
        }
        $data .= str_replace(array(
          '{id}',
          '{type}',
          '{comments}',
          '{signature}',
          '{text}',
          '{name}',
          '{date}',
          '{time}',
          '{attachments}',
          '{info}',
          '{fields}',
          '{label}',
          '{count}',
          '{display}',
          '{display2}',
          '{display3}',
          '{icon}',
          '{sublinks}'
        ), array(
          $R->id,
          $R->replyType,
          $this->parser->mswTxtParsingEngine($R->comments, ($this->settings->enableBBCode == 'no' && $R->replyType == 'admin' ? true : false)),
          $siggie,
          $lng[0],
          $replyName,
          $this->datetime->mswDateTimeDisplay($R->ts, $this->settings->dateformat),
          $this->datetime->mswDateTimeDisplay($R->ts, $this->settings->timeformat),
          $attach,
          mswCD($R->ipAddresses),
          $fields,
          $label,
          (++$reps),
          (!$siggie ? ' style="display:none"' : ''),
          (!$fields ? ' style="display:none"' : ''),
          (!$attach ? ' style="display:none"' : ''),
          $icon,
          (!empty($sublinks) ? str_replace('{links}', implode(SUBLINK_SEPARATOR, $sublinks), $sub) : '')
        ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-reply.htm'));
      }
    }
    return ($data ? trim($data) : $none);
  }

  // Rename attachment..
  public function rename($name, $ticket, $reply, $incr) {
    $rand = substr(md5(uniqid(rand(), 1)), 3, 20);
    $ext  = str_replace('.php', '.phps', substr(strrchr(strtolower($name), '.'), 1));
    return $ticket . ($reply > 0 ? '_' . $reply : '') . '-' . $incr . '-' . $rand . '.' . ($ext ? $ext : 'txt');
  }

  // Add attachment..
  public function addAttachment($data = array()) {
    if (is_dir($this->settings->attachpath) && is_writeable($this->settings->attachpath)) {
      if ($this->upload->isUploaded($data['temp'])) {
        $FN = ($this->settings->rename == 'yes' ? tickets::rename($data['name'], $data['tID'], $data['rID'], $data['incr']) : mswCleanFile($data['name']));
        $U  = $this->settings->attachpath . '/' . $FN;
        $Y  = date('Y', $this->datetime->mswTimeStamp());
        $M  = date('m', $this->datetime->mswTimeStamp());
        // Attempt to create folder if it doesn`t exist..
        if (!is_dir($this->settings->attachpath . '/' . $Y)) {
          $this->upload->folderCreation($this->settings->attachpath . '/' . $Y, $this->internal['chmod']);
        }
        if (is_dir($this->settings->attachpath . '/' . $Y)) {
          if (!is_dir($this->settings->attachpath . '/' . $Y . '/' . $M)) {
            $this->upload->folderCreation($this->settings->attachpath . '/' . $Y . '/' . $M, $this->internal['chmod']);
          }
          if (is_dir($this->settings->attachpath . '/' . $Y . '/' . $M)) {
            $U = $this->settings->attachpath . '/' . $Y . '/' . $M . '/' . $FN;
          }
        }
        // Upload temp file..
        $this->upload->moveFile($data['temp'], $U);
        // Required by some servers to make image viewable and accessible via FTP..
        $this->upload->chmodFile($U, $this->internal['chmod-after']);
      }
      if (file_exists($U)) {
        // Add to database..
        mswSQL_query("INSERT INTO `" . DB_PREFIX . "attachments` (
        `ts`,
        `ticketID`,
        `replyID`,
        `department`,
        `fileName`,
        `fileSize`,
        `mimeType`
        ) VALUES (
        UNIX_TIMESTAMP(),
        '{$data['tID']}',
        '{$data['rID']}',
        '{$data['dept']}',
        '" . basename($U) . "',
        '{$data['size']}',
        '{$data['mime']}'
        )", __file__, __line__);
        $ID = mswSQL_insert_id();
        // Remove temp file if it still exists..
        if (file_exists($data['temp'])) {
          @unlink($data['temp']);
        }
        return array(
          $ID,
          $U
        );
      }
    }
  }

  public function attachments($ticket, $reply = 0, $count = 0) {
    $data = '';
    $wrap = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-attachment-wrapper.htm');
    // Are attachments enabled?
    if ($this->settings->attachment == 'no') {
      return '';
    }
    $q = mswSQL_query("SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
         WHERE `ticketID`  = '{$ticket}'
         AND `replyID`     = '{$reply}'
         ORDER BY `id`
         ", __file__, __line__);
    if ($count) {
      return mswSQL_numrows($q);
    }
    if (mswSQL_numrows($q) > 0) {
      while ($ATT = mswSQL_fetchobj($q)) {
        $split = explode('-', $ATT->addDate);
        $base  = $this->settings->attachpath . '/';
        // Check for newer folder structure..
        if (@file_exists($this->settings->attachpath . '/' . $split[0] . '/' . $split[1] . '/' . $ATT->fileName)) {
          $base = $this->settings->attachpath . '/' . $split[0] . '/' . $split[1] . '/';
        }
        $fileName = substr($ATT->fileName, 0, strpos($ATT->fileName, '.'));
        // Only show file if it exists..
        if (file_exists($base . $ATT->fileName)) {
          $data .= str_replace(array(
            '{ext}',
            '{id}',
            '{file}',
            '{size}',
            '{file_name}'
          ), array(
            substr(strrchr(strtoupper($ATT->fileName), '.'), 1),
            $ATT->id,
            substr($ATT->fileName, 0, strpos($ATT->fileName, '.')),
            mswFSC($ATT->fileSize),
            (tickets::ATTACH_FILE_NAME_TRUNCATION > 0 ? (strlen($fileName) > tickets::ATTACH_FILE_NAME_TRUNCATION ? substr($fileName, 0, tickets::ATTACH_FILE_NAME_TRUNCATION) . '..' : $fileName) : $fileName)
          ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-attachment.htm'));
        }
      }
    }
    return ($data ? str_replace('{attachments}', trim($data), $wrap) : '');
  }

  public function add($tdata = array()) {
    $spam = (isset($tdata['spam']) && $tdata['spam'] == 'yes' ? 'yes' : 'no');
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "tickets` (
    `ts`,
    `lastrevision`,
    `department`,
    `assignedto`,
    `visitorID`,
    `subject`,
    `mailBodyFilter`,
    `comments`,
    `priority`,
    `ticketStatus`,
    `ipAddresses`,
    `ticketNotes`,
    `isDisputed`,
    `source`,
    `spamFlag`
    ) VALUES (
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP(),
    '{$tdata['dept']}',
    '{$tdata['assigned']}',
    '{$tdata['visitor']}',
    '" . mswSQL($tdata['subject']) . "',
    '" . mswSQL($tdata['quoteBody']) . "',
    '" . mswSQL($tdata['comments']) . "',
    '" . mswSQL($tdata['priority']) . "',
    '{$tdata['ticketStatus']}',
    '" . mswSQL($tdata['ip']) . "',
    '" . mswSQL($tdata['notes']) . "',
    '{$tdata['disputed']}',
    '" . (isset($tdata['source']) ? $tdata['source'] : 'standard') . "',
    '{$spam}'
    )", __file__, __line__);
    $id = mswSQL_insert_id();
    // If assigned, enable department assign option automatically..
    // Possibly from admin created ticket..
    if ($tdata['assigned'] != '') {
      mswSQL_query("UPDATE `" . DB_PREFIX . "departments` SET
      `manual_assign` = 'yes'
      WHERE `id`      = '{$tdata['dept']}'
      ", __file__, __line__);
    }
    // Custom fields..
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
        $fdata = '';
        // If value is array, its checkboxes..
        if (is_array($v)) {
          if (!empty($v)) {
            $fdata = implode('#####', $v);
          }
        } else {
          $fdata = $v;
        }
        $k = (int) $k;
        // If data exists, update or add entry..
        // If blank or 'nothing-selected', delete if exists..
        if ($fdata != '' && $fdata != 'nothing-selected' && mswSQL_rows('ticketfields WHERE `ticketID` = \'' . $id . '\' AND `fieldID` = \'' . $k . '\' AND `replyID` = \'0\'') == 0) {
          mswSQL_query("INSERT INTO `" . DB_PREFIX . "ticketfields` (
          `fieldData`,`ticketID`,`fieldID`,`replyID`
          ) VALUES (
          '" . mswSQL($fdata) . "','{$id}','{$k}','0'
          )", __file__, __line__);
        }
      }
    }
    // Return new ticket id..
    return $id;
  }

  public function reply($rdata = array()) {
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "replies` (
    `ts`,
    `ticketID`,
    `comments`,
    `mailBodyFilter`,
    `replyType`,
    `replyUser`,
    `ipAddresses`,
    `disputeUser`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '{$rdata['ticket']}',
    '" . mswSQL($rdata['comments']) . "',
    '" . mswSQL($rdata['quoteBody']) . "',
    '{$rdata['repType']}',
    '{$rdata['visitor']}',
    '{$rdata['ip']}',
    '{$rdata['disID']}'
    )", __file__, __line__);
    $id = mswSQL_insert_id();
    // Update ticket revision date
    // If ticket is waiting assignment it must remain in the start position..
    if ($id > 0) {
      mswSQL_query("UPDATE `" . DB_PREFIX . "tickets` SET
      `lastrevision`  = UNIX_TIMESTAMP(),
	    `ticketStatus`  = 'open'
      WHERE `id`      = '{$rdata['ticket']}'
      ", __file__, __line__);
    }
    // Custom fields..
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
        if ($data != '' && $data != 'nothing-selected' && mswSQL_rows('ticketfields WHERE `ticketID` = \'' . $rdata['ticket'] . '\' AND `fieldID` = \'' . $k . '\' AND `replyID` = \'' . $id . '\'') == 0) {
          mswSQL_query("INSERT INTO `" . DB_PREFIX . "ticketfields` (
          `fieldData`,`ticketID`,`fieldID`,`replyID`
          ) VALUES (
          '" . mswSQL($data) . "','{$rdata['ticket']}','{$k}','{$id}'
          )", __file__, __line__);
        }
      }
    }
    return $id;
  }

  public function getLastReply($id) {
    global $msg_script17;
    $q = mswSQL_query("SELECT `ts`,`replyType`,`replyUser`,`disputeUser` FROM `" . DB_PREFIX . "replies`
         WHERE `ticketID` = '{$id}'
		     ORDER BY `id` DESC
		     LIMIT 1
		     ", __file__, __line__);
    $R = mswSQL_fetchobj($q);
    if (isset($R->ts)) {
      switch ($R->replyType) {
        case 'admin':
          $A    = mswSQL_table('users', 'id', $R->replyUser);
          $info = array(
            (isset($A->name) ? mswSH($A->name) : $msg_script17),
            $R->ts,
            $R->replyType
          );
          break;
        case 'visitor':
          if ($R->disputeUser > 0) {
            $U    = mswSQL_table('portal', 'id', $R->disputeUser, '', '`name`');
            $info = array(
              (isset($U->name) ? mswSH($U->name) : $msg_script17),
              $R->ts,
              $R->replyType
            );
          } else {
            $U    = mswSQL_table('portal', 'id', $R->replyUser, '', '`name`');
            $info = array(
              (isset($U->name) ? mswSH($U->name) : $msg_script17),
              $R->ts,
              $R->replyType
            );
          }
          break;
      }
      return $info;
    }
    return array(
      '0',
      '0',
      ''
    );
  }

  public function disputeList($email, $visID, $lv, $count = false, $queryAdd = '') {
    global $msg_portal8, $msg_public_history9, $msg_public_history10, $msg_portal21,
    $msg_showticket23, $msg_showticket24, $msg_script30, $msg_showticket30, $msg_public_dashboard6,
    $msg_public_dashboard8, $msadminlang_tickets_3_7;
    $data = '';
    $IDs  = tickets::disID($visID);
    $sch  = '';
    $qft  = array();
    $oft  = 'ORDER BY `' . DB_PREFIX . 'tickets`.`id` DESC';
    // Check for search mode..
    if (isset($_GET['qd'])) {
      // Load the skip words array..
      include(PATH . 'control/skipwords.php');
      $chop = array_map('trim', explode(' ', urldecode($_GET['qd'])));
      if (!empty($chop)) {
        foreach ($chop AS $word) {
          if (!in_array($word, $searchSkipWords) && strlen($word) > 1) {
            $word = strtolower($word);
            $sch .= (!$sch ? '' : 'OR ') . "LOWER(`subject`) LIKE '%" . mswSQL($word) . "%' OR LOWER(`comments`) LIKE '%" . mswSQL($word) . "%'";
          }
        }
        if ($sch) {
          $qft[] = 'AND (' . $sch . ')';
        }
      }
    }
    // Order filters..
    if (isset($_GET['order'])) {
      switch ($_GET['order']) {
        // Subject (ascending)..
        case 'subject_asc':
          $oft = 'ORDER BY `subject`';
          break;
        // Subject (descending)..
        case 'subject_desc':
          $oft = 'ORDER BY `subject` desc';
          break;
        // TicketID (ascending)..
        case 'id_asc':
          $oft = 'ORDER BY `ticketID`';
          break;
        // TicketID (descending)..
        case 'id_desc':
          $oft = 'ORDER BY `ticketID` desc';
          break;
        // Priority (ascending)..
        case 'pr_asc':
          $oft = 'ORDER BY `levelName`';
          break;
        // Priority (descending)..
        case 'pr_desc':
          $oft = 'ORDER BY `levelName` desc';
          break;
        // Department (ascending)..
        case 'dept_asc':
          $oft = 'ORDER BY `deptName`';
          break;
        // Department (descending)..
        case 'dept_desc':
          $oft = 'ORDER BY `deptName` desc';
          break;
        // Date Updated (ascending)..
        case 'rev_asc':
          $oft = 'ORDER BY `lastrevision`';
          break;
        // Date Updated (descending)..
        case 'rev_desc':
          $oft = 'ORDER BY `lastrevision` desc';
          break;
        // Date Added (ascending)..
        case 'date_asc':
          $oft = 'ORDER BY `' . DB_PREFIX . 'tickets`.`ts`';
          break;
        // Date Added (descending)..
        case 'date_desc':
          $oft = 'ORDER BY `' . DB_PREFIX . 'tickets`.`ts` desc';
          break;
      }
    }
    // Service level and department filters..
    if (isset($_GET['filter'])) {
      $qft[] = 'AND `priority` = \'' . mswSQL($_GET['filter']) . '\'';
    }
    if (isset($_GET['dept'])) {
      $qft[] = 'AND `department` = \'' . mswSQL($_GET['dept']) . '\'';
    }
    $lWrap = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/tickets/tickets-last-reply-date.htm');
    $q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS *,
         `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
		     `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
	       `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
	       `" . DB_PREFIX . "departments`.`name` AS `deptName`,
	       `" . DB_PREFIX . "levels`.`name` AS `levelName`,
		     (SELECT count(*) FROM `" . DB_PREFIX . "disputes`
	         WHERE `" . DB_PREFIX . "disputes`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
	       ) AS `disputeCount`,
	       (SELECT count(*) FROM `" . DB_PREFIX . "replies`
          WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
         ) AS `replyCount`
		     FROM `" . DB_PREFIX . "tickets`
		     LEFT JOIN `" . DB_PREFIX . "departments`
	       ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
		     LEFT JOIN `" . DB_PREFIX . "portal`
	       ON `" . DB_PREFIX . "tickets`.`visitorID`  = `" . DB_PREFIX . "portal`.`id`
	       LEFT JOIN `" . DB_PREFIX . "levels`
	       ON (`" . DB_PREFIX . "tickets`.`priority` = 
           IF (`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'), 
             `" . DB_PREFIX . "levels`.`id`,
             `" . DB_PREFIX . "levels`.`marker`
           )
         )
		     WHERE (`" . DB_PREFIX . "portal`.`email`   = '{$email}'
         AND `isDisputed` = 'yes'
			   AND `spamFlag`   = 'no'
			    " . $queryAdd . "
			    " . (!empty($qft) ? implode(mswNL(), $qft) : '') . "
         ) OR (
          `" . DB_PREFIX . "tickets`.`id` IN(" . (!empty($IDs) ? mswSQL(implode(',', $IDs)) : '0') . ")
          AND `isDisputed` = 'yes'
			    AND `spamFlag`   = 'no'
			    " . $queryAdd . "
			    " . (!empty($qft) ? implode(mswNL(), $qft) : '') . "
         )
         $oft
		     LIMIT " . $lv[0] . "," . $lv[1] . "
         ", __file__, __line__);
    if ($count) {
      $c = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
      return (isset($c->rows) ? $c->rows : '0');
    }
    while ($T = mswSQL_fetchobj($q)) {
      $last    = tickets::getLastReply($T->ticketID);
      // Ticket starter..
      $starter = mswSH($T->ticketName);
      $lastRep = '';
      $replyBy = '- - - -';
      if ($last[0] != '0') {
        $lastRep = str_replace(array(
          '{date}',
          '{time}'
        ), array(
          $this->datetime->mswDateTimeDisplay($last[1], $this->settings->dateformat),
          $this->datetime->mswDateTimeDisplay($last[1], $this->settings->timeformat)
        ), $lWrap);
        $replyBy = $last[0];
      }
      $nextRepInfo = tickets::dashboardStatus($T);
      $data .= str_replace(array(
        '{ticket_id}',
        '{subject}',
        '{priority}',
        '{dept}',
        '{started_by}',
        '{url}',
        '{text_alt}',
        '{start_date}',
        '{start_time}',
        '{last_reply}',
        '{status}',
        '{icon}',
        '{users_in_dispute}',
        '{view}',
        '{last_reply_dashboard}',
        '{next_reply_info}'
      ), array(
        mswTicketNumber($T->ticketID, $this->settings->minTickDigits, $T->tickno),
        mswSH($T->subject),
        tickets::levels($T->priority),
        $this->system->department($T->department, $msg_script30),
        $starter,
        $this->settings->scriptpath . '/?d=' . $T->ticketID,
        $msg_portal8,
        $this->datetime->mswDateTimeDisplay($T->ticketStamp, $this->settings->dateformat),
        $this->datetime->mswDateTimeDisplay($T->ticketStamp, $this->settings->timeformat),
        $replyBy . $lastRep,
        (isset($this->tk_statuses[$T->ticketStatus][0]) ? $this->tk_statuses[$T->ticketStatus][0] : $msg_showticket23),
        (!in_array($T->ticketStatus, array('close','closed')) ? 'check-square' : ($T->ticketStatus == 'closed' ? 'lock' : 'minus-square')),
        str_replace(array(
          '{text}'
        ), array(
          str_replace('{count}', ($T->disputeCount + 1), $msg_showticket30)
        ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/tickets/tickets-dispute-users.htm')),
        $msg_public_dashboard6,
        $nextRepInfo,
        str_replace('{count}', mswNFM($T->replyCount), $msadminlang_tickets_3_7[13])
      ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/tickets/ticket-list-entry.htm'));
    }
    return ($data ? trim($data) : str_replace('{text}', ($sch ? $msg_portal21 : ($queryAdd ? $msg_public_dashboard8 : $msg_public_history10)), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/tickets/tickets-no-data.htm')));
  }

  public function ticketList($email, $lv, $count = false, $queryAdd = '') {
    global $msg_portal8, $msg_public_history7, $msg_portal7, $msg_portal21, $msg_showticket23,
    $msg_showticket24, $msg_script30, $msg_public_dashboard6, $msg_public_dashboard7,
    $msadminlang_tickets_3_7;
    $data = '';
    $sch  = '';
    $qft  = array();
    $oft  = 'ORDER BY `' . DB_PREFIX . 'tickets`.`id` DESC';
    // Check for search mode..
    if (isset($_GET['qt'])) {
      // Load the skip words array..
      include(PATH . 'control/skipwords.php');
      $chop = array_map('trim', explode(' ', urldecode($_GET['qt'])));
      if (!empty($chop)) {
        foreach ($chop AS $word) {
          if (!in_array($word, $searchSkipWords) && strlen($word) > 1) {
            $word = strtolower($word);
            $sch .= (!$sch ? '' : 'OR ') . "LOWER(`subject`) LIKE '%" . mswSQL($word) . "%' OR LOWER(`comments`) LIKE '%" . mswSQL($word) . "%'";
          }
        }
        if ($sch) {
          $qft[] = 'AND (' . $sch . ')';
        }
      }
    }
    // Order filters..
    if (isset($_GET['order'])) {
      switch ($_GET['order']) {
        // Subject (ascending)..
        case 'subject_asc':
          $oft = 'ORDER BY `subject`';
          break;
        // Subject (descending)..
        case 'subject_desc':
          $oft = 'ORDER BY `subject` desc';
          break;
        // TicketID (ascending)..
        case 'id_asc':
          $oft = 'ORDER BY `ticketID`';
          break;
        // TicketID (descending)..
        case 'id_desc':
          $oft = 'ORDER BY `ticketID` desc';
          break;
        // Priority (ascending)..
        case 'pr_asc':
          $oft = 'ORDER BY `levelName`';
          break;
        // Priority (descending)..
        case 'pr_desc':
          $oft = 'ORDER BY `levelName` desc';
          break;
        // Department (ascending)..
        case 'dept_asc':
          $oft = 'ORDER BY `deptName`';
          break;
        // Department (descending)..
        case 'dept_desc':
          $oft = 'ORDER BY `deptName` desc';
          break;
        // Date Updated (ascending)..
        case 'rev_asc':
          $oft = 'ORDER BY `lastrevision`';
          break;
        // Date Updated (descending)..
        case 'rev_desc':
          $oft = 'ORDER BY `lastrevision` desc';
          break;
        // Date Added (ascending)..
        case 'date_asc':
          $oft = 'ORDER BY `' . DB_PREFIX . 'tickets`.`ts`';
          break;
        // Date Added (descending)..
        case 'date_desc':
          $oft = 'ORDER BY `' . DB_PREFIX . 'tickets`.`ts` desc';
          break;
      }
    }
    // Service level and department filters..
    if (isset($_GET['filter'])) {
      $qft[] = 'AND `priority` = \'' . mswSQL($_GET['filter']) . '\'';
    }
    if (isset($_GET['dept'])) {
      $qft[] = 'AND `department` = \'' . mswSQL($_GET['dept']) . '\'';
    }
    $lWrap = mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/tickets/tickets-last-reply-date.htm');
    $q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS *,
         `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
		     `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
	       `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
	       `" . DB_PREFIX . "departments`.`name` AS `deptName`,
	       `" . DB_PREFIX . "levels`.`name` AS `levelName`,
	       (SELECT count(*) FROM `" . DB_PREFIX . "replies`
          WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
         ) AS `replyCount`
		     FROM `" . DB_PREFIX . "tickets`
		     LEFT JOIN `" . DB_PREFIX . "departments`
	       ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
		     LEFT JOIN `" . DB_PREFIX . "portal`
	       ON `" . DB_PREFIX . "tickets`.`visitorID`  = `" . DB_PREFIX . "portal`.`id`
	       LEFT JOIN `" . DB_PREFIX . "levels`
	       ON (`" . DB_PREFIX . "tickets`.`priority` = 
           IF (`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'), 
             `" . DB_PREFIX . "levels`.`id`,
             `" . DB_PREFIX . "levels`.`marker`
           )
         )
         WHERE `" . DB_PREFIX . "portal`.`email`    = '{$email}'
		     AND `isDisputed`                       = 'no'
		     AND `spamFlag`                         = 'no'
		     " . $queryAdd . "
		     " . (!empty($qft) ? implode(mswNL(), $qft) : '') . "
         $oft
		     LIMIT " . $lv[0] . "," . $lv[1] . "
         ", __file__, __line__);
    if ($count) {
      $c = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
      return (isset($c->rows) ? $c->rows : '0');
    }
    while ($T = mswSQL_fetchobj($q)) {
      $last    = tickets::getLastReply($T->ticketID);
      // Ticket starter..
      $starter = mswSH($T->ticketName);
      $lastRep = '';
      $replyBy = '- - - -';
      if ($last[0] != '0') {
        $lastRep = str_replace(array(
          '{date}',
          '{time}'
        ), array(
          $this->datetime->mswDateTimeDisplay($last[1], $this->settings->dateformat),
          $this->datetime->mswDateTimeDisplay($last[1], $this->settings->timeformat)
        ), $lWrap);
        $replyBy = $last[0];
      }
      $nextRepInfo = tickets::dashboardStatus($T);
      $data .= str_replace(array(
        '{ticket_id}',
        '{subject}',
        '{priority}',
        '{dept}',
        '{started_by}',
        '{url}',
        '{text_alt}',
        '{start_date}',
        '{start_time}',
        '{last_reply}',
        '{status}',
        '{icon}',
        '{users_in_dispute}',
        '{view}',
        '{last_reply_dashboard}',
        '{next_reply_info}'
      ), array(
        mswTicketNumber($T->ticketID, $this->settings->minTickDigits, $T->tickno),
        mswSH($T->subject),
        tickets::levels($T->priority),
        $this->system->department($T->department, $msg_script30),
        $starter,
        $this->settings->scriptpath . '/?t=' . $T->ticketID,
        $msg_portal8,
        $this->datetime->mswDateTimeDisplay($T->ticketStamp, $this->settings->dateformat),
        $this->datetime->mswDateTimeDisplay($T->ticketStamp, $this->settings->timeformat),
        $replyBy . $lastRep,
        (isset($this->tk_statuses[$T->ticketStatus][0]) ? $this->tk_statuses[$T->ticketStatus][0] : $msg_showticket23),
        (!in_array($T->ticketStatus, array('close','closed')) ? 'check-square' : ($T->ticketStatus == 'closed' ? 'lock' : 'minus-square')),
        '',
        $msg_public_dashboard6,
        $nextRepInfo,
        str_replace('{count}', mswNFM($T->replyCount), $msadminlang_tickets_3_7[13])
      ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/tickets/ticket-list-entry.htm'));
    }
    return ($data ? trim($data) : str_replace('{text}', ($sch ? $msg_portal21 : ($queryAdd ? $msg_public_dashboard7 : $msg_portal7)), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/tickets/tickets-no-data.htm')));
  }

  public function dashboardStatus($t) {
    global $msg_public_dashboard9, $msg_public_history6, $msg_showticket23;
    if (in_array($t->ticketStatus, array('close','closed'))) {
      return $msg_public_history6;
    }
    if ($t->assignedto == 'waiting') {
      return $msg_public_dashboard9;
    }
    return (isset($this->tk_statuses[$t->ticketStatus][0]) ? $this->tk_statuses[$t->ticketStatus][0] : $msg_showticket23);
  }

  public function disID($id) {
    $ids = array();
    $q = mswSQL_query("SELECT `ticketID` FROM `" . DB_PREFIX . "disputes`
         WHERE `visitorID` = '{$id}'
         GROUP BY `ticketID`
		     ORDER BY `id`
         ", __file__, __line__);
    while ($U = mswSQL_fetchobj($q)) {
      $ids[] = $U->ticketID;
    }
    return $ids;
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