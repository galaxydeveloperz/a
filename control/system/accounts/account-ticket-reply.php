<?php

/* System - Accounts - Ticket Reply
----------------------------------------------------------*/

$MSTICKET->upload = $MSUPL;

if (defined('AJAX_HANDLER') && isset($LI_ACC->id)) {
  $tType = (isset($_POST['ticketType']) && in_array($_POST['ticketType'], array('ticket','dispute')) ? $_POST['ticketType'] : 'ticket');
  $tkID  = (isset($_POST['ticketID']) ? (int) $_POST['ticketID'] : '0');
  if ($tkID > 0) {
    switch($tType) {
      case 'ticket':
        $T = mswSQL_table('tickets', 'id', $tkID, 'AND `visitorID` = \'' . $LI_ACC->id . '\' AND `spamFlag` = \'no\' AND `isDisputed` = \'no\'');
        break;
      case 'dispute':
        $T = mswSQL_table('tickets', 'id', $tkID, 'AND `visitorID` = \'' . $LI_ACC->id . '\' AND `spamFlag` = \'no\' AND `isDisputed` = \'yes\'');
        if (!isset($T->id)) {
          // Check if this user is in the dispute list...
          $PRIV = mswSQL_table('disputes', 'visitorID', $LI_ACC->id, 'AND `ticketID` = \'' . $tkID . '\'');
          // If privileges allow viewing of dispute, requery without email..
          if (isset($PRIV->id)) {
            $T = mswSQL_table('tickets', 'id', $tkID);
          }
        }
        break;
    }
    // If ticket ok, proceed..
    if (isset($T->id) && $T->assignedto != 'waiting') {
      if ($_POST['comments'] == '') {
        $eFields[] = $msadminlang3_1createticket[4];
      }
      // Attachments..
      if ($SETTINGS->attachment == 'yes' && !empty($_FILES['file']['tmp_name'])) {
        $attCnt  = count($_FILES['file']['tmp_name']);
        // Check limit..
        if (LICENCE_VER == 'locked' && $attCnt > RESTR_ATTACH) {
          $countOfBoxes = RESTR_ATTACH;
        }
        $attachE = array();
        for ($i = 0; $i < (isset($countOfBoxes) ? $countOfBoxes : $attCnt); $i++) {
          if ($SETTINGS->attachboxes > 1) {
            $fname = $_FILES['file']['name'][$i];
            $ftemp = $_FILES['file']['tmp_name'][$i];
            $fsize = $_FILES['file']['size'][$i];
            $fmime = $_FILES['file']['type'][$i];
          } else {
            $fname = $_FILES['file']['name'];
            $ftemp = $_FILES['file']['tmp_name'];
            $fsize = $_FILES['file']['size'];
            $fmime = $_FILES['file']['type'];
          }
          if ($fname && $ftemp && $fsize > 0) {
            if (!$MSTICKET->size($fsize)) {
              $attachE[] = str_replace(array('{file}', '{max}'),array(mswSH($fname),mswFSC($SETTINGS->maxsize)),$msadminlang3_1createticket[6]);
            } else {
              if (!$MSTICKET->type($fname)) {
                $attachE[] = str_replace(array('{file}', '{allowed}'),array(mswSH($fname),str_replace(array('|','.'),array(', ',''), $SETTINGS->filetypes)),$msadminlang3_1createticket[7]);
              } else {
                $ticketAttachments[$i]['ext']  = (strpos($fname, '.') !== false ? strrchr(strtolower($fname), '.') : '');
                $ticketAttachments[$i]['temp'] = $ftemp;
                $ticketAttachments[$i]['size'] = $fsize;
                $ticketAttachments[$i]['name'] = $fname;
                $ticketAttachments[$i]['type'] = $fmime;
              }
            }
          }
        }
        // If error, clear all attachment temp files..
        if (!empty($attachE)) {
          for ($i = 0; $i < count($_FILES['file']['tmp_name']); $i++) {
            @unlink($_FILES['file']['tmp_name'][$i]);
          }
          $ticketAttachments = array();
          $eFields[]         = implode('<br>', $attachE);
        }
      }
      // Check required custom fields..
      $customCheckFields = $MSFIELDS->check('reply', $T->department, $LI_ACC->id);
      if (!empty($customCheckFields)) {
        $eFields[] = str_replace('{count}', count($customCheckFields), $msadminlang3_1createticket[8]) . '<hr>' . implode('<br>', $customCheckFields);
      }
      // All ok?
      if (empty($eFields)) {
        // Add reply..
        $replyID = $MSTICKET->reply(array(
          'ticket' => $T->id,
          'visitor' => $LI_ACC->id,
          'quoteBody' => '',
          'comments' => $_POST['comments'],
          'repType' => 'visitor',
          'ip' => mswSQL(mswIP()),
          'disID' => (isset($PRIV->id) ? $LI_ACC->id : '0')
        ));
        // Proceed if ok..
        if ($replyID > 0) {
          // Add attachments..
          if ($SETTINGS->attachment == 'yes' && !empty($ticketAttachments)) {
            for ($i = 0; $i < count($ticketAttachments); $i++) {
              $a_name = $ticketAttachments[$i]['name'];
              $a_temp = $ticketAttachments[$i]['temp'];
              $a_size = $ticketAttachments[$i]['size'];
              $a_mime = $ticketAttachments[$i]['type'];
              if ($a_name && $a_temp && $a_size > 0) {
                $atID = $MSTICKET->addAttachment(array(
                  'temp' => $a_temp,
                  'name' => $a_name,
                  'size' => $a_size,
                  'mime' => $a_mime,
                  'tID' => $T->id,
                  'rID' => $replyID,
                  'dept' => $T->department,
                  'incr' => $i
                ));
                $attString[] = $SETTINGS->scriptpath . '/?attachment=' . $atID[0];
              }
            }
          }
          // History log..
          $MSTICKET->historyLog($T->id, str_replace(array(
            '{visitor}',
            '{id}'
          ), array(
            mswSH($LI_ACC->name),
            $replyID
          ), $msg_ticket_history['vis-reply-add']));
          // Dispute ticket or standard operations..
          switch ($T->isDisputed) {
            case 'no':
              // Was ticket closed..
              if (isset($_POST['close'])) {
                $closeRrows = $MSTICKET->openclose($T->id, 'close');
                // History if affected rows..
                if ($closeRrows > 0) {
                  $MSTICKET->historyLog($T->id, str_replace('{user}', mswSH($LI_ACC->name), $msg_ticket_history['vis-ticket-close']));
                  // Should we switch emails off?
                  if ($SETTINGS->closenotify == 'yes') {
                    define('EMAILS_OFF', 1);
                  }
                }
              }
              break;
            default:
              break;
          }
          // Pass ticket number as custom mail header..
          $MSMAIL->xheaders['X-TicketNo'] = mswTicketNumber($T->id, $SETTINGS->minTickDigits, $T->tickno);
          // Mail tags..
          if (!defined('EMAILS_OFF')) {
            $MSMAIL->addTag('{ACC_NAME}', $LI_ACC->name);
            $MSMAIL->addTag('{TICKET}', mswTicketNumber($T->id, $SETTINGS->minTickDigits, $T->tickno));
            $MSMAIL->addTag('{SUBJECT}', $T->subject);
            $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($_POST['comments']));
            $MSMAIL->addTag('{DEPT}', $MSYS->department($T->department, $msg_script30));
            $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($T->priority));
            $MSMAIL->addTag('{STATUS}', $MSYS->status((isset($closeRrows) && $closeRrows > 0 ? 'close' : $T->ticketStatus), $ticketStatusSel));
            $MSMAIL->addTag('{ATTACHMENTS}', (!empty($attString) ? implode(mswNL(), $attString) : $msg_script17));
            $MSMAIL->addTag('{CUSTOM}', $MSFIELDS->email($T->id, $replyID));
            $MSMAIL->addTag('{ID}', $T->id);
            // Send message to support staff..
            if ($T->assignedto && $T->assignedto != 'waiting') {
              $sqlClause = 'WHERE `userID` IN(' . $T->assignedto . ') AND `admin` = \'no\' AND `notify` = \'yes\'';
            } else {
              $sqlClause = 'WHERE `deptID` = \'' . $T->department . '\' AND `admin` = \'no\' AND `notify` = \'yes\'';
            }
            $qU = mswSQL_query("SELECT `" . DB_PREFIX . "users`.`name` AS `teamName`,`email`,`email2`,`language` FROM `" . DB_PREFIX . "userdepts`
                  LEFT JOIN `" . DB_PREFIX . "departments`
                  ON `" . DB_PREFIX . "userdepts`.`deptID`  = `" . DB_PREFIX . "departments`.`id`
                  LEFT JOIN `" . DB_PREFIX . "users`
                  ON `" . DB_PREFIX . "userdepts`.`userID`  = `" . DB_PREFIX . "users`.`id`
                  $sqlClause
                  GROUP BY `email`
                  ORDER BY `" . DB_PREFIX . "users`.`name`
                  ", __file__, __line__);
            while ($STAFF = mswSQL_fetchobj($qU)) {
              $langFile = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/ticket-reply.txt';
              $langSet = $SETTINGS->language;
              if ($STAFF->language && file_exists(PATH . 'content/language/' . $STAFF->language . '/mail-templates/ticket-reply.txt')) {
                $langSet = $STAFF->language;
                $langFile = PATH . 'content/language/' . $STAFF->language . '/mail-templates/ticket-reply.txt';
              }
              $MSMAIL->addTag('{NAME}', $STAFF->teamName);
              $MSMAIL->sendMSMail(array(
                'from_email' => $SETTINGS->email,
                'from_name' => $SETTINGS->website,
                'to_email' => $STAFF->email,
                'to_name' => $STAFF->teamName,
                'subject' => str_replace(array(
                  '{website}',
                  '{ticket}'
                ), array(
                  $SETTINGS->website,
                  mswTicketNumber($T->id, $SETTINGS->minTickDigits, $T->tickno)
                ), $emailSubjects['reply-notify']),
                'replyto' => array(
                  'name' => $SETTINGS->website,
                  'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                ),
                'template' => $langFile,
                'language' => $langSet,
                'alive' => 'yes',
                'add-emails' => $STAFF->email2
              ));
            }
            // Now send to admins if ticket assign is off..
            if ($T->assignedto != 'waiting') {
              $qUA = mswSQL_query("SELECT `name`, `email`, `email2`, `language` FROM `" . DB_PREFIX . "users`
                     WHERE `admin` = 'yes'
                     AND `notify`  = 'yes'
                     ORDER BY `id`
                     ", __file__, __line__);
              while ($ASTAFF = mswSQL_fetchobj($qUA)) {
                $langFile = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/ticket-reply.txt';
                $langSet = $SETTINGS->language;
                if ($ASTAFF->language && file_exists(PATH . 'content/language/' . $ASTAFF->language . '/mail-templates/ticket-reply.txt')) {
                  $langSet = $ASTAFF->language;
                  $langFile = PATH . 'content/language/' . $ASTAFF->language . '/mail-templates/ticket-reply.txt';
                }
                $MSMAIL->addTag('{NAME}', $ASTAFF->name);
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $ASTAFF->email,
                  'to_name' => $ASTAFF->name,
                  'subject' => str_replace(array(
                    '{website}',
                    '{ticket}',
                    '{name}'
                  ), array(
                    $SETTINGS->website,
                    mswTicketNumber($T->id, $SETTINGS->minTickDigits, $T->tickno),
                    $ASTAFF->name
                  ), $emailSubjects['reply-notify']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => $langFile,
                  'language' => $langSet,
                  'alive' => 'yes',
                  'add-emails' => $ASTAFF->email2
                ));
              }
            }
          }
          // If this ticket is a dispute, send notification to relevant users..
          if ($T->isDisputed == 'yes') {
            // Check if this ticket was originally opened by imap..
            // If it was, set the reply-to address as the imap address..
            // This is so any replies sent go back to the ticket..
            if ($T->source == 'imap') {
              $IMD = mswSQL_table('imap', 'im_dept', $T->department);
              if (isset($IMD->im_email) && mswIsValidEmail($IMD->im_email)) {
                $replyToAddr = $IMD->im_email;
              }
            }
            // Get all users in this dispute..
            $ticketDisputeUsers = $MSTICKET->disputeUsers($T->id);
            // Add original ticket starter to the mix..
            array_push($ticketDisputeUsers, $T->visitorID);
            // Send, but skip person currently logged in..
            if (!empty($ticketDisputeUsers)) {
              $qDU = mswSQL_query("SELECT `name`,`email`,`language` FROM `" . DB_PREFIX . "portal`
                     WHERE `id` IN(" . mswSQL(implode(',', $ticketDisputeUsers)) . ")
                     AND `id`   != '{$LI_ACC->id}'
                     GROUP BY `email`
                     ORDER BY `name`
                     ", __file__, __line__);
              while ($D_USR = mswSQL_fetchobj($qDU)) {
                $pLang = '';
                $temp  = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/dispute-reply.txt';
                // Get correct language file..
                if (isset($D_USR->language) && file_exists(PATH . 'content/language/' . $D_USR->language . '/mail-templates/dispute-reply.txt')) {
                  $pLang = $D_USR->language;
                  $temp  = PATH . 'content/language/' . $D_USR->language . '/mail-templates/dispute-reply.txt';
                }
                $MSMAIL->addTag('{USER}', $LI_ACC->name);
                $MSMAIL->addTag('{NAME}', $D_USR->name);
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $D_USR->email,
                  'to_name' => $D_USR->name,
                  'subject' => str_replace(array(
                    '{website}',
                    '{ticket}'
                  ), array(
                    $SETTINGS->website,
                    mswTicketNumber($T->id, $SETTINGS->minTickDigits, $T->tickno)
                  ), $emailSubjects['dispute-notify']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => (isset($replyToAddr) ? $replyToAddr : ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email))
                  ),
                  'template' => $temp,
                  'language' => ($pLang ? $pLang : $SETTINGS->language),
                  'alive' => 'yes'
                ));
              }
            }
          }
          $MSMAIL->smtpClose();
          // Finish with message..
          $json = array(
            'status' => 'reload'
          );
        }
      } else {
        $json = array(
          'status' => 'err',
          'msg' => implode('<br>', $eFields)
        );
      }
    }
  }
}

?>