<?php

/* System - Accounts
----------------------------------------------------------*/

// Load cleantalk..
include(PATH . 'control/classes/system/class.cleantalk.php');
$aspam_api       = $SOCIAL->params('ctalk');
$CTALK           = new cleanTalk();
$CTALK->settings = $SETTINGS;
$CTALK->social   = $aspam_api;
$CTALK->ssn      = $SSN;

// Spam settings..
$spamParams = array(
  'key' => (isset($aspam_api['ctalk']['key']) ? $aspam_api['ctalk']['key'] : ''),
  'enabled' => (isset($aspam_api['ctalk']['enabletk']) ? 'yes' : 'no'),
  'skip-login' => (isset($aspam_api['ctalk']['loggedin']) ? 'yes' : 'no'),
  'add-to-spam' => (isset($aspam_api['ctalk']['folder']) ? 'yes' : 'no'),
  'log' => (isset($aspam_api['ctalk']['log']) ? 'yes' : 'no')
);

// Is spam system enabled?
if ($spamParams['key'] && $spamParams['enabled'] == 'yes') {
  define('MSW_ANTI_SPAM_ENABLED', 1);
}

// Ticket creation..
$MSTICKET->upload = $MSUPL;

if (defined('AJAX_HANDLER') && isset($_POST['comments'], $_POST['priority'], $_POST['dept'])) {
  $isSpam = 'no';
  $skipSpam = 'no';
  // Is user logged in?
  if (MS_PERMISSIONS != 'guest' && isset($LI_ACC->name) && !isset($_POST['name'])) {
    $_POST['name']  = mswCD($LI_ACC->name);
    $_POST['email'] = $LI_ACC->email;
    $skipSpam = 'yes';
  }
  if (isset($_POST['name']) && $_POST['name'] == '') {
    $eFields[] = $msadminlang3_1createticket[1];
  }
  if (isset($_POST['email']) && !mswIsValidEmail($_POST['email'])) {
   $eFields[] = $msg_main13;
  }
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
  // Check for open tickets..
  if (empty($eFields) && $MSTICKET->isTicketOpen(array(
    'acc' => (MS_PERMISSIONS != 'guest' && isset($LI_ACC->id) ? $LI_ACC->id : '0'),
    'email' => $_POST['email']
  )) == 'yes') {
    $eFields[] = $msadminlang3_7createticket[0];
  }
  // Spam check..
  if (empty($eFields) && defined('MSW_ANTI_SPAM_ENABLED') && $skipSpam == 'no') {
    $ctkc = $CTALK->check(array(
      'method' => 'check_message',
      'email' => (isset($_POST['email']) ? $_POST['email'] : ''),
      'name' => (isset($_POST['name']) ? $_POST['name'] : ''),
      'comms' => (isset($_POST['comments']) ? $_POST['comments'] : ''),
      'ct_ts' => (isset($_POST['js_ts']) ? $_POST['js_ts']: '')
    ));
    if (!isset($ctkc['allow']) || $ctkc['allow'] == 0) {
      $eFields[] = $msadminlang_public_3_7[0];
      $json = array(
        'status' => 'err',
        'msg' => implode('<br>', $eFields)
      );
      // For version 3.1+
      $other = array(
        'sys' => $msadminlang3_1[2]
      );
      // Stop here..
      echo $MSJSON->encode(array_merge($json, $other));
      exit;
    }
  }
  // If there are errors at this point, we can stop.
  if (!empty($eFields)) {
    $json = array(
      'status' => 'err',
      'msg' => implode('<br>', $eFields)
    );
    // For version 3.1+
    $other = array(
      'sys' => $msadminlang3_1[2]
    );
    // Stop here..
    echo $MSJSON->encode(array_merge($json, $other));
    exit;
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
  // If not logged in, lets see if this account exists..
  if (!isset($LI_ACC->id)) {
    $LI_ACC = mswSQL_table('portal', 'email', mswSQL(strtolower($_POST['email'])));
  }
  // Check required custom fields..
  $customCheckFields = $MSFIELDS->check('ticket', (int) $_POST['dept'], (isset($LI_ACC->id) ? $LI_ACC->id : '0'));
  if (!empty($customCheckFields)) {
    $eFields[] = str_replace('{count}', count($customCheckFields), $msadminlang3_1createticket[8]) . '<hr>' . implode('<br>', $customCheckFields);
  }
  // All ok?
  if (empty($eFields) && isset($_POST['dept']) && (int) $_POST['dept'] > 0) {
    $deptID = (int) $_POST['dept'];
    // Department preferences..
    $DP = mswSQL_table('departments', 'id', $deptID, '', '`manual_assign`,`auto_response`,`response`,`response_sbj`');
    // Is person logged in or does person already have account?
    if (isset($LI_ACC->name)) {
      $name   = $LI_ACC->name;
      $email  = $LI_ACC->email;
      $pass   = '';
      $userID = $LI_ACC->id;
    } else {
      define('NEW_ACC_CREATION', 1);
      $name   = mswCD($_POST['name']);
      $email  = $_POST['email'];
      $pass   = $MSACC->ms_generate();
      $mailT  = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-account.txt';
      // Create account..
      $userID = $MSACC->add(array(
        'name' => $name,
        'email' => $email,
        'pass' => $pass,
        'enabled' => 'yes',
        'verified' => 'yes',
        'timezone' => $SETTINGS->timezone,
        'ip' => mswSQL(mswIP()),
        'notes' => '',
        'language' => $SETTINGS->language
      ));
      // Send email about new account..
      $MSMAIL->addTag('{ACC_NAME}', $name);
      $MSMAIL->addTag('{ACC_EMAIL}', $email);
      $MSMAIL->addTag('{PASS}', $pass);
      $MSMAIL->addTag('{LOGIN_URL}', $SETTINGS->scriptpath);
      $MSMAIL->sendMSMail(array(
        'from_email' => $SETTINGS->email,
        'from_name' => $SETTINGS->website,
        'to_email' => $email,
        'to_name' => $name,
        'subject' => str_replace(array(
          '{website}'
        ), array(
          $SETTINGS->website
        ), $emailSubjects['new-account']),
        'replyto' => array(
          'name' => $SETTINGS->website,
          'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
        ),
        'template' => $mailT,
        'alive' => 'yes',
        'language' => $SETTINGS->language
      ));
    }
    // Add ticket to database..
    if ($userID > 0) {
      $ID = $MSTICKET->add(array(
        'dept' => $deptID,
        'assigned' => ($DP->manual_assign == 'yes' ? 'waiting' : ''),
        'visitor' => $userID,
        'subject' => $_POST['subject'],
        'quoteBody' => '',
        'comments' => $_POST['comments'],
        'priority' => $_POST['priority'],
        'ticketStatus' => 'open',
        'ip' => mswSQL(mswIP()),
        'notes' => '',
        'disputed' => 'no'
      ));
      $ticketNumber = $MSTICKET->ticket($ID);
      // Proceed if ticket added ok..
      if ($ID > 0) {
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
                'tID' => $ID,
                'rID' => 0,
                'dept' => $deptID,
                'incr' => $i
              ));
              $attString[] = $SETTINGS->scriptpath . '/?attachment=' . $atID[0];
            }
          }
        }
        // Pass ticket number as custom mail header..
        $MSMAIL->xheaders['X-TicketNo'] = mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber);
        // Mail tags..
        $MSMAIL->addTag('{ACC_NAME}', $name);
        $MSMAIL->addTag('{ACC_EMAIL}', $email);
        $MSMAIL->addTag('{SUBJECT}', $MSBB->cleaner($_POST['subject']));
        $MSMAIL->addTag('{TICKET}', mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber));
        $MSMAIL->addTag('{DEPT}', $MSYS->department($deptID, $msg_script30));
        $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($_POST['priority']));
        $MSMAIL->addTag('{STATUS}', $MSYS->status('open', $ticketStatusSel));
        $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($_POST['comments']));
        $MSMAIL->addTag('{ATTACHMENTS}', (!empty($attString) ? implode(mswNL(), $attString) : $msg_script17));
        $MSMAIL->addTag('{CUSTOM}', $MSFIELDS->email($ID, 0));
        $MSMAIL->addTag('{ID}', $ID);
        // Send message to support staff if manual assign is off for department..
        // This doesn`t include administrators..
        if ($DP->manual_assign == 'no') {
          $qU = mswSQL_query("SELECT `" . DB_PREFIX . "users`.`name` AS `teamName`,`email`,`email2`,`language` FROM `" . DB_PREFIX . "userdepts`
                LEFT JOIN `" . DB_PREFIX . "departments`
                ON `" . DB_PREFIX . "userdepts`.`deptID`  = `" . DB_PREFIX . "departments`.`id`
                LEFT JOIN `" . DB_PREFIX . "users`
                ON `" . DB_PREFIX . "userdepts`.`userID`  = `" . DB_PREFIX . "users`.`id`
                WHERE `deptID`  = '{$deptID}'
                AND `admin`   = 'no'
                AND `notify`  = 'yes'
                GROUP BY `email`
				        ORDER BY `" . DB_PREFIX . "users`.`name`
                ", __file__, __line__);
          while ($STAFF = mswSQL_fetchobj($qU)) {
            $langFile = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-staff.txt';
            $langSet = $SETTINGS->language;
            if ($STAFF->language && file_exists(PATH . 'content/language/' . $STAFF->language . '/mail-templates/new-ticket-staff.txt')) {
              $langSet = $STAFF->language;
              $langFile = PATH . 'content/language/' . $STAFF->language . '/mail-templates/new-ticket-staff.txt';
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
                mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber)
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
        // Now send to admins..
        $qUA = mswSQL_query("SELECT `name`, `email`, `email2`, `language` FROM `" . DB_PREFIX . "users`
               WHERE `admin` = 'yes'
               AND `notify`  = 'yes'
               ORDER BY `id`
               ", __file__, __line__);
        while ($ASTAFF = mswSQL_fetchobj($qUA)) {
          $langFile = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-admin.txt';
          $langSet = $SETTINGS->language;
          if ($ASTAFF->language && file_exists(PATH . 'content/language/' . $ASTAFF->language . '/mail-templates/new-ticket-admin.txt')) {
            $langSet = $ASTAFF->language;
            $langFile = PATH . 'content/language/' . $ASTAFF->language . '/mail-templates/new-ticket-admin.txt';
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
              mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber),
              $ASTAFF->name
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
        // Send auto responder to person who opened ticket..
        if (!defined('NEW_ACC_CREATION') && file_exists(LANG_PATH . 'mail-templates/new-ticket-visitor.txt')) {
          $mailT = LANG_PATH . 'mail-templates/new-ticket-visitor.txt';
          $pLang = $LI_ACC->language;
        } else {
          $mailT = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-visitor.txt';
        }
        $depCusResponse = array();
        // Is custom department response enabled?
        if (property_exists($DP, 'auto_response') && $DP->auto_response == 'yes') {
          $depCusResponse['subject'] = $DP->response_sbj;
          $depCusResponse['message'] = $DP->response;
        }
        $MSMAIL->addTag('{NAME}', $name);
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
            mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber)
          ), $emailSubjects['new-ticket-vis']),
          'replyto' => array(
            'name' => $SETTINGS->website,
            'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
          ),
          'template' => $mailT,
          'dep' => $depCusResponse,
          'alive' => 'yes',
          'language' => (isset($pLang) ? $pLang : $SETTINGS->language)
        ));
        // Close smtp
        $MSMAIL->smtpClose();
        // Write history log..
        $MSTICKET->historyLog($ID, str_replace(array(
          '{visitor}'
        ), array(
          $name
        ), $msg_ticket_history['new-ticket-visitor']));
        // All done, so set session vars and show thanks page..
        $SSN->set(array(
          'create_id' => $ID,
          'create_email' => $email,
          'create_pass' => $pass,
          'create_tickno' => $ticketNumber
        ));
        $json = array(
          'status' => 'ok',
          'field' => 'redirect',
          'msg' => $SETTINGS->scriptpath . '/?p=tk-msg'
        );
      }
    }
  } else {
    $json = array(
      'status' => 'err',
      'msg' => implode('<br>', $eFields)
    );
  }
}

?>