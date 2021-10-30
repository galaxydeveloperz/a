<?php

/* EMAIL DIGEST
   Run as Cron Job (See docs)
-----------------------------------------------------------------------------*/

define('PATH', substr(dirname(__file__), 0, -13) . '/');
define('PARENT', 1);
define('EMAIL_DIGEST', 1);
define('CRON_RUN', 1);

// Log digest, no emails
define('LOG_DIGEST_NO_EMAILS', 0);
define('LOG_DIGEST_FILE_LOC', PATH . 'logs/email-digest-{userid}.log');

// For debugging ONLY
define('EMAIL_DIGEST_LOG', 0);
define('EMAIL_DIGEST_LOG_LOC', PATH . 'logs/email-digest-debug.log');

include(PATH . 'control/classes/system/class.errors.php');
if (ERR_HANDLER_ENABLED) {
  register_shutdown_function('msFatalErr');
  set_error_handler('msErrorhandler');
}

// Session class we don't need here, so just initialise it
$SSN = new stdclass();

include(PATH . 'control/system/init.php');

// Set limits
if (MS_SET_MEM_ALLOCATION_LIMIT) {
  @ini_set('memory_limit', MS_SET_MEM_ALLOCATION_LIMIT);
}
@set_time_limit(MS_SET_TIME_OUT_LIMIT);

include(PATH . 'control/classes/mailer/mail-init.php');

$runZone = MSTZ_SET;
$startTime = $MSDT->mswDateTimeDisplay(0, $SETTINGS->dateformat) . '/' . $MSDT->mswDateTimeDisplay(0, $SETTINGS->timeformat);
$sendCnt = 0;

function mswDigestLog($act, $dt) {
  if (EMAIL_DIGEST_LOG && is_writeable(dirname(EMAIL_DIGEST_LOG_LOC))) {
    mswFPC(EMAIL_DIGEST_LOG_LOC, '[' . date('j F Y @ H:i:s', $dt->mswTimeStamp()) . '] ' . $act . mswNL() . '- - - - - - - - - - - - -' . mswNL());
  }
}

//-------------------------
// Run Auto Close First
//-------------------------

if (file_exists(PATH . 'control/cron/close-tickets.php')) {
  mswDigestLog('Running auto close ops first', $MSDT);
  include(PATH . 'control/cron/close-tickets.php');
  mswDigestLog('Auto close ops finished, starting email digest ops', $MSDT);
}

if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
  $_GET['id'] = (int) $_GET['id'];
  mswDigestLog('ID parameter detected, running only for user ID: ' . $_GET['id'], $MSDT);
}

//-------------------------
// Loop staff members
//-------------------------

$sflp = 0;
$qU = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "users`
      WHERE `notify` = 'yes'
	    AND `digest`   = 'yes'
	    AND `digestops` != ''
      " . (isset($_GET['id']) ? 'AND `id` = \'' . mswSQL($_GET['id']) . '\'' : '') . "
      ORDER BY `id`
      ", __file__, __line__);
while ($STAFF = mswSQL_fetchobj($qU)) {

  ++$sflp;
  
  //-------------------
  // Vars, Arrays
  //-------------------

  $timezone    = ($STAFF->timezone && in_array($STAFF->timezone, array_keys($timezones)) ? $STAFF->timezone : $SETTINGS->timezone);
  date_default_timezone_set($timezone);
  $langSet     = ($STAFF->language ? $STAFF->language : $SETTINGS->language);
  $otherStaffPerms = array('no', 'no');
  $pagePerms   = explode('|', $STAFF->pageAccess);
  $emailDigest = array();
  $dept        = array();
  $dayOfWeek   = date('w', $MSDT->mswTimeStamp());
  $todayDay    = date('D', $MSDT->mswTimeStamp());
  $emailOps    = @unserialize($STAFF->digestops);
  $emailOpDays = ($STAFF->digestdays ? @unserialize($STAFF->digestdays) : array('all'));
  
  //------------------------------------------------
  // Check if day restriction is set
  //------------------------------------------------
  
  if (is_array($emailOps) && !empty($emailOps) && in_array($dayOfWeek, $emailOpDays) || in_array('all', $emailOpDays)) {

    mswDigestLog('Send ops: ' . print_r($emailOps, true), $MSDT);
    
    //------------------------------------------------
    // Permissions for assign email and spam email
    //------------------------------------------------
    
    if ($STAFF->admin == 'yes') {
      $otherStaffPerms = array('yes', 'yes');
      mswDigestLog('Administrator identified (' . $STAFF->name . ')', $MSDT);
    } else {
      mswDigestLog('Other user identified (' . $STAFF->name . '), check permissions', $MSDT);
      if (in_array('assign', $pagePerms)) {
        $otherStaffPerms[0] = 'yes';
        mswDigestLog('Assign permissions set to yes', $MSDT);
      }
      if (in_array('spam', $pagePerms)) {
        $otherStaffPerms[1] = 'yes';
        mswDigestLog('Spam permissions set to yes', $MSDT);
      }
    }

    //---------------------
    // User departments
    //---------------------

    if ($STAFF->admin == 'no' && $STAFF->assigned == 'no') {
      $qUD = mswSQL_query("SELECT `deptID` FROM `" . DB_PREFIX . "userdepts`
             WHERE `userID` = '{$STAFF->id}'
             ", __file__, __line__);
      while ($UD = mswSQL_fetchobj($qUD)) {
        $dept[] = $UD->deptID;
      }
      if (!empty($dept)) {
        mswDigestLog('Departments loaded for staff: ' . print_r($dept, true), $MSDT);
      }
    }

    //----------------------------------
    // Tickets awaiting assignment
    //----------------------------------

    if (in_array('tba', $emailOps) && $otherStaffPerms[0] == 'yes') {
      $q = mswSQL_query("SELECT `subject`,`priority`,`tickno`,`ticketStatus`,
           `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
           `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
           `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
           `" . DB_PREFIX . "departments`.`name` AS `deptName`,
           `" . DB_PREFIX . "levels`.`name` AS `levelName`
           FROM `" . DB_PREFIX . "tickets`
           LEFT JOIN `" . DB_PREFIX . "departments`
           ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
           LEFT JOIN `" . DB_PREFIX . "portal`
           ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
           LEFT JOIN `" . DB_PREFIX . "levels`
           ON (`" . DB_PREFIX . "tickets`.`priority` = 
             IF (`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'), 
               `" . DB_PREFIX . "levels`.`id`,
               `" . DB_PREFIX . "levels`.`marker`
             )
           )
           WHERE `isDisputed` = 'no'
           AND `assignedto`   = 'waiting'
           AND `spamFlag`     = 'no'
           ORDER BY FIELD(`" . DB_PREFIX . "tickets`.`priority`,'high','medium','low'),`levelName`
           ", __file__, __line__);
      $countRows = mswSQL_numrows($q);
      if ($q && $countRows > 0) {
        $emailDigest[] = str_repeat('-', 75) . mswNL() . '<b>(' . mswNFM($countRows) . ') ' . strtoupper($msemail_digest['tba']) . '</b>' . mswNL() . str_repeat('-', 75);
        while ($T = mswSQL_fetchobj($q)) {
          // Hyperlink..
          $link = mswNL();
          $link .= $SETTINGS->scriptpath . '/' . $SETTINGS->afolder . '/?ticket=' . $T->ticketID;
          // Get last reply..
          $last = $MSTICKET->getLastReply($T->ticketID);
          switch ($T->ticketStatus) {
            case 'open':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket14);
              break;
            case 'close':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket15);
              break;
            case 'closed':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket16);
              break;
            default:
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_script17);
              break;
          }
          $emailDigest[] = str_replace(array(
            '{priority}',
            '{subject}',
            '{ticket}',
            '{status}'
          ), array(
            $MSYS->levels($T->priority),
            mswCD($T->subject),
            mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno),
            $tkStatus
          ), $msg_edigest3);
          $emailDigest[] = str_replace(array(
            '{name}',
            '{updated}'
          ), array(
            mswCD($T->ticketName) . ' (' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->timeformat) . ')',
            ($last[0] != '0' ? mswCD($last[0]) . ' (' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->timeformat) . ')' : $msg_script17)
          ), $msg_edigest5) . $link;
          mswDigestLog('Ticket (#' . mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno) . ') awaiting assignment. >> ' . $STAFF->name . ' (' . $STAFF->email . ')', $MSDT);
        }
      } else {
        //$emailDigest[] = $msg_edigest;
        mswDigestLog('No tickets awaiting assignment found', $MSDT);
      }
    } else {
      mswDigestLog('tba is not in the send ops array or spam permissions are set to no', $MSDT);
    }

    //----------------------------------
    // Tickets flagged as spam
    //----------------------------------

    if (in_array('tfs', $emailOps) && $otherStaffPerms[1] == 'yes') {
      $q = mswSQL_query("SELECT `subject`,`priority`,`tickno`,`ticketStatus`,
           `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
           `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
           `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
           `" . DB_PREFIX . "departments`.`name` AS `deptName`,
           `" . DB_PREFIX . "levels`.`name` AS `levelName`
           FROM `" . DB_PREFIX . "tickets`
           LEFT JOIN `" . DB_PREFIX . "departments`
           ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
           LEFT JOIN `" . DB_PREFIX . "portal`
           ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
           LEFT JOIN `" . DB_PREFIX . "levels`
           ON (`" . DB_PREFIX . "tickets`.`priority` = 
             IF (`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'), 
               `" . DB_PREFIX . "levels`.`id`,
               `" . DB_PREFIX . "levels`.`marker`
             )
           )
           WHERE `spamFlag` = 'yes'
           ORDER BY FIELD(`" . DB_PREFIX . "tickets`.`priority`,'high','medium','low'),`levelName`
           ", __file__, __line__);
      $countRows = mswSQL_numrows($q);
      if ($q && $countRows > 0) {
        $emailDigest[] = str_repeat('-', 75) . mswNL() . '<b>(' . mswNFM($countRows) . ') ' . strtoupper($msemail_digest['tfs']) . '</b>' . mswNL() . str_repeat('-', 75);
        while ($T = mswSQL_fetchobj($q)) {
          // Hyperlink..
          $link = mswNL();
          $link .= $SETTINGS->scriptpath . '/' . $SETTINGS->afolder . '/?ticket=' . $T->ticketID;
          // Get last reply..
          $last = $MSTICKET->getLastReply($T->ticketID);
          switch ($T->ticketStatus) {
            case 'open':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket14);
              break;
            case 'close':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket15);
              break;
            case 'closed':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket16);
              break;
            default:
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_script17);
              break;
          }
          $emailDigest[] = str_replace(array(
            '{priority}',
            '{subject}',
            '{ticket}',
            '{status}'
          ), array(
            $MSYS->levels($T->priority),
            mswCD($T->subject),
            mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno),
            $tkStatus
          ), $msg_edigest3);
          $emailDigest[] = str_replace(array(
            '{name}',
            '{updated}'
          ), array(
            mswCD($T->ticketName) . ' (' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->timeformat) . ')',
            ($last[0] != '0' ? mswCD($last[0]) . ' (' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->timeformat) . ')' : $msg_script17)
          ), $msg_edigest5) . $link;
          mswDigestLog('Ticket (#' . mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno) . ') flagged as spam. >> ' . $STAFF->name . ' (' . $STAFF->email . ')', $MSDT);
        }
      } else {
        //$emailDigest[] = $msg_edigest;
        mswDigestLog('No tickets flagged as spam', $MSDT);
      }
    } else {
      mswDigestLog('tfs is not in the send ops array or spam permission are set to no', $MSDT);
    }

    //------------------------------
    // New tickets, no replies
    //------------------------------

    if (in_array('ope', $emailOps)) {
      $q = mswSQL_query("SELECT `subject`,`priority`,`tickno`,`ticketStatus`,
           `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
           `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
           `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
           `" . DB_PREFIX . "departments`.`name` AS `deptName`,
           `" . DB_PREFIX . "levels`.`name` AS `levelName`
           FROM `" . DB_PREFIX . "tickets`
           LEFT JOIN `" . DB_PREFIX . "departments`
           ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
           LEFT JOIN `" . DB_PREFIX . "portal`
           ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
           LEFT JOIN `" . DB_PREFIX . "levels`
           ON (`" . DB_PREFIX . "tickets`.`priority` = 
             IF (`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'), 
               `" . DB_PREFIX . "levels`.`id`,
               `" . DB_PREFIX . "levels`.`marker`
             )
           )
    	     WHERE `ticketStatus` NOT IN('close','closed')
           AND `isDisputed`     = 'no'
           AND `assignedto`    != 'waiting'
    	     AND `spamFlag`       = 'no'
           " . (!empty($dept) ? 'AND (`department` IN(' . mswSQL(implode(',', $dept)) . ') OR FIND_IN_SET(\'' . $STAFF->id . '\', `assignedto`) > 0)' : '') . "
    	     " . ($STAFF->admin == 'no' && empty($dept) && $STAFF->assigned == 'yes' ? 'AND FIND_IN_SET(\'' . $STAFF->id . '\', `assignedto`) > 0' : '') . "
           AND (SELECT count(*) FROM `" . DB_PREFIX . "replies`
             WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
           ) = 0
           ORDER BY FIELD(`" . DB_PREFIX . "tickets`.`priority`,'high','medium','low'),`levelName`
           ", __file__, __line__);
      $countRows = mswSQL_numrows($q);
      if ($q && $countRows > 0) {
        $emailDigest[] = str_repeat('-', 75) . mswNL() . '<b>(' . mswNFM($countRows) . ') ' . strtoupper($msemail_digest['ope']) . '</b>' . mswNL() . str_repeat('-', 75);
        while ($T = mswSQL_fetchobj($q)) {
          // Hyperlink..
          $link = mswNL();
          $link .= $SETTINGS->scriptpath . '/' . $SETTINGS->afolder . '/?ticket=' . $T->ticketID;
          // Get last reply..
          $last = $MSTICKET->getLastReply($T->ticketID);
          switch ($T->ticketStatus) {
            case 'open':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket14);
              break;
            case 'close':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket15);
              break;
            case 'closed':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket16);
              break;
            default:
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_script17);
              break;
          }
          $emailDigest[] = str_replace(array(
            '{priority}',
            '{subject}',
            '{ticket}',
            '{status}'
          ), array(
            $MSYS->levels($T->priority),
            mswCD($T->subject),
            mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno),
            $tkStatus
          ), $msg_edigest3);
          $emailDigest[] = str_replace(array(
            '{name}',
            '{updated}'
          ), array(
            mswCD($T->ticketName) . ' (' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->timeformat) . ')',
            ($last[0] != '0' ? mswCD($last[0]) . ' (' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->timeformat) . ')' : $msg_script17)
          ), $msg_edigest5) . $link;
          mswDigestLog('New Ticket (#' . mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno) . '), no replies. >> ' . $STAFF->name . ' (' . $STAFF->email . ')', $MSDT);
          }
      } else {
        //$emailDigest[] = $msg_edigest;
        mswDigestLog('No new tickets with no replies found', $MSDT);
      }
    } else {
      mswDigestLog('ope is not in the send ops array', $MSDT);
    }

    //----------------------------------------
    // Tickets awaiting staff response
    //----------------------------------------

    if (in_array('ots', $emailOps)) {
      $q = mswSQL_query("SELECT `subject`,`priority`,`tickno`,`ticketStatus`,
           `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
           `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
           `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
           `" . DB_PREFIX . "departments`.`name` AS `deptName`,
           `" . DB_PREFIX . "levels`.`name` AS `levelName`
           FROM `" . DB_PREFIX . "tickets`
           LEFT JOIN `" . DB_PREFIX . "departments`
    	     ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
    	     LEFT JOIN `" . DB_PREFIX . "portal`
    	     ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
    	     LEFT JOIN `" . DB_PREFIX . "levels`
    	     ON (`" . DB_PREFIX . "tickets`.`priority` = 
             IF (`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'), 
               `" . DB_PREFIX . "levels`.`id`,
               `" . DB_PREFIX . "levels`.`marker`
             )
           )
    	     WHERE `ticketStatus`   NOT IN('close','closed')
           AND `isDisputed`     = 'no'
           AND `assignedto`    != 'waiting'
    	     AND `spamFlag`       = 'no'
           " . (!empty($dept) ? 'AND (`department` IN(' . mswSQL(implode(',', $dept)) . ') OR FIND_IN_SET(\'' . $STAFF->id . '\', `assignedto`) > 0)' : '') . "
    	     " . ($STAFF->admin == 'no' && empty($dept) && $STAFF->assigned == 'yes' ? 'AND FIND_IN_SET(\'' . $STAFF->id . '\', `assignedto`) > 0' : '') . "
           AND (SELECT `replyType` FROM `" . DB_PREFIX . "replies`
             WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
             ORDER BY `id` DESC
             LIMIT 1
           ) = 'visitor'
           ORDER BY FIELD(`" . DB_PREFIX . "tickets`.`priority`,'high','medium','low'),`levelName`
           ", __file__, __line__);
      $countRows = mswSQL_numrows($q);
      if ($q && $countRows > 0) {
        $emailDigest[] = str_repeat('-', 75) . mswNL() . '<b>(' . mswNFM($countRows) . ') ' . strtoupper($msemail_digest['ots']) . '</b>' . mswNL() . str_repeat('-', 75);
        while ($T = mswSQL_fetchobj($q)) {
          // Hyperlink..
          $link = mswNL();
          $link .= $SETTINGS->scriptpath . '/' . $SETTINGS->afolder . '/?ticket=' . $T->ticketID;
          // Get last reply..
          $last = $MSTICKET->getLastReply($T->ticketID);
          switch ($T->ticketStatus) {
            case 'open':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket14);
              break;
            case 'close':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket15);
              break;
            case 'closed':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket16);
              break;
            default:
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_script17);
              break;
          }
          $emailDigest[] = str_replace(array(
            '{priority}',
            '{subject}',
            '{ticket}',
            '{status}'
          ), array(
            $MSYS->levels($T->priority),
            mswCD($T->subject),
            mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno),
            $tkStatus
          ), $msg_edigest3);
          $emailDigest[] = str_replace(array(
            '{name}',
            '{updated}'
          ), array(
            mswCD($T->ticketName) . ' (' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->timeformat) . ')',
            ($last[0] != '0' ? mswCD($last[0]) . ' (' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->timeformat) . ')' : $msg_script17)
          ), $msg_edigest5) . $link;
          mswDigestLog('Ticket (#' . mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno) . ') awaiting staff response. >> ' . $STAFF->name . ' (' . $STAFF->email . ')', $MSDT);
        }
      } else {
        //$emailDigest[] = $msg_edigest;
        mswDigestLog('No tickets awaiting staff response', $MSDT);
      }
    } else {
      mswDigestLog('ots is not in the send ops array', $MSDT);
    }

    //-----------------------------------------
    // Tickets awaiting visitor response
    //-----------------------------------------

    if (in_array('otv', $emailOps)) {
      $q = mswSQL_query("SELECT `subject`,`priority`,`tickno`,`ticketStatus`,
           `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
           `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
           `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
           `" . DB_PREFIX . "departments`.`name` AS `deptName`,
           `" . DB_PREFIX . "levels`.`name` AS `levelName`
           FROM `" . DB_PREFIX . "tickets`
           LEFT JOIN `" . DB_PREFIX . "departments`
    	     ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
    	     LEFT JOIN `" . DB_PREFIX . "portal`
    	     ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
    	     LEFT JOIN `" . DB_PREFIX . "levels`
    	     ON (`" . DB_PREFIX . "tickets`.`priority` = 
             IF (`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'), 
               `" . DB_PREFIX . "levels`.`id`,
               `" . DB_PREFIX . "levels`.`marker`
             )
           )
    	     WHERE `ticketStatus` NOT IN('close','closed')
           AND `isDisputed`     = 'no'
           AND `assignedto`    != 'waiting'
    	     AND `spamFlag`       = 'no'
           " . (!empty($dept) ? 'AND (`department` IN(' . mswSQL(implode(',', $dept)) . ') OR FIND_IN_SET(\'' . $STAFF->id . '\', `assignedto`) > 0)' : '') . "
    	     " . ($STAFF->admin == 'no' && empty($dept) && $STAFF->assigned == 'yes' ? 'AND FIND_IN_SET(\'' . $STAFF->id . '\', `assignedto`) > 0' : '') . "
           AND (SELECT `replyType` FROM `" . DB_PREFIX . "replies`
             WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
             ORDER BY `id` DESC
             LIMIT 1
           ) = 'admin'
           ORDER BY FIELD(`" . DB_PREFIX . "tickets`.`priority`,'high','medium','low'),`levelName`
           ", __file__, __line__);
      $countRows = mswSQL_numrows($q);
      if ($q && $countRows > 0) {
        $emailDigest[] = str_repeat('-', 75) . mswNL() . '<b>(' . mswNFM($countRows) . ') ' . strtoupper($msemail_digest['otv']) . '</b>' . mswNL() . str_repeat('-', 75);
        while ($T = mswSQL_fetchobj($q)) {
          // Hyperlink..
          $link = mswNL();
          $link .= $SETTINGS->scriptpath . '/' . $SETTINGS->afolder . '/?ticket=' . $T->ticketID;
          // Get last reply..
          $last = $MSTICKET->getLastReply($T->ticketID);
          switch ($T->ticketStatus) {
            case 'open':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket14);
              break;
            case 'close':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket15);
              break;
            case 'closed':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket16);
              break;
            default:
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_script17);
              break;
          }
          $emailDigest[] = str_replace(array(
            '{priority}',
            '{subject}',
            '{ticket}',
            '{status}'
          ), array(
            $MSYS->levels($T->priority),
            mswCD($T->subject),
            mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno),
            $tkStatus
          ), $msg_edigest3);
          $emailDigest[] = str_replace(array(
            '{name}',
            '{updated}'
          ), array(
            mswCD($T->ticketName) . ' (' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->timeformat) . ')',
            ($last[0] != '0' ? mswCD($last[0]) . ' (' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->timeformat) . ')' : $msg_script17)
          ), $msg_edigest5) . $link;
          mswDigestLog('Ticket (#' . mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno) . ') awaiting visitor response. >> ' . $STAFF->name . ' (' . $STAFF->email . ')', $MSDT);
        }
      } else {
        //$emailDigest[] = $msg_edigest;
        mswDigestLog('No tickets awaiting visitor response', $MSDT);
      }
    } else {
      mswDigestLog('otv is not in the send ops array', $MSDT);
    }

    //-----------------------------
    // New disputes, if enabled
    //-----------------------------

    if ($SETTINGS->disputes == 'yes' && in_array('odp', $emailOps)) {
      $q = mswSQL_query("SELECT `subject`,`priority`,`tickno`,`ticketStatus`,
           `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
           `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
           `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
           `" . DB_PREFIX . "departments`.`name` AS `deptName`,
           `" . DB_PREFIX . "levels`.`name` AS `levelName`
           FROM `" . DB_PREFIX . "tickets`
           LEFT JOIN `" . DB_PREFIX . "departments`
           ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
           LEFT JOIN `" . DB_PREFIX . "portal`
           ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
           LEFT JOIN `" . DB_PREFIX . "levels`
           ON (`" . DB_PREFIX . "tickets`.`priority` = 
             IF (`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'), 
               `" . DB_PREFIX . "levels`.`id`,
               `" . DB_PREFIX . "levels`.`marker`
             )
           )
  	       WHERE `ticketStatus` NOT IN('close','closed')
           AND `isDisputed`     = 'yes'
           AND `assignedto`    != 'waiting'
  		     AND `spamFlag`       = 'no'
           " . (!empty($dept) ? 'AND (`department` IN(' . mswSQL(implode(',', $dept)) . ') OR FIND_IN_SET(\'' . $STAFF->id . '\', `assignedto`) > 0)' : '') . "
  	       " . ($STAFF->admin == 'no' && empty($dept) && $STAFF->assigned == 'yes' ? 'AND FIND_IN_SET(\'' . $STAFF->id . '\', `assignedto`) > 0' : '') . "
           AND (SELECT count(*) FROM `" . DB_PREFIX . "replies`
             WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
           ) = 0
           ORDER BY FIELD(`" . DB_PREFIX . "tickets`.`priority`,'high','medium','low'),`levelName`
           ", __file__, __line__);
      $countRows = mswSQL_numrows($q);
      if ($q && $countRows > 0) {
        $emailDigest[] = str_repeat('-', 75) . mswNL() . '<b>(' . mswNFM($countRows) . ') ' . strtoupper($msemail_digest['odp']) . '</b>' . mswNL() . str_repeat('-', 75);
        while ($T = mswSQL_fetchobj($q)) {
          // Hyperlink..
          $link = mswNL();
          $link .= $SETTINGS->scriptpath . '/' . $SETTINGS->afolder . '/?ticket=' . $T->ticketID;
          // Get last reply..
          $last = $MSTICKET->getLastReply($T->ticketID);
          switch ($T->ticketStatus) {
            case 'open':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket14);
              break;
            case 'close':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket15);
              break;
            case 'closed':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket16);
              break;
            default:
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_script17);
              break;
          }
          $emailDigest[] = str_replace(array(
            '{priority}',
            '{subject}',
            '{ticket}',
            '{status}'
          ), array(
            $MSYS->levels($T->priority),
            mswCD($T->subject),
            mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno),
            $tkStatus
          ), $msg_edigest3);
          $emailDigest[] = str_replace(array(
            '{name}',
            '{updated}',
            '{count}'
          ), array(
            mswCD($T->ticketName) . ' (' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->timeformat) . ')',
            ($last[0] != '0' ? mswCD($last[0]) . ' (' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->timeformat) . ')' : $msg_script17),
            0
          ), $msg_edigest6) . $link;
          mswDigestLog('Dispute Ticket (#' . mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno) . ') is new. >> ' . $STAFF->name . ' (' . $STAFF->email . ')', $MSDT);
        }
      } else {
        //$emailDigest[] = $msg_edigest2;
        mswDigestLog('No new disputes found', $MSDT);
      }
    } else {
      mswDigestLog('odp is not in the send ops array or disputes are disabled', $MSDT);
    }

    //--------------------------------------
    // Disputes awaiting staff response
    //--------------------------------------

    if ($SETTINGS->disputes == 'yes' && in_array('ods', $emailOps)) {
      $q = mswSQL_query("SELECT `subject`,`priority`,`tickno`,`ticketStatus`,
           `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
           `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
           `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
           `" . DB_PREFIX . "departments`.`name` AS `deptName`,
           `" . DB_PREFIX . "levels`.`name` AS `levelName`
           FROM `" . DB_PREFIX . "tickets`
           LEFT JOIN `" . DB_PREFIX . "departments`
           ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
           LEFT JOIN `" . DB_PREFIX . "portal`
           ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
           LEFT JOIN `" . DB_PREFIX . "levels`
           ON (`" . DB_PREFIX . "tickets`.`priority` = 
             IF (`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'), 
               `" . DB_PREFIX . "levels`.`id`,
               `" . DB_PREFIX . "levels`.`marker`
             )
           )
           WHERE `ticketStatus` NOT IN('close','closed')
           AND `isDisputed`     = 'yes'
           AND `assignedto`    != 'waiting'
  		     AND `spamFlag`       = 'no'
           " . (!empty($dept) ? 'AND (`department` IN(' . mswSQL(implode(',', $dept)) . ') OR FIND_IN_SET(\'' . $STAFF->id . '\', `assignedto`) > 0)' : '') . "
  	       " . ($STAFF->admin == 'no' && empty($dept) && $STAFF->assigned == 'yes' ? 'AND FIND_IN_SET(\'' . $STAFF->id . '\', `assignedto`) > 0' : '') . "
           AND (SELECT `replyType` FROM `" . DB_PREFIX . "replies`
             WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
             ORDER BY `id` DESC
             LIMIT 1
           ) = 'visitor'
           ORDER BY FIELD(`" . DB_PREFIX . "tickets`.`priority`,'high','medium','low'),`levelName`
           ", __file__, __line__);
      $countRows = mswSQL_numrows($q);
      if ($q && $countRows > 0) {
        $emailDigest[] = str_repeat('-', 75) . mswNL() . '<b>(' . mswNFM($countRows) . ') ' . strtoupper($msemail_digest['ods']) . '</b>' . mswNL() . str_repeat('-', 75);
        while ($T = mswSQL_fetchobj($q)) {
          // Hyperlink..
          $link = mswNL();
          $link .= $SETTINGS->scriptpath . '/' . $SETTINGS->afolder . '/?ticket=' . $T->ticketID;
          // Get last reply..
          $last = $MSTICKET->getLastReply($T->ticketID);
          switch ($T->ticketStatus) {
            case 'open':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket14);
              break;
            case 'close':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket15);
              break;
            case 'closed':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket16);
              break;
            default:
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_script17);
              break;
          }
          $emailDigest[] = str_replace(array(
            '{priority}',
            '{subject}',
            '{ticket}',
            '{status}'
          ), array(
            $MSYS->levels($T->priority),
            mswCD($T->subject),
            mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno),
            $tkStatus
          ), $msg_edigest3);
          $emailDigest[] = str_replace(array(
            '{name}',
            '{updated}',
            '{count}'
          ), array(
            mswCD($T->ticketName) . ' (' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->timeformat) . ')',
            ($last[0] != '0' ? mswCD($last[0]) . ' (' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->timeformat) . ')' : $msg_script17),
            0
          ), $msg_edigest6) . $link;
          mswDigestLog('Dispute Ticket (#' . mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno) . ') awaiting staff response. >> ' . $STAFF->name . ' (' . $STAFF->email . ')', $MSDT);
        }
      } else {
        //$emailDigest[] = $msg_edigest2;
        mswDigestLog('No dispute tickets awaiting staff response', $MSDT);
      }
    } else {
      mswDigestLog('ods is not in the send ops array or disputes are disabled', $MSDT);
    }

    //------------------------------------
    // Disputes awaiting visitor response
    //------------------------------------

    if ($SETTINGS->disputes == 'yes' && in_array('odv', $emailOps)) {
      $q = mswSQL_query("SELECT `subject`,`priority`,`tickno`,`ticketStatus`,
           `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
           `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
           `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
           `" . DB_PREFIX . "departments`.`name` AS `deptName`,
           `" . DB_PREFIX . "levels`.`name` AS `levelName`
           FROM `" . DB_PREFIX . "tickets`
           LEFT JOIN `" . DB_PREFIX . "departments`
           ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
           LEFT JOIN `" . DB_PREFIX . "portal`
           ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
           LEFT JOIN `" . DB_PREFIX . "levels`
           ON (`" . DB_PREFIX . "tickets`.`priority` = 
             IF (`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'), 
               `" . DB_PREFIX . "levels`.`id`,
               `" . DB_PREFIX . "levels`.`marker`
             )
           )
           WHERE `ticketStatus` NOT IN('close','closed')
           AND `isDisputed`     = 'yes'
           AND `assignedto`    != 'waiting'
  		     AND `spamFlag`       = 'no'
           " . (!empty($dept) ? 'AND (`department` IN(' . mswSQL(implode(',', $dept)) . ') OR FIND_IN_SET(\'' . $STAFF->id . '\', `assignedto`) > 0)' : '') . "
  	       " . ($STAFF->admin == 'no' && empty($dept) && $STAFF->assigned == 'yes' ? 'AND FIND_IN_SET(\'' . $STAFF->id . '\', `assignedto`) > 0' : '') . "
           AND (SELECT `replyType` FROM `" . DB_PREFIX . "replies`
             WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
             ORDER BY `id` DESC
             LIMIT 1
           ) = 'admin'
           ORDER BY FIELD(`" . DB_PREFIX . "tickets`.`priority`,'high','medium','low'),`levelName`
           ", __file__, __line__);
      $countRows = mswSQL_numrows($q);
      if ($q && $countRows > 0) {
        $emailDigest[] = str_repeat('-', 75) . mswNL() . '<b>(' . mswNFM($countRows) . ') ' . strtoupper($msemail_digest['odv']) . '</b>' . mswNL() . str_repeat('-', 75);
        while ($T = mswSQL_fetchobj($q)) {
          // Hyperlink..
          $link = mswNL();
          $link .= $SETTINGS->scriptpath . '/' . $SETTINGS->afolder . '/?ticket=' . $T->ticketID;
          // Get last reply..
          $last = $MSTICKET->getLastReply($T->ticketID);
          switch ($T->ticketStatus) {
            case 'open':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket14);
              break;
            case 'close':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket15);
              break;
            case 'closed':
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_viewticket16);
              break;
            default:
              $tkStatus = (isset($ticketStatusSel[$T->ticketStatus][0]) ? $ticketStatusSel[$T->ticketStatus][0] : $msg_script17);
              break;
          }
          $emailDigest[] = str_replace(array(
            '{priority}',
            '{subject}',
            '{ticket}',
            '{status}'
          ), array(
            $MSYS->levels($T->priority),
            mswCD($T->subject),
            mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno),
            $tkStatus
          ), $msg_edigest3);
          $emailDigest[] = str_replace(array(
            '{name}',
            '{updated}',
            '{count}'
          ), array(
            mswCD($T->ticketName) . ' (' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($T->ticketStamp, $SETTINGS->timeformat) . ')',
            ($last[0] != '0' ? mswCD($last[0]) . ' (' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($last[1], $SETTINGS->timeformat) . ')' : $msg_script17),
            0
          ), $msg_edigest6) . $link;
          mswDigestLog('Dispute Ticket (#' . mswTicketNumber($T->ticketID, $SETTINGS->minTickDigits, $T->tickno) . ') awaiting visitor response. >> ' . $STAFF->name . ' (' . $STAFF->email . ')', $MSDT);
        }
      } else {
        //$emailDigest[] = $msg_edigest2;
        mswDigestLog('No dispute tickets awaiting visitor response', $MSDT);
      }
    } else {
      mswDigestLog('odv is not in the send ops array or disputes are disabled', $MSDT);
    }

    //-----------------------------------------------------
    // Send Mail (or log), but only if there is data
    //-----------------------------------------------------

    if (!empty($emailDigest)) {

      if (LOG_DIGEST_NO_EMAILS) {
        $thisFile = str_replace('{userid}', $STAFF->id, LOG_DIGEST_FILE_LOC);
        $data = str_replace(array('<b>','</b>'),array(),implode(mswNL(2), $emailDigest));
        if (is_writeable(dirname($thisFile))) {
          if (file_exists($thisFile)) {
            unlink($thisFile);
          }
          mswFPC($thisFile, $data);
          mswDigestLog('Email digest written to log file (' . $thisFile . ') for: ' . $STAFF->name, $MSDT);
        } else {
          mswDigestLog($thisFile .  ' directory is not writeable', $MSDT);
        }
      } else {
        
        $langFile = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/email-digest.txt';
        if ($langSet != $SETTINGS->language) {
          if (file_exists(PATH . 'content/language/' . $langSet . '/mail-templates/email-digest.txt')) {
            $langFile = PATH . 'content/language/' . $langSet . '/mail-templates/email-digest.txt';
          }
        }
        mswDigestLog('Language file is: ' . $langFile, $MSDT);

        if (file_exists($langFile)) {
          $MSMAIL->addTag('{DIGEST}', implode(mswNL(2), $emailDigest));
          $MSMAIL->addTag('{DATE}', $MSDT->mswDateTimeDisplay(0, $SETTINGS->dateformat));
          $MSMAIL->addTag('{TIME}', $MSDT->mswDateTimeDisplay(0, $SETTINGS->timeformat));
          $MSMAIL->addTag('{NAME}', $STAFF->name);
          $MSMAIL->sendMSMail(array(
            'from_email' => $SETTINGS->email,
            'from_name' => $SETTINGS->website,
            'to_email' => $STAFF->email,
            'to_name' => $STAFF->name,
            'subject' => str_replace(array(
              '{website}'
              ), array(
              $SETTINGS->website
              ), $emailSubjects['email-digest']
            ),
            'replyto' => array(
            'name' => $SETTINGS->website,
            'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
            ),
            'template' => $langFile,
            'language' => $langSet,
            'alive' => 'yes',
            'add-emails' => $STAFF->email2
          ));
          ++$sendCnt;
          mswDigestLog('Email ops completed for ' . $STAFF->name . '(' . $STAFF->email . '), Ticket content: ' . count($emailDigest), $MSDT);
        } else {
          mswDigestLog('Error, mail template missing: "' . PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/' . $mailT . '"', $MSDT);
        }
      
      }

    } else {
      mswDigestLog('No email to be sent to ' . $STAFF->name . '(' . $STAFF->email . '), no ticket data found.', $MSDT);
    }
  
  } else {
    if (empty($emailOps)) {
      mswDigestLog('No email to be sent to ' . $STAFF->name . '(' . $STAFF->email . ') as no send options are set in staff settings for email digest', $MSDT);
    } else {
      mswDigestLog('No email to be sent to ' . $STAFF->name . '(' . $STAFF->email . ') as day restriction in place in staff settings and no emails are to be sent on ' . $todayDay . '.', $MSDT);
    }
  }
}

//------------------
// The End
//------------------

if ($sendCnt > 0 && !LOG_DIGEST_NO_EMAILS) {
  $MSMAIL->smtpClose();
}

// No staff enabled?
if ($sflp == 0) {
  mswDigestLog('[Error] No send options are set in staff settings for email digest, so all staff ignored.', $MSDT);
}

date_default_timezone_set($runZone);

$runmsg = str_replace(array(
  '{started}',
  '{finished}'
), array(
  $startTime,
  $MSDT->mswDateTimeDisplay(0, $SETTINGS->dateformat) . '/' . $MSDT->mswDateTimeDisplay(0, $SETTINGS->timeformat)
), $msg_edigest4);

mswDigestLog($runmsg, $MSDT);
echo '[' . date('j F Y @ H:iA', $MSDT->mswTimeStamp()) . '] ' . $runmsg . PHP_EOL . str_repeat('-=', 50) . PHP_EOL;

?>