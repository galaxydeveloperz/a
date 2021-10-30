<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT') || !defined('AJAX_TICK_REPLY')) {
  exit;
}

// Priority levels and statuses
include(BASE_PATH . 'control/system/loader.php');

$replyToAddr = '';
$isDispute   = ($SETTINGS->disputes == 'yes' && $_POST['isDisputed'] == 'yes' ? 'yes' : 'no');
// Is ticket staff locked?
if ($SETTINGS->adminlock == 'yes') {
  $TL = mswSQL_table('tickets', 'id', (int) $_POST['ticketID']);
  if ($TL->lockteam > 0 && $TL->lockteam != $MSTEAM->id) {
    $USR = mswSQL_table('users', 'id', $TL->lockteam);
    $json = array(
      'msg' => 'err',
      'sys' => $msadminlang3_1[2],
      'info' => str_replace('{name}', mswSH($USR->name), $msadminlang_tickets_3_7[21])
    );
    echo $JSON->encode($json);
    exit;
  }
}
// Add reply..
$ret = $MSTICKET->addTicketReply();
/* RETURN PARAMETERS
   merged     = yes/no for merged
   ticketID   = Ticket ID
   subject    = Merged ticket subject
   newreply   = Reply ID
   oldrantick = Old random ticket no (if applicable)
---------------------------------------------------------------------*/
// Get merged parent ticket or current ticket..
$TICKET = mswSQL_table('tickets', 'id', $ret['ticketID']);
// Visitor Info..
$PORTAL = mswSQL_table('portal', 'id', $TICKET->visitorID);
// Add attachments..
$attString   = array();
if (!empty($_FILES['file']['tmp_name'])) {
  for ($i = 0; $i < count($_FILES['file']['tmp_name']); $i++) {
    $name = $_FILES['file']['name'][$i];
    $temp = $_FILES['file']['tmp_name'][$i];
    $size = $_FILES['file']['size'][$i];
    $mime = $_FILES['file']['type'][$i];
    if ($name && $temp && $size > 0) {
      $atID = $MSPTICKETS->addAttachment(array(
        'temp' => $temp,
        'name' => $name,
        'size' => $size,
        'mime' => $mime,
        'tID' => $TICKET->id,
        'rID' => $ret['newreply'],
        'dept' => $TICKET->department,
        'incr' => $i
      ));
      $attString[] = $SETTINGS->scriptpath . '/?dl' . $atID[0];
      $attPath[$atID[1]] = basename($atID[1]);
    }
  }
}
// Write history if enabled..
if (isset($_POST['history']) || USER_ADMINISTRATOR == 'no') {
  $MSTICKET->historyLog($TICKET->id, str_replace(array(
    '{user}',
    '{id}',
    '{from}',
    '{to}'
  ), array(
    $MSTEAM->name,
    $ret['newreply'],
    ($ret['merged'] == 'yes' ? mswTicketNumber($_POST['ticketID'], $SETTINGS->minTickDigits, $ret['oldrantick']) : ''),
    ($ret['merged'] == 'yes' ? mswTicketNumber($TICKET->id, $SETTINGS->minTickDigits, $TICKET->tickno) : '')
  ), $msg_ticket_history['team-reply-add' . ($ret['merged'] == 'yes' ? '-merge' : '')]));
}
// Mail if enabled..
if ($_POST['mail'] == 'yes') {
  // Everything in the post array..
  foreach ($_POST AS $key => $value) {
    if (!is_array($value)) {
      $MSMAIL->addTag('{' . strtoupper($key) . '}', $MSBB->cleaner($value));
    }
  }
  // Tags..
  $MSMAIL->addTag('{SIGNATURE}', ($MSTEAM->emailSigs == 'yes' && $MSTEAM->signature ? $MSTEAM->signature : ''));
  $MSMAIL->addTag('{SUBJECT_OLD}', $ret['subject']);
  $MSMAIL->addTag('{ATTACHMENTS}', (!empty($attString) ? implode(mswNL(), $attString) : $msg_script17));
  $MSMAIL->addTag('{NAME}', (isset($PORTAL->name) ? $PORTAL->name : ''));
  $MSMAIL->addTag('{MERGED_TICKET}', ($ret['merged'] == 'yes' ? mswTicketNumber($_POST['ticketID'], $SETTINGS->minTickDigits, $ret['oldrantick']) : ''));
  $MSMAIL->addTag('{TICKET}', mswTicketNumber($TICKET->id, $SETTINGS->minTickDigits, $TICKET->tickno));
  $MSMAIL->addTag('{SUBJECT}', $TICKET->subject);
  $MSMAIL->addTag('{COMMENTS}', $TICKET->comments);
  $MSMAIL->addTag('{REPCOMMS}', $_POST['comments']);
  $MSMAIL->addTag('{DEPT}', $MSYS->department($TICKET->department, $msg_script30));
  $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($TICKET->priority));
  $MSMAIL->addTag('{STATUS}', $MSYS->status($TICKET->ticketStatus, $ticketStatusSel));
  $MSMAIL->addTag('{USER}', ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name));
  $MSMAIL->addTag('{CUSTOM}', $MSCFMAN->email($ret['ticketID'], $ret['newreply']));
  $MSMAIL->addTag('{ID}', $TICKET->id);
  // Pass ticket number as custom mail header..
  $MSMAIL->xheaders['X-TicketNo'] = mswTicketNumber($TICKET->id, $SETTINGS->minTickDigits, $TICKET->tickno);
  // If this ticket was opened by imap, the return address should be the imap address..
  if ($TICKET->source == 'imap') {
    $IDEPT = mswSQL_table('imap', 'im_dept', $TICKET->department, '', '`im_email`');
    if (isset($IDEPT->im_email) && $IDEPT->im_email) {
      $replyToAddr = $IDEPT->im_email;
    }
  }
  // What mail templates are we using..
  switch ($isDispute) {
    case 'yes':
      if ($PORTAL->language && file_exists(LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-dispute-reply.txt')) {
        $mailT = LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-dispute-reply.txt';
        $pLang = $PORTAL->language;
      } else {
        $mailT = LANG_PATH . 'admin-dispute-reply.txt';
      }
      break;
    default:
      if ($TICKET->source == 'imap') {
        if ($PORTAL->language && file_exists(LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-ticket-reply' . ($ret['merged'] == 'yes' ? '-merged-imap' : '-imap') . '.txt')) {
          $mailT = LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-ticket-reply' . ($ret['merged'] == 'yes' ? '-merged-imap' : '-imap') . '.txt';
          $pLang = $PORTAL->language;
        } else {
          $mailT = LANG_PATH . 'admin-ticket-reply' . ($ret['merged'] == 'yes' ? '-merged-imap' : '-imap') . '.txt';
        }
      } else {
        if (isset($PORTAL->language) && file_exists(LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-ticket-reply' . ($ret['merged'] == 'yes' ? '-merged' : '') . '.txt')) {
          $mailT = LANG_BASE_PATH . $PORTAL->language . '/mail-templates/admin-ticket-reply' . ($ret['merged'] == 'yes' ? '-merged' : '') . '.txt';
          $pLang = $PORTAL->language;
        } else {
          $mailT = LANG_PATH . 'admin-ticket-reply' . ($ret['merged'] == 'yes' ? '-merged' : '') . '.txt';
        }
      }
      break;
  }
  // Ticket subject for email...
  $ticketSbj = str_replace(array(
    '{website}',
    '{ticket}'
  ), array(
    $SETTINGS->website,
    mswTicketNumber($TICKET->id, $SETTINGS->minTickDigits, $TICKET->tickno)
  ), $emailSubjects['admin-reply']);
  // If imap ticket, subject references ticket subject, rather than default message..
  if ($TICKET->source == 'imap' && $isDispute == 'no') {
    $ticketSbj = str_replace(array(
      '{subject}',
      '{ticket}'
    ), array(
      $TICKET->subject,
      mswTicketNumber($TICKET->id, $SETTINGS->minTickDigits, $TICKET->tickno)
    ), $emailSubjects['ticket-imap-reply']);
  }
  // Include attachments for imap emails?
  if ($SETTINGS->imap_attach == 'yes' && !empty($attPath) && $TICKET->source == 'imap' && $isDispute == 'no') {
    $MSMAIL->attachments = $attPath;
  }
  // Send email to original ticket creator..
  if (isset($PORTAL->email)) {
    $MSMAIL->sendMSMail(array(
      'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
      'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
      'to_email' => $PORTAL->email,
      'to_name' => $PORTAL->name,
      'subject' => $ticketSbj,
      'replyto' => array(
        'name' => $SETTINGS->website,
        'email' => ($replyToAddr ? $replyToAddr : ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email))
      ),
      'template' => $mailT,
      'language' => (isset($pLang) ? $pLang : (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language)),
      'alive' => 'yes'
    ));
  }
  // Clear attachments..
  if (!empty($attPath)) {
    $MSMAIL->clearAttachments();
  }
  // If this is a dispute, notify other users in dispute..
  if ($isDispute == 'yes' && $SETTINGS->disputes == 'yes') {
    $q = mswSQL_query("SELECT `name`,`email`,`language` FROM `" . DB_PREFIX . "disputes`
	       LEFT JOIN `" . DB_PREFIX . "portal`
         ON `" . DB_PREFIX . "disputes`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
         WHERE `" . DB_PREFIX . "disputes`.`ticketID` = '{$TICKET->id}'
			   GROUP BY `email`
			   ORDER BY `name`
			   ", __file__, __line__);
    while ($D_USR = mswSQL_fetchobj($q)) {
      $pLang = '';
      // Check which templates to use based on language..
      if ($D_USR->language && file_exists(LANG_BASE_PATH . $D_USR->language . '/mail-templates/admin-dispute-reply.txt')) {
        $mailT = LANG_BASE_PATH . $D_USR->language . '/mail-templates/admin-dispute-reply.txt';
        $pLang = $D_USR->language;
      } else {
        $mailT = LANG_PATH . 'admin-dispute-reply.txt';
      }
      $MSMAIL->sendMSMail(array(
        'from_email' => ($MSTEAM->emailFrom ? $MSTEAM->emailFrom : $MSTEAM->email),
        'from_name' => ($MSTEAM->nameFrom ? $MSTEAM->nameFrom : $MSTEAM->name),
        'to_email' => $D_USR->email,
        'to_name' => $D_USR->name,
        'subject' => $ticketSbj,
        'replyto' => array(
          'name' => $SETTINGS->website,
          'email' => ($replyToAddr ? $replyToAddr : ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email))
        ),
        'template' => $mailT,
        'language' => ($pLang ? $pLang : (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language)),
        'alive' => 'yes'
      ));
    }
  }
  $MSMAIL->smtpClose();
}

// Staff notification
if (USER_ADMINISTRATOR == 'yes' || $MSTEAM->staffupnotify == 'yes') {
  if (!empty($_POST['staffmail'])) {
    $sSent = 0;
    foreach($_POST['staffmail'] AS $staffSendID) {
      $qU = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "users`
            WHERE `id` = '{$staffSendID}'
            AND `notify` = 'yes'
            ", __file__, __line__);
      $STAFF = mswSQL_fetchobj($qU);
      if (isset($STAFF->id)) {
        $langFile = BASE_PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/admin-ticket-update-staff-notify.txt';
        $langSet = $SETTINGS->language;
        if ($STAFF->language && file_exists(BASE_PATH . 'content/language/' . $STAFF->language . '/mail-templates/admin-ticket-update-staff-notify.txt')) {
          $langSet = $STAFF->language;
          $langFile = BASE_PATH . 'content/language/' . $STAFF->language . '/mail-templates/admin-ticket-update-staff-notify.txt';
        }
        $MSMAIL->addTag('{STAFF_MEMBER}', $MSTEAM->name);
        $MSMAIL->addTag('{STARTER}', $PORTAL->name);
        $MSMAIL->addTag('{NAME}', $STAFF->name);
        $MSMAIL->addTag('{TICKET}', mswTicketNumber($TICKET->id, $SETTINGS->minTickDigits, $TICKET->tickno));
        $MSMAIL->addTag('{SUBJECT}', $TICKET->subject);
        $MSMAIL->addTag('{COMMENTS}', $TICKET->comments);
        $MSMAIL->addTag('{REPCOMMS}', $_POST['comments']);
        $MSMAIL->addTag('{DEPT}', $MSYS->department($TICKET->department, $msg_script30));
        $MSMAIL->addTag('{PRIORITY}', $MSYS->levels($TICKET->priority));
        $MSMAIL->addTag('{STATUS}', $MSYS->status($TICKET->ticketStatus, $ticketStatusSel));
        $MSMAIL->addTag('{CUSTOM}', $MSCFMAN->email($ret['ticketID'], $ret['newreply']));
        $MSMAIL->addTag('{ATTACHMENTS}', (!empty($attString) ? implode(mswNL(), $attString) : $msg_script17));
        $MSMAIL->addTag('{ID}', $TICKET->id);
        $MSMAIL->sendMSMail(array(
          'from_email' => $SETTINGS->email,
          'from_name' => $SETTINGS->website,
          'to_email' => $STAFF->email,
          'to_name' => $STAFF->name,
          'subject' => str_replace(array(
            '{website}',
            '{ticket}',
            '{staff}'
          ), array(
            $SETTINGS->website,
            mswTicketNumber($TICKET->id, $SETTINGS->minTickDigits, $TICKET->tickno),
            $MSTEAM->name
          ), $emailSubjects['admin-team-notification-update']),
          'replyto' => array(
            'name' => $SETTINGS->website,
            'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
          ),
          'template' => $langFile,
          'language' => $langSet,
          'alive' => 'yes',
          'add-emails' => $STAFF->email2
        ));
        ++$sSent;
      }
      // Close smtp
      if ($sSent > 0) {
        $MSMAIL->smtpClose();
      }
    }
  }
}

// Staff selections
$MSUSERS->staffSaveSelections($MSTEAM->id);

// Reload or redirect..
if ($ret['merged'] == 'no') {
  if (CONF_DIALOG) {
    $link = getTicketLink(array(
      't' => $TICKET,
      'l' => array($msg_adheader5,$msg_adheader6,$msg_adheader28,$msg_adheader29,$msg_adheader63,$msg_adheader32),
      's' => $ticketStatusSel
    ));
    $json = array(
      'msg' => 'ok',
      'sys' => $msadminlang3_1[2],
      'info' => $msg_viewticket47
    );
    $json['buttons'] = array();
    $json['buttons'][] = '<a href="?p=view-' . ($TICKET->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&amp;id=' . $TICKET->id . '">' . $msgloballang4_3[4] . '</a>';
    if ($link[0] && $link[1]) {
      $json['buttons'][] = '<a href="' . $link[0] . '">' . $link[1] . '</a>';
    }
    $json['buttons'][] = '<a href="?p=add">' . $msg_dept . '</a>';
    // Append control buttons
    include(PATH . 'templates/system/control-btns.php');
    $json['info'] .= $c_b;
  } else {
    $json['msg'] = 'reload';
  }
} else {
  $json = array(
    'msg' => 'ok',
    'field' => 'redirect',
    'redirect' => 'index.php?p=view-ticket&merged=' . ltrim($_POST['mergeid'], '0')
  );
}

echo $JSON->encode($json);
exit;

?>