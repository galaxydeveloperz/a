<?php

/* System - API - Create Tickets
----------------------------------------------------------*/

if (!defined('PARENT') || !defined('MS_PERMISSIONS') || !defined('API_LOADER')) {
  $HEADERS->err403();
}

// Load download class for mime types..
include(PATH . 'control/classes/system/class.download.php');
$DL = new msDownload();

// Load mailer params..
include(PATH . 'control/classes/mailer/mail-init.php');

// Ticket data array from API..
$added      = 0;
$ticketData = $MSAPI->ticket($read, $levelPrKeys);

// Loop data..
if (!empty($ticketData['tickets'])) {
  $countOfTickets = count($ticketData['tickets']);
  $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] ' . $countOfTickets . ' ticket(s) found in incoming data. Preparing to loop ticket(s)..');
  for ($i=0; $i < $countOfTickets; $i++) {
    $attString = array();
    $name      = (isset($ticketData['tickets'][$i]['name']) ? trim($ticketData['tickets'][$i]['name']) : '');
    $email     = (isset($ticketData['tickets'][$i]['email']) ? trim($ticketData['tickets'][$i]['email']) : '');
    // If name is blank, we use email address..
    if ($name == '') {
      $name = $email;
    }
    $deptID    = (isset($ticketData['tickets'][$i]['dept']) ? (int) trim($ticketData['tickets'][$i]['dept']) : '0');
    $subject   = (isset($ticketData['tickets'][$i]['subject']) ? trim($ticketData['tickets'][$i]['subject']) : '');
    $comments  = (isset($ticketData['tickets'][$i]['comments']) ? trim($ticketData['tickets'][$i]['comments']) : '');
    // Check values
    $in_prior  = (isset($ticketData['tickets'][$i]['priority']) ? trim($ticketData['tickets'][$i]['priority']) : '');
    $priority  = (isset($ticketLevelSel[$in_prior]) ? $in_prior : 'low');
    $in_sts    = (isset($ticketData['tickets'][$i]['status']) ? trim($ticketData['tickets'][$i]['status']) : '');
    $status    = (isset($ticketStatusSel[$in_sts][0]) ? $in_sts : 'open');
    $language  = (isset($ticketData['tickets'][$i]['language']) && is_dir(PATH . 'content/language/' . $ticketData['tickets'][$i]['language']) ? trim($ticketData['tickets'][$i]['language']) : 'english');
    $pLang     = $language;
    // Add ticket..
    if ($name && mswIsValidEmail($email) && $deptID > 0 && $subject && $comments && $priority) {
      $DP = mswSQL_table('departments', 'id', $deptID, '', '`manual_assign`,`auto_response`,`response`,`response_sbj`');
      if (isset($DP->manual_assign)) {
        // Does account exist?
        $LI_ACC = mswSQL_table('portal', 'email', mswSQL($email));
        if (isset($LI_ACC->id)) {
          $name   = $LI_ACC->name;
          $email  = $LI_ACC->email;
          $pass   = '';
          $userID = $LI_ACC->id;
          if (file_exists(PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/new-ticket-visitor.txt')) {
            $mailR = PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/new-ticket-visitor.txt';
            $pLang = $LI_ACC->language;
          } else {
            $mailR = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-visitor.txt';
          }
          $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Account does exist for ' . $email);
          $MSAPI->log('Email template for ' . $name . ' is ' . $mailR);
        } else {
          $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] New account to be created for email ' . $email);
          $pass   = $MSACC->ms_generate();
          $mailT  = PATH . 'content/language/' . $language . '/mail-templates/new-account.txt';
          $mailR  = PATH . 'content/language/' . $language . '/mail-templates/new-ticket-visitor.txt';
          // Create account..
          $userID = $MSACC->add(array(
            'name' => $name,
            'email' => $email,
            'pass' => $pass,
            'enabled' => 'yes',
            'verified' => 'yes',
            'timezone' => $SETTINGS->timezone,
            'ip' => '',
            'notes' => '',
            'language' => $language
          ));
          // Send email about new account..
          if ($userID > 0) {
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Account created successfully. ID: ' . $userID);
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
                '{website}',
                '{name}'
              ), array(
                $SETTINGS->website,
                $name
              ), $emailSubjects['new-account']),
              'replyto' => array(
                'name' => $SETTINGS->website,
                'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
              ),
              'template' => $mailT,
              'language' => $language,
              'alive' => 'yes'
            ));
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Email sent to ' . $name . ' <' . $email . '>');
          } else {
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error, account could not be created. Refer to the error log if it exists.');
          }
        }
        // Create ticket..
        if ($userID > 0) {
          $ID = $MSTICKET->add(array(
            'dept' => $deptID,
            'assigned' => ($DP->manual_assign == 'yes' ? 'waiting' : ''),
            'visitor' => $userID,
            'subject' => $subject,
            'quoteBody' => '',
            'comments' => $comments,
            'priority' => $priority,
            'ticketStatus' => $status,
            'ip' => '',
            'notes' => '',
            'disputed' => 'no',
            'source' => 'api'
          ));
          $ticketNumber = $MSTICKET->ticket($ID);
          // Proceed if ticket added ok..
          if ($ID > 0) {
            ++$added;
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] New ticket added. ID: ' . $ID);
            // Add custom fields..
            if (!empty($ticketData['tickets'][$i]['fields'])) {
              $countOfFields = count($ticketData['tickets'][$i]['fields']);
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] ' . $countOfFields . ' custom field(s) found in incoming data. Preparing to add field(s)..');
              foreach ($ticketData['tickets'][$i]['fields'] AS $fKey => $fVal) {
                $fieldID = substr($fKey, 1);
                if ((int) $fieldID > 0 && mswSQL_rows('cusfields WHERE `id` = \'' . (int) $fieldID . '\'') > 0) {
                  $MSAPI->insertField($ID, $fieldID, $fVal);
                  $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Field (' . $fKey . ') accepted.');
                } else {
                  $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Field (' . $fKey . ') ignored, field ID ' . $fieldID . ' invalid or not found.');
                }
              }
            } else {
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] No custom field data found.');
            }
            // Add attachments..
            if (!empty($ticketData['tickets'][$i]['attachments'])) {
              $countOfAttachments = count($ticketData['tickets'][$i]['attachments']);
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] ' . $countOfAttachments . ' attachment(s) found in incoming data. Preparing to add attachment(s)..');
              for ($a = 0; $a < $countOfAttachments; $a++) {
                $ext    = (isset($ticketData['tickets'][$i]['attachments'][$a]['ext']) ? $ticketData['tickets'][$i]['attachments'][$a]['ext'] : '');
                $file   = (isset($ticketData['tickets'][$i]['attachments'][$a]['data']) ? $ticketData['tickets'][$i]['attachments'][$a]['data'] : '');
                $fnme   = (isset($ticketData['tickets'][$i]['attachments'][$a]['name']) ? $ticketData['tickets'][$i]['attachments'][$a]['name'] : '');
                if ($ext && $file) {
                  $ifRenamed = $MSTICKET->rename($ID . '.' . $ext, $ID, 0, ($a + 1));
                  // If file name not set OR file name exists, create new name..
                  $n = ($fnme ? $fnme : $ifRenamed);
                  // At this point we must upload the file to get file size..
                  // Replace any spaces in data with + symbol to maintain incoming data modified by urldecode..
                  $flder  = $MSAPI->uploadEmailAttachment($n, strtr($file, ' ', '+'), $ifRenamed);
                  $folder = $flder[0];
                  $n      = $flder[1];
                  if ($folder[0] && @file_exists($SETTINGS->attachpath . '/' . $folder . $n)) {
                    $fSize = @filesize($SETTINGS->attachpath . '/' . $folder . $n);
                    if ($fSize > 0) {
                      if (!$MSTICKET->size($fSize)) {
                        $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Size (' . mswFSC($fSize) . ') too big and attachment ignored/deleted');
                        @unlink($SETTINGS->attachpath . '/' . $folder . $n);
                      } else {
                        // Try and determine mime type..
                        $mime = $DL->mime($SETTINGS->attachpath . '/' . $folder . $n, '');
                        $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Mime type determined as ' . $mime);
                        // Add attachment data to database..
                        $atID = $MSAPI->addAttachmentToDB($ID, 0, $n, $fSize, $deptID, $mime);
                        if ($atID > 0) {
                          $attString[] = $SETTINGS->scriptpath . '/?attachment=' . $atID;
                          $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Attachment (' . basename($n) . ') accepted. ID: ' . $atID . ' @ ' . mswFSC($fSize));
                        } else {
                          $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error, attachment could not be added. Refer to the error log if it exists.');
                        }
                      }
                    } else {
                      $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] File size of attachment 0 bytes. Ignored. Maybe permissions or error reading file.');
                    }
                  } else {
                    $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] File attachment could not be saved. Either folder (' . $folder . ') doesn`t exist, has invalid permissions or could not be created.');
                  }
                } else {
                  $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] File attachment ignored, no incoming data or extension value. Check source code.');
                }
              }
            } else {
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] No attachments found.');
            }
            // Write log entry..
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Writing to history log if enabled.');
            $MSTICKET->historyLog($ID, str_replace(array(
              '{visitor}'
            ), array(
              $name
            ), $msg_ticket_history['new-ticket-visitor-api']));
            // Pass ticket number as custom mail header..
            $MSMAIL->xheaders['X-TicketNo'] = mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber);
            // Send emails..
            $MSMAIL->addTag('{ACC_NAME}', $name);
            $MSMAIL->addTag('{ACC_EMAIL}', $email);
            $MSMAIL->addTag('{SUBJECT}', $MSBB->cleaner($subject));
            $MSMAIL->addTag('{TICKET}', mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber));
            $MSMAIL->addTag('{DEPT}', $MSYS->department($deptID, $msg_script30));
            $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($priority));
            $MSMAIL->addTag('{STATUS}', $MSYS->status($status, $ticketStatusSel));
            $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($comments));
            $MSMAIL->addTag('{ATTACHMENTS}', (!empty($attString) ? implode(mswNL(), $attString) : $msg_script17));
            $MSMAIL->addTag('{ID}', $ID);
            $MSMAIL->addTag('{CUSTOM}', $MSFIELDS->email($ID, 0));
            // Send message to support staff if manual assign is off for department..
            // This doesn`t include administrators
            if ($DP->manual_assign == 'no') {
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Preparing to send emails to staff..');
              $qU = mswSQL_query("SELECT `" . DB_PREFIX . "users`.`name` AS `teamName`,
                    `email`,`email2`,`language` FROM `" . DB_PREFIX . "userdepts`
                    LEFT JOIN `" . DB_PREFIX . "departments`
                    ON `" . DB_PREFIX . "userdepts`.`deptID`  = `" . DB_PREFIX . "departments`.`id`
                    LEFT JOIN `" . DB_PREFIX . "users`
                    ON `" . DB_PREFIX . "userdepts`.`userID`  = `" . DB_PREFIX . "users`.`id`
                    WHERE `deptID`  = '{$deptID}'
                    AND `admin` = 'no'
                    AND `notify` = 'yes'
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
                $MSAPI->log('Email template for ' . $STAFF->teamName . ' is ' . $langFile);
                $MSMAIL->addTag('{NAME}', $STAFF->teamName);
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $STAFF->email,
                  'to_name' => $STAFF->teamName,
                  'subject' => str_replace(array(
                    '{website}',
                    '{ticket}',
                    '{name}'
                  ), array(
                    $SETTINGS->website,
                    mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber),
                    $STAFF->teamName
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
                $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Email sent to ' . $STAFF->teamName . ' <' . $STAFF->email . '>');
              }
            } else {
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] No emails sent to staff as ticket is awaiting assignment');
            }
            // Now send to admins..
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Preparing to send emails to administrators..');
            $qUA = mswSQL_query("SELECT `name`, `email`, `email2`, `language` FROM `" . DB_PREFIX . "users`
                   WHERE `admin` = 'yes'
                   AND `notify`  = 'yes'
                   ORDER BY `id`
                   ", __file__, __line__);
            if (mswSQL_numrows($qUA) > 0) {
              while ($ASTAFF = mswSQL_fetchobj($qUA)) {
                $langFile = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-ticket-admin.txt';
                $langSet = $SETTINGS->language;
                if ($ASTAFF->language && file_exists(PATH . 'content/language/' . $ASTAFF->language . '/mail-templates/new-ticket-admin.txt')) {
                  $langSet = $ASTAFF->language;
                  $langFile = PATH . 'content/language/' . $ASTAFF->language . '/mail-templates/new-ticket-admin.txt';
                }
                $MSAPI->log('Email template for ' . $ASTAFF->name . ' is ' . $langFile);
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
                $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Email sent to ' . $ASTAFF->name . ' <' . $ASTAFF->email . '>');
              }
            } else {
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] No notifications enabled for administrators');
            }
            // Send email to visitor..
            $depCusResponse = array();
            // Is custom department response enabled?
            if (property_exists($DP, 'auto_response') && $DP->auto_response == 'yes') {
              $depCusResponse['subject'] = $DP->response_sbj;
              $depCusResponse['message'] = $DP->response;
              $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Custom department message detected. Subject and message override unless blank..');
            }
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Preparing to send new ticket confirmation to visitor..');
            $MSMAIL->addTag('{NAME}', $name);
            $MSMAIL->sendMSMail(array(
              'from_email' => $SETTINGS->email,
              'from_name' => $SETTINGS->website,
              'to_email' => $email,
              'to_name' => $name,
              'subject' => str_replace(array(
                '{website}',
                '{ticket}',
                '{name}'
              ), array(
                $SETTINGS->website,
                mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber),
                $name
              ), $emailSubjects['new-ticket-vis']),
              'replyto' => array(
                'name' => $SETTINGS->website,
                'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
              ),
              'dep' => $depCusResponse,
              'template' => $mailR,
              'language' => ($pLang ? $pLang : $SETTINGS->language)
            ));
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Email sent to ' . $name . ' <' . $email . '>');
          } else {
            $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error: Ticket could not be created. Refer to the error log if it exists.');
          }
        } else {
          $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error: User ID not found.');
        }
      } else {
        $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error: Department not found for ID ' . $deptID . '. Ticket ignored.');
      }
    } else {
      $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error: Name,Email,Dept,Subject,Comments & Priority are required, check data. Ticket ignored.');
    }
  }
  // We are done, so add response..
  if ($added > 0) {
    $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] ' . $added . ' ticket(s) successfully created. API ops completed, finally show response');
    $MSAPI->response('OK', str_replace('{count}', $added, $msg_api), array(
      'ticketID' => (isset($ID) ? $ID : '0')
    ));
  } else {
    $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] No tickets created from incoming data. Check log file.');
    $MSAPI->response('ERROR', $msg_api2);
  }
  exit;
}

?>