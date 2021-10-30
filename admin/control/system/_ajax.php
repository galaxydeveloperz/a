<?php

/* AJAX
----------------------------------------------------------*/

if (!defined('PARENT') || !isset($_GET['ajax'])) {
  echo '<a href="../../index.php">&raquo;&raquo;</a>';
  exit;
}

// Set limits
if (MS_SET_MEM_ALLOCATION_LIMIT) {
  @ini_set('memory_limit', MS_SET_MEM_ALLOCATION_LIMIT);
}
@set_time_limit(MS_SET_TIME_OUT_LIMIT);

// Load classes not loaded by main system..
include(BASE_PATH . 'control/classes/class.accounts.php');
include(PATH . 'control/classes/class.accounts.php');
include(BASE_PATH . 'control/classes/class.upload.php');
$MSACC           = new accountSystem();
$MSPTL           = new accounts();
$MSUPL           = new msUpload();
$MSPTL->settings = $SETTINGS;
$MSPTL->ssn      = $SSN;
$MSACC->settings = $SETTINGS;
$MSACC->ssn      = $SSN;

$json = array(
  'msg' => 'err',
  'info' => $msadminlang3_1[3],
  'sys' => $msadminlang3_1[2],
  'delconfirm' => 0
);

// Load mail params
include(BASE_PATH . 'control/classes/mailer/mail-init.php');

// Parse based on directive..
switch ($_GET['ajax']) {

  //=========================
  // Mailbox
  //=========================

  case 'mbmove':
  case 'mbread':
  case 'mbunread':
  case 'mbdel':
  case 'mbclear':
  case 'mbcompose':
  case 'mbreply':
  case 'mbfolders':
    include(PATH . 'control/classes/class.mailbox.php');
    $MSMB           = new mailBox();
    $MSMB->settings = $SETTINGS;
    $MSMB->datetime = $MSDT;
    switch($_GET['ajax']) {
      case 'mbmove':
        $_GET['param'] = preg_replace('/[^0-9a-zA-Z]/', '', $_GET['param']);
        $MSMB->moveTo($_GET['param'], $MSTEAM->id);
        $folderName = $MSMB->getFolderName(array(
          'staff' => $MSTEAM->id,
          'folder' => $_GET['param'],
          'lang' => array($msg_mailbox, $msg_mailbox2, $msg_mailbox3)
        ));
        $json = array(
          'msg' => 'ok',
          'infotxt' => str_replace(array('{count}','{folder}'), array((!empty($_POST['del']) ? count($_POST['del']) : '0'), $folderName), $msg_mailbox27)
        );
        $json['buttons'] = array();
        $json['buttons'][] = '<a href="#" onclick="window.location.reload();return false">' . $msgloballang4_3[5] . '</a>';
        $json['buttons'][] = '<a href="?p=mailbox' . ($_GET['param'] != 'inbox' ? '&amp;f=' . $_GET['param'] : '') . '">' . $folderName . '</a>';
        $json['buttons'][] = '<a href="?p=mailbox&amp;new=1">' . $msg_mailbox4 . '</a>';
        break;
      case 'mbread':
      case 'mbunread':
        $MSMB->mark($_GET['ajax'], $MSTEAM->id);
        $json = array(
          'msg' => 'ok',
          'infotxt' => str_replace('{count}', (!empty($_POST['del']) ? count($_POST['del']) : '0'), ($_GET['ajax'] == 'mbread' ? $msg_mailbox25 : $msg_mailbox26)),
          'buttons' => array(
            '<a href="#" onclick="window.location.reload();return false">' . $msgloballang4_3[5] . '</a>',
            '<a href="?p=mailbox&amp;new=1">' . $msg_mailbox4 . '</a>'
          )
        );
        break;
      case 'mbdel':
        if ($MSTEAM->mailDeletion == 'yes' || USER_ADMINISTRATOR == 'yes') {
          $rows = $MSMB->delete($MSTEAM->id);
          $json = array(
            'msg' => 'ok',
            'infotxt' => str_replace('{count}', (!empty($_POST['del']) ? count($_POST['del']) : '0'), $msg_mailbox28),
            'buttons' => array(
              '<a href="#" onclick="window.location.reload();return false">' . $msgloballang4_3[5] . '</a>',
              '<a href="?p=mailbox&amp;new=1">' . $msg_mailbox4 . '</a>'
            )
          );
        }
        break;
      case 'mbclear':
        if ($MSTEAM->mailDeletion == 'yes' || USER_ADMINISTRATOR == 'yes') {
          $MSMB->emptyBin($MSTEAM->id);
          $json = array(
            'msg' => 'ok',
            'infotxt' => $msg_mailbox29,
            'buttons' => array(
              '<a href="#" onclick="window.location.reload();return false">' . $msgloballang4_3[5] . '</a>',
              '<a href="?p=mailbox&amp;new=1">' . $msg_mailbox4 . '</a>'
            )
          );
        }
        break;
      case 'mbcompose':
        if (isset($_POST['subject'],$_POST['message']) && $_POST['subject'] && $_POST['message'] && !empty($_POST['staff'])) {
          foreach ($_POST['staff'] AS $staffID) {
            $id = $MSMB->add(array(
              'staff' => $MSTEAM->id,
              'to' => $staffID,
              'subject' => $_POST['subject'],
              'message' => $_POST['message']
            ));
            // Proceed if added ok..
            // Are we sending notification to staff mailbox?
            if ($id > 0 && $MSTEAM->mailCopy == 'yes') {
              $USR = mswSQL_table('users', 'id', $staffID, '', '`name`,`email`,`email2`,`notify`,`language`');
              if (isset($USR->name) && $USR->notify == 'yes') {
                $langFile = BASE_PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/mailbox-notification.txt';
                $langSet = $SETTINGS->language;
                if ($USR->language && file_exists(BASE_PATH . 'content/language/' . $USR->language . '/mail-templates/mailbox-notification.txt')) {
                  $langSet = $USR->language;
                  $langFile = BASE_PATH . 'content/language/' . $USR->language . '/mail-templates/mailbox-notification.txt';
                }
                $MSMAIL->addTag('{NAME}', $USR->name);
                $MSMAIL->addTag('{SENDER}', $MSTEAM->name);
                // Send mail..
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $USR->email,
                  'to_name' => $USR->name,
                  'subject' => str_replace(array(
                    '{website}',
                    '{user}'
                  ), array(
                    $SETTINGS->website,
                    $MSTEAM->name
                  ), $emailSubjects['mailbox-notify']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => $langFile,
                  'language' => $langSet,
                  'add-emails' => $USR->email2,
                  'alive' => 'yes'
                ));
              }
            }
          }
          $MSMAIL->smtpClose();
          $json = array(
            'msg' => 'ok',
            'infotxt' => $msg_mailbox9,
            'buttons' => array(
              '<a href="?p=mailbox&amp;f=outbox">' . $msg_mailbox2 . '</a>',
              '<a href="?p=mailbox&amp;new=1">' . $msg_mailbox4 . '</a>'
            )
          );
        } else {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msgadminlang3_1mailbox[5]
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'mbreply':
        if (isset($_POST['message']) && $_POST['message'] && isset($_POST['msgID'])) {
          // Get other person in message..
          $MID = (int) $_POST['msgID'];
          $OT  = mswSQL_table('mailassoc', 'mailID', $MID, 'AND `staffID` != \'' . $MSTEAM->id . '\'');
          if (isset($OT->staffID)) {
            $id = $MSMB->reply(array(
              'staff' => $MSTEAM->id,
              'to' => $OT->staffID,
              'id' => $MID,
              'message' => $_POST['message']
            ));
            // Proceed if added ok..
            // Are we sending notification to staff mailbox?
            if ($id > 0 && $MSTEAM->mailCopy == 'yes') {
              $USR = mswSQL_table('users', 'id', $OT->staffID, '', '`name`,`email`,`email2`,`notify`,`language`');
              if (isset($USR->name) && $USR->notify == 'yes') {
                $langFile = BASE_PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/mailbox-notification-reply.txt';
                $langSet = $SETTINGS->language;
                if ($USR->language && file_exists(BASE_PATH . 'content/language/' . $USR->language . '/mail-templates/mailbox-notification-reply.txt')) {
                  $langSet = $USR->language;
                  $langFile = BASE_PATH . 'content/language/' . $USR->language . '/mail-templates/mailbox-notification-reply.txt';
                }
                $MSMAIL->addTag('{NAME}', $USR->name);
                $MSMAIL->addTag('{SENDER}', $MSTEAM->name);
                $MSMAIL->addTag('{TOPIC}', $_POST['subject']);
                // Send mail..
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $USR->email,
                  'to_name' => $USR->name,
                  'subject' => str_replace(array(
                    '{website}',
                    '{user}'
                  ), array(
                    $SETTINGS->website,
                    $MSTEAM->name
                  ), $emailSubjects['mailbox-notify']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => $langFile,
                  'language' => $langSet,
                  'add-emails' => $USR->email2
                ));
              }
            }
            $json = array(
              'msg' => 'ok',
              'infotxt' => $msg_mailbox31,
              'buttons' => array(
                '<a href="#" onclick="window.location.reload();return false">' . $msgloballang4_3[5] . '</a>',
                '<a href="?p=mailbox&amp;new=1">' . $msg_mailbox4 . '</a>'
              )
            );
          }
        } else {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msgadminlang3_1mailbox[6]
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'mbfolders':
        $MSMB->folders($MSTEAM->id);
        $json = array(
          'msg' => 'ok'
        );
        break;
    }
    if ($json['msg'] != 'err') {
      $json = array(
        'msg' => 'ok',
        'info' => (isset($json['infotxt']) ? $json['infotxt'] : ''),
        'buttons' => (!empty($json['buttons']) ? $json['buttons'] : array()),
        'delconfirm' => (isset($rows) ? $rows : '0')
      );
    }
    break;

  //=========================
  // Tickets
  //=========================

  case 'ticket':
  case 'tickdel':
  case 'tickexp':
  case 'ticknotes':
  case 'tickaccept':
  case 'tickaccept2':
  case 'tickassign':
  case 'tickreply':
  case 'tickrepdel':
  case 'tickcsdel':
  case 'tickedit':
  case 'tickdept':
  case 'tickrepedit':
  case 'tickdispusers':
  case 'tickresponse':
  case 'tickdelhis':
  case 'tickhisexp':
  case 'tickattdel':
  case 'tickopen':
  case 'ticket-action':
  case 'history-entry':
  case 'release-lock':
  case 'tickdraft-save':
  case 'tickdraft-load':
    $improws = 0;
    // Priority levels and statuses
    include(BASE_PATH . 'control/system/loader.php');
    switch($_GET['ajax']) {
      case 'ticket':
        // Call the relevant classes..
        include_once(BASE_PATH . 'control/classes/class.tickets.php');
        include_once(BASE_PATH . 'control/classes/class.fields.php');
        $MSPTICKETS           = new tickets();
        $MSCFMAN              = new customFieldManager();
        $MSPTICKETS->settings = $SETTINGS;
        $MSPTICKETS->datetime = $MSDT;
        $MSPTICKETS->upload   = $MSUPL;
        $MSCFMAN->dt          = $MSDT;
        if ($_POST['subject'] && $_POST['comments'] && $_POST['name'] && mswIsValidEmail($_POST['email'])) {
          // Check if account exists for email address..
          $PORTAL = mswSQL_table('portal', 'email', mswSQL($_POST['email']));
          // Check language..
          if (isset($_PORTAL->id) && $PORTAL->language && file_exists(LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-add-ticket.txt')) {
            $mailT = LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-add-ticket.txt';
            $pLang = $PORTAL->language;
          } else {
            $mailT = LANG_PATH . 'admin-add-ticket.txt';
          }
          $pass  = '';
          $ipAdr = (isset($PORTAL->ip) ? $PORTAL->ip : '');
          // If portal account doesn`t exist, we need to create it..
          if (!isset($PORTAL->id)) {
            $pass   = $MSACC->ms_generate();
            $mailT  = LANG_PATH . 'admin-add-ticket-new.txt';
            $userID = $MSACC->add(array(
              'name' => $_POST['name'],
              'email' => $_POST['email'],
              'pass' => $pass,
              'enabled' => 'yes',
              'verified' => 'yes',
              'timezone' => '',
              'ip' => '',
              'notes' => '',
              'language' => (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language)
            ));
          }
          // Add ticket to database..
          if ((isset($userID) && $userID > 0) || isset($PORTAL->id)) {
            $ID = $MSPTICKETS->add(array(
              'dept' => (int) $_POST['dept'],
              'assigned' => (isset($_POST['waiting']) ? 'waiting' : (!empty($_POST['assigned']) ? implode(',', $_POST['assigned']) : '')),
              'visitor' => (isset($userID) ? $userID : $PORTAL->id),
              'subject' => $_POST['subject'],
              'quoteBody' => '',
              'comments' => $_POST['comments'],
              'priority' => $_POST['priority'],
              'ticketStatus' => $_POST['status'],
              'ip' => $ipAdr,
              'notes' => $_POST['notes'],
              'disputed' => 'no'
            ));
            // Add attachments, history, send emails..
            if ($ID > 0) {
              $ticketNumber = $MSPTICKETS->ticket($ID);
              // Attachments..
              $attString = array();
              if (!empty($_FILES['file']['tmp_name'])) {
                for ($i = 0; $i < count($_FILES['file']['tmp_name']); $i++) {
                  $a_name = $_FILES['file']['name'][$i];
                  $a_temp = $_FILES['file']['tmp_name'][$i];
                  $a_size = $_FILES['file']['size'][$i];
                  $a_mime = $_FILES['file']['type'][$i];
                  if ($a_name && $a_temp && $a_size > 0) {
                    $atID  = $MSPTICKETS->addAttachment(array(
                      'temp' => $a_temp,
                      'name' => $a_name,
                      'size' => $a_size,
                      'mime' => $a_mime,
                      'tID' => $ID,
                      'rID' => 0,
                      'dept' => $_POST['dept'],
                      'incr' => $i
                    ));
                    $attString[] = $SETTINGS->scriptpath . '/?attachment=' . $atID[0];
                  }
                }
              }
              // Log..
              $MSTICKET->historyLog($ID, str_replace(array(
                '{user}'
              ), array(
                $MSTEAM->name
              ), $msg_ticket_history['new-ticket-admin']));
              // Everything in the post array..
              foreach ($_POST AS $key => $value) {
                if (!is_array($value)) {
                  $MSMAIL->addTag('{' . strtoupper($key) . '}', $MSBB->cleaner($value));
                }
              }
              // Pass ticket number as custom mail header..
              $MSMAIL->xheaders['X-TicketNo'] = mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber);
              // Send notification to visitor if enabled..
              if (isset($_POST['accMail']) && !in_array($_POST['status'], array('close','closed'))) {
                // Tags..
                $MSMAIL->addTag('{NAME}', $_POST['name']);
                $MSMAIL->addTag('{TITLE}', $_POST['subject']);
                $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($_POST['comments']));
                $MSMAIL->addTag('{EMAIL}', $_POST['email']);
                $MSMAIL->addTag('{PASSWORD}', $pass);
                $MSMAIL->addTag('{ID}', $ID);
                $MSMAIL->sendMSMail(array(
                  'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
                  'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
                  'to_email' => $_POST['email'],
                  'to_name' => $_POST['name'],
                  'subject' => str_replace(array(
                    '{website}',
                    '{ticket}'
                  ), array(
                    $SETTINGS->website,
                    mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber)
                  ), $emailSubjects['new-ticket']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => $mailT,
                  'language' => (isset($pLang) ? $pLang : (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language)),
                  'alive' => 'yes'
                ));
              }
              // Send notification to support staff..
              // If ticket is waiting assignment, no emails are sent..
              if (isset($_POST['assignMail']) && !isset($_POST['waiting']) && !in_array($_POST['status'], array('close','closed'))) {
                // Are we notifying staff who are assigned to this ticket?
                $userList = array();
                if (!empty($_POST['assigned'])) {
                  $as = mswSQL(implode(',', $_POST['assigned']));
                  $q = mswSQL_query("SELECT `id`,`name`,`email`,`email2`,`language` FROM `" . DB_PREFIX . "users`
                        WHERE `id`  IN({$as})
                        AND `id`    != '{$MSTEAM->id}'
                        AND `admin`  = 'no'
                        AND `notify` = 'yes'
                        ORDER BY `id`
                        ", __file__, __line__);
                  while ($USR = mswSQL_fetchobj($q)) {
                    $userList[$USR->id] = array(
                      $USR->name,
                      $USR->email,
                      $USR->email2,
                      $USR->language
                    );
                  }
                  $mailT = 'admin-ticket-assign.txt';
                } else {
                  $q = mswSQL_query("SELECT `" . DB_PREFIX . "users`.`id` AS `usrID`,`name`,`email`,`email2`,`language` FROM `" . DB_PREFIX . "userdepts`
                       LEFT JOIN `" . DB_PREFIX . "users`
                       ON `" . DB_PREFIX . "userdepts`.`userID`  = `" . DB_PREFIX . "users`.`id`
                       WHERE `deptID`                        = '{$_POST['dept']}'
                       AND `" . DB_PREFIX . "users`.`id`    != '{$MSTEAM->id}'
                       AND `admin`                           = 'no'
                       AND `notify`                          = 'yes'
                       GROUP BY `" . DB_PREFIX . "userdepts`.`userID`
                       ORDER BY `" . DB_PREFIX . "userdepts`.`userID`
                       ", __file__, __line__);
                  while ($USR = mswSQL_fetchobj($q)) {
                    $userList[$USR->usrID] = array(
                      $USR->name,
                      $USR->email,
                      $USR->email2,
                      $USR->language
                    );
                  }
                  $mailT = 'admin-add-ticket-staff-notify.txt';
                }
                // Tags..
                $MSMAIL->addTag('{TITLE}', $_POST['subject']);
                $MSMAIL->addTag('{TICKETS}', str_replace(array(
                  '{id}',
                  '{subject}'
                ), array(
                  mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber),
                  $_POST['subject']
                ), $msg_assign7));
                $MSMAIL->addTag('{TEAM_NAME}', $MSTEAM->name);
                $MSMAIL->addTag('{ASSIGNEE}', $MSTEAM->name);
                $MSMAIL->addTag('{TICKET}', mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber));
                $MSMAIL->addTag('{ACC_NAME}', $_POST['name']);
                $MSMAIL->addTag('{ACC_EMAIL}', $_POST['email']);
                $MSMAIL->addTag('{SUBJECT}', $_POST['subject']);
                $MSMAIL->addTag('{DEPT}', $MSYS->department($_POST['dept'], $msg_script30));
                $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($_POST['priority']));
                $MSMAIL->addTag('{STATUS}', $MSYS->status($_POST['status'], $ticketStatusSel));
                $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($_POST['comments']));
                $MSMAIL->addTag('{CUSTOM}', $MSCFMAN->email($ID, 0));
                $MSMAIL->addTag('{ATTACHMENTS}', (!empty($attString) ? implode(mswNL(), $attString) : $msg_script17));
                $MSMAIL->addTag('{ID}', $ID);
                // Anyone to send a message to..
                if (!empty($userList)) {
                  foreach ($userList AS $k => $v) {
                    $teamID = $k;
                    $name   = $v[0];
                    $email  = $v[1];
                    $email2 = $v[2];
                    $mlang  = $v[3];
                    $langFile = BASE_PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/' . $mailT;
                    $langSet = $SETTINGS->language;
                    if ($mlang && file_exists(BASE_PATH . 'content/language/' . $mlang . '/mail-templates/' . $mailT)) {
                      $langSet = $mlang;
                      $langFile = BASE_PATH . 'content/language/' . $mlang . '/mail-templates/' . $mailT;
                    }
                    $MSMAIL->addTag('{NAME}', $name);
                    $MSMAIL->sendMSMail(array(
                      'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
                      'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
                      'to_email' => $email,
                      'to_name' => $name,
                      'subject' => str_replace(array(
                        '{website}',
                        '{ticket}'
                      ), array(
                        $SETTINGS->website,
                        mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber)
                      ), $emailSubjects['new-ticket-team']),
                      'replyto' => array(
                        'name' => $SETTINGS->website,
                        'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                      ),
                      'template' => $langFile,
                      'language' => $langSet,
                      'alive' => 'yes',
                      'add-emails' => $email2
                    ));
                  }
                }
                // Send mail to admins if applicable..
                // Applies to department level filtering only, not assigned..
                if (empty($_POST['assigned'])) {
                  $qUA = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "users`
                         WHERE `admin` = 'yes'
                         AND `notify`  = 'yes'
                         AND `id`     != '{$MSTEAM->id}'
                         ORDER BY `id`
                         ", __file__, __line__);
                  while ($ASTAFF = mswSQL_fetchobj($qUA)) {
                    $langFile = BASE_PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/' . $mailT;
                    $langSet = $SETTINGS->language;
                    if ($ASTAFF->language && file_exists(BASE_PATH . 'content/language/' . $ASTAFF->language . '/mail-templates/' . $mailT)) {
                      $langSet = $ASTAFF->language;
                      $langFile = BASE_PATH . 'content/language/' . $ASTAFF->language . '/mail-templates/' . $mailT;
                    }
                    $MSMAIL->addTag('{NAME}', $ASTAFF->name);
                    $MSMAIL->sendMSMail(array(
                      'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
                      'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
                      'to_email' => $ASTAFF->email,
                      'to_name' => $ASTAFF->name,
                      'subject' => str_replace(array(
                        '{website}',
                        '{ticket}'
                      ), array(
                        $SETTINGS->website,
                        mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber)
                      ), $emailSubjects['new-ticket-team']),
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
              // Log for closed..
              if (in_array($_POST['status'], array('close','closed'))) {
                $MSTICKET->historyLog($ID, str_replace(array(
                  '{user}'
                ), array(
                  $MSTEAM->name
                ), $msg_ticket_history['new-ticket-admin-' . $_POST['status']]));
              }
              // Redirect to ticket..
              $json = array(
                'msg' => 'ok',
                'field' => 'redirect',
                'redirect' => 'index.php?p=edit-ticket&showAdd=yes&id=' . $ID
              );
              echo $JSON->encode($json);
              exit;
            }
          }
          $MSMAIL->smtpClose();
        } else {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msadminlang3_1adminaddticket[0]
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'tickdel':
        if (USER_DEL_PRIV == 'yes') {
          $tick = $MSTICKET->deleteTickets();
          $rows = (!empty($_POST['del']) ? count($_POST['del']) : '0');
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'tickexp':
        include_once(BASE_PATH . 'control/classes/system/class.download.php');
        $MSDL = new msDownload();
        $file = $MSTICKET->exportTicketStats($MSDT, $MSDL);
        switch($file) {
          case 'err':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => str_replace('{path}', PATH . 'export', $msadminlang3_1backup[0])
            );
            echo $JSON->encode($json);
            exit;
            break;
          case 'none':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1[8]
            );
            echo $JSON->encode($json);
            exit;
            break;
          default:
            $json = array(
              'msg' => 'ok-dl',
              'file' => ADMIN_FLDR . '/export/' . basename($file),
              'type' => 'text/csv'
            );
            echo $JSON->encode($json);
            exit;
            break;
        }
        break;
      case 'ticknotes':
        $ID   = (isset($_GET['id']) ? (int) $_GET['id'] : '0');
        if ($ID > 0) {
          $rows = $MSTICKET->updateNotes($ID);
          // History log..
          if ($rows > 0) {
            $MSTICKET->historyLog($ID, str_replace(array(
              '{user}'
            ), array(
              $MSTEAM->name
            ), $msg_ticket_history['ticket-notes-edit']));
          }
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'tickaccept':
      case 'tickaccept2':
        $rows = $MSTICKET->notSpam();
        // If rows were affected, write log for each ticket and send relevant emails..
        if ($rows > 0) {
          foreach ($_POST['del'] AS $tID) {
            $replyToAddr = '';
            $MSTICKET->historyLog($tID, str_replace(array(
              '{user}'
            ), array(
              $MSTEAM->name
            ), $msg_ticket_history['ticket-spam-accept']));
            // Load data..
            if ($_GET['ajax'] == 'tickaccept') {
              $ST     = mswSQL_table('tickets', 'id', $tID);
              $PORTAL = mswSQL_table('portal', 'id', $ST->visitorID);
              // Pass ticket number as custom mail header..
              $MSMAIL->xheaders['X-TicketNo'] = mswTicketNumber($tID, $SETTINGS->minTickDigits, $ST->tickno);
              // Mail tags..
              $MSMAIL->addTag('{ACC_NAME}', $PORTAL->name);
              $MSMAIL->addTag('{ACC_EMAIL}', $PORTAL->email);
              $MSMAIL->addTag('{SUBJECT}', $MSBB->cleaner($ST->subject));
              $MSMAIL->addTag('{TICKET}', mswTicketNumber($tID, $SETTINGS->minTickDigits, $ST->tickno));
              $MSMAIL->addTag('{DEPT}', $MSYS->department($ST->department, $msg_script30));
              $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($ST->priority));
              $MSMAIL->addTag('{STATUS}', $MSYS->status($ST->ticketStatus, $ticketStatusSel));
              $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($ST->comments));
              $MSMAIL->addTag('{ATTACHMENTS}', $MSTICKET->attachList($tID));
              $MSMAIL->addTag('{ID}', $tID);
              $MSMAIL->addTag('{CUSTOM}', $msg_script17);
              // Is this ticket going to be assigned?
              // Send to all none admins except logged in staff..
              if ($ST->assignedto != 'waiting') {
                $qU = mswSQL_query("SELECT `" . DB_PREFIX . "users`.`name` AS `teamName`,`email`,`email2`,`language` FROM `" . DB_PREFIX . "userdepts`
                      LEFT JOIN `" . DB_PREFIX . "departments`
                      ON `" . DB_PREFIX . "userdepts`.`deptID`  = `" . DB_PREFIX . "departments`.`id`
                      LEFT JOIN `" . DB_PREFIX . "users`
                      ON `" . DB_PREFIX . "userdepts`.`userID`  = `" . DB_PREFIX . "users`.`id`
                      WHERE `deptID`  = '{$ST->department}'
                      AND `admin`   = 'no'
                      AND `notify`  = 'yes'
                      AND `id`     != '{$MSTEAM->id}'
                      GROUP BY `email`
                      ORDER BY `" . DB_PREFIX . "users`.`name`
                      ", __file__, __line__);
                while ($STAFF = mswSQL_fetchobj($qU)) {
                  $langFile = BASE_PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/mail-templates/new-ticket-staff.txt';
                  $langSet = $SETTINGS->language;
                  if ($STAFF->language && file_exists(BASE_PATH . 'content/language/' . $STAFF->language . '/mail-templates/mail-templates/new-ticket-staff.txt')) {
                    $langSet = $STAFF->language;
                    $langFile = BASE_PATH . 'content/language/' . $STAFF->language . '/mail-templates/mail-templates/new-ticket-staff.txt';
                  }
                  $MSMAIL->addTag('{NAME}', $STAFF->teamName);
                  $MSMAIL->sendMSMail(array(
                    'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
                    'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
                    'to_email' => $STAFF->email,
                    'to_name' => $STAFF->teamName,
                    'subject' => str_replace(array(
                      '{website}',
                      '{ticket}'
                    ), array(
                      $SETTINGS->website,
                      mswTicketNumber($tID, $SETTINGS->minTickDigits, $ST->tickno)
                    ), $emailSubjects['new-ticket']),
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
              }
              // Send to admins, except logged in user if applicable.
              $qUA = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "users`
                     WHERE `admin` = 'yes'
                     AND `notify`  = 'yes'
                     AND `id`     != '{$MSTEAM->id}'
                     ORDER BY `id`
                     ", __file__, __line__);
              while ($ASTAFF = mswSQL_fetchobj($qUA)) {
                $langFile = BASE_PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-admin.txt';
                $langSet = $SETTINGS->language;
                if ($ASTAFF->language && file_exists(BASE_PATH . 'content/language/' . $ASTAFF->language . '/mail-templates/new-ticket-admin.txt')) {
                  $langSet = $ASTAFF->language;
                  $langFile = BASE_PATH . 'content/language/' . $ASTAFF->language . '/mail-templates/new-ticket-admin.txt';
                }
                $MSMAIL->addTag('{NAME}', $ASTAFF->name);
                $MSMAIL->sendMSMail(array(
                  'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
                  'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
                  'to_email' => $ASTAFF->email,
                  'to_name' => $ASTAFF->name,
                  'subject' => str_replace(array(
                    '{website}',
                    '{ticket}'
                  ), array(
                    $SETTINGS->website,
                    mswTicketNumber($tID, $SETTINGS->minTickDigits, $ST->tickno)
                  ), $emailSubjects['new-ticket']),
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
              // Notify visitor..
              $IDEPT = mswSQL_table('imap', 'im_dept', $ST->department, '', '`im_email`');
              if (isset($IDEPT->im_email) && $IDEPT->im_email) {
                $replyToAddr = $IDEPT->im_email;
              }
              if (file_exists(BASE_PATH . 'content/language/' . $PORTAL->language . '/mail-templates/new-ticket-visitor.txt')) {
                $mailT = BASE_PATH . 'content/language/' . $PORTAL->language . '/mail-templates/new-ticket-visitor.txt';
                $pLang = $PORTAL->language;
              } else {
                $mailT = BASE_PATH . 'content/language/' . (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language) . '/mail-templates/new-ticket-visitor.txt';
              }
              $MSMAIL->addTag('{NAME}', $PORTAL->name);
              $MSMAIL->sendMSMail(array(
                'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
                'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
                'to_email' => $PORTAL->email,
                'to_name' => $PORTAL->name,
                'subject' => str_replace(array(
                  '{website}',
                  '{ticket}'
                ), array(
                  $SETTINGS->website,
                  mswTicketNumber($tID, $SETTINGS->minTickDigits, $ST->tickno)
                ), $emailSubjects['new-ticket-vis']),
                'replyto' => array(
                  'name' => $SETTINGS->website,
                  'email' => ($replyToAddr ? $replyToAddr : ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email))
                ),
                'template' => $mailT,
                'language' => (isset($pLang) ? $pLang : (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language)),
                'alive' => 'yes'
              ));
            }
          }
          $MSMAIL->smtpClose();
        }
        $json = array(
          'msg' => 'ok'
        );
        break;
      case 'tickassign':
        if (!empty($_POST['users'])) {
          if (!empty($_POST['del'])) {
            $userNotify = array();
            $tickets    = array();
            $accepted   = array();
            $tkInfo     = array();
            foreach ($_POST['del'] AS $ID) {
              if (!empty($_POST['users'][$ID])) {
                // Ticket information..
                $SUPTICK = mswSQL_table('tickets', 'id', $ID);
                // Array of ticket subjects assigned to users..
                foreach ($_POST['users'][$ID] AS $userID) {
                  $tickets[$userID][] = str_replace(array(
                    '{id}',
                    '{subject}'
                  ), array(
                    mswTicketNumber($ID, $SETTINGS->minTickDigits, $SUPTICK->tickno),
                    $SUPTICK->subject
                  ), $msg_assign7);
                  // Skip if it`s the logged in staff member
                  if ($userID != $MSTEAM->id) {
                    $userNotify[] = $userID;
                  }
                }
                // Update ticket..
                $MSTICKET->ticketUserAssign($ID, implode(',', $_POST['users'][$ID]), $msg_ticket_history['assign']);
                $accepted[] = $ID;
                // Prevent further lookups on ticket info and store it in an array
                $tkInfo[$ID] = array(
                  'ticket' => $SUPTICK,
                  'assigned' => $_POST['users'][$ID]
                );
              }
            }
          } else {
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1tickets[0]
            );
            echo $JSON->encode($json);
            exit;
          }
          // Email users..
          $mcon_s = 0;
          if (!empty($userNotify) && !empty($tickets) && isset($_POST['mail'])) {
            $q = mswSQL_query("SELECT `id`,`name`,`email`,`email2`,`language` FROM `" . DB_PREFIX . "users`
                 WHERE `id` IN(" . mswSQL(implode(',', $userNotify)) . ")
                 AND `notify` = 'yes'
                 GROUP BY `id`
                 ORDER BY `name`
                 ", __file__, __line__);
            while ($USERS = mswSQL_fetchobj($q)) {
              $langFile = BASE_PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/admin-ticket-assign.txt';
              $langSet = $SETTINGS->language;
              if ($USERS->language && file_exists(BASE_PATH . 'content/language/' . $USERS->language . '/mail-templates/admin-ticket-assign.txt')) {
                $langSet = $USERS->language;
                $langFile = BASE_PATH . 'content/language/' . $USERS->language . '/mail-templates/admin-ticket-assign.txt';
              }
              $MSMAIL->addTag('{ASSIGNEE}', $MSTEAM->name);
              $MSMAIL->addTag('{NAME}', $USERS->name);
              $MSMAIL->addTag('{TICKETS}', trim(implode(mswNL(), $tickets[$USERS->id])));
              // Send mail..
              $MSMAIL->sendMSMail(array(
                'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
                'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
                'to_email' => $USERS->email,
                'to_name' => $USERS->name,
                'subject' => str_replace(array(
                  '{website}',
                  '{user}'
                ), array(
                  $SETTINGS->website,
                  $MSTEAM->name
                ), $emailSubjects['ticket-assign']),
                'replyto' => array(
                  'name' => $SETTINGS->website,
                  'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                ),
                'template' => $langFile,
                'language' => $langSet,
                'add-emails' => $USERS->email2,
                'alive' => 'yes'
              ));
              ++$mcon_s;
            }
            if (!isset($_POST['vismail'])) {
              $MSMAIL->smtpClose();
            }
          }
          // Send emails to visitors
          if (!empty($accepted) && isset($_POST['vismail'])) {
            // Call the relevant classes..
            include_once(PATH . 'control/classes/class.tickets.php');
            $MSPTICK           = new supportTickets();
            $MSPTICK->settings = $SETTINGS;
            $MSPTICK->dt       = $MSDT;
            foreach ($accepted AS $tickID) {
              if (!empty($tkInfo[$tickID]['ticket']) && !empty($tkInfo[$tickID]['assigned'])) {
                $userTicket = $tkInfo[$tickID]['ticket'];
                $assigned = implode(',', $tkInfo[$tickID]['assigned']);
                $PORTAL = mswSQL_table('portal', 'id', $userTicket->visitorID);
                if (isset($PORTAL->id)) {
                  // Subject..
                  $ticketSbj = str_replace(array(
                    '{subject}',
                    '{ticket}'
                  ), array(
                    $userTicket->subject,
                    mswTicketNumber($userTicket->id, $SETTINGS->minTickDigits, $userTicket->tickno)
                  ), $emailSubjects['ticket-imap-reply']);
                  // Send email..
                  if (isset($PORTAL->language) && file_exists(LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-ticket-assign-visitor.txt')) {
                    $mailT = LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-ticket-assign-visitor.txt';
                    $pLang = $PORTAL->language;
                  } else {
                    $mailT = LANG_PATH . 'admin-ticket-assign-visitor.txt';
                  }
                  $MSMAIL->addTag('{NAME}', $PORTAL->name);
                  $MSMAIL->addTag('{SUBJECT}', $userTicket->subject);
                  $MSMAIL->addTag('{TICKET}', mswTicketNumber($userTicket->id, $SETTINGS->minTickDigits, $userTicket->tickno));
                  $MSMAIL->addTag('{ASSIGNED}', $MSPTICK->assignedTeam($assigned, mswNL()));
                  $MSMAIL->sendMSMail(array(
                    'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
                    'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
                    'to_email' => $PORTAL->email,
                    'to_name' => $PORTAL->name,
                    'subject' => $ticketSbj,
                    'replyto' => array(
                      'name' => $SETTINGS->website,
                      'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                    ),
                    'template' => $mailT,
                    'language' => (isset($pLang) ? $pLang : (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language)),
                    'alive' => 'yes'
                  ));
                  ++$mcon_s;
                }
              }
            }
          }
          if ($mcon_s > 0) {
            $MSMAIL->smtpClose();
          }
          $json = array(
            'msg' => 'ok',
            'accepted' => $accepted,
            'sys' => $msadminlang3_1[2],
            'info' => str_replace('{count}', count($accepted), $msg_assign4),
            'buttons' => array(
              '<a href="?p=assign">' . $msg_assign6 . '</a>',
              '<a href="?p=add">' . $msg_open7 . '</a>'
            )
          );
          include(PATH . 'templates/system/control-btns.php');
          $json['info'] .= $c_b;
          echo $JSON->encode($json);
          exit;
        } else {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msadminlang3_1tickets[0]
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'tickreply':
        define('AJAX_TICK_REPLY', 1);
        if (isset($_POST['comments']) && $_POST['comments']) {
          // Call the relevant classes..
          include_once(BASE_PATH . 'control/classes/class.tickets.php');
          include_once(BASE_PATH . 'control/classes/class.fields.php');
          $MSPTICKETS            = new tickets();
          $MSCFMAN               = new customFieldManager();
          $MSPTICKETS->settings  = $SETTINGS;
          $MSPTICKETS->datetime  = $MSDT;
          $MSPTICKETS->upload    = $MSUPL;
          $MSCFMAN->dt           = $MSDT;
          include(PATH . 'control/system/tickets/ticket-reply.php');
        } else {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msadminlang3_1adminviewticket[15]
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'tickrepdel':
        if (USER_DEL_PRIV == 'yes') {
          $ID = (int) $_GET['param'];
          $RP = mswSQL_table('replies', 'id', $ID);
          $TK = mswSQL_table('tickets', 'id', $RP->ticketID);
          switch ($RP->replyType) {
            case 'admin':
              $NME = mswSQL_table('users', 'id', $RP->replyUser);
              break;
            default:
              $NME = mswSQL_table('portal', 'id', $RP->replyUser);
              break;
          }
          if (isset($TK->id)) {
            $rows = $MSTICKET->deleteReply($RP, $TK, $ID);
            // History log..
            if ($rows > 0) {
              $MSTICKET->historyLog($TK->id, str_replace(array(
                '{user}',
                '{id}',
                '{poster}'
              ), array(
                $MSTEAM->name,
                $ID,
                (isset($NME->name) ? $NME->name : $msg_script17)
              ), $msg_ticket_history['reply-delete']));
            }
          }
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'tickcsdel':
        if (USER_DEL_PRIV == 'yes') {
          $ID = (isset($_GET['param']) ? (int) $_GET['param'] : '0');
          if ($ID > 0) {
            // Get ticket ID and field info..
            $FLD = mswSQL_table('ticketfields', 'id', $ID);
            if (isset($FLD->id)) {
              $rows = $MSTICKET->deleteCF($ID);
              $CF = mswSQL_table('cusfields', 'id', $FLD->fieldID);
              if (isset($CF->id)) {
                // History log..
                if ($rows > 0) {
                  // Write ticket log entry..
                  if ($FLD->replyID > 0) {
                    $action = str_replace(array('{user}', '{field}', '{reply}'), array($MSTEAM->name, $CF->fieldInstructions, $FLD->replyID), $msg_ticket_history['ticket-field-reply-deletion']);
                  } else {
                    $action = str_replace(array('{user}', '{field}'), array($MSTEAM->name, $CF->fieldInstructions), $msg_ticket_history['ticket-field-deletion']);
                  }
                  $MSTICKET->historyLog($FLD->ticketID, $action);
                }
                $json = array(
                  'msg' => 'ok'
                );
              }
            }
          }
        }
        break;
      case 'tickedit':
        if (USER_EDIT_T_PRIV == 'yes') {
          if ((int) $_POST['dept'] == '0') {
            $eFields[] = $msadminlang3_1createticket[2];
          }
          if ($_POST['subject'] == '') {
            $eFields[] = $msadminlang3_1createticket[3];
          }
          if ($_POST['comments'] == '') {
            $eFields[] = $msadminlang3_1createticket[4];
          }
          if (!in_array($_POST['priority'], $levelPrKeys)) {
            $eFields[] = $msadminlang3_1createticket[5];
          }
          if (!in_array($_POST['status'], $statusPrKeys)) {
            $eFields[] = $msticketstatuses4_3[10];
          }
          if (!empty($eFields)) {
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => implode('<br>', $eFields)
            );
          } else {
            $trows = $MSTICKET->updateTicket();
            // Log if affected rows..
            if ($trows > 0) {
              $MSTICKET->historyLog($_POST['id'], str_replace(array(
                '{user}'
              ), array(
                $MSTEAM->name
              ), $msg_ticket_history['edit-ticket']));
            }
            $SUPTICK = mswSQL_table('tickets', 'id', (int) $_POST['id']);
            $link = getTicketLink(array(
              't' => $SUPTICK,
              'l' => array($msg_adheader5,$msg_adheader6,$msg_adheader28,$msg_adheader29,$msg_adheader63,$msg_adheader32),
              's' => $ticketStatusSel
            ));
            $json = array(
              'msg' => 'ok',
              'infotxt' => $msg_viewticket23
            );
            $json['buttons'] = array();
            $json['buttons'][] = '<a href="?p=edit-ticket&amp;id=' . (int) $_POST['id'] . '">' . $msg_script9 . '</a>';
            $json['buttons'][] = '<a href="?p=view-' . ($SUPTICK->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&amp;id=' . (int) $_POST['id'] . '">' . $msg_script10 . '</a>';
            if ($link[0] && $link[1]) {
              $json['buttons'][] = '<a href="' . $link[0] . '">' . $link[1] . '</a>';
            }
            $json['buttons'][] = '<a href="?p=add">' . $msg_dept . '</a>';
          }
        }
        break;
      case 'tickrepedit':
        if (USER_EDIT_R_PRIV == 'yes') {
          if ($_POST['comments'] == '') {
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1createticket[4]
            );
          } else {
            $MSTICKET->updateTicketReply($msg_ticket_history['reply-edit']);
            $json = array(
              'msg' => 'ok',
              'infotxt' => $msg_viewticket38
            );
            $SUPTICK = mswSQL_table('tickets', 'id', (int) $_POST['ticketID']);
            $link = getTicketLink(array(
              't' => $SUPTICK,
              'l' => array($msg_adheader5,$msg_adheader6,$msg_adheader28,$msg_adheader29,$msg_adheader63,$msg_adheader32),
              's' => $ticketStatusSel
            ));
            $json['buttons'] = array();
            $json['buttons'][] = '<a href="?p=edit-reply&amp;id=' . (int) $_POST['replyID'] . '">' . $msg_script9 . '</a>';
            $json['buttons'][] = '<a href="?p=view-' . ($SUPTICK->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&amp;id=' . (int) $_POST['ticketID'] . '">' . $msg_open7 . '</a>';
            if ($link[0] && $link[1]) {
              $json['buttons'][] = '<a href="' . $link[0] . '">' . $link[1] . '</a>';
            }
            $json['buttons'][] = '<a href="?p=add">' . $msg_dept . '</a>';
          }
        }
        break;
      case 'tickdept':
        $fields = '';
        $dept   = (isset($_GET['dp']) ? (int) $_GET['dp'] : '0');
        $tickID = (isset($_GET['id']) ? (int) $_GET['id'] : '0');
        $area   = (!isset($_GET['ar']) ? 'ticket' : (in_array($_GET['ar'], array(
          'ticket',
          'reply',
          'admin'
        )) ? $_GET['ar'] : 'ticket'));
        $isAssign = mswSQL_rows('departments WHERE `id` = \'' . $dept . '\' AND `manual_assign` = \'yes\'');
        // Custom fields..
        $qF = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "cusfields`
              WHERE FIND_IN_SET('{$area}',`fieldLoc`)  > 0
              AND `enField`                            = 'yes'
              AND FIND_IN_SET('{$dept}',`departments`) > 0
              ORDER BY `orderBy`
              ", __file__, __line__);
        if (mswSQL_numrows($qF) > 0) {
          while ($FIELDS = mswSQL_fetchobj($qF)) {
            $html = '';
            if ($tickID > 0) {
              $TF   = mswSQL_table('ticketfields','ticketID',(int) $tickID,' AND `replyID` = \'0\' AND `fieldID` = \'' . $FIELDS->id . '\'');
              $html = (isset($TF->fieldData) ? $TF->fieldData : '');
            }
            switch ($FIELDS->fieldType) {
              case 'textarea':
                $fields .= $MSFM->buildTextArea(mswCD($FIELDS->fieldInstructions), $FIELDS->id, (++$tabIndex), $html);
                break;
              case 'input':
                $fields .= $MSFM->buildInputBox(mswCD($FIELDS->fieldInstructions), $FIELDS->id, (++$tabIndex), $html);
                break;
              case 'calendar':
                $fields .= $MSFM->buildCalBox(mswCD($FIELDS->fieldInstructions), $FIELDS->id, (++$tabIndex), $html);
                break;
              case 'select':
                $fields .= $MSFM->buildSelect(mswCD($FIELDS->fieldInstructions), $FIELDS->id, $FIELDS->fieldOptions, (++$tabIndex), $html);
                break;
              case 'checkbox':
                $fields .= $MSFM->buildCheckBox(mswCD($FIELDS->fieldInstructions), $FIELDS->id, $FIELDS->fieldOptions, $html);
                break;
            }
          }
        }
        $json = array(
          'fields' => $fields,
          'assign' => ($isAssign > 0 ? 'yes' : 'no')
        );
        if ($area == 'ticket') {
          $D = mswSQL_table('departments', 'id', $dept, ' AND `auto_admin` = \'yes\'');
          if (isset($D->id)) {
            $json['subject'] = $D->dept_subject;
            $json['comments'] = $D->dept_comments;
            $json['priority'] = $D->dept_priority;
          }
        }
        echo $JSON->encode($json);
        exit;
        break;
      case 'tickdispusers':
        $tickID = (isset($_POST['disputeID']) ? (int) $_POST['disputeID'] : '0');
        $TICKET = mswSQL_table('tickets', 'id', $tickID);
        $other  = array();
        $new    = array();
        $del    = array();
        if (isset($TICKET->visitorID)) {
          $USER = mswSQL_table('portal', 'id', $TICKET->visitorID);
          if (!empty($_POST['userID']) && $tickID > 0 && isset($USER->id)) {
            // Anything to delete?
            if (!empty($_POST['duser'])) {
              $toGo = array();
              foreach ($_POST['duser'] AS $dduser) {
                $dduser = substr($dduser, 6);
                $D_USER = mswSQL_table('disputes', 'id', (int) $dduser);
                if (isset($D_USER->visitorID)) {
                  $D_PORTAL = mswSQL_table('portal', 'id', $D_USER->visitorID);
                  if (isset($D_PORTAL->id)) {
                    if ($D_PORTAL->name) {
                      $del[] = mswCD($D_PORTAL->name);
                    }
                  }
                }
                $toGo[] = $dduser;
              }
              $MSTICKET->removeDisputeUsersFromTicket($toGo);
            }
            // Loop existing..
            foreach ($_POST['userID'] AS $k) {
              if (substr($k, 0, 2) == 't_') {
                $name   = $USER->name;
                $email  = $USER->email;
                $sbj    = $emailSubjects['dispute-notify'];
                $userID = $USER->id;
              } else {
                $PORTAL = mswSQL_table('portal', 'id', (int) $k);
                if (isset($PORTAL->id)) {
                  $name   = $PORTAL->name;
                  $email  = $PORTAL->email;
                  $sbj    = $emailSubjects['dispute'];
                  $pass   = '';
                  if ($PORTAL->language && file_exists(LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-dispute-user-current.txt')) {
                    $mailT = LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-dispute-user-current.txt';
                    $pLang = $PORTAL->language;
                  } else {
                    $mailT = LANG_PATH . 'admin-dispute-user-current.txt';
                  }
                  $userID  = $PORTAL->id;
                  $other[] = $name;
                } else {
                  $name   = (isset($_POST['nm_' . $k]) ? mswCD($_POST['nm_' . $k]) : '');
                  $email  = (isset($_POST['em_' . $k]) && mswIsValidEmail($_POST['em_' . $k]) ? $_POST['em_' . $k] : '');
                  $sbj    = $emailSubjects['dispute'];
                  if ($name && $email) {
                    $pass   = $MSACC->ms_generate();
                    $mailT  = LANG_PATH . 'admin-dispute-user-new.txt';
                    $userID = $MSPTL->add(array(
                      'name' => $name,
                      'email' => $email,
                      'userPass' => $pass,
                      'enabled' => 'yes',
                      'timezone' => '',
                      'ip' => '',
                      'notes' => ''
                    ));
                    $PORTAL        = new stdclass();
                    $PORTAL->email = $email;
                    $other[]       = $name;
                  }
                }
              }
              if ($name && $email) {
                $send  = (!empty($_POST['notify']) && in_array($k, $_POST['notify']) ? 'yes' : 'no');
                $priv  = (!empty($_POST['priv']) && in_array($k, $_POST['priv']) ? 'yes' : 'no');
                // If this user isn`t in dispute already, add them..
                // Else, just update privileges..
                if (substr($k, 0, 2) != 't_') {
                  if (mswSQL_rows('disputes WHERE `ticketID` = \'' . $tickID . '\' AND `visitorID` = \'' . $userID . '\'') == 0) {
                    $MSTICKET->addDisputeUser($tickID, $userID, $priv);
                    $new[] = $name;
                  } else {
                    $MSTICKET->updateDisputePrivileges($userID, $tickID, 'user', $priv);
                  }
                } else {
                  $MSTICKET->updateDisputePrivileges($userID, $tickID, 'ticket', $priv);
                }
                // Send notification if enabled..
                if (substr($k, 0, 2) != 't_') {
                  if ($send == 'yes') {
                    $MSMAIL->addTag('{NAME}', $name);
                    $MSMAIL->addTag('{TITLE}', $TICKET->subject);
                    $MSMAIL->addTag('{EMAIL}', $email);
                    $MSMAIL->addTag('{PASSWORD}', $pass);
                    $MSMAIL->addTag('{ID}', $tickID);
                    $MSMAIL->addTag('{USER}', $USER->name);
                    $MSMAIL->sendMSMail(array(
                      'from_email' => $SETTINGS->email,
                      'from_name' => $SETTINGS->website,
                      'to_email' => $email,
                      'to_name' => $name,
                      'subject' => str_replace(array(
                        '{website}',
                        '{ticket}'
                      ), array(
                        $SETTINGS->website,
                        mswTicketNumber($tickID, $SETTINGS->minTickDigits, $TICKET->tickno)
                      ), $sbj),
                      'replyto' => array(
                        'name' => $SETTINGS->website,
                        'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                      ),
                      'template' => $mailT,
                      'language' => (isset($pLang) ? $pLang : (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language)),
                      'alive' => 'yes'
                    ));
                  }
                }
              }
            }
            // Send to ticket starter..
            $send    = (!empty($_POST['notify']) && in_array('t_' . $USER->id, $_POST['notify']) ? 'yes' : 'no');
            if ($send == 'yes' && !empty($other)) {
              $pLang = '';
               if ($USER->language && file_exists(LANG_BASE_PATH . $USER->language . '/mail-templates/admin-dispute-notification.txt')) {
                 $pLang = $USER->language;
               }
               $MSMAIL->addTag('{NAME}', $USER->name);
               $MSMAIL->addTag('{TITLE}', $TICKET->subject);
               $MSMAIL->addTag('{PEOPLE}', implode(mswNL(), $other));
               $MSMAIL->addTag('{ID}', $tickID);
               $MSMAIL->sendMSMail(array(
                 'from_email' => $SETTINGS->email,
                 'from_name' => $SETTINGS->website,
                 'to_email' => $USER->email,
                 'to_name' => $USER->name,
                 'subject' => str_replace(array(
                   '{website}',
                   '{ticket}'
                 ), array(
                   $SETTINGS->website,
                   mswTicketNumber($tickID, $SETTINGS->minTickDigits, $TICKET->tickno)
                 ), $emailSubjects['dispute-notify-update']),
                 'replyto' => array(
                   'name' => $SETTINGS->website,
                   'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                 ),
                 'template' => LANG_PATH . 'admin-dispute-notification.txt',
                 'language' => ($pLang ? $pLang : (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language)),
                 'alive' => 'yes'
               ));
            }
            // Anything delete?
            if (!empty($del)) {
              $MSTICKET->historyLog($tickID, str_replace(array(
                '{users}',
                '{admin}'
              ), array(
                implode(', ', $del),
                $MSTEAM->name
              ), $msg_ticket_history['dis-user-rem']));
            }
            // Add new users to ticket history log..
            if (!empty($new)) {
              $MSTICKET->historyLog($tickID, str_replace(array(
                '{users}',
                '{admin}'
              ), array(
                implode(', ', $new),
                $MSTEAM->name
              ), $msg_ticket_history['dis-user-add']));
            }
            $MSMAIL->smtpClose();
          }
          $json = array(
            'msg' => 'ok',
            'sys' => $msadminlang3_1[2],
            'infotxt' => $mssuptickets4_3[0]
          );
          $link = getTicketLink(array(
            't' => $TICKET,
            'l' => array($msg_adheader5,$msg_adheader6,$msg_adheader28,$msg_adheader29,$msg_adheader63,$msg_adheader32),
            's' => $ticketStatusSel
          ));
          $json['buttons'] = array();
          $json['buttons'][] = '<a href="?p=view-dispute&amp;disputeUsers=' . $TICKET->id . '">' . $msgloballang4_3[5] . '</a>';
          $json['buttons'][] = '<a href="?p=edit-ticket&amp;id=' . $TICKET->id . '">' . $msg_script9 . '</a>';
          $json['buttons'][] = '<a href="?p=view-' . ($TICKET->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&amp;id=' . $TICKET->id . '">' . $msg_script10 . '</a>';
          if ($link[0] && $link[1]) {
            $json['buttons'][] = '<a href="' . $link[0] . '">' . $link[1] . '</a>';
          }
          $json['buttons'][] = '<a href="?p=add">' . $msg_dept . '</a>';
        }
        break;
      case 'tickresponse':
        if (isset($_GET['id'])) {
          $SR   = mswSQL_table('responses', 'id', (int) $_GET['id']);
          $json = array(
            'msg' => 'ok',
            'response' => (isset($SR->answer) ? mswCD($SR->answer) : '')
          );
        }
        echo $JSON->encode($json);
        exit;
        break;
      case 'tickdelhis':
        if (USER_DEL_PRIV == 'yes' && isset($_GET['id']) && isset($_GET['t'])) {
          $ID = ($_GET['id'] != 'all' ? (int) $_GET['id'] : 'all');
          $TK = (int) $_GET['t'];
          $MSTICKET->deleteTicketHistory($ID, $TK);
          $json = array(
            'msg' => 'ok',
            'html' => $msg_viewticket111
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'tickhisexp':
        include(BASE_PATH . 'control/classes/system/class.download.php');
        $MSDL = new msDownload();
        $file = $MSTICKET->exportTicketHistory($MSDL, $MSDT);
        switch($file) {
          case 'err':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => str_replace('{path}', PATH . 'export', $msadminlang3_1backup[0])
            );
            echo $JSON->encode($json);
            exit;
            break;
          case 'none':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1[8]
            );
            echo $JSON->encode($json);
            exit;
            break;
          default:
            $json = array(
              'msg' => 'ok-dl',
              'file' => ADMIN_FLDR . '/export/' . basename($file),
              'type' => 'text/csv'
            );
            echo $JSON->encode($json);
            exit;
            break;
        }
        break;
      case 'tickattdel':
        if (USER_DEL_PRIV == 'yes') {
          $ID  = (isset($_GET['param']) ? (int) $_GET['param'] : '0');
          $A   = mswSQL_table('attachments', 'id', $ID);
          if (isset($A->ticketID)) {
            $MSTICKET->deleteAttachments(array(
              $ID
            ));
            // Write ticket log entry..
            if ($A->replyID > 0) {
              $action = str_replace(array('{user}', '{aid}', '{attachment}', '{reply}'), array($MSTEAM->name, $A->id, $A->fileName, $A->replyID), $msg_ticket_history['ticket-attachment-reply-deletion']);
            } else {
              $action = str_replace(array('{user}', '{aid}', '{attachment}'), array($MSTEAM->name, $A->id, $A->fileName), $msg_ticket_history['ticket-attachment-deletion']);
            }
            $MSTICKET->historyLog($A->ticketID, $action);
            $cnt  = mswSQL_rows('attachments WHERE `ticketID` = \'' . $A->ticketID . '\' AND `replyID` = \'' . $A->replyID . '\'');
            $json = array(
              'msg' => 'ok',
              'rep' => $A->replyID,
              'cnt' => $cnt
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        break;
      case 'tickopen':
        if (!empty($_POST['del'])) {
          $rows = $MSTICKET->batchReOpenTickets();
          // Write history entry..
          foreach ($_POST['del'] AS $t) {
            $MSTICKET->historyLog($t, str_replace(array(
              '{user}'
            ), array(
              $MSTEAM->name
            ), $msg_ticket_history['vis-ticket-open']));
          }
          $json = array(
            'msg' => 'ok'
          );
        } else {
          $json = array(
            'msg' => 'err',
            'info' => $msadminlang3_1[26],
            'sys' => $msadminlang3_1[2]
          );
        }
        break;
      case 'ticket-action':
        $json = array(
          'msg' => 'err',
          'info' => $msadminlang3_1[3],
          'sys' => $msadminlang3_1[2]
        );
        if (isset($_GET['id'], $_GET['act'])) {
          $_GET['id'] = (int) $_GET['id'];
          $TK = mswSQL_table('tickets', 'id', $_GET['id']);
          // Check for custom ticket status action..
          if (substr($_GET['act'], 0, 7) == 'status-') {
            $cstStatus = (int) substr($_GET['act'], 7);
            if (isset($ticketStatusSel[$cstStatus][0]) && $cstStatus > 3) {
              $_GET['act'] = 'status-change';
              $_GET['status-change-id'] = $cstStatus;
            }
            $action = (isset($msg_ticket_history['admin-custom-status-change']) ? str_replace(array('{status}','{user}'), array($ticketStatusSel[$cstStatus][0],$MSTEAM->name), $msg_ticket_history['admin-custom-status-change']) : '');
          } else {
            $cstStatus = ($_GET['act'] == 'lock' ? 'closed' : $_GET['act']);
            if (isset($ticketStatusSel[$cstStatus][0])) {
              $action = str_replace(array('{status}','{user}'), array($ticketStatusSel[$cstStatus][0],$MSTEAM->name), $msg_ticket_history['admin-custom-status-change']);
            } else {
              $action = (isset($msg_ticket_history['ticket-status-' . $_GET['act']]) ? $msg_ticket_history['ticket-status-' . $_GET['act']] : '');
            }
          }
          if ($TK->spamFlag == 'yes' && isset($msg_ticket_history['ticket-status-spam-open'])) {
            $action = str_replace('{user}', $MSTEAM->name, $msg_ticket_history['ticket-status-spam-open']);
          }
          if (isset($TK->id) && $action) {
            if ($_GET['act'] == 'close' && USER_CLOSE_PRIV == 'no') {
              $sysError = true;
            }
            if ($_GET['act'] == 'lock' && USER_LOCK_PRIV == 'no') {
              $sysError = true;
            }
            if ($TK->spamFlag == 'yes' && USER_ADMINISTRATOR == 'no' && !in_array('spam',$userAccess)) {
              $sysError = true;
            }
            if (!isset($sysError)) {
              $rows = $MSTICKET->updateTicketStatus();
              if ($rows > 0) {
                $MSTICKET->historyLog($_GET['id'], str_replace(array(
                  '{user}'
                ), array(
                  $MSTEAM->name
                ), $action));
              }
              $tkStatus = '';
              $actMsg = (isset($msg_ticket_actioned[$_GET['act']]) ? $msg_ticket_actioned[$_GET['act']] : '');
              $buttons = $MSTICKET->buttonRebuild(array(
                'id' => $_GET['id'],
                'action_txt' => mswJSClean($msg_script_action),
                'txt' => $msadminlang_tickets_3_7[8],
                'txt1' => $msadminlang_tickets_3_7[9],
                'txt2' => $msadminlang_tickets_3_7[10],
                'txt3' => $msgloballang4_3[6],
                'type' => ($TK->isDisputed == 'yes' ? 'dispute' : 'ticket'),
                'act' => $_GET['act']
              ));
              switch ($_GET['act']) {
                case 'open':
                  $tkStatus = (isset($ticketStatusSel['open'][0]) ? $ticketStatusSel['open'][0] : $msg_viewticket14);
                  break;
                case 'close':
                  $tkStatus = (isset($ticketStatusSel['close'][0]) ? $ticketStatusSel['close'][0] : $msg_viewticket15);
                  break;
                case 'lock':
                  $tkStatus = (isset($ticketStatusSel['closed'][0]) ? $ticketStatusSel['closed'][0] : $msg_viewticket16);
                  break;
                case 'spam':
                  $tkStatus = $msadminlang3_7[8];
                  break;
                case 'status-change':
                  $actMsg = str_replace('{status}', $ticketStatusSel[$cstStatus][0], $msg_ticket_actioned['status-changed']);
                  $tkStatus = $ticketStatusSel[$cstStatus][0];
                  $noButtonBuild = 'yes';
                  break;
              }
              if ($actMsg) {
                $json = array(
                  'msg' => 'ok',
                  'sys' => $actMsg,
                  'info' => $actMsg,
                  'status' => $tkStatus,
                  'html' => (!isset($noButtonBuild) ? $buttons : 'no-build')
                );
              }
            }
          }
        }
        break;
      case 'history-entry':
        if ($SETTINGS->ticketHistory == 'yes' && (USER_ADMINISTRATOR == 'yes' || $MSTEAM->ticketHistory == 'yes')) {
          if (isset($_GET['id'], $_POST['his'])) {
            $TK = mswSQL_table('tickets', 'id', (int) $_GET['id']);
            if (isset($TK->id)) {
              $h = $MSTICKET->historyLog($_GET['id'], $_POST['his'], $MSTEAM->id);
              if ($h > 0) {
                $HS = mswSQL_table('tickethistory', 'id', $h);
                $html = strtr(mswTmp(PATH . 'templates/system/html/tickets/history.htm'), array(
                  '{id}' => $h,
                  '{date}' => $MSDT->mswDateTimeDisplay($HS->ts, $SETTINGS->dateformat),
                  '{time}' => $MSDT->mswDateTimeDisplay($HS->ts, $SETTINGS->timeformat),
                  '{staff}' => mswSH($MSTEAM->name),
                  '{ip}' => loadIPAddresses($HS->ip),
                  '{post}' => mswSH($_POST['his']),
                  '{ticket}' => $_GET['id'],
                  '{conf_text}' => mswJSClean($msg_script_action),
                  '{text}' => mswSH($msg_public_history12)
                ));
                $json = array(
                  'msg' => 'ok',
                  'delp' => USER_DEL_PRIV,
                  'html' => $html
                );
              }
            }
          }
        }
        break;
      case 'release-lock':
        if (isset($_GET['id'])) {
          $MSTICKET->locker(array(
            'action' => 'release',
            'id' => (int) $_GET['id']
          ));
        }
        echo $JSON->encode(array('status' => 'ok', 'txt' => $msadminlang_dashboard_3_7[12]));
        exit;
        break;
      case 'tickdraft-save':
        if (isset($_POST['id'],$_POST['draft'])) {
          $time = str_replace(
            array('{date}','{time}'), 
            array(
              $MSDT->mswDateTimeDisplay($MSDT->mswTimeStamp(), $SETTINGS->dateformat),
              $MSDT->mswDateTimeDisplay($MSDT->mswTimeStamp(), $SETTINGS->timeformat)
            ), $mssuptickets4_3[6]
          );
          if ($SSN->active('draft_' . $_POST['id']) == 'yes') {
            $SSN->delete(array(
              'draft_' . $_POST['id'],
              'time_' . $_POST['id']
            ));
          }
          $SSN->set(array(
            'draft_' . $_POST['id'] => $_POST['draft'],
            'time_' . $_POST['id'] => $time
          ));
          $json = array(
            'msg' => 'saved',
            'text' => $time
          );
        }
        echo $JSON->encode($json);
        exit;
        break;
      case 'tickdraft-load':
        if (isset($_GET['id'])) {
          if ($SSN->active('draft_' . $_GET['id']) == 'yes') {
            $draft = $SSN->get('draft_' . $_GET['id']);
            $time = $SSN->get('time_' . $_GET['id']);
            $json = array(
              'msg' => 'saved',
              'draft' => mswSH($draft),
              'text' => $time
            );
          } else {
            $json = array(
              'msg' => 'no'
            );
          }
        }
        echo $JSON->encode($json);
        exit;
        break;
    }
    if (!in_array($_GET['ajax'], array('ticket-action', 'history-entry')) && $json['msg'] != 'err') {
      $json = array(
        'msg' => 'ok',
        'info' => (isset($json['infotxt']) ? $json['infotxt'] : ''),
        'buttons' => (!empty($json['buttons']) ? $json['buttons'] : array()),
        'delconfirm' => (isset($rows) ? $rows : '0'),
        'importrows' => ($improws > 0 ? mswNFM($improws) : '0')
      );
    }
    break;

  //=========================
  // FAQ
  //=========================

  case 'faqcat':
  case 'faqcatseq':
  case 'faqcatdel':
  case 'faqcatstate':
  case 'faq':
  case 'faqseq':
  case 'faqdel':
  case 'faqreset':
  case 'faqstate':
  case 'faqimport':
  case 'faqimport-upload':
  case 'faqattach':
  case 'faqattachseq':
  case 'faqattachdel':
  case 'faqattachstate':
  case 'faqdelhis':
  case 'faqhisexp':
    include_once(PATH . 'control/classes/class.faq.php');
    $FAQ           = new faqCentre();
    $FAQ->settings = $SETTINGS;
    $FAQ->dt       = $MSDT;
    $FAQ->ssn      = $SSN;
    $improws       = 0;
    switch($_GET['ajax']) {
      // Categories..
      case 'faqcat':
        if (isset($_POST['process'])) {
          if ($_POST['name']) {
            if (LICENCE_VER == 'locked') {
              if ((mswSQL_rows('categories') + 1) > RESTR_FAQ_CATS) {
                $json = array(
                  'msg' => 'err',
                  'info' => 'Free version restriction. Max allowed: ' . RESTR_FAQ_CATS,
                  'sys' => $msadminlang3_1[2]
                );
                echo $JSON->encode($json);
                exit;
              }
            }
            $q = mswSQL_query("SELECT count(*) AS `c` FROM `" . DB_PREFIX . "categories`
                 WHERE LOWER(`name`) = '" . mswSQL(strtolower($_POST['name'])) . "'
                 AND `subcat` = '" . (int) $_POST['subcat'] . "'
                 ", __file__, __line__);
            $F = mswSQL_fetchobj($q);
            if (isset($F->c) && $F->c > 0) {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => $msadminlang_faq_3_7[6]
              );
              echo $JSON->encode($json);
              exit;
            }
            $ID = $FAQ->addCategory();
            $json = array(
              'msg' => 'ok',
              'infotxt' => $msg_kbasecats,
              'buttons' => array(
                '<a href="?p=faq-cat&amp;edit=' . $ID . '">' . $msg_script9 . '</a>',
                '<a href="?p=faq-catman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=faq-cat">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang_faq_3_7[9],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        if (isset($_POST['update'])) {
          if ($_POST['name']) {
            $FAQ->updateCategory();
            $json = array(
              'msg' => 'ok',
              'infotxt' => $msg_kbasecats7,
              'buttons' => array(
                '<a href="?p=faq-cat&amp;edit=' . (int) $_POST['update'] . '">' . $msg_script9 . '</a>',
                '<a href="?p=faq-catman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=faq-cat">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang_faq_3_7[9],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        break;
      case 'faqcatseq':
        $FAQ->orderCatSequence();
        $json = array(
          'msg' => 'ok',
          'infotxt' => $msg_kbase45,
          'buttons' => array(
            '<a href="?p=faq-catman">' . $msgloballang4_3[3] . '</a>',
            '<a href="?p=faq-cat">' . $msg_dept . '</a>'
          )
        );
        break;
      case 'faqcatdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $FAQ->deleteCategories();
        }
        break;
      case 'faqcatstate':
        $FAQ->enableDisableCats();
        break;
      // Questions..
      case 'faq':
        if (isset($_POST['process'])) {
          if ($_POST['question'] && $_POST['answer'] && $_POST['cat'] > 0) {
            if (LICENCE_VER == 'locked') {
              if ((mswSQL_rows('faq') + 1) > RESTR_FAQ_QUE) {
                $json = array(
                  'msg' => 'err',
                  'info' => 'Free version restriction. Max allowed: ' . RESTR_FAQ_QUE,
                  'sys' => $msadminlang3_1[2]
                );
                echo $JSON->encode($json);
                exit;
              }
            }
            $q = mswSQL_query("SELECT count(*) AS `c` FROM `" . DB_PREFIX . "faq`
                 WHERE LOWER(`question`) = '" . mswSQL(strtolower($_POST['question'])) . "'
                 AND `cat` = '" . (int) $_POST['cat'] . "'
                 ", __file__, __line__);
            $F = mswSQL_fetchobj($q);
            if (isset($F->c) && $F->c > 0) {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => $msadminlang_faq_3_7[7]
              );
              echo $JSON->encode($json);
              exit;
            }
            $ID = $FAQ->addQuestion();
            $FAQ->historyLog($ID, str_replace('{staff}', $MSTEAM->name, $msfaq4_3[2]));
            $json = array(
              'msg' => 'ok',
              'infotxt' => $msg_kbase7,
              'buttons' => array(
                '<a href="?p=faq&amp;edit=' . $ID . '">' . $msg_script9 . '</a>',
                '<a href="?p=faqman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=faq">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang_faq_3_7[8],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        if (isset($_POST['update']) && $_POST['cat'] > 0) {
          if ($_POST['question'] && $_POST['answer']) {
            $aff = $FAQ->updateQuestion();
            if ($aff > 0) {
              $FAQ->historyLog((int) $_POST['update'], str_replace('{staff}', $MSTEAM->name, $msfaq4_3[3]));
            }
            $json = array(
              'msg' => 'ok',
              'infotxt' => $msg_kbase8,
              'buttons' => array(
                '<a href="?p=faq&amp;edit=' . (int) $_POST['update'] . '">' . $msg_script9 . '</a>',
                '<a href="?p=faqman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=faq">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang_faq_3_7[8],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        break;
      case 'faqseq':
        $FAQ->orderQueSequence();
        $json = array(
          'msg' => 'ok',
          'infotxt' => $msg_kbase45,
          'buttons' => array(
            '<a href="?p=faqman">' . $msgloballang4_3[3] . '</a>',
            '<a href="?p=faq">' . $msg_dept . '</a>'
          )
        );
        break;
      case 'faqdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $FAQ->deleteQuestions();
        }
        break;
      case 'faqreset':
        $FAQ->resetCounts(
          str_replace('{staff}', $MSTEAM->name, $msfaq4_3[6])
        );
        $json = array(
          'msg' => 'ok',
          'infotxt' => $msg_kbase21,
          'buttons' => array(
            '<a href="?p=faqman">' . $msgloballang4_3[3] . '</a>',
            '<a href="?p=faq">' . $msg_dept . '</a>'
          )
        );
        break;
      case 'faqstate':
        $state = $FAQ->enableDisableQuestions();
        $FAQ->historyLog((int) $_GET['id'], str_replace('{staff}', $MSTEAM->name, $msfaq4_3[($state == 'yes' ? 4 : 5)]));
        break;
      case 'faqimport':
      case 'faqimport-upload':
        switch($_GET['ajax']) {
          case 'faqimport':
            $improws = $FAQ->batchImportQuestions(
              str_replace('{staff}', $MSTEAM->name, $msfaq4_3[2])
            );
            echo $JSON->encode(array(
              'msg' => 'ok',
              'faq' => $improws
            ));
            exit;
            break;
          case 'faqimport-upload':
            $path = PATH . 'export/faqimport.csv';
            if (file_exists($path)) {
              @unlink($path);
            }
            if ($MSUPL->isUploaded($_FILES['file']['tmp_name'])) {
              $SSN->set(array('upload_file' => $path));
              $MSUPL->moveFile($_FILES['file']['tmp_name'], $path);
              // Get count of lines to import..
              if (file_exists($path)) {
                if ($_FILES['file']['size'] < CSV_COUNT_MAX_LINES_SIZE) {
                  $improws = count(file($path, FILE_SKIP_EMPTY_LINES));
                }
              } else {
                $json = array(
                  'msg' => 'err',
                  'sys' => $msadminlang3_1[2],
                  'info' => str_replace('{error}', (isset($_FILES['file']['error']) ? $MSUPL->error($_FILES['file']['error']) : $msg_script17), $msadminlang3_1[7])
                );
                echo $JSON->encode($json);
                exit;
              }
              if (file_exists($_FILES['file']['tmp_name'])) {
                @unlink($_FILES['file']['tmp_name']);
              }
            } else {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => str_replace('{error}', (isset($_FILES['file']['error']) ? $MSUPL->error($_FILES['file']['error']) : $msg_script17), $msadminlang3_1[7])
              );
              echo $JSON->encode($json);
              exit;
            }
            break;
        }
        break;
      // Attachments..
      case 'faqattach':
        include_once(BASE_PATH . 'control/classes/system/class.download.php');
        $MSDL = new msDownload();
        if (isset($_POST['process'])) {
          $arows = $FAQ->addAttachments($MSDL, $MSUPL);
          if ($arows == 0) {
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1faq[15]
            );
            $json['msg'] = 'reload';
            echo $JSON->encode($json);
            exit;
          } else {
            $json = array(
              'msg' => 'ok',
              'infotxt' => str_replace('{count}', mswNFM($arows), $msg_attachments10),
              'buttons' => array(
                '<a href="?p=attachman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=attachments">' . $msg_dept . '</a>'
              )
            );
          }
        }
        if (isset($_POST['update'])) {
          $ret = $FAQ->updateAttachment($MSUPL);
          $json = array(
            'msg' => 'ok',
            'infotxt' => $msg_attachments13,
            'buttons' => array(
              '<a href="?p=attachments&amp;edit=' . (int) $_POST['update'] . '">' . ($ret == 'yes' ? $msgloballang4_3[4] : $msg_script9) . '</a>',
              '<a href="?p=attachman">' . $msgloballang4_3[3] . '</a>',
              '<a href="?p=attachments">' . $msg_dept . '</a>'
            )
          );
        }
        break;
      case 'faqattachseq':
        $FAQ->orderAttSequence();
        $json = array(
          'msg' => 'ok',
          'infotxt' => $msg_kbase45,
          'buttons' => array(
            '<a href="?p=attachman">' . $msgloballang4_3[3] . '</a>',
            '<a href="?p=attachments">' . $msg_dept . '</a>'
          )
        );
        break;
      case 'faqattachdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $FAQ->deleteAttachments();
        }
        break;
      case 'faqattachstate':
        $FAQ->enableDisableAtt();
        break;
      case 'faqdelhis':
        if (USER_DEL_PRIV == 'yes' && isset($_GET['id'], $_GET['f'])) {
          $ID = ($_GET['id'] != 'all' ? (int) $_GET['id'] : 'all');
          $TK = (int) $_GET['f'];
          $FAQ->deleteFAQHistory($ID, $TK);
          $json = array(
            'msg' => 'ok',
            'html' => $msfaq4_3[1]
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'faqhisexp':
        include(BASE_PATH . 'control/classes/system/class.download.php');
        $MSDL = new msDownload();
        $file = $FAQ->exportFAQHistory($MSDL, $MSDT);
        switch($file) {
          case 'err':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => str_replace('{path}', PATH . 'export', $msadminlang3_1backup[0])
            );
            echo $JSON->encode($json);
            exit;
            break;
          case 'none':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1[8]
            );
            echo $JSON->encode($json);
            exit;
            break;
          default:
            $json = array(
              'msg' => 'ok-dl',
              'file' => ADMIN_FLDR . '/export/' . basename($file),
              'type' => 'text/csv'
            );
            echo $JSON->encode($json);
            exit;
            break;
        }
        break;
    }
    $json = array(
      'msg' => 'ok',
      'info' => (isset($json['infotxt']) ? $json['infotxt'] : ''),
      'buttons' => (!empty($json['buttons']) ? $json['buttons'] : array()),
      'delconfirm' => (isset($rows) ? $rows : '0'),
      'importrows' => ($improws > 0 ? mswNFM($improws) : '0')
    );
    break;

  //=========================
  // Imap Accounts
  //=========================

  case 'imap':
  case 'imdel':
  case 'imstate':
  case 'imfolders':
  case 'imapban':
    include_once(PATH . 'control/classes/class.imap.php');
    $MSIMAP = new imap();
    switch($_GET['ajax']) {
      case 'imap':
        if (isset($_POST['process'])) {
          if ($_POST['im_host']) {
            if (LICENCE_VER == 'locked') {
              if ((mswSQL_rows('imap') + 1) > RESTR_IMAP) {
                $json = array(
                  'msg' => 'err',
                  'info' => 'Free version restriction. Max allowed: ' . RESTR_IMAP,
                  'sys' => $msadminlang3_1[2]
                );
                echo $JSON->encode($json);
                exit;
              }
            }
            $ID = $MSIMAP->addImapAccount();
            $json = array(
              'infotxt' => $msg_imap22,
              'buttons' => array(
                '<a href="?p=imap&amp;edit=' . $ID . '">' . $msg_script9 . '</a>',
                '<a href="?p=imapman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=imap">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang_imap_3_7[5],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        if (isset($_POST['update'])) {
          if ($_POST['im_host']) {
            $MSIMAP->editImapAccount();
            $json = array(
              'infotxt' => $msg_imap23,
              'buttons' => array(
                '<a href="?p=imap&amp;edit=' . (int) $_POST['update'] . '">' . $msg_script9 . '</a>',
                '<a href="?p=imapman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=imap">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang_imap_3_7[5],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        break;
      case 'imdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSIMAP->deleteImapAccounts();
        }
        break;
      case 'imstate':
        $MSIMAP->enableDisable();
        break;
      case 'imfolders':
        $html   = '';
        $msg    = $msadminlang3_1[3];
        $action = 'err';
        if (function_exists('imap_open')) {
          $host = ($_POST['host'] ? mswCD($_POST['host']) : 'xx');
          $port = ($_POST['port'] ? mswCD($_POST['port']) : '1');
          $flag = ($_POST['flags'] ? mswCD($_POST['flags']) : '');
          $ssl  = (isset($_POST['ssl']) && in_array($_POST['ssl'], ['yes','no']) ? $_POST['ssl'] : 'no');
          $user = mswCD($_POST['user']);
          $pass = mswCD($_POST['pass']);
          $mbox = imap_open('{' . $host . ':' . $port . '/imap' . ($ssl == 'yes' ? '/ssl' : '') . $flag . '}', $user, $pass);
          if ($mbox) {
            $list = @imap_list($mbox, '{' . $host . ':' . $port . '}', '*');
            if (is_array($list)) {
              sort($list);
              $html = '<option value="0">' . $msg_imap26 . '</option>';
              foreach ($list AS $box) {
                $box   = str_replace('{' . $host . ':' . $port . '}', '', imap_utf7_decode($box));
                $html .= '<option value="' . $box . '">' . $box . '</option>';
              }
              $action = 'ok';
            } else {
              $msg = $msg_script_action2;
            }
            @imap_close($mbox);
            @imap_errors();
            @imap_alerts();
            if (imap_last_error()) {
              $msg = imap_last_error();
            }
          } else {
            // Mask errors to prevent callback failure..
            @imap_errors();
            @imap_alerts();
            if (imap_last_error()) {
              $msg = imap_last_error();
            } else {
              $msg = $msg_script_action2;
            }
          }
        } else {
          $msg = $msadminlang3_1[5];
        }
        echo $JSON->encode(array(
          'msg' => $action,
          'info' => $msg,
          'sys' => $msadminlang3_1[2],
          'html' => trim($html)
        ));
        exit;
        break;
      case 'imapban':
        $MSIMAP->banFilters();
        $json = array(
          'infotxt' => $msmessageslang4_3[0],
          'buttons' => array(
            '<a href="?p=imapman">' . $msgloballang4_3[3] . '</a>',
            '<a href="?p=imap">' . $msg_dept . '</a>'
          )
        );
        break;
    }
    $json = array(
      'msg' => 'ok',
      'info' => (isset($json['infotxt']) ? $json['infotxt'] : ''),
      'buttons' => (!empty($json['buttons']) ? $json['buttons'] : array()),
      'delconfirm' => (isset($rows) ? $rows : '0')
    );
    break;

  //=========================
  // Standard Responses
  //=========================

  case 'response':
  case 'srseq':
  case 'srdel':
  case 'srstate':
  case 'srimport-upload':
  case 'srimport':
    include_once(PATH . 'control/classes/class.responses.php');
    $MSSTR           = new standardResponses();
    $MSSTR->settings = $SETTINGS;
    $MSSTR->ssn      = $SSN;
    $improws         = 0;
    switch($_GET['ajax']) {
      case 'response':
        if (isset($_POST['process'])) {
          if ($_POST['title'] && $_POST['answer']) {
            if (LICENCE_VER == 'locked') {
              if ((mswSQL_rows('responses') + 1) > RESTR_RESPONSES) {
                $json = array(
                  'msg' => 'err',
                  'info' => 'Free version restriction. Max allowed: ' . RESTR_RESPONSES,
                  'sys' => $msadminlang3_1[2]
                );
                echo $JSON->encode($json);
                exit;
              }
            }
            $ID = $MSSTR->addResponse();
            $json = array(
              'msg' => 'ok',
              'infotxt' => $msg_response7,
              'buttons' => array(
                '<a href="?p=standard-responses&amp;edit=' . $ID . '">' . $msg_script9 . '</a>',
                '<a href="?p=responseman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=standard-responses">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang_responses_3_7[0],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        if (isset($_POST['update'])) {
          if ($_POST['title'] && $_POST['answer']) {
            $MSSTR->updateResponse();
            $json = array(
              'msg' => 'ok',
              'infotxt' => $msg_response8,
              'buttons' => array(
                '<a href="?p=standard-responses&amp;edit=' . (int) $_POST['update'] . '">' . $msg_script9 . '</a>',
                '<a href="?p=responseman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=standard-responses">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang_responses_3_7[0],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        break;
      case 'srimport':
      case 'srimport-upload':
        switch($_GET['ajax']) {
          case 'srimport':
            $MSSTR->batchImportSR();
            break;
          case 'srimport-upload':
            $path = PATH . 'export/srimport.csv';
            if (file_exists($path)) {
              @unlink($path);
            }
            if ($MSUPL->isUploaded($_FILES['file']['tmp_name'])) {
              $SSN->set(array('upload_file' => $path));
              $MSUPL->moveFile($_FILES['file']['tmp_name'], $path);
              // Get count of lines to import..
              if (file_exists($path)) {
                if ($_FILES['file']['size'] < CSV_COUNT_MAX_LINES_SIZE) {
                  $improws = count(file($path, FILE_SKIP_EMPTY_LINES));
                }
              } else {
                $json = array(
                  'msg' => 'err',
                  'sys' => $msadminlang3_1[2],
                  'info' => str_replace('{error}', (isset($_FILES['file']['error']) ? $MSUPL->error($_FILES['file']['error']) : $msg_script17), $msadminlang3_1[7])
                );
                echo $JSON->encode($json);
                exit;
              }
              if (file_exists($_FILES['file']['tmp_name'])) {
                @unlink($_FILES['file']['tmp_name']);
              }
            } else {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => str_replace('{error}', (isset($_FILES['file']['error']) ? $MSUPL->error($_FILES['file']['error']) : $msg_script17), $msadminlang3_1[7])
              );
              echo $JSON->encode($json);
              exit;
            }
            break;
        }
        break;
      case 'srseq':
        $MSSTR->orderSequence();
        $json = array(
          'msg' => 'ok',
          'infotxt' => $msg_kbase45,
          'buttons' => array(
            '<a href="?p=responseman">' . $msgloballang4_3[3] . '</a>',
            '<a href="?p=standard-responses">' . $msg_dept . '</a>'
          )
        );
        break;
      case 'srdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSSTR->deleteResponses();
        }
        break;
      case 'srstate':
        $MSSTR->enableDisable();
        break;
    }
    $json = array(
      'msg' => 'ok',
      'info' => (isset($json['infotxt']) ? $json['infotxt'] : ''),
      'buttons' => (!empty($json['buttons']) ? $json['buttons'] : array()),
      'delconfirm' => (isset($rows) ? $rows : '0'),
      'importrows' => ($improws > 0 ? mswNFM($improws) : '0')
    );
    break;

  //=========================
  // Custom Pages
  //=========================

  case 'pages':
  case 'pgseq':
  case 'pgdel':
  case 'pgstate':
    include_once(PATH . 'control/classes/class.pages.php');
    $MSPGS           = new csPages();
    $MSPGS->settings = $SETTINGS;
    $improws         = 0;
    switch($_GET['ajax']) {
      case 'pages':
        if (isset($_POST['process'])) {
          if ($_POST['title'] && $_POST['information']) {
            if (LICENCE_VER == 'locked') {
              if ((mswSQL_rows('pages') + 1) > RESTR_PAGES) {
                $json = array(
                  'msg' => 'err',
                  'info' => 'Free version restriction. Max allowed: ' . RESTR_PAGES,
                  'sys' => $msadminlang3_1[2]
                );
                echo $JSON->encode($json);
                exit;
              }
            }
            $ID = $MSPGS->addPage();
            $json = array(
              'infotxt' => $msadminpages4_3[6],
              'buttons' => array(
                '<a href="?p=pages&amp;edit=' . $ID . '">' . $msg_script9 . '</a>',
                '<a href="?p=pageman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=pages">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang_pages_3_7[0],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        if (isset($_POST['update'])) {
          if ($_POST['title'] && $_POST['information']) {
            $MSPGS->updatePage();
            $json = array(
              'infotxt' => $msadminpages4_3[7],
              'buttons' => array(
                '<a href="?p=pages&amp;edit=' . (int) $_POST['update'] . '">' . $msg_script9 . '</a>',
                '<a href="?p=pageman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=pages">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang_pages_3_7[0],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        break;
      case 'pgseq':
        $MSPGS->orderSequence();
        $json = array(
          'msg' => 'ok',
          'infotxt' => $msg_kbase45,
          'buttons' => array(
            '<a href="?p=pageman">' . $msgloballang4_3[3] . '</a>',
            '<a href="?p=pages">' . $msg_dept . '</a>'
          )
        );
        break;
      case 'pgdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSPGS->deletePages();
        }
        break;
      case 'pgstate':
        $MSPGS->enableDisable();
        break;
    }
    $json = array(
      'msg' => 'ok',
      'info' => (isset($json['infotxt']) ? $json['infotxt'] : ''),
      'buttons' => (!empty($json['buttons']) ? $json['buttons'] : array()),
      'delconfirm' => (isset($rows) ? $rows : '0'),
      'importrows' => ($improws > 0 ? mswNFM($improws) : '0')
    );
    break;
    
  //=========================
  // Admin Pages
  //=========================

  case 'apages':
  case 'apgseq':
  case 'apgdel':
  case 'apgstate':
    include_once(PATH . 'control/classes/class.pages.php');
    $MSPGS           = new csPages();
    $MSPGS->settings = $SETTINGS;
    $improws         = 0;
    switch($_GET['ajax']) {
      case 'apages':
        if (isset($_POST['process'])) {
          if ($_POST['title'] && $_POST['information']) {
            $ID = $MSPGS->addAdminPage();
            $json = array(
              'infotxt' => $msadminpages4_3[6],
              'buttons' => array(
                '<a href="?p=apages&amp;edit=' . $ID . '">' . $msg_script9 . '</a>',
                '<a href="?p=apages">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=apages">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang_pages_3_7[0],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        if (isset($_POST['update'])) {
          if ($_POST['title'] && $_POST['information']) {
            $MSPGS->updateAdminPage();
            $json = array(
              'infotxt' => $msadminpages4_3[7],
              'buttons' => array(
                '<a href="?p=apages&amp;edit=' . (int) $_POST['update'] . '">' . $msg_script9 . '</a>',
                '<a href="?p=apages">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=apages">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang_pages_3_7[0],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        break;
      case 'apgseq':
        $MSPGS->orderSequence('admin_pages');
        $json = array(
          'msg' => 'ok',
          'infotxt' => $msg_kbase45,
          'buttons' => array(
            '<a href="?p=apages">' . $msgloballang4_3[3] . '</a>',
            '<a href="?p=apages">' . $msg_dept . '</a>'
          )
        );
        break;
      case 'apgdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSPGS->deletePages('admin_pages');
        }
        break;
      case 'apgstate':
        $MSPGS->enableDisable('admin_pages');
        break;
    }
    $json = array(
      'msg' => 'ok',
      'info' => (isset($json['infotxt']) ? $json['infotxt'] : ''),
      'buttons' => (!empty($json['buttons']) ? $json['buttons'] : array()),
      'delconfirm' => (isset($rows) ? $rows : '0'),
      'importrows' => ($improws > 0 ? mswNFM($improws) : '0')
    );
    break;

  //=========================
  // Accounts
  //=========================

  case 'accounts':
  case 'accdel':
  case 'accstate':
  case 'accimp-upload':
  case 'accimp':
  case 'accexp':
    // Include relevant classes..
    $MSACC             = new accounts();
    $MSACC->settings   = $SETTINGS;
    $MSACC->timezones  = $timezones;
    $MSACC->ssn        = $SSN;
    $improws           = 0;
    switch($_GET['ajax']) {
      case 'accounts':
        if (isset($_POST['cs_rf']) && $SSN->active('csrf_token') == 'yes' && $_POST['cs_rf'] == $SSN->get('csrf_token')) {
          if (isset($_POST['process'])) {
            if ($_POST['name'] && mswIsValidEmail($_POST['email'])) {
              if ($MSACC->check($_POST['email']) == 'exists') {
                $json = array(
                  'msg' => 'err',
                  'sys' => $msadminlang3_1[2],
                  'info' => $msadminlang3_1[1]
                );
              } else {
                if ($_POST['userPass'] == '') {
                  $MSPACC             = new accountSystem();
                  $MSPACC->settings   = $SETTINGS;
                  $_POST['userPass']  = $MSPACC->ms_generate();
                }
                $ID = $MSACC->add();
                // Send welcome email?
                if (isset($_POST['welcome'])) {
                  // Message tags..
                  $MSMAIL->addTag('{NAME}', $_POST['name']);
                  $MSMAIL->addTag('{EMAIL}', $_POST['email']);
                  $MSMAIL->addTag('{PASSWORD}', $_POST['userPass']);
                  // Send..
                  $MSMAIL->sendMSMail(array(
                    'from_email' => $SETTINGS->email,
                    'from_name' => $SETTINGS->website,
                    'to_email' => $_POST['email'],
                    'to_name' => $_POST['name'],
                    'subject' => str_replace(array(
                      '{website}'
                    ), array(
                      $SETTINGS->website
                    ), $emailSubjects['add']),
                    'replyto' => array(
                      'name' => $SETTINGS->website,
                      'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                    ),
                    'template' => LANG_PATH . 'admin-add-account.txt'
                  ));
                }
                $json = array(
                  'msg' => 'ok',
                  'infotxt' => $msg_accounts21,
                  'buttons' => array(
                    '<a href="?p=accounts&amp;edit=' . $ID . '">' . $msg_script9 . '</a>',
                    '<a href="?p=accountman">' . $msgloballang4_3[3] . '</a>',
                    '<a href="?p=accounts">' . $msg_dept . '</a>'
                  )
                );
              }
            } else {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => $msadminlang3_1[0]
              );
            }
          }
        }
        if (isset($_POST['update'])) {
          if ($_POST['name'] && mswIsValidEmail($_POST['email'])) {
            if ($MSACC->check($_POST['email']) == 'exists') {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => $msadminlang3_1[1]
              );
            } else {
              $MSACC->update();
              // Anything to move?
              if (isset($_POST['dest_email']) && mswIsValidEmail($_POST['dest_email'])) {
                $MSACC->move($_POST['old_email'], $_POST['dest_email']);
              }
              $json = array(
                'msg' => 'ok',
                'infotxt' => $msg_accounts22,
                'buttons' => array(
                  '<a href="?p=accounts&amp;edit=' . (int) $_POST['update'] . '">' . $msg_script9 . '</a>',
                  '<a href="?p=accountman">' . $msgloballang4_3[3] . '</a>',
                  '<a href="?p=accounts">' . $msg_dept . '</a>'
                )
              );
            }
          }
        }
        break;
      case 'accimp':
      case 'accimp-upload':
        switch($_GET['ajax']) {
          case 'accimp':
            $data = $MSACC->import();
            if (!empty($data) && isset($_POST['welcome'])) {
              foreach ($data AS $k => $v) {
                // Message tags..
                $MSMAIL->addTag('{NAME}', $v[0]);
                $MSMAIL->addTag('{EMAIL}', $v[1]);
                $MSMAIL->addTag('{PASSWORD}', $v[2]);
                // Send..
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $v[1],
                  'to_name' => $v[0],
                  'subject' => str_replace(array(
                    '{website}'
                  ), array(
                    $SETTINGS->website
                  ), $emailSubjects['add']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => LANG_PATH . 'admin-add-account.txt',
                  'language' => (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language),
                  'alive' => 'yes'
                ));
              }
              $MSMAIL->smtpClose();
            }
            $json = array(
              'msg' => 'ok',
              'infotxt' => str_replace('{count}', mswNFM(count($data)), $msg_accounts35),
              'buttons' => array(
                '<a href="?p=accountman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=accounts">' . $msg_dept . '</a>'
              )
            );
            break;
          case 'accimp-upload':
            $path = PATH . 'export/accimport.csv';
            if (file_exists($path)) {
              @unlink($path);
            }
            if ($MSUPL->isUploaded($_FILES['file']['tmp_name'])) {
              $SSN->set(array('upload_file' => $path));
              $MSUPL->moveFile($_FILES['file']['tmp_name'], $path);
              // Get count of lines to import..
              if (file_exists($path)) {
                if ($_FILES['file']['size'] < CSV_COUNT_MAX_LINES_SIZE) {
                  $improws = count(file($path, FILE_SKIP_EMPTY_LINES));
                }
                $json = array(
                  'msg' => 'ok'
                );
              } else {
                $json = array(
                  'msg' => 'err',
                  'sys' => $msadminlang3_1[2],
                  'info' => str_replace('{error}', (isset($_FILES['file']['error']) ? $MSUPL->error($_FILES['file']['error']) : $msg_script17), $msadminlang3_1[7])
                );
                echo $JSON->encode($json);
                exit;
              }
              if (file_exists($_FILES['file']['tmp_name'])) {
                @unlink($_FILES['file']['tmp_name']);
              }
            } else {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => str_replace('{error}', (isset($_FILES['file']['error']) ? $MSUPL->error($_FILES['file']['error']) : $msg_script17), $msadminlang3_1[7])
              );
              echo $JSON->encode($json);
              exit;
            }
            break;
        }
        break;
      case 'accexp':
        include(BASE_PATH . 'control/classes/system/class.download.php');
        $MSDL = new msDownload();
        $file = $MSACC->export($msg_accounts37,$msadminlang3_1[9],$MSDL);
        switch($file) {
          case 'err':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => str_replace('{path}', PATH . 'export', $msadminlang3_1backup[0])
            );
            echo $JSON->encode($json);
            exit;
            break;
          case 'none':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1[8]
            );
            echo $JSON->encode($json);
            exit;
            break;
          default:
            $json = array(
              'msg' => 'ok-dl',
              'file' => ADMIN_FLDR . '/export/' . basename($file),
              'type' => 'text/csv'
            );
            echo $JSON->encode($json);
            exit;
            break;
        }
        break;
      case 'accdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSACC->delete($MSTICKET);
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'accstate':
        $MSACC->enable();
        break;
    }
    if ($json['msg'] != 'err') {
      $json = array(
        'msg' => 'ok',
        'info' => (isset($json['infotxt']) ? $json['infotxt'] : ''),
        'buttons' => (!empty($json['buttons']) ? $json['buttons'] : array()),
        'delconfirm' => (isset($rows) ? $rows : '0'),
        'importrows' => ($improws > 0 ? mswNFM($improws) : '0')
      );
    }
    break;

  //=========================
  // Support Team
  //=========================

  case 'team':
  case 'tmdel':
  case 'tmstate':
  case 'tmprofile':
  case 'tmrep':
    switch($_GET['ajax']) {
      case 'team':
        if (isset($_POST['cs_rf']) && $SSN->active('csrf_token') == 'yes' && $_POST['cs_rf'] == $SSN->get('csrf_token')) {
          if (isset($_POST['process'])) {
            if ($_POST['name'] && mswIsValidEmail($_POST['email'])) {
              if (LICENCE_VER == 'locked') {
                if ((mswSQL_rows('users') + 1) > RESTR_USERS) {
                  $json = array(
                    'msg' => 'err',
                    'info' => 'Free version restriction. Max allowed: ' . RESTR_USERS,
                    'sys' => $msadminlang3_1[2]
                  );
                  echo $JSON->encode($json);
                  exit;
                }
              }
              if ($MSUSERS->check($_POST['email']) == 'exists') {
                $json = array(
                  'msg' => 'err',
                  'sys' => $msadminlang3_1[2],
                  'info' => $msadminlang3_1[1]
                );
              } else {
                if ($_POST['accpass'] == '') {
                  $_POST['accpass'] = $MSACC->ms_generate();
                }
                $ID = $MSUSERS->add($MSTEAM->id);
                // Send mail..
                if (isset($_POST['welcome'])) {
                  $langFile = BASE_PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/admin-new-team.txt';
                  $langSet = $SETTINGS->language;
                  if ($_POST['language'] && is_dir(BASE_PATH . 'content/language/' . $_POST['language']) && file_exists(BASE_PATH . 'content/language/' . $_POST['language'] . '/mail-templates/admin-new-team.txt')) {
                    $langSet = $_POST['language'];
                    $langFile = BASE_PATH . 'content/language/' . $_POST['language'] . '/mail-templates/admin-new-team.txt';
                  }
                  // Message tags..
                  $MSMAIL->addTag('{NAME}', mswCD($_POST['name']));
                  $MSMAIL->addTag('{EMAIL}', $_POST['email']);
                  $MSMAIL->addTag('{PASSWORD}', $_POST['accpass']);
                  // Send..
                  $MSMAIL->sendMSMail(array(
                    'from_email' => $SETTINGS->email,
                    'from_name' => mswCD($SETTINGS->website),
                    'to_email' => $_POST['email'],
                    'to_name' => $_POST['name'],
                    'subject' => str_replace(array(
                      '{website}'
                    ), array(
                      $SETTINGS->website
                    ), $emailSubjects['team-account']),
                    'replyto' => array(
                      'name' => $SETTINGS->website,
                      'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                    ),
                    'template' => $langFile,
                    'language' => $langSet
                  ));
                }
                $json = array(
                  'msg' => 'ok',
                  'infotxt' => $msg_user6,
                  'buttons' => array(
                    '<a href="?p=team&amp;edit=' . $ID . '">' . $msg_script9 . '</a>',
                    '<a href="?p=teamman">' . $msgloballang4_3[3] . '</a>',
                    '<a href="?p=team">' . $msg_dept . '</a>'
                  )
                );
              }
            } else {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => $msadminlang3_1[0]
              );
            }
          }
        }
        if (isset($_POST['update'])) {
          if ($_POST['name'] && mswIsValidEmail($_POST['email'])) {
            if ($MSUSERS->check($_POST['email']) == 'exists') {
              $json = array(
                'msg' => 'err',
                'sys' => $msadminlang3_1[2],
                'info' => $msadminlang3_1[1]
              );
            } else {
              // Check edit for global user..
              if ($_POST['update'] == '1' && $MSTEAM->id != '1') {
                $json = array(
                  'msg' => 'err',
                  'sys' => $msadminlang3_1[2],
                  'info' => $msadminlang3_1[3]
                );
                echo $JSON->encode($json);
                exit;
              }
              $MSUSERS->update($MSTEAM->id);
              $json = array(
                'msg' => 'ok',
                'infotxt' => $msg_user15,
                'buttons' => array(
                  '<a href="?p=team&amp;edit=' . (int) $_POST['update'] . '">' . $msg_script9 . '</a>',
                  '<a href="?p=teamman">' . $msgloballang4_3[3] . '</a>',
                  '<a href="?p=team">' . $msg_dept . '</a>'
                )
              );
            }
          }
        }
        break;
      case 'tmprofile':
        if ($_POST['name'] && mswIsValidEmail($_POST['email'])) {
          if ($MSUSERS->check($_POST['email']) == 'exists') {
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1[1]
            );
          } else {
            $urows = $MSUSERS->profile($MSTEAM);
            $json = array(
              'msg' => 'ok',
              'infotxt' => $msg_staffprofile
            );
          }
        } else {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msadminlang3_1[0]
          );
        }
        break;
      case 'tmdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSUSERS->delete();
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'tmstate':
        $MSUSERS->enable();
        break;
      case 'tmrep':
        include(BASE_PATH . 'control/classes/system/class.download.php');
        $MSDL        = new msDownload();
        $MSUSERS->dl = $MSDL;
        $build = $MSUSERS->report(array(
          'l' => array(
            $msadminlang_user_3_7[20]
          ),
          'ids' => array(1)
        ));
        switch($build) {
          case 'none':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1[8]
            );
            break;
          default:
            $json = array(
              'msg' => 'ok-dl',
              'file' => ADMIN_FLDR . '/export/' . basename($build),
              'type' => 'text/csv'
            );
            echo $JSON->encode($json);
            exit;
            break;
        }
        break;
    }
    if ($json['msg'] != 'err') {
      $json = array(
        'msg' => 'ok',
        'info' => (isset($json['infotxt']) ? $json['infotxt'] : ''),
        'buttons' => (!empty($json['buttons']) ? $json['buttons'] : array()),
        'delconfirm' => (isset($rows) ? $rows : '0')
      );
    }
    break;

  //=========================
  // Custom Fields
  //=========================

  case 'fields':
  case 'fldseq':
  case 'flddel':
  case 'fldstate':
    include_once(PATH . 'control/classes/class.fields.php');
    $MSFIELDS = new fields();
    switch($_GET['ajax']) {
      case 'fields':
        if (isset($_POST['process'])) {
          if (isset($_POST['fieldInstructions']) && $_POST['fieldInstructions'] && isset($_POST['fieldType'])) {
            if (LICENCE_VER == 'locked') {
              if ((mswSQL_rows('cusfields') + 1) > RESTR_FIELDS) {
                $json = array(
                  'msg' => 'err',
                  'info' => 'Free version restriction. Max allowed: ' . RESTR_FIELDS,
                  'sys' => $msadminlang3_1[2]
                );
                echo $JSON->encode($json);
                exit;
              }
            }
            $ID = $MSFIELDS->addCustomField();
            $json = array(
              'infotxt' => $msg_customfields12,
              'buttons' => array(
                '<a href="?p=fields&amp;edit=' . $ID . '">' . $msg_script9 . '</a>',
                '<a href="?p=fieldsman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=fields">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang3_7fields[0],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        if (isset($_POST['update'])) {
          if (isset($_POST['fieldInstructions']) && $_POST['fieldInstructions'] && isset($_POST['fieldType'])) {
            $MSFIELDS->editCustomField();
            $json = array(
              'infotxt' => $msg_customfields13,
              'buttons' => array(
                '<a href="?p=fields&amp;edit=' . (int) $_POST['update'] . '">' . $msg_script9 . '</a>',
                '<a href="?p=fieldsman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=fields">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang3_7fields[0],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        break;
      case 'fldseq':
        $MSFIELDS->orderSequence();
        $json = array(
          'msg' => 'ok',
          'infotxt' => $msg_kbase45,
          'buttons' => array(
            '<a href="?p=fieldsman">' . $msgloballang4_3[3] . '</a>',
            '<a href="?p=fields">' . $msg_dept . '</a>'
          )
        );
        break;
      case 'flddel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSFIELDS->deleteCustomFields();
        }
        break;
      case 'fldstate':
        $MSFIELDS->enableDisable();
        break;
    }
    $json = array(
      'msg' => 'ok',
      'info' => (isset($json['infotxt']) ? $json['infotxt'] : ''),
      'buttons' => (!empty($json['buttons']) ? $json['buttons'] : array()),
      'delconfirm' => (isset($rows) ? $rows : '0')
    );
    break;
    
  //=========================
  // Ticket Statuses
  //=========================

  case 'status':
  case 'statseq':
  case 'statdel':
    include_once(PATH . 'control/classes/class.statuses.php');
    $MSSTS = new statuses();
    switch($_GET['ajax']) {
      case 'status':
        if (isset($_POST['process'])) {
          if (isset($_POST['name']) && $_POST['name']) {
            $ID = $MSSTS->addStatus();
            $json = array(
              'infotxt' => $msticketstatuses4_3[6],
              'buttons' => array(
                '<a href="?p=status&amp;edit=' . $ID . '">' . $msg_script9 . '</a>',
                '<a href="?p=statusman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=status">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msticketstatuses4_3[8],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        if (isset($_POST['update'])) {
          if (isset($_POST['name']) && $_POST['name']) {
            $MSSTS->updateStatus();
            $json = array(
              'infotxt' => $msticketstatuses4_3[7],
              'buttons' => array(
                '<a href="?p=status&amp;edit=' . (int) $_POST['update'] . '">' . $msg_script9 . '</a>',
                '<a href="?p=statusman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=status">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msticketstatuses4_3[8],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        break;
      case 'statseq':
        $MSSTS->orderSequence();
        $json = array(
          'msg' => 'ok',
          'infotxt' => $msg_kbase45,
          'buttons' => array(
            '<a href="?p=statusman">' . $msgloballang4_3[3] . '</a>',
            '<a href="?p=status">' . $msg_dept . '</a>'
          )
        );
        break;
      case 'statdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSSTS->deleteStatuses();
        }
        break;
    }
    $json = array(
      'msg' => 'ok',
      'info' => (isset($json['infotxt']) ? $json['infotxt'] : ''),
      'html' => (isset($json['html']) ? $json['html'] : ''),
      'buttons' => (!empty($json['buttons']) ? $json['buttons'] : array()),
      'delconfirm' => (isset($rows) ? $rows : '0')
    );
    break;

  //=========================
  // Priority levels
  //=========================

  case 'levels':
  case 'levseq':
  case 'levdel':
    include_once(PATH . 'control/classes/class.levels.php');
    $MSLVL = new levels();
    switch($_GET['ajax']) {
      case 'levels':
        if (isset($_POST['process'])) {
          if (isset($_POST['name']) && $_POST['name']) {
            $ID = $MSLVL->addLevel();
            $json = array(
              'infotxt' => $msg_levels7,
              'buttons' => array(
                '<a href="?p=levels&amp;edit=' . $ID . '">' . $msg_script9 . '</a>',
                '<a href="?p=levelsman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=levels">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang3_7prlevels[4],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        if (isset($_POST['update'])) {
          if (isset($_POST['name']) && $_POST['name']) {
            $MSLVL->updateLevel();
            $json = array(
              'infotxt' => $msg_levels12,
              'buttons' => array(
                '<a href="?p=levels&amp;edit=' . (int) $_POST['update'] . '">' . $msg_script9 . '</a>',
                '<a href="?p=levelsman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=levels">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang3_7prlevels[4],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        break;
      case 'levseq':
        $MSLVL->orderSequence();
        $json = array(
          'msg' => 'ok',
          'infotxt' => $msg_kbase45,
          'buttons' => array(
            '<a href="?p=levelsman">' . $msgloballang4_3[3] . '</a>',
            '<a href="?p=levels">' . $msg_dept . '</a>'
          )
        );
        break;
      case 'levdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSLVL->deleteLevels();
        }
        break;
    }
    $json = array(
      'msg' => 'ok',
      'info' => (isset($json['infotxt']) ? $json['infotxt'] : ''),
      'buttons' => (!empty($json['buttons']) ? $json['buttons'] : array()),
      'delconfirm' => (isset($rows) ? $rows : '0')
    );
    break;

  //=========================
  // Department
  //=========================

  case 'dept':
  case 'deptseq':
  case 'depdel':
    include_once(PATH . 'control/classes/class.departments.php');
    $MSDEPT = new departments();
    switch($_GET['ajax']) {
      case 'dept':
        if (isset($_POST['process'])) {
          if (isset($_POST['name']) && $_POST['name']) {
            if (LICENCE_VER == 'locked') {
              if ((mswSQL_rows('departments') + 1) > RESTR_DEPTS) {
                $json = array(
                  'msg' => 'err',
                  'info' => 'Free version restriction. Max allowed: ' . RESTR_DEPTS,
                  'sys' => $msadminlang3_1[2]
                );
                echo $JSON->encode($json);
                exit;
              }
            }
            $ID = $MSDEPT->add($MSTEAM->id);
            $json = array(
              'infotxt' => $msg_dept7,
              'buttons' => array(
                '<a href="?p=dept&amp;edit=' . $ID . '">' . $msg_script9 . '</a>',
                '<a href="?p=deptman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=dept">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang_dept_3_7[1],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        if (isset($_POST['update'])) {
          if (isset($_POST['name']) && $_POST['name']) {
            $MSDEPT->update();
            $json = array(
              'infotxt' => $msg_dept12,
              'buttons' => array(
                '<a href="?p=dept&amp;edit=' . (int) $_POST['update'] . '">' . $msg_script9 . '</a>',
                '<a href="?p=deptman">' . $msgloballang4_3[3] . '</a>',
                '<a href="?p=dept">' . $msg_dept . '</a>'
              )
            );
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msadminlang_dept_3_7[1],
              'sys' => $msadminlang3_1[2]
            );
            echo $JSON->encode($json);
            exit;
          }
        }
        break;
      case 'deptseq':
        $MSDEPT->order();
        $json = array(
          'msg' => 'ok',
          'infotxt' => $msg_kbase45,
          'buttons' => array(
            '<a href="?p=deptman">' . $msgloballang4_3[3] . '</a>',
            '<a href="?p=dept">' . $msg_dept . '</a>'
          )
        );
        break;
      case 'depdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSDEPT->delete();
        }
        break;
    }
    $json = array(
      'msg' => 'ok',
      'info' => (isset($json['infotxt']) ? $json['infotxt'] : ''),
      'buttons' => (!empty($json['buttons']) ? $json['buttons'] : array()),
      'delconfirm' => (isset($rows) ? $rows : '0')
    );
    break;

  //=========================
  // Settings / Tools
  //=========================

  case 'tlsettings':
  case 'tlpurge':
  case 'tlendis':
  case 'tlreset':
  case 'delmenu':
    switch ($_GET['ajax']) {
      case 'tlsettings':
        $MSSET->upload = $MSUPL;
        if ($_POST['email'] == '') {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msadminlang_settings_3_7[17]
          );
        } else {
          $MSSET->updateSettings();
          $json = array(
            'msg' => 'ok',
            'info' => $msg_settings8,
            'buttons' => array(
              '<a href="?p=settings">' . $msgloballang4_3[4] . '</a>'
            )
          );
        }
        break;
      case 'tlpurge':
        if (isset($_POST['type'])) {
          switch($_POST['type']) {
            case 'tickets':
              if (USER_DEL_PRIV == 'yes' || USER_ADMINISTRATOR == 'yes') {
                if (isset($_POST['days1']) && (int) $_POST['days1'] > 0 && !empty($_POST['dept1'])) {
                  $counts = $MSTICKET->purgeTickets();
                  $json = array(
                    'msg' => 'ok-tools',
                    'report' => str_replace(array('{count1}', '{count2}', '{count3}'),array($counts[0], $counts[1], $counts[2]), $msg_tools8),
                    'sys' => $msadminlang3_1[18]
                  );
                }
              }
              break;
            case 'attachments':
              if (USER_DEL_PRIV == 'yes' || USER_ADMINISTRATOR == 'yes') {
                if (isset($_POST['days2']) && (int) $_POST['days2'] > 0 && !empty($_POST['dept2'])) {
                  $counts = $MSTICKET->purgeAttachments();
                  $json = array(
                    'msg' => 'ok-tools',
                    'report' => str_replace('{count}', $count, $msg_tools9),
                    'sys' => $msadminlang3_1[18]
                  );
                }
              }
              break;
            case 'accounts':
              if (USER_DEL_PRIV == 'yes' || USER_ADMINISTRATOR == 'yes') {
                if (isset($_POST['days3']) && (int) $_POST['days3'] > 0) {
                  $data  = $MSPTL->purgeAccounts();
                  $count = count($data);
                  if ($count > 0 && isset($_POST['mail'])) {
                    foreach ($data AS $k => $v) {
                      $pLang = (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language);
                      $mailT = LANG_BASE_PATH . (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language) . '/mail-templates/account-deleted.txt';
                      if ($v['lang'] && file_exists(LANG_BASE_PATH . $v['lang'] . '/mail-templates/account-deleted.txt')) {
                        $mailT = LANG_BASE_PATH . $v['lang'] . '/mail-templates/account-deleted.txt';
                        $pLang = $v['lang'];
                      }
                      $MSMAIL->addTag('{NAME}', $v['name']);
                      $MSMAIL->sendMSMail(array(
                        'from_email' => $SETTINGS->email,
                        'from_name' => $SETTINGS->website,
                        'to_email' => $v['email'],
                        'to_name' => $v['name'],
                        'subject' => str_replace(array(
                          '{website}'
                        ), array(
                          $SETTINGS->website
                        ), $emailSubjects['acc-deletion']),
                        'replyto' => array(
                          'name' => $SETTINGS->website,
                          'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                        ),
                        'template' => $mailT,
                        'language' => $pLang,
                        'alive' => 'yes'
                      ));
                    }
                    $MSMAIL->smtpClose();
                  }
                }
                $json = array(
                  'msg' => 'ok-tools',
                  'report' => str_replace('{count}', $count, $msg_tools25),
                  'sys' => $msadminlang3_1[18]
                );
              }
              break;
          }
        }
        break;
      case 'tlendis':
        if (!empty($_POST['tbls']) && in_array($_POST['action'], array('enable','disable'))) {
          $MSSET->batchEnableDisable($batchEnDisFields);
          $json = array(
            'msg' => 'ok'
          );
        } else {
          $json = array(
            'msg' => 'err',
            'sys' => $msadminlang3_1[2],
            'info' => $msadminlang3_1[17]
          );
          echo $JSON->encode($json);
          exit;
        }
        break;
      case 'tlreset':
        if (USER_ADMINISTRATOR == 'yes') {
          $cnt = array(
            0,
            0
          );
          // Account visitors..
          if (isset($_POST['visitors'])) {
            $qA = mswSQL_query("SELECT `name`,`email`,`language` FROM `" . DB_PREFIX . "portal`
                  " . (!isset($_POST['disabled']) ? 'WHERE `enabled` = \'yes\'' : '') . "
                  GROUP BY `email`
                  ORDER BY `name`
                  ", __file__, __line__);
            while ($ACC = mswSQL_fetchobj($qA)) {
              $pLang = '';
              if ($ACC->language && file_exists(LANG_BASE_PATH . $ACC->language . '/mail-templates/html-wrapper.html')) {
                $pLang = $ACC->language;
              }
              // New password..
              $newPass = $MSACC->ms_password($ACC->email, $MSACC->ms_generate());
              // Send email..
              if (isset($_POST['sendmail'])) {
                $MSMAIL->addTag('{NAME}', $ACC->name);
                $MSMAIL->addTag('{EMAIL}', $ACC->email);
                $MSMAIL->addTag('{PASS}', $newPass);
                $MSMAIL->addTag('{LOGIN_URL}', $SETTINGS->scriptpath . '/?p=login');
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $ACC->email,
                  'to_name' => $ACC->name,
                  'subject' => str_replace(array(
                    '{website}'
                  ), array(
                    $SETTINGS->website
                  ), $emailSubjects['reset']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => $_POST['message'],
                  'language' => ($pLang ? $pLang : (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language)),
                  'alive' => 'yes'
                ));
              }
            }
            $cnt[0] = mswSQL_numrows($qA);
          }
          // Support team..
          if (isset($_POST['team'])) {
            $qU = mswSQL_query("SELECT `id`,`name`,`email`,`language` FROM `" . DB_PREFIX . "users`
                  WHERE `id` > 1
                  " . (!isset($_POST['disabled']) ? 'AND `enabled` = \'yes\'' : '') . "
                  GROUP BY `email`
                  ORDER BY `name`
                  ", __file__, __line__);
            while ($USR = mswSQL_fetchobj($qU)) {
              // New password..
              $newPass = $MSUSERS->password($USR->id, $MSACC->ms_generate());
              // Send email..
              if (isset($_POST['sendmail'])) {
                $langSet = $SETTINGS->language;
                if ($USR->language) {
                  $langSet = $USR->language;
                }
                $MSMAIL->addTag('{NAME}', $USR->name);
                $MSMAIL->addTag('{EMAIL}', $USR->email);
                $MSMAIL->addTag('{PASS}', $newPass);
                $MSMAIL->addTag('{LOGIN_URL}', $SETTINGS->scriptpath . '/' . $SETTINGS->afolder);
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $USR->email,
                  'to_name' => $USR->name,
                  'subject' => str_replace(array(
                    '{website}'
                  ), array(
                    $SETTINGS->website
                  ), $emailSubjects['reset']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => $_POST['message'],
                  'language' => $langSet,
                  'alive' => 'yes'
                ));
              }
            }
            $MSMAIL->smtpClose();
            $cnt[1] = mswSQL_numrows($qU);
          }
          $json = array(
            'msg' => 'ok-tools',
            'report' => str_replace(array('{count}', '{count2}'),array(mswNFM($cnt[0]), mswNFM($cnt[1])), $msg_tools18),
            'sys' => $msadminlang3_1[19]
          );
        }
        break;
      case 'delmenu':
        $MSSET->resetMenu();
        $json = array(
          'msg' => 'ok',
          'info' => $msadminlang4_3[14],
          'buttons' => array(
            '<a href="?p=settings">' . $msgloballang4_3[4] . '</a>'
          )
        );
        break;
    }
    break;

  //===========================
  // Entry Log
  //===========================

  case 'logdel':
  case 'logclr':
  case 'log':
    switch($_GET['ajax']) {
      case 'logdel':
        if (USER_DEL_PRIV == 'yes') {
          $rows = $MSSET->deleteLogs();
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'logclr':
        if (USER_DEL_PRIV == 'yes') {
          $MSSET->clearLogFile();
          $json = array(
            'msg' => 'ok'
          );
        }
        break;
      case 'log':
        include(BASE_PATH . 'control/classes/system/class.download.php');
        $MSDL = new msDownload();
        $file = $MSSET->exportLogFile($MSDL);
        switch($file) {
          case 'err':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => str_replace('{path}', PATH . 'export', $msadminlang3_1backup[0])
            );
            echo $JSON->encode($json);
            exit;
            break;
          case 'none':
            $json = array(
              'msg' => 'err',
              'sys' => $msadminlang3_1[2],
              'info' => $msadminlang3_1[8]
            );
            echo $JSON->encode($json);
            exit;
            break;
          default:
            $json = array(
              'msg' => 'ok-dl',
              'file' => ADMIN_FLDR . '/export/' . basename($file),
              'type' => 'text/csv'
            );
            echo $JSON->encode($json);
            exit;
            break;
        }
        break;
    }
    if ($json['msg'] != 'err') {
      $json = array(
        'msg' => 'ok',
        'delconfirm' => (isset($rows) ? $rows : '0')
      );
    }
    break;

  //===========================
  // Backup
  //===========================

  case 'backup':
    include(BASE_PATH . 'control/classes/class.backup.php');
    if (!is_writeable(BASE_PATH . 'backups') || !is_dir(BASE_PATH . 'backups')) {
      $json = array(
        'msg' => 'err',
        'sys' => $msadminlang3_1[2],
        'info' => str_replace('{path}', BASE_PATH . 'backups', $msadminlang3_1backup[0])
      );
    } else {
      $time     = date('H:i:s', $MSDT->mswTimeStamp());
      $download = (isset($_POST['download']) ? 'yes' : 'no');
      $compress = (isset($_POST['compress']) ? 'yes' : 'no');
      // Force download if off and no emails..
      if ($download == 'no' && $_POST['emails'] == '') {
        $download = 'yes';
      }
      // File path..
      if ($compress == 'yes') {
        $filepath = BASE_PATH . 'backups/' . $msg_script33 . '-' . date('dMY', $MSDT->mswTimeStamp()) . '-' . date('His', $MSDT->mswTimeStamp()) . '.gz';
      } else {
        $filepath = BASE_PATH . 'backups/' . $msg_script33 . '-' . date('dMY', $MSDT->mswTimeStamp()) . '-' . date('His', $MSDT->mswTimeStamp()) . '.sql';
      }
      // Save backup..
      $BACKUP           = new dbBackup($filepath, ($compress == 'yes' ? true : false));
      $BACKUP->settings = $SETTINGS;
      $BACKUP->dt       = $MSDT;
      $BACKUP->doDump();
      // Copy email addresses if set..
      if ($_POST['emails'] && file_exists($filepath)) {
        // Update backup emails..
        $MSSET->updateBackupEmails();
        // Check how many emails we have..
        $emails = array();
        if (strpos($_POST['emails'], ',') !== false) {
          $emails = array_map('trim', explode(',', $_POST['emails']));
        } else {
          $emails[] = $_POST['emails'];
        }
        // Message tags..
        $MSMAIL->addTag('{HELPDESK}', mswCD($SETTINGS->website));
        $MSMAIL->addTag('{DATE_TIME}', $MSDT->mswDateTimeDisplay($MSDT->mswTimeStamp(), $SETTINGS->dateformat) . ' @ ' . $MSDT->mswDateTimeDisplay($MSDT->mswTimeStamp(), $SETTINGS->timeformat));
        $MSMAIL->addTag('{VERSION}', SCRIPT_VERSION);
        $MSMAIL->addTag('{FILE}', basename($filepath));
        $MSMAIL->addTag('{SCRIPT}', SCRIPT_NAME);
        $MSMAIL->addTag('{SIZE}', mswFSC(@filesize($filepath)));
        // Send emails..
        foreach ($emails AS $recipient) {
          $MSMAIL->attachments[$filepath] = basename($filepath);
          $MSMAIL->sendMSMail(array(
            'from_email' => $SETTINGS->email,
            'from_name' => $SETTINGS->website,
            'to_email' => $recipient,
            'to_name' => $recipient,
            'subject' => str_replace(array(
              '{website}',
              '{date}',
              '{time}'
            ), array(
              $SETTINGS->website,
              $MSDT->mswDateTimeDisplay($MSDT->mswTimeStamp(), $SETTINGS->dateformat),
              $time
            ), $emailSubjects['db-backup']),
            'replyto' => array(
              'name' => $SETTINGS->website,
              'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
            ),
            'template' => LANG_PATH . 'backup.txt',
            'language' => (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language),
            'alive' => 'yes'
          ));
        }
        $MSMAIL->smtpClose();
      }
      // Download file if applicable..
      if ($download == 'yes' && file_exists($filepath)) {
        $json = array(
          'msg' => 'ok-dl',
          'file' => 'backups/' . basename($filepath),
          'type' => 'text/plain'
        );
      } else {
        // Clear file from server..
        if (file_exists($filepath)) {
          @unlink($filepath);
        }
        $json = array(
          'msg' => 'ok',
          'info' => $msg_script32
        );
      }
    }
    break;

  //===========================
  // Report
  //===========================

  case 'report':
    include(BASE_PATH . 'control/classes/system/class.download.php');
    $MSDL = new msDownload();
    $file = $MSSET->exportReportCSV($MSDL);
    switch($file) {
      case 'err':
        $json = array(
          'msg' => 'err',
          'sys' => $msadminlang3_1[2],
          'info' => str_replace('{path}', PATH . 'export', $msadminlang3_1backup[0])
        );
        break;
      case 'none':
        $json = array(
          'msg' => 'err',
          'sys' => $msadminlang3_1[2],
          'info' => $msadminlang3_1[8]
        );
        break;
      default:
        $json = array(
          'msg' => 'ok-dl',
          'file' => ADMIN_FLDR . '/export/' . basename($file),
          'type' => 'text/csv'
        );
        break;
    }
    break;

  //===========================
  // Password generator..
  //===========================

  case 'passgen':
    $pass = $MSACC->ms_generate();
    $json = array(
      'pass' => $pass
    );
    break;

  //=============================
  // Dispute account search..
  //=============================

  case 'dispute-users':
    $searched = $MSTICKET->searchDisputeUsers();
    if (empty($searched)) {
      $json = array(
        'text' => $msg_viewticket117
      );
    } else {
      $json = $searched;
    }
    break;

  //======================
  // Mail Test
  //======================

  case 'mailtest':
    $cnt    = 0;
    $others = '';
    if (isset($_POST['emails'])) {
      $list = array_map('trim', explode(',', $_POST['emails']));
      if (!empty($list)) {
        $cnt   = count($list);
        $first = $list[0];
        unset($list[0]);
        if (!empty($list)) {
          $others = implode(',', $list);
        }
        // Send test..
        $MSMAIL->sendMSMail(array(
          'from_email' => $SETTINGS->email,
          'from_name' => $SETTINGS->website,
          'to_email' => $first,
          'to_name' => $SETTINGS->website,
          'subject' => str_replace(array(
            '{website}'
          ), array(
            $SETTINGS->website
          ), $emailSubjects['test-message']),
          'replyto' => array(
            'name' => $SETTINGS->website,
            'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
          ),
          'template' => str_replace('{website}', $SETTINGS->website, $msg_script_action10),
          'language' => (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language),
          'add-emails' => $others
        ));
      }
    }
    $json = array(
      'msg' => str_replace('{count}', $cnt, $msg_script_action9)
    );
    break;

  //==================
  // Login
  //==================

  case 'login':
    if (isset($_POST['cs_rf']) && $SSN->active('csrf_token') == 'yes' && $_POST['cs_rf'] == $SSN->get('csrf_token')) {
      if (isset($_POST['user'],$_POST['pass']) && $_POST['user'] && $_POST['pass']) {
        if (!mswIsValidEmail($_POST['user'])) {
          $json = array(
            'msg' => 'err',
            'info' => $msg_login6
          );
        } else {
          $USER = mswSQL_table('users', 'email', mswSQL($_POST['user']), ' AND `enabled` = \'yes\'');
          if (isset($USER->email) && mswPassHash(array('type' => 'calc', 'val' => $_POST['pass'], 'hash' => $USER->accpass))) {
            $json['msg'] = 'ok';
            // Update page access..
            if ($USER->id > 0) {
              $upa              = userAccessPages($USER->id);
              $USER->pageAccess = $upa;
            }
            // Add entry log..
            if ($USER->enableLog == 'yes') {
              $MSUSERS->log($USER);
            }
            // Set session..
            $SSN->set(array('_ms_mail' => $USER->email, '_ms_key' => $USER->accpass));
            // Set cookie..
            if (isset($_POST['cookie']) && COOKIE_NAME) {
              if ((COOKIE_SSL && mswSSL() == 'yes') || !COOKIE_SSL) {
                $SSN->set_c(array(
                  array(
                    '_msc_mail',
                    $USER->email,
                    time() + 60 * 60 * 24 * COOKIE_EXPIRY_DAYS
                  ),
                  array(
                    '_msc_key',
                    $USER->accpass,
                    time() + 60 * 60 * 24 * COOKIE_EXPIRY_DAYS
                  )
                ));
              }
            }
            // Run some cleanup ops..
            $MSSET->cleanUpOps();
            // Check for ticket vars..
            if ($SSN->active('thisTicket') == 'yes') {
              $thisTicket = mswReverseTicketNumber($SSN->get('thisTicket'));
              $SUPTICK    = mswSQL_table('tickets', 'id', $thisTicket);
              $SSN->delete(array('thisTicket'));
              $userAccess = explode('|', $USER->pageAccess);
              if ($SUPTICK->assignedto == 'waiting' && (in_array('assign', $userAccess) || $USER->id == 1)) {
                $json['redirect'] = 'index.php?p=assign';
              } elseif ($SUPTICK->assignedto == 'waiting' && !in_array('assign', $userAccess)) {
                $json['redirect'] = 'index.php';
              } else {
                $json['redirect'] = 'index.php?p=view-' . (isset($SUPTICK->isDisputed) && $SUPTICK->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&id=' . $thisTicket;
              }
            } else {
              // Do we have any unread messages?
              // If yes, do we redirect to mailbox?
              if ($USER->mailbox == 'yes' && $USER->mailScreen == 'yes') {
                if (mswSQL_rows('mailassoc WHERE `staffID` = \'' . $USER->id . '\' AND `folder` = \'inbox\' AND `status` = \'unread\'') > 0) {
                  $json['redirect'] = 'index.php?p=mailbox';
                }
              }
              $json['redirect'] = 'index.php';
            }
          } else {
            $json = array(
              'msg' => 'err',
              'info' => $msg_login4
            );
          }
        }
      }
    }
    break;

  //==================
  // Auto Path
  //==================

  case 'autopath':
    switch ($_GET['type']) {
      case 'http':
        $svr  = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $path = 'http://' . substr($svr, 0, strpos($svr, $SETTINGS->afolder)) . 'content/' . $msg_settings128;
        break;
      default:
        $spt  = PATH;
        $path = BASE_PATH . 'content/' . $msg_settings128;
        break;
    }
    $json = array(
      'path' => $path
    );
    break;

  //======================
  // File Download
  //======================

  case 'fdl':
    if (isset($_GET['infp']) && isset($_GET['infpt']) && file_exists(BASE_PATH . $_GET['infp'])) {
      include(BASE_PATH . 'control/classes/system/class.download.php');
      $MSDL = new msDownload();
      $MSDL->dl(BASE_PATH . $_GET['infp'], $_GET['infpt']);
      exit;
    }
    break;

  //=======================
  // Search Accounts
  //=======================

  case 'search-accounts':
    $field = (isset($_POST['ffld']) ? $_POST['ffld'] : 'name');
    $value = (isset($_POST['fval']) ? $_POST['fval'] : '');
    $email = (isset($_POST['emal']) ? $_POST['emal'] : '');
    if (in_array($field, array('name','email','dest_email')) && $value) {
      $ret = $MSPTL->searchAccounts($field, $value, $email);
      if (!empty($ret)) {
        $json = array(
          'msg' => 'ok',
          'accounts' => $ret
        );
      } else {
        $json = array(
          'msg' => 'err',
          'info' => $msadminlang3_1[25],
          'sys' => $msadminlang3_1[2]
        );
      }
    }
    break;

  //=======================
  // Auto Complete
  //=======================

  case 'auto-users':
  case 'auto-response':
  case 'auto-merge':
  case 'auto-search-acc':
  case 'auto-search-team':
    switch($_GET['ajax']) {
      case 'auto-users':
        if (isset($_GET['term'])) {
          $arr = $MSPTL->autoSearch((in_array('accounts', $userAccess) || USER_ADMINISTRATOR == 'yes' ? 'yes' : 'no'));
        }
        if (!empty($arr)) {
          echo $JSON->encode($arr);
        } else {
          echo $JSON->encode(array($msadminlang3_1adminviewticket[10]));
        }
        break;
      case 'auto-response':
        if (isset($_GET['term'])) {
          include_once(PATH . 'control/classes/class.responses.php');
          $MSSTR           = new standardResponses();
          $MSSTR->settings = $SETTINGS;
          $MSSTR->ssn      = $SSN;
          $arr             = $MSSTR->autoSearch();
        }
        if (!empty($arr)) {
          echo $JSON->encode($arr);
        } else {
          echo $JSON->encode(array($msadminlang3_1adminviewticket[10]));
        }
        break;
      case 'auto-merge':
        if (isset($_GET['term'])) {
          $arr = $MSTICKET->mergeSearch($ticketFilterAccess,$msadminlang3_1adminviewticket[16]);
        }
        if (!empty($arr)) {
          echo $JSON->encode($arr);
        } else {
          echo $JSON->encode(array($msadminlang3_1adminviewticket[10]));
        }
        break;
      case 'auto-search-acc':
        if (isset($_GET['term'])) {
          $arr = $MSPTL->searchAccountsPages($_GET['term']);
        }
        if (!empty($arr)) {
          echo $JSON->encode($arr);
        } else {
          echo $JSON->encode(array($msadminlang3_1adminviewticket[10]));
        }
        break;
      case 'auto-search-team':
        if (isset($_GET['term'])) {
          $arr = $MSUSERS->searchTeamPages($_GET['term']);
        }
        if (!empty($arr)) {
          echo $JSON->encode($arr);
        } else {
          echo $JSON->encode(array($msadminlang3_1adminviewticket[10]));
        }
        break;
    }
    exit;
    break;

  //=======================
  // Version Check
  //=======================

  case 'vc':
    $html = $MSSET->mswSoftwareVersionCheck();
    echo $JSON->encode(array(
      'html' => mswNL2BR($html)
    ));
    exit;
    break;

  //=======================
  // API Key
  //=======================

  case 'api-key':
    $length = (API_KEY_LENGTH > 100 ? 100 : API_KEY_LENGTH);
    $chars  = array_merge(range(1, 9), range('A', 'Z'), array(
      '-',
      '-',
      '-'
    ));
    shuffle($chars);
    $key = '';
    for ($i = 0; $i < $length; $i++) {
      shuffle($chars);
      $key .= $chars[rand(1, 9)];
    }
    echo $JSON->encode(array(
      'key' => trim($key)
    ));
    exit;
    break;

  //=======================
  // Password Reset
  //=======================

  case 'pass-reset':
    if (defined('PASS_RESET')) {
      if (empty($_POST['id'])) {
        $json = array(
          'msg' => 'err',
          'info' => $msadminlang3_1[23],
          'sys' => $msadminlang3_1[2],
          'delconfirm' => 0
        );
        echo $JSON->encode($json);
        exit;
      }
      $ret = $MSUSERS->reset($MSACC);
      if (isset($_POST['sendem']) && !empty($ret)) {
        for ($i = 0; $i < count($ret); $i++) {
          $q = mswSQL_query("SELECT `id`,`name`,`email`,`email2`,`language` FROM `" . DB_PREFIX . "users`
               WHERE `id` = '{$ret[$i]['id']}'
               ", __file__, __line__);
          while ($USERS = mswSQL_fetchobj($q)) {
            $langFile = BASE_PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/admin-pass-reset.txt';
            $langSet = $SETTINGS->language;
            if ($USERS->language && file_exists(BASE_PATH . 'content/language/' . $USERS->language . '/mail-templates/admin-pass-reset.txt')) {
              $langSet = $USERS->language;
              $langFile = BASE_PATH . 'content/language/' . $USERS->language . '/mail-templates/admin-pass-reset.txt';
            }
            $MSMAIL->addTag('{NAME}', $USERS->name);
            $MSMAIL->addTag('{EMAIL}', $USERS->email);
            $MSMAIL->addTag('{PASS}', $ret[$i]['pass']);
            // Send mail..
            $MSMAIL->sendMSMail(array(
              'from_email' => $SETTINGS->email,
              'from_name' => $SETTINGS->website,
              'to_email' => $USERS->email,
              'to_name' => $USERS->name,
              'subject' => str_replace(array(
                '{website}',
                '{user}'
              ), array(
                $SETTINGS->website,
                $USERS->name
              ), $emailSubjects['reset']),
              'replyto' => array(
                'name' => $SETTINGS->website,
                'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
              ),
              'template' => $langFile,
              'language' => $langSet,
              'alive' => 'yes',
              'add-emails' => $USERS->email2
            ));
          }
          $MSMAIL->smtpClose();
        }
      }
      $json['msg'] = 'ok';
      $json['info'] = $msgloballang4_3[12];
    }
    break;

  case 'menu-panel':
    $SSN->set(array('adm_menu_panel' => preg_replace('/[^0-9a-zA-Z]/', '', $_GET['pnl'])));
    $arr['status'] = 'ok';
    break;

  //--------------------------
  // Attachment download..
  //--------------------------

  case 'dl':
  case 'token':
    $json = array(
      'status' => 'err',
      'msg' => $mspubliclang3_7[6]
    );
    switch ($_GET['ajax']) {
      case 'dl':
        if (isset($_GET['id'])) {
          $A = mswSQL_table('faqattach', 'id', (int) $_GET['id'], ' AND `enAtt` = \'yes\'');
          if (isset($A->id)) {
            if ($A->remote) {
              $json['status'] = 'remote';
              $json['remote'] = $A->remote;
            } else {
              if (isset($A->id) && $A->path && @file_exists($SETTINGS->attachpathfaq . '/' . $A->path)) {
                $json['status'] = 'token';
                $json['token'] = $A->id;
              }
            }
          }
        }
        break;
      case 'token':
        if (isset($_GET['cde'])) {
          $A = mswSQL_table('faqattach', 'id', (int) $_GET['cde'], ' AND `enAtt` = \'yes\'');
          if (isset($A->id)) {
            include(BASE_PATH . 'control/classes/system/class.download.php');
            $D = new msDownload();
            $m = $D->mime($SETTINGS->attachpathfaq . '/' . $A->path, $A->mimeType);
            $D->dl($SETTINGS->attachpathfaq . '/' . $A->path, $m, 'no');
            exit;
          }
        }
        break;
    }
    break;

  //---------------------
  // Ticket attachments
  //---------------------

  case 'dla':
  case 'tokena':
    $json = array(
      'status' => 'err',
      'msg' => $mspubliclang3_7[6]
    );
    switch ($_GET['ajax']) {
      case 'dla':
        if (isset($_GET['id'])) {
          $A = mswSQL_table('attachments', 'id', (int) $_GET['id'], '', '*,DATE(FROM_UNIXTIME(`ts`)) AS `addDate`');
          $SUPTICK = mswSQL_table('tickets', 'id', $A->ticketID);
          if (isset($A->id) && mswDeptPerms($A->department, $userDeptAccess, array('assigned' => $SUPTICK->assignedto, 'team' => $MSTEAM->id)) != 'fail') {
            $split = explode('-', $A->addDate);
            $base  = $SETTINGS->attachpath . '/';
            // Check for newer folder structure..
            // Earlier versions had no sub folders..
            if (@file_exists($SETTINGS->attachpath . '/' . $split[0] . '/' . $split[1] . '/' . $A->fileName)) {
              $base = $SETTINGS->attachpath . '/' . $split[0] . '/' . $split[1] . '/';
            }
            if (isset($A->id) && $A->fileName && @file_exists($base . $A->fileName)) {
              $json['status'] = 'token';
              $json['token'] = $A->id;
            }
          }
        }
        break;
      case 'tokena':
        if (isset($_GET['cde'])) {
          $A = mswSQL_table('attachments', 'id', (int) $_GET['cde'], '', '*,DATE(FROM_UNIXTIME(`ts`)) AS `addDate`');
          $SUPTICK = mswSQL_table('tickets', 'id', $A->ticketID);
          if (isset($A->id) && mswDeptPerms($A->department, $userDeptAccess, array('assigned' => $SUPTICK->assignedto, 'team' => $MSTEAM->id)) != 'fail') {
            $split = explode('-', $A->addDate);
            $base  = $SETTINGS->attachpath . '/';
            // Check for newer folder structure..
            // Earlier versions had no sub folders..
            if (@file_exists($SETTINGS->attachpath . '/' . $split[0] . '/' . $split[1] . '/' . $A->fileName)) {
              $base = $SETTINGS->attachpath . '/' . $split[0] . '/' . $split[1] . '/';
            }
            include(BASE_PATH . 'control/classes/system/class.download.php');
            $D = new msDownload();
            $m = $D->mime($base . $A->fileName, $A->mimeType);
            $D->dl($base . $A->fileName, $m, 'no');
            exit;
          }
        }
        break;
    }
    break;

  //---------------------
  // Unread Mailbox
  //---------------------

  case 'unread-mailbox':
    $json = array(
      'cnt' => (isset($MSTEAM->id) ? mswUnreadMailbox($MSTEAM->id) : '0')
    );
    break;

}

if (defined('CONF_DIALOG') && isset($json['msg']) && $json['msg'] == 'ok') {
  $json['sys'] = $msadminlang3_1[2];
  $json['info'] = (isset($json['info']) && $json['info'] ? $json['info'] : $msadminlang3_7[9]);
  // If applicable, append control buttons
  if (!empty($json['buttons'])) {
    include(PATH . 'templates/system/control-btns.php');
    $json['info'] .= $c_b;
  }
}
// If we are this far, stop and parse json response..
echo $JSON->encode($json);
exit;

?>