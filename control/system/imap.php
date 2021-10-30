<?php

/* Tickets by Email - Imap Functions
----------------------------------------------------------*/

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Memory/timeouts..
if ($SETTINGS->imap_memory > 0) {
  @ini_set('memory_limit', (int) $SETTINGS->imap_memory . 'M');
}
if ($SETTINGS->imap_timeout > 0) {
  @set_time_limit($SETTINGS->imap_timeout);
}

// Load anti spam system..
include(PATH . 'control/classes/system/class.cleantalk.php');
$aspam_api       = $SOCIAL->params('ctalk');
$CTALK           = new cleanTalk();
$CTALK->settings = $SETTINGS;
$CTALK->social   = $aspam_api;
$CTALK->ssn      = $SSN;

// Spam settings..
$spamParams = array(
  'key' => (isset($aspam_api['ctalk']['key']) && $aspam_api['ctalk']['key'] ? $aspam_api['ctalk']['key'] : ''),
  'enabled' => (isset($aspam_api['ctalk']['enableimap']) ? 'yes' : 'no'),
  'skip-active' => (isset($aspam_api['ctalk']['active']) ? 'yes' : 'no'),
  'add-to-spam' => (isset($aspam_api['ctalk']['folder']) ? 'yes' : 'no'),
  'log' => (isset($aspam_api['ctalk']['log']) ? 'yes' : 'no'),
  'name' => 'cleanTalk',
  'ban-filters' => mswSQL_rows('imapban')
);

// Is spam system enabled?
if (($spamParams['key'] && $spamParams['enabled'] == 'yes') ||
  ($SETTINGS->spam_score_header && $SETTINGS->spam_score_value > 0)) {
  if ($spamParams['enabled'] == 'no') {
    $spamParams['enabled'] == 'yes';
  }
  define('MSW_ANTI_SPAM_ENABLED', 1);
}

// Initialise vars..
$pipes      = array(0, 0, 0, 0, 0, 0, 0);
$pipeID     = (isset($_GET[$SETTINGS->imap_param]) ? (int) $_GET[$SETTINGS->imap_param] : (defined('IMAP_CRON_ID') ? IMAP_CRON_ID : ''));
$time_start = $MSDT->microtimeFloat();

// Check imap..
if (!function_exists('imap_open')) {
  die('PHP <a href="http://php.net/manual/en/book.imap.php">imap functions</a> not found! Your server must be compiled
  with imap support for this function to run. Refer to installation instructions on the PHP website or try uncommenting the following value in
	your PHP.ini file and then reboot your server:<br><br><b>extension=php_imap.dll</b><br><br>For Cpanel and WHM setups, use EasyApache to recompile
	with imap support.');
}

// Legacy check..
if ((int) $pipeID == 0 || $pipeID == 'yes' || $pipeID == '') {
  $pipeID = '1';
}

// Get imap data..
$IMDT  = mswSQL_table('imap', 'id', (int) $pipeID);

if (!isset($IMDT->id)) {
  die($pipeID . ' is an invalid imap id. Check url');
}

if ($IMDT->im_piping == 'no') {
  die('Imap account not active. Please enable in settings');
}

// Department info..
$DP = mswSQL_table('departments', 'id', $IMDT->im_dept, '', '`manual_assign`,`auto_response`,`response`,`response_sbj`');

// Check department..
if (!isset($DP->manual_assign)) {
  die($pipeID . ' has not been assigned to any department. Update in department settings');
}

// Is debug enabled?
if ($SETTINGS->imap_debug == 'yes') {
  if (!is_dir(PATH . 'logs') || !is_writeable(PATH . 'logs')) {
    die('Imap debug enabled in settings, but "logs" folder either doesn`t exist or isn`t writeable. Please update.');
  }
}

// Filters
$FLTRS = mswSQL_table('imapban', 'id', 1);

// Load download class for mime types..
include(PATH . 'control/classes/system/class.download.php');
$DL = new msDownload();

// Load mailer params..
include(PATH . 'control/classes/mailer/mail-init.php');

// Read mailbox and run..
include(PATH . 'control/classes/class.upload.php');
$MSUPL            = new msUpload();
$MSIMAP           = new imapRoutine($IMDT);
$MSIMAP->settings = $SETTINGS;
$MSIMAP->datetime = $MSDT;
$MSIMAP->upload   = $MSUPL;
$mailbox          = $MSIMAP->connectToMailBox();

// Log..
$MSIMAP->log('Imap account found, preparing to connect to mailbox..');
if ($mailbox) {
  $count = imap_num_msg($mailbox);
  $loop  = ($count > $IMDT->im_messages ? $IMDT->im_messages : $count);
  if ($count > 0) {
    $MSIMAP->log('Connection successful (' . $count . ' emails found): Reading in reverse order (oldest first) - ' . $loop . ' of ' . $count);
  } else {
    $MSIMAP->log('Connection successful: No emails in mailbox folder: ' . $IMDT->im_name);
  }
  // Process messages in reverse order so last message is latest..
  for ($i = $loop; $i > 0; $i--) {
    // Vars initialisation for loop..
    $isSpam     = 'no';
    $spamBypass = 'no';
    $spamDetect = 'no';
    $replyID    = 0;
    $attString  = array();
    $aCount     = 0;
    $message    = $MSIMAP->readMailBox($mailbox, $i);
    $MSIMAP->log('Data from mailbox: {nl}{nl}' . print_r($message, true));
    $mailSubject = array();
    $mailTemps   = array();
    $skipMessage = 'no';
    $skipReply   = 'no';
    $fltrAccSkp  = 'no';
    $banFltSpam  = 'no';
    $acpStatus   = 'yes';
    $defStatus   = (isset($ticketStatusSel[$IMDT->im_status][0]) ? $IMDT->im_status : 'open');
    // Check custom status is not locked to visitors (replies only)..
    if ($message['ticketID'][0] == 'yes') {
      if (!in_array($IMDT->im_status, array('open','close','closed'))) {
        $getStatus   = mswSQL_table('statuses', 'id', (int) $IMDT->im_status);
        $acpStatus   = (isset($getStatus->visitor) && $getStatus->visitor == 'yes' ? 'no' : 'yes');
      } else {
        $acpStatus = 'yes';
      }
      if ($acpStatus == 'no') {
        $skipMessage = 'yes';
      }
    }
    // Is this email coming from a staff account?
    $STAFF_ACC = mswSQL_table('users', 'email', mswSQL(strtolower($message['email'])));
    if (isset($STAFF_ACC->id)) {
      $MSIMAP->log('Staff account detected for email: ' . $message['email'] . '. Staff tickets are ignored as reply should come from admin interface.');
    } else {
      // Are ban filters enabled?
      // Replies are not checked..
      if ($skipMessage == 'no' && $message['ticketID'][0] == 'no' && $spamParams['ban-filters'] > 0) {
        $MSIMAP->log('Ban filters found. Checking name,email,subject and comments for matches..');
        // Are filters disabled for active accounts?
        if (isset($FLTRS->account) && $FLTRS->account == 'yes') {
          $LI_ACC_BF = mswSQL_table('portal', 'email', mswSQL(strtolower($message['email'])));
          if (isset($LI_ACC_BF->id) && $LI_ACC_BF->enabled == 'yes' && $LI_ACC_BF->verified == 'yes') {
            $fltrAccSkp = 'yes';
            $MSIMAP->log('Ban filters will be skipped due to "Disable if Active Account Exists For Email Address" setting. Account email: ' . $message['email']);
          }
        }
        // Proceed with checks if enabled..
        if ($fltrAccSkp == 'no') {
          $filters = $MSIMAP->filters(array(
            'name' => $message['from'],
            'email' => $message['email'],
            'subject' => $MSIMAP->decodeString($message['subject']),
            'comments' => $MSBB->cleaner($MSIMAP->decodeString($message['body']))
          ));
          $skipMessage = $filters['txt'];
          switch($skipMessage) {
            case 'no':
              $MSIMAP->log('No matches found, all fields have passed the ban filter check.');
              break;
            case 'yes':
              $MSIMAP->log('Email will be ignored (or moved to Spam Tickets) due to ban filter match against ticket "' . $filters['type'] . '" for the filter "' . $filters['filter'] . '"');
              if (isset($FLTRS->spam) && $FLTRS->spam == 'yes') {
                $skipMessage = 'no';
                $banFltSpam = 'yes';
              } else {
                $MSIMAP->log('Email to be auto deleted as setting "Move to Spam Tickets Instead of Deleting Ticket" is set to no');
                $pipes[6] = (++$pipes[6]);
              }
              break;
          }
        }
      } else {
        if ($acpStatus == 'no') {
          $MSIMAP->log('Ban filters skipped as custom status ticket lock prevents reply anyway');
        } else {
          $MSIMAP->log('No ban filters enabled or reply has been detected and ban filters are disabled for replies');
        }
      }
      // Are we skipping email..
      if ($skipMessage == 'no') {
        $name    = $message['from'];
        $email   = $message['email'];
        // If name is blank, we use email address..
        if ($name == '') {
          $name = $email;
        }
        $subject = $MSIMAP->decodeString($message['subject']);
        $MSIMAP->log('Decoding subject:{nl}{nl}' . $subject);
        $priority = $IMDT->im_priority;
        // For comments, decode body if required and remove any bb tags..
        // BB tags should not be present in standard emails..
        $comments = $MSBB->cleaner($MSIMAP->decodeString($message['body']));
        $MSIMAP->log('Parsing comments:{nl}{nl}' . $comments);
        // Get account info..
        // If we already have it from ban check, just assign var..
        if (isset($LI_ACC_BF->id)) {
          $LI_ACC = $LI_ACC_BF;
        } else {
          $LI_ACC = mswSQL_table('portal', 'email', mswSQL(strtolower($email)));
        }
        // Is account disabled?
        if (isset($LI_ACC->id) && $LI_ACC->enabled == 'no' && $LI_ACC->verified == 'yes') {
          $MSIMAP->log('Account for ' . $name . ' (' . $email . ') is disabled. Tickets are not permitted from this account. Reason: ' . ($LI_ACC->reason ? $LI_ACC->reason : $msg_script17));
        } else {
          // Is the spam filter enabled for this account?
          // Not enabled for replies..
          if ($message['ticketID'][0] == 'no') {
            if ($banFltSpam == 'no' && defined('MSW_ANTI_SPAM_ENABLED')) {
              $MSIMAP->log('Anti spam system enabled, starting spam checks..');
              if (isset($LI_ACC->id) && $LI_ACC->enabled == 'yes' && $LI_ACC->verified == 'yes' && $spamParams['skip-active'] == 'yes') {
                // If visitor has IP, we`ll use that for messages..
                if ($LI_ACC->ip) {
                  $MSMAIL->addTag('{IP}', $LI_ACC->ip);
                }
                $MSIMAP->log('Email will be allowed with no spam checks. "For Imap Tickets Disable if Account is Already Active" in Settings > ' . $spamParams['name'] . ' API set to YES.');
              } else {
                // Check spam score header, then cleanTalk if enabled
                if ($SETTINGS->spam_score_header && $SETTINGS->spam_score_value > 0 &&
                    isset($message['spamScore']) && $message['spamScore'] >= $SETTINGS->spam_score_value) {
                  $spamDetect = 'yes';
                  $MSIMAP->log('Email detected as spam via spam score header (' . $SETTINGS->spam_score_header . '). Spam score in email is ' . $message['spamScore'] . ', limit is ' . $SETTINGS->spam_score_value);
                } else { 
                  $MSIMAP->log('Sending data to ' . $spamParams['name'] . ' for analysis..');
                  // Check submission..
                  $ctkc = $CTALK->check(array(
                    'method' => 'check_message',
                    'email' => $email,
                    'name' => $name,
                    'comms' => $comments,
                    'ct_ts' => date('Y', $MSDT->mswTimeStamp()),
                    'ip' => (isset($LI_ACC->ip) ? $LI_ACC->ip : ''),
                    'protocol' => 'imap'
                  ));
                  if (!isset($ctkc['allow']) || $ctkc['allow'] == 0) {
                    $spamDetect = 'yes';
                    $MSIMAP->log('Email detected as spam via CleanTalk sysem. Code: ' . (isset($ctkc['codes']) ? $ctkc['codes'] : $msg_script17) . ', Reason: ' . (isset($ctkc['comment']) ? $ctkc['comment'] : $msg_script17) . ' (Check logs in "logs" folder for more info or log into your cleanTalk account).');
                  }
                }
                // Accept or reject..
                if ($spamDetect == 'yes') {
                  // Are we accepting this message?
                  if ($spamParams['add-to-spam'] == 'no') {
                    $MSIMAP->log('Email will be deleted. "If Spam Ticket Detected, Add to Spam Tickets" in Settings > ' . $spamParams['name'] . ' API set to NO.');
                    $spamBypass = 'yes';
                  } else {
                    $MSIMAP->log('Email accepted and will be viewable on "Spam Tickets" screen. "If Spam Ticket Detected, Add to Spam Tickets" in Settings > ' . $spamParams['name'] . ' API set to YES.');
                    $isSpam = 'yes';
                  }
                } else {
                  $MSIMAP->log('Email passed spam filters and will be allowed');
                }
              }
            } else {
              // Block from ban filter..
              if ($banFltSpam == 'yes') {
                $MSIMAP->log('Email accepted and will be viewable on "Spam Tickets" screen. "Move to Spam Tickets Instead of Deleting Ticket" is set to yes for imap ban filters');
                $isSpam = 'yes';
              }
            }
          } else {
            $MSIMAP->log('Spam filter disabled or reply has been detected as spam filters are disabled for replies');
          }
          // Ignore blank emails..
          if (($message['body'] != '' || !isset($LI_ACC->id)) && $spamBypass == 'no') {
            if ($isSpam == 'yes') {
              $pipes[5] = (++$pipes[5]);
              $MSIMAP->log('Due to email being flagged as spam, all notifications will be disabled except to administrators.');
            } else {
              $pipes[0] = (++$pipes[0]);
            }
            // Is this a brand new message or a reply..
            if ($message['ticketID'][0] == 'no') {
              // Can this ticket be allowed if a ticket is already open?
              $openlimit_res = 'no';
              if ($SETTINGS->openlimit == 'yes') {
                $openlimit_res = $MSTICKET->isTicketOpen(array(
                  'acc' => (isset($LI_ACC->id) ? $LI_ACC->id : '0'),
                  'email' => $email
                ));
              }
              if ($openlimit_res == 'no') {
                $MSIMAP->log('Preparing to add new ticket..');
                // Is this first ticket from user email..
                if (isset($LI_ACC->id)) {
                  $name   = $LI_ACC->name;
                  $email  = $LI_ACC->email;
                  $pass   = '';
                  $userID = $LI_ACC->id;
                  $MSIMAP->log('Account does exist for ' . $email);
                } else {
                  $MSIMAP->log('New account to be created for email ' . $email);
                  $pass = $MSACC->ms_generate();
                  if (defined('IMAP_CRON_LANG') && file_exists(PATH . 'content/language/' . IMAP_CRON_LANG . '/mail-templates/new-account.txt')) {
                    $mailT = PATH . 'content/language/' . IMAP_CRON_LANG . '/mail-templates/new-account.txt';
                  } else {
                    $mailT = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-account.txt';
                  }
                  $MSIMAP->log('Email template is ' . $mailT);
                  // Create account..
                  $userID = $MSACC->add(array(
                    'name' => $name,
                    'email' => $email,
                    'pass' => $pass,
                    'enabled' => 'yes',
                    'verified' => 'yes',
                    'timezone' => '',
                    'ip' => '',
                    'notes' => '',
                    'language' => (defined('IMAP_CRON_LANG') ? IMAP_CRON_LANG : $SETTINGS->language)
                  ));
                  // Send email about new account..
                  if ($userID > 0) {
                    $MSIMAP->log('Account created successfully. ID: ' . $userID);
                    // Send notification if enabled..
                    if ($SETTINGS->imap_notify == 'yes') {
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
                        'language' => $SETTINGS->language,
                        'alive' => 'yes'
                      ));
                      $MSIMAP->log('Email sent to ' . $name . ' <' . $email . '>');
                    } else {
                      $MSIMAP->log('Account creation email NOT sent as this option is disabled in setting. Settings & Tools > Settings > General > Imap Settings');
                    }
                  } else {
                    $MSIMAP->log('Fatal error, account could not be created. Refer to the error log if it exists.');
                  }
                }
                // Create ticket..
                $ID = $MSTICKET->add(array(
                  'dept' => $IMDT->im_dept,
                  'assigned' => ($DP->manual_assign == 'yes' ? 'waiting' : ''),
                  'visitor' => $userID,
                  'subject' => $subject,
                  'quoteBody' => '',
                  'comments' => $comments,
                  'priority' => $priority,
                  'ticketStatus' => $defStatus,
                  'ip' => '',
                  'notes' => '',
                  'disputed' => 'no',
                  'source' => 'imap',
                  'spam' => $isSpam
                ));
                $ticketNumber = $MSTICKET->ticket($ID);
                // History entry..
                $MSTICKET->historyLog($ID, str_replace(array(
                  '{visitor}'
                ), array(
                  $name
                ), $msg_ticket_history['new-ticket-visitor-imap']));
                // Proceed if ticket added ok..
                if ($ID > 0) {
                  $MSIMAP->log('New ticket added. ID: ' . $ID . ', Ticket No: ' . $ticketNumber);
                  $mailSubject['staff'] = str_replace(array(
                    '{website}',
                    '{ticket}',
                    '{name}'
                  ), array(
                    $SETTINGS->website,
                    mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber),
                    $name
                  ), $emailSubjects['new-ticket']);
                  $mailSubject['vis']   = '[#' . mswTicketNumber($ID, $SETTINGS->minTickDigits, $ticketNumber) . '] ' . $msg_public_ticket12;
                  $mailTemps['staff']   = 'new-ticket-staff.txt';
                  $mailTemps['admin']   = 'new-ticket-admin.txt';
                  $mailTemps['vis']     = 'new-ticket-visitor.txt';
                  if ($isSpam == 'no') {
                    if ($DP->manual_assign == 'no') {
                      $pipes[1] = (++$pipes[1]);
                    } else {
                      $pipes[4] = (++$pipes[4]);
                    }
                  }
                  // Ticket info..
                  $T = mswSQL_table('tickets', 'id', $ID);
                } else {
                  $MSIMAP->log('Fatal error, ticket could not be created. Refer to the error log if it exists.');
                }
              } else {
                $mailTemps['vis'] = 'ticket-multiple-open-not-permitted.txt';
                if (isset($LI_ACC->language) && file_exists(PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/' . $mailTemps['vis'])) {
                  $mailT = PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/' . $mailTemps['vis'];
                } else {
                  if (defined('IMAP_CRON_LANG') && file_exists(PATH . 'content/language/' . IMAP_CRON_LANG . '/mail-templates/' . $mailTemps['vis'])) {
                    $mailT = PATH . 'content/language/' . IMAP_CRON_LANG . '/mail-templates/' . $mailTemps['vis'];
                  } else {
                    $mailT = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/' . $mailTemps['vis'];
                  }
                }
                $MSIMAP->log('Email template for ' . $name . ' is ' . $mailT);
                $MSIMAP->log('Account/email already has at least 1 open ticket. Ticket disallowed because "Prevent Tickets From Being Opened If At Least One Ticket Is Already Open" is set to yes in ticket settings');
                $MSMAIL->addTag('{NAME}', $name);
                $MSMAIL->addTag('{SUBJECT}', $subject);
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
                  ), $emailSubjects['multiple-open-disallowed']),
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => $mailT,
                  'language' => $SETTINGS->language
                ));
                $MSIMAP->log('Email sent to ' . $name . ' <' . $email . '>');
              }
            } else {
              // Add reply..check permissions allow reply..
              $ID = $message['ticketID'][1];
              // Is this a random ticket code?
              if (strpos($ID, '-') !== false) {
                $MSIMAP->log('Random ticket ID detected - Check permissions before accepting reply. Ticket cannot be closed.');
                $T = mswSQL_table('tickets', 'tickno', mswSQL($ID), 'AND `visitorID` = \'' . $LI_ACC->id . '\' AND `ticketStatus` != \'closed\' AND `spamFlag` = \'no\'');
                $ID = (isset($T->id) ? $T->id : '0');
              } else {
                $MSIMAP->log('Standard ticket ID detected - Check permissions before accepting reply. Ticket cannot be closed.');
                $T = mswSQL_table('tickets', 'id', $ID, 'AND `visitorID` = \'' . $LI_ACC->id . '\' AND `ticketStatus` != \'closed\' AND `spamFlag` = \'no\'');
              }
              // Can tickets be re-opened by imap?
              if (isset($T->id) && $T->ticketStatus == 'close' && $SETTINGS->imap_open == 'yes') {
                $MSIMAP->log('Ticket #' . mswTicketNumber($ID, $SETTINGS->minTickDigits, $T->tickno) . ' is closed and cannot be reopened by imap due to "Tickets Cannot Be ReOpened by Email" setting (Settings > Imap Settings)');
                $skipReply = 'yes';
                $mailSB = str_replace(array(
                  '{website}',
                  '{ticket}',
                  '{name}'
                ), array(
                  $SETTINGS->website,
                  mswTicketNumber($ID, $SETTINGS->minTickDigits, $T->tickno),
                  $name
                ), $emailSubjects['reopen-not-allowed']);
                $mailTemps['vis'] = 'ticket-reopen-not-permitted.txt';
                $pDALang = '';
                if (isset($LI_ACC->language) && file_exists(PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/' . $mailTemps['vis'])) {
                  $mailT = PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/' . $mailTemps['vis'];
                  $pDALang = $LI_ACC->language;
                } else {
                  if (defined('IMAP_CRON_LANG') && file_exists(PATH . 'content/language/' . IMAP_CRON_LANG . '/mail-templates/' . $mailTemps['vis'])) {
                    $mailT = PATH . 'content/language/' . IMAP_CRON_LANG . '/mail-templates/' . $mailTemps['vis'];
                    $pDALang = IMAP_CRON_LANG;
                  } else {
                    $mailT = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/' . $mailTemps['vis'];
                  }
                }
                $MSIMAP->log('Email template for ' . $name . ' is ' . $langFile);
                $MSMAIL->addTag('{ID}', $ID);
                $MSMAIL->addTag('{SUBJECT}', $MSBB->cleaner($subject));
                $MSMAIL->addTag('{NAME}', $name);
                $MSMAIL->addTag('{TICKET}', mswTicketNumber($ID, $SETTINGS->minTickDigits, $T->tickno));
                $MSMAIL->sendMSMail(array(
                  'from_email' => $SETTINGS->email,
                  'from_name' => $SETTINGS->website,
                  'to_email' => $email,
                  'to_name' => $name,
                  'subject' => $mailSB,
                  'replyto' => array(
                    'name' => $SETTINGS->website,
                    'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                  ),
                  'template' => $mailT,
                  'language' => ($pDALang ? $pDALang : $SETTINGS->language),
                  'alive' => 'yes'
                ));
                $MSIMAP->log('Information about disallowed ticket emailed to ' . $name . ' <' . $email . '>');
              } else {
                if (isset($T->id)) {
                  $replyID = $MSTICKET->reply(array(
                    'ticket' => $ID,
                    'visitor' => $LI_ACC->id,
                    'quoteBody' => '',
                    'comments' => $comments,
                    'repType' => 'visitor',
                    'ip' => $LI_ACC->ip,
                    'disID' => 0,
                    'assigned' => $T->assignedto
                  ));
                  // History entry..
                  $MSTICKET->historyLog($ID, str_replace(array(
                    '{visitor}',
                    '{id}'
                  ), array(
                    $name,
                    $replyID
                  ), $msg_ticket_history['vis-reply-add-imap']));
                  // Proceed if ok..
                  if ($replyID > 0) {
                    $MSIMAP->log('Reply successfully added. ID: ' . $replyID, ' Ticket No: ' . $T->tickno);
                    $mailSubject['staff'] = str_replace(array(
                      '{website}',
                      '{ticket}',
                      '{name}'
                    ), array(
                      $SETTINGS->website,
                      mswTicketNumber($ID, $SETTINGS->minTickDigits, $T->tickno),
                      $name
                    ), $emailSubjects['reply-notify']);
                    $mailSubject['vis']   = '';
                    $mailTemps['staff']   = 'ticket-reply.txt';
                    $mailTemps['admin']   = 'ticket-reply.txt';
                    $mailTemps['vis']     = '';
                    $pipes[2]             = (++$pipes[2]);
                  } else {
                    $MSIMAP->log('Fatal error, reply could not added. Refer to the error log if it exists.');
                  }
                } else {
                  $MSIMAP->log('Permission denied. Ticket could not be found. Is ticket in spam tickets or locked?');
                }
              }
            }
            // Attachments..
            if ($skipReply == 'no') {
              $MSIMAP->log('Check for attachments..');
              if ($IMDT->im_attach == 'yes' && isset($T->id)) {
                $attachments = $MSIMAP->readAttachments($mailbox, $i);
                $MSIMAP->log(count($attachments) . ' attachment(s) found');
                if (!empty($attachments) && LICENCE_VER == 'locked' && count($attachments) > RESTR_ATTACH) {
                  $countOfBoxes = RESTR_ATTACH;
                }
                if (!empty($attachments)) {
                  $restrictions = array(
                    'Rename' => ucfirst($SETTINGS->rename),
                    'FileTypes' => ($SETTINGS->filetypes ? $SETTINGS->filetypes : 'No Restrictions (Not recommended)'),
                    'MaxSize' => ($SETTINGS->maxsize > 0 ? mswFSC($SETTINGS->maxsize) : 'No Limits')
                  );
                  $MSIMAP->log('Restrictions Imposed: {nl}{nl}' . print_r($restrictions, true));
                  $MSIMAP->log('Preparing to loop and check attachment(s)');
                  for ($j = 0; $j < (isset($countOfBoxes) ? $countOfBoxes : count($attachments)); $j++) {
                    ++$aCount;
                    $MSIMAP->log('Check Attachment: ' . $attachments[$aCount]['file']);
                    // Check for valid file type..
                    if ($MSTICKET->type($attachments[$aCount]['file'])) {
                      $n      = ($SETTINGS->rename == 'yes' ? $MSTICKET->rename($attachments[$aCount]['file'], $ID, $replyID, ($j + 1)) : $attachments[$aCount]['file']);
                      // At this point we must upload the file to get file size..
                      $folder = $MSIMAP->uploadEmailAttachment($n, $attachments[$aCount]['attachment']);
                      // If file upload now exists, check file size..
                      if ($folder && @file_exists($SETTINGS->attachpath . '/' . $folder . $n)) {
                        $fSize = @filesize($SETTINGS->attachpath . '/' . $folder . $n);
                        if ($fSize > 0) {
                          if (!$MSTICKET->size($fSize)) {
                            $MSIMAP->log('Size (' . mswFSC($fSize) . ') too big and attachment ignored/deleted');
                            @unlink($SETTINGS->attachpath . '/' . $folder . $n);
                          } else {
                            // Try and determine mime type..
                            $mime = $DL->mime($attachments[$aCount]['file'], '');
                            $MSIMAP->log('Mime type determined as ' . $mime);
                            // Add attachment data to database..
                            $atID = $MSIMAP->addAttachmentToDB($ID, $replyID, $n, $fSize, $mime);
                            if ($atID > 0) {
                              $pipes[3]    = (++$pipes[3]);
                              $attString[] = $SETTINGS->scriptpath . '/?attachment=' . $atID;
                              $MSIMAP->log('Attachment (' . basename($n) . ') accepted. ID: ' . $atID . ' @ ' . mswFSC($fSize));
                            } else {
                              $MSIMAP->log('Fatal error, attachment could not be added. Refer to the error log if it exists.');
                            }
                          }
                        } else {
                          $MSIMAP->log('File size 0 bytes, ignored.');
                        }
                      }
                    } else {
                      $MSIMAP->log('Type (' . strrchr(strtolower($attachments[$aCount]['file']), '.') . ') invalid and attachment ignored.');
                    }
                  }
                }
              } else {
                $MSIMAP->log('Attachments not enabled and ignored');
              }
              // Write log entry..
              if (isset($T->id)) {
                // If spam not detected, normal ticket..
                if ($isSpam == 'no') {
                  // Convert quoted-printable string to an 8 bit string..
                  // Helps make message cleaner..
                  if (function_exists('quoted_printable_decode')) {
                    $comments = quoted_printable_decode($comments);
                  }
                  // Pass ticket number as custom mail header..
                  $MSMAIL->xheaders['X-TicketNo'] = mswTicketNumber($ID, $SETTINGS->minTickDigits, (isset($ticketNumber) ? $ticketNumber : $T->tickno));
                  // Mail tags and send emails..
                  $MSMAIL->addTag('{ACC_NAME}', $name);
                  $MSMAIL->addTag('{ACC_EMAIL}', $email);
                  $MSMAIL->addTag('{SUBJECT}', $MSBB->cleaner($subject));
                  $MSMAIL->addTag('{TICKET}', mswTicketNumber($ID, $SETTINGS->minTickDigits, (isset($ticketNumber) ? $ticketNumber : $T->tickno)));
                  $MSMAIL->addTag('{DEPT}', $MSYS->department($IMDT->im_dept, $msg_script30));
                  $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($priority));
                  $MSMAIL->addTag('{STATUS}', $MSYS->status($defStatus, $ticketStatusSel));
                  $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($comments));
                  $MSMAIL->addTag('{ATTACHMENTS}', (!empty($attString) ? implode(mswNL(), $attString) : $msg_script17));
                  $MSMAIL->addTag('{ID}', $ID);
                  $MSMAIL->addTag('{CUSTOM}', $msg_script17);
                  // Send message to staff.
                  // If new ticket, is manual assign off?
                  $staffSend = 'no';
                  $MSIMAP->log('Preparing to send emails to staff..');
                  if (isset($mailTemps['staff'])) {
                    if ($DP->manual_assign == 'no' && $replyID == 0) {
                      $qU = mswSQL_query("SELECT `" . DB_PREFIX . "users`.`name` AS `teamName`,`email`,`email2`,`language` FROM `" . DB_PREFIX . "userdepts`
                            LEFT JOIN `" . DB_PREFIX . "departments`
                            ON `" . DB_PREFIX . "userdepts`.`deptID`  = `" . DB_PREFIX . "departments`.`id`
                            LEFT JOIN `" . DB_PREFIX . "users`
                            ON `" . DB_PREFIX . "userdepts`.`userID`  = `" . DB_PREFIX . "users`.`id`
                            WHERE `deptID`  = '{$IMDT->im_dept}'
                            AND `admin`   = 'no'
                            AND `notify`  = 'yes'
                            GROUP BY `email`
                            ORDER BY `" . DB_PREFIX . "users`.`name`
                            ", __file__, __line__);
                      $staffSend = 'yes';
                    } else {
                      // If reply, is ticket assigned..
                      if ($replyID > 0) {
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
                        $staffSend = 'yes';
                      }
                    }
                    // Any sending to do??
                    if ($staffSend == 'yes') {
                      while ($STAFF = mswSQL_fetchobj($qU)) {
                        $langFile = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/' . $mailTemps['staff'];
                        $langSet = $SETTINGS->language;
                        if ($STAFF->language && file_exists(PATH . 'content/language/' . $STAFF->language . '/mail-templates/' . $mailTemps['staff'])) {
                          $langSet = $STAFF->language;
                          $langFile = PATH . 'content/language/' . $STAFF->language . '/mail-templates/' . $mailTemps['staff'];
                        }
                        $MSIMAP->log('Email template for ' . $STAFF->teamName . ' is ' . $langFile);
                        $MSMAIL->addTag('{NAME}', $STAFF->teamName);
                        $MSMAIL->sendMSMail(array(
                          'from_email' => $SETTINGS->email,
                          'from_name' => $SETTINGS->website,
                          'to_email' => $STAFF->email,
                          'to_name' => $STAFF->teamName,
                          'subject' => $mailSubject['staff'],
                          'replyto' => array(
                            'name' => $SETTINGS->website,
                            'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                          ),
                          'template' => $langFile,
                          'language' => $langSet,
                          'alive' => 'yes',
                          'add-emails' => $STAFF->email2
                        ));
                        $MSIMAP->log('Email sent to ' . $STAFF->teamName . ' <' . $STAFF->email . '>');
                      }
                    }
                  }
                  // Anything to send to admins?
                  $MSIMAP->log('Preparing to send to administrators.');
                  if (isset($mailTemps['admin'])) {
                    $qUA = mswSQL_query("SELECT `name`, `email`, `email2`,`language` FROM `" . DB_PREFIX . "users`
                           WHERE `admin` = 'yes'
                           AND `notify`  = 'yes'
                           ORDER BY `id`
                           ", __file__, __line__);
                    if (mswSQL_numrows($qUA) > 0) {
                      while ($ASTAFF = mswSQL_fetchobj($qUA)) {
                        $langFile = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/' . $mailTemps['admin'];
                        $langSet = $SETTINGS->language;
                        if ($ASTAFF->language && file_exists(PATH . 'content/language/' . $ASTAFF->language . '/mail-templates/' . $mailTemps['admin'])) {
                          $langSet = $ASTAFF->language;
                          $langFile = PATH . 'content/language/' . $ASTAFF->language . '/mail-templates/' . $mailTemps['admin'];
                        }
                        $MSIMAP->log('Email template for ' . $ASTAFF->name . ' is ' . $langFile);
                        $MSMAIL->addTag('{NAME}', $ASTAFF->name);
                        $MSMAIL->sendMSMail(array(
                          'from_email' => $SETTINGS->email,
                          'from_name' => $SETTINGS->website,
                          'to_email' => $ASTAFF->email,
                          'to_name' => $ASTAFF->name,
                          'subject' => $mailSubject['staff'],
                          'replyto' => array(
                            'name' => $SETTINGS->website,
                            'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                          ),
                          'template' => $langFile,
                          'language' => $langSet,
                          'alive' => 'yes',
                          'add-emails' => $ASTAFF->email2
                        ));
                        $MSIMAP->log('Email sent to ' . $ASTAFF->name . ' <' . $ASTAFF->email . '>');
                      }
                    } else {
                      $MSIMAP->log('Notifications are disabled for all administrators');
                    }
                  }
                  // Anything to send to visitor?
                  $MSIMAP->log('Preparing to send new ticket confirmation to visitor..');
                  $pLang = '';
                  if (isset($mailTemps['vis'], $mailSubject['vis']) && $mailSubject['vis'] && $mailTemps['vis'] && $replyID == 0) {
                    if (isset($LI_ACC->language) && file_exists(PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/' . $mailTemps['vis'])) {
                      $mailT = PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/' . $mailTemps['vis'];
                      $pLang = $LI_ACC->language;
                    } else {
                      if (defined('IMAP_CRON_LANG') && file_exists(PATH . 'content/language/' . IMAP_CRON_LANG . '/mail-templates/' . $mailTemps['vis'])) {
                        $mailT = PATH . 'content/language/' . IMAP_CRON_LANG . '/mail-templates/' . $mailTemps['vis'];
                        $pLang = IMAP_CRON_LANG;
                      } else {
                        $mailT = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/' . $mailTemps['vis'];
                      }
                    }
                    $depCusResponse = array();
                    // Is custom department response enabled?
                    if (property_exists($DP, 'auto_response') && $DP->auto_response == 'yes') {
                      $depCusResponse['subject'] = $DP->response_sbj;
                      $depCusResponse['message'] = $DP->response;
                      $MSIMAP->log('Custom department message detected. Subject and message override unless blank');
                    }
                    $MSIMAP->log('Email template for ' . $name . ' is ' . $mailT);
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
                        mswTicketNumber($ID, $SETTINGS->minTickDigits, (isset($ticketNumber) ? $ticketNumber : $T->tickno)),
                        $name
                      ), $emailSubjects['new-ticket-vis']),
                      'replyto' => array(
                        'name' => $SETTINGS->website,
                        'email' => ($IMDT->im_email ? $IMDT->im_email : ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email))
                      ),
                      'template' => $mailT,
                      'dep' => $depCusResponse,
                      'language' => ($pLang ? $pLang : $SETTINGS->language)
                    ));
                    $MSIMAP->log('Email sent to ' . $name . ' <' . $email . '>');
                  }
                } else {
                  // Convert quoted-printable string to an 8 bit string..
                  // Helps make message cleaner..
                  if (function_exists('quoted_printable_decode')) {
                    $comments = quoted_printable_decode($comments);
                  }
                  // Spam notification..
                  // Mail tags and send emails..
                  $MSMAIL->addTag('{ACC_NAME}', $name);
                  $MSMAIL->addTag('{ACC_EMAIL}', $email);
                  $MSMAIL->addTag('{SUBJECT}', $MSBB->cleaner($subject));
                  $MSMAIL->addTag('{DEPT}', $MSYS->department($IMDT->im_dept, $msg_script30));
                  $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($priority));
                  $MSMAIL->addTag('{STATUS}', $MSYS->status($defStatus, $ticketStatusSel));
                  $MSMAIL->addTag('{COMMENTS}', $MSBB->cleaner($comments));
                  $MSMAIL->addTag('{ATTACHMENTS}', (!empty($attString) ? implode(mswNL(), $attString) : $msg_script17));
                  $MSMAIL->addTag('{CUSTOM}', $msg_script17);
                  // Anything to send to admins..
                  $MSIMAP->log('Preparing to send spam notification to administrators');
                  if (isset($mailTemps['admin'])) {
                    $qUA = mswSQL_query("SELECT `name`, `email`, `email2`, `language` FROM `" . DB_PREFIX . "users`
                           WHERE `admin` = 'yes'
                           AND `notify`  = 'yes'
                           AND `spamnotify` = 'yes'
                           ORDER BY `id`
                           ", __file__, __line__);
                    if (mswSQL_numrows($qUA) > 0) {
                      while ($ASTAFF = mswSQL_fetchobj($qUA)) {
                        $langFile = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/spam-notification.txt';
                        $langSet = $SETTINGS->language;
                        if ($ASTAFF->language && file_exists(PATH . 'content/language/' . $ASTAFF->language . '/mail-templates/spam-notification.txt')) {
                          $langSet = $ASTAFF->language;
                          $langFile = PATH . 'content/language/' . $ASTAFF->language . '/mail-templates/spam-notification.txt';
                        }
                        $MSIMAP->log('Email template for ' . $ASTAFF->name . ' is ' . $langFile);
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
                            mswTicketNumber($ID, $SETTINGS->minTickDigits, (isset($ticketNumber) ? $ticketNumber : $T->tickno)),
                            $ASTAFF->name
                          ), $emailSubjects['spam-notify']),
                          'replyto' => array(
                            'name' => $SETTINGS->website,
                            'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
                          ),
                          'template' => $langFile,
                          'language' => $langSet,
                          'add-emails' => $ASTAFF->email2
                        ));
                        $MSIMAP->log('Email sent to ' . $ASTAFF->name . ' <' . $ASTAFF->email . '>');
                      }
                    } else {
                      $MSIMAP->log('Notifications not enabled for administrators');
                    }
                  }
                }
              }
            }
          } else {
            $MSIMAP->log('Blank email body, ignore');
          }
          // If spam filter is enabled, and message is spam, are we just deleting?
          if (defined('MSW_ANTI_SPAM_ENABLED') && $spamDetect == 'yes') {
            $pipes[6] = (++$pipes[6]);
            $MSIMAP->flagMessage($mailbox, $i);
          } else {
            // Are we moving message..
            if ($IMDT->im_protocol == 'imap') {
              if ($IMDT->im_move) {
                $MSIMAP->log('Move option enabled, moving ticket to ' . $IMDT->im_move);
                $MSIMAP->moveMail($mailbox, $i);
              } else {
                $MSIMAP->log('Message flagged for deletion after loop has finished');
                $MSIMAP->flagMessage($mailbox, $i);
              }
            }
          }
        }
      } else {
        // Ban filter deletion..
        if (isset($FLTRS->spam) && $FLTRS->spam == 'no') {
          $MSIMAP->log('Message flagged for deletion by ban filters after loop has finished');
          $MSIMAP->flagMessage($mailbox, $i);
        }
        if ($acpStatus == 'no' && isset($ticketStatusSel[$IMDT->im_status][0])) {
          $MSIMAP->log('Status "' . $ticketStatusSel[$IMDT->im_status][0] . '" has the setting "Ticket Locked to Visitors" set to YES. Reply is ignored.');
        }
      }
    }
  }
  // Close mailbox..closes mailbox and removes messages marked for deletion..
  $MSIMAP->closeMailbox($mailbox);
  if ($count > 0) {
    if ($IMDT->im_move) {
      $MSIMAP->log('Mailbox closed');
    } else {
      $MSIMAP->log('Mailbox closed and tickets purged');
    }
  }

  // Time calculations..
  $memory   = (function_exists('memory_get_usage') ? round(memory_get_usage() / 1048576, 2) . 'MB' : $msgloballang4_3[9]);
  $peak     = (function_exists('memory_get_peak_usage') ? round(memory_get_peak_usage() / 1048576, 2) . 'MB' : $msgloballang4_3[9]);
  $duration = round($MSDT->microtimeFloat() - $time_start, 2) . ' ' . $msgloballang4_3[10];

  // Cron output
  // For manual run, show as html table..
  $output   = array();
  $output[] = $imap_cron_output[0] . ' ' . $MSDT->mswDateTimeDisplay(0, $SETTINGS->dateformat) . ' @ ' . $MSDT->mswDateTimeDisplay(0, $SETTINGS->timeformat);
  $output[] = $imap_cron_output[11] . ' ' . $IMDT->im_host . ':' . $IMDT->im_port;
  $output[] =  mswNFM($pipes[0]) . ' ' . $imap_cron_output[1];
  if ($pipes[1] > 0) {
    $output[] =  mswNFM($pipes[1]) . ' ' . $imap_cron_output[5];
  }
  if ($pipes[4] > 0) {
    $output[] =  mswNFM($pipes[4]) . ' ' . $imap_cron_output[2];
  }
  if ($spamParams['enabled'] == 'yes' || $spamParams['ban-filters'] > 0) {
    if ($pipes[5] > 0) {
      $output[] =  mswNFM($pipes[5]) . ' ' . $imap_cron_output[3];
    }
    if ($pipes[6] > 0) {
      $output[] =  mswNFM($pipes[6]) . ' ' . $imap_cron_output[4];
    }
  }
  if ($pipes[2] > 0) {
    $output[] =  mswNFM($pipes[2]) . ' ' . ($pipes[2] > 1 ? $imap_cron_output[6] : $imap_cron_output[12]);
  }
  if ($pipes[3] > 0) {
    $output[] =  mswNFM($pipes[3]) . ' ' . $imap_cron_output[7];
  }
  $output[] =  mswNL() . $imap_cron_output[8] . ' ' . $memory;
  $output[] =  $imap_cron_output[9] . ' ' . $peak;
  $output[] =  $imap_cron_output[10] . ' ' . $duration;
  echo (isset($_GET['output']) && $_GET['output'] == 'html' ? imapHTML($output, array($msg_adheader24)) : implode(mswNL(), $output));
  $MSIMAP->log('Operation completed. Information: ' . mswNL(2) . implode(mswNL(), $output));
} else {
  $MSIMAP->log('Fatal error, could not connect to mailbox');
}

function imapHTML($d = array(), $l = array()) {
  $html   = array();
  $html[] = '<div class="fluid-container">';
  $html[] = '  <div class="panel panel-default">';
  $html[] = '    <div class="panel-heading">';
  $html[] = '      <i class="fa fa-envelope fa-fw"></i> ' . $l[0];
  $html[] = '    </div>';
  $html[] = '    <div class="panel-body">';
  $html[] = '      <div class="table-responsive">';
  $html[] = '        <table class="table table-striped">';
  $html[] = '          <tbody>';
  $lcnt   = count($d);
  for($i=0; $i<$lcnt; $i++) {
    switch($i) {
      case 0:
        $html[] = '<tr><td style="font-weight:bold"><i class="fa fa-server fa-fw"></i> ' . $d[$i] . '</td></tr>';
        break;
      case 1:
        $html[] = '<tr><td style="color:#008097"><i class="fa fa-check fa-fw"></i> ' . $d[$i] . '</td></tr>';
        break;
      case 2:
        $html[] = '<tr><td><i class="fa fa-envelope-o fa-fw"></i> ' . $d[$i] . '</td></tr>';
        break;
      case ($lcnt - 1):
      case ($lcnt - 2):
      case ($lcnt - 3):
        $html[] = '<tr><td style="color:#008097"><i class="fa fa-info-circle fa-fw"></i> ' . $d[$i] . '</td></tr>';
        break;
      default:
        $html[] = '<tr><td><i class="fa fa-caret-right fa-fw"></i> ' . $d[$i] . '</td></tr>';
        break;
    }
  }
  $html[] = '          </tbody>';
  $html[] = '        </table>';
  $html[] = '      </div>';
  $html[] = '    </div>';
  $html[] = '  </div>';
  $html[] = '</div>';
  return implode(mswNL(), $html);
}

?>