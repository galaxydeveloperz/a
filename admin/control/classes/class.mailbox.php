<?php

/* CLASS FILE
----------------------------------*/

class mailBox {

  public $settings;
  public $datetime;

  public function getRecipient($id, $user) {
    global $msg_script17;
    $q = mswSQL_query("SELECT `staffID` FROM `" . DB_PREFIX . "mailassoc`
         WHERE `mailID` = '{$id}'
		     AND `staffID` != '{$user}'
		     LIMIT 1
		     ", __file__, __line__);
    $MA = mswSQL_fetchobj($q);
    $U  = mswSQL_table('users', 'id', (isset($MA->staffID) ? $MA->staffID : '0'));
    return (isset($U->name) ? $U->name : $msg_script17);
  }

  public function autoPurge($staff, $days) {
    mswSQL_query("DELETE FROM `" . DB_PREFIX . "mailassoc`
    WHERE `staffID` = '{$staff}'
    AND `folder`    = 'bin'
    AND DATEDIFF(NOW(),DATE(FROM_UNIXTIME(`lastUpdate`))) >= {$days}
    ", __file__, __line__);
    // Any messages not attached to folders are removed..
    mailBox::assocChecker();
  }

  public function getLastReply($id) {
    global $msg_script17;
    $q = mswSQL_query("SELECT `ts`,`staffID` FROM `" . DB_PREFIX . "mailreplies`
         WHERE `mailID` = '{$id}'
		     ORDER BY `id` DESC
		     ", __file__, __line__);
    $R = mswSQL_fetchobj($q);
    if (isset($R->ts)) {
      $A    = mswSQL_table('users', 'id', $R->staffID);
      $info = array(
        (isset($A->name) ? $A->name : $msg_script17),
        $R->ts
      );
      return $info;
    }
    return array(
      '0',
      '0'
    );
  }

  public function add($data) {
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "mailbox` (
    `ts`,
    `staffID`,
    `subject`,
    `message`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '{$data['staff']}',
    '" . mswSQL($data['subject']) . "',
    '" . mswSQL($data['message']) . "'
    )", __file__, __line__);
    $id = mswSQL_insert_id();
    // Association..
    mailBox::assoc(array(
      'staff' => $data['staff'],
      'id' => $id,
      'folder' => 'outbox',
      'status' => 'read'
    ));
    mailBox::assoc(array(
      'staff' => $data['to'],
      'id' => $id,
      'folder' => 'inbox',
      'status' => 'unread'
    ));
    return $id;
  }

  public function reply($data) {
    mswSQL_query("INSERT INTO `" . DB_PREFIX . "mailreplies` (
    `ts`,
    `mailID`,
    `staffID`,
    `message`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '{$data['id']}',
    '{$data['staff']}',
    '" . mswSQL($data['message']) . "'
    )", __file__, __line__);
    $id = mswSQL_insert_id();
    // Association..
    mailBox::assoc(array(
      'staff' => $data['staff'],
      'id' => $data['id'],
      'folder' => 'outbox',
      'status' => 'read'
    ));
    mailBox::assoc(array(
      'staff' => $data['to'],
      'id' => $data['id'],
      'folder' => 'inbox',
      'status' => 'unread'
    ));
    return $id;
  }

  public function assoc($data) {
    if (mswSQL_rows('mailassoc WHERE `staffID` = \'' . $data['staff'] . '\' AND `mailID` = \'' . $data['id'] . '\'') == 0) {
      mswSQL_query("INSERT INTO `" . DB_PREFIX . "mailassoc` (
      `staffID`,
      `mailID`,
      `folder`,
      `status`,
      `lastUpdate`
      ) VALUES (
      '{$data['staff']}',
      '{$data['id']}',
      '{$data['folder']}',
      '{$data['status']}',
      UNIX_TIMESTAMP()
      )", __file__, __line__);
    } else {
      mswSQL_query("UPDATE `" . DB_PREFIX . "mailassoc` SET
      `folder`        = '{$data['folder']}',
      `status`        = '{$data['status']}',
      `lastUpdate`    = UNIX_TIMESTAMP()
      WHERE `staffID` = '{$data['staff']}'
      AND `mailID`    = '{$data['id']}'
	    ", __file__, __line__);
    }
  }

  public function folders($staff) {
    $deleted = 0;
    $folders = array(
      "'inbox'",
      "'outbox'",
      "'bin'"
    );
    // Existing..
    if (!empty($_POST['folder'])) {
      // Update..
      foreach ($_POST['folder'] AS $fK => $fV) {
        if ($fV) {
          mswSQL_query("UPDATE `" . DB_PREFIX . "mailfolders` SET
          `folder`      = '" . mswSQL($fV) . "'
          WHERE `id`    = '" . mswSQL($fK) . "'
          AND `staffID` = '{$staff}'
          ", __file__, __line__);
          $folders[] = "'" . $fK . "'";
        }
      }
      // Delete messages if folder no longer exists..
      if (!empty($folders)) {
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "mailassoc`
	      WHERE `staffID`   = '{$staff}'
	      AND `folder` NOT IN(" . mswSQL(implode(',', $folders)) . ")
        ", __file__, __line__);
        $deleted = mswSQL_affrows();
        mswSQL_truncate(array('mailassoc'));
        // Now delete folders not in array..
        mswSQL_query("DELETE FROM `" . DB_PREFIX . "mailfolders`
	      WHERE `staffID`   = '{$staff}'
	      AND `id`     NOT IN(" . mswSQL(implode(',', $folders)) . ")
        ", __file__, __line__);
        mswSQL_truncate(array('mailfolders'));
      }
    }
    // New..
    if (!empty($_POST['new'])) {
      foreach ($_POST['new'] AS $fV) {
        if ($fV) {
          mswSQL_query("INSERT INTO `" . DB_PREFIX . "mailfolders` (
          `staffID`,
          `folder`
          ) VALUES (
          '{$staff}',
          '" . mswSQL($fV) . "'
          )", __file__, __line__);
        }
      }
    }
    // Any messages not attached to folders are removed..
    mailBox::assocChecker();
    return $deleted;
  }

  public function mark($mark, $staff, $ids = array()) {
    $flag = substr($mark, 2);
    $fid  = (!empty($ids) ? implode(',', $ids) : (!empty($_POST['del']) ? implode(',', $_POST['del']) : '0'));
    // If status is unread, move to inbox..
    switch($flag) {
      case 'unread':
        mswSQL_query("UPDATE `" . DB_PREFIX . "mailassoc` SET
        `status`        = '{$flag}',
        `folder`        = 'inbox'
        WHERE `mailID` IN(" . mswSQL($fid). ")
        AND `staffID`   = '{$staff}'
        ", __file__, __line__);
        break;
      default:
        mswSQL_query("UPDATE `" . DB_PREFIX . "mailassoc` SET
        `status`        = '{$flag}'
        WHERE `mailID` IN(" . mswSQL($fid). ")
        AND `staffID`   = '{$staff}'
        ", __file__, __line__);
        break;
    }
    return mswSQL_affrows();
  }
  
  public function getFolderName($d = array()) {
    global $msg_script17;
    $fldr = $msg_script17;
    switch($d['folder']) {
      case 'inbox':
        $fldr = $d['lang'][0];
        break;
      case 'outbox':
        $fldr = $d['lang'][1];
        break;
      case 'bin':
        $fldr = $d['lang'][2];
        break;
      default:
        $d['folder'] = (int) $d['folder'];
        $q = mswSQL_query("SELECT `folder` FROM `" . DB_PREFIX . "mailfolders`
             WHERE `id`    = '{$d['folder']}'
             AND `staffID` = '{$d['staff']}'
             ", __file__, __line__);
        $FD = mswSQL_fetchobj($q);
        $fldr = (isset($FD->folder) ? mswCD($FD->folder) : $msg_script17);
        break;
    }
    return $fldr;
  }

  public function moveTo($folder, $staff) {
    mswSQL_query("UPDATE `" . DB_PREFIX . "mailassoc` SET
    `folder`        = '" . mswSQL($folder) . "'
    WHERE `mailID` IN(" . (!empty($_POST['del']) ? mswSQL(implode(',', $_POST['del'])) : '0'). ")
    AND `staffID`   = '{$staff}'
    ", __file__, __line__);
    return mswSQL_affrows();
  }

  public function delete($staff) {
    mswSQL_query("DELETE FROM `" . DB_PREFIX . "mailassoc`
    WHERE `mailID` IN(" . (!empty($_POST['del']) ? mswSQL(implode(',', $_POST['del'])) : '0'). ")
    AND `staffID`   = '{$staff}'
    ", __file__, __line__);
    $rows = mswSQL_affrows();
    // Any messages not attached to folders are removed..
    mailBox::assocChecker();
    return $rows;
  }

  public function emptyBin($staff) {
    mswSQL_query("DELETE FROM `" . DB_PREFIX . "mailassoc`
    WHERE `staffID` = '{$staff}'
    AND `folder`    = 'bin'
    ", __file__, __line__);
    // Any messages not attached to folders are removed..
    mailBox::assocChecker();
  }

  public function assocChecker() {
    mswSQL_query("DELETE FROM `" . DB_PREFIX . "mailbox`
    WHERE (SELECT count(*) FROM `" . DB_PREFIX . "mailassoc`
     WHERE `" . DB_PREFIX . "mailassoc`.`mailID` = `" . DB_PREFIX . "mailbox`.`id`
    ) = 0
    ", __file__, __line__);
    if (mswSQL_rows('mailbox') == 0) {
      mswSQL_truncate(array('mailbox','mailassoc','mailreplies','mailfolders'), true);
    }
  }

  public function perms() {
    $users = array();
    $ID    = (int) $_GET['msg'];
    $qAs = mswSQL_query("SELECT `staffID` FROM `" . DB_PREFIX . "mailassoc`
           WHERE `mailID` = '{$ID}'
           ", __file__, __line__);
    while ($MA = mswSQL_fetchobj($qAs)) {
      $users[] = $MA->staffID;
    }
    return $users;
  }

}

?>