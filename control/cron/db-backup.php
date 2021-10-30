<?php

/* DB BACKUP
   Run as Cron Job (See docs)
------------------------------------------------------------------------------*/

define('PATH', substr(dirname(__file__), 0, -13) . '/');
define('PARENT', 1);
define('CRON_RUN', 1);

include(PATH . 'control/classes/system/class.errors.php');
if (ERR_HANDLER_ENABLED) {
  set_error_handler('msErrorhandler');
}

// Session class we don't need here, so just initialise it
$SSN = new stdclass();

date_default_timezone_set('UTC');
include(PATH . 'control/system/init.php');

// Set limits
if (MS_SET_MEM_ALLOCATION_LIMIT) {
  @ini_set('memory_limit', MS_SET_MEM_ALLOCATION_LIMIT);
}
@set_time_limit(MS_SET_TIME_OUT_LIMIT);

include(PATH . 'control/classes/mailer/mail-init.php');
include(PATH . 'control/classes/class.backup.php');

if (!is_dir(PATH . 'backups') || !is_writeable(PATH . 'backups')) {
  die('"<b>' . PATH . 'backups' . '</b>" folder must exist and be writeable. Please check directory and permissions..');
}

$filepath         = PATH . 'backups/' . $msg_script33 . '-' . date('dMY', $MSDT->mswTimeStamp()) . '-' . date('His', $MSDT->mswTimeStamp()) . '.gz';
$BACKUP           = new dbBackup($filepath, true);
$BACKUP->settings = $SETTINGS;
$BACKUP->dt       = $MSDT;

//-----------------------
// Backup database
//-----------------------

$BACKUP->doDump();

//-------------------------------------------
// Send emails if there are backup emails..
//-------------------------------------------

if (file_exists($filepath) && $SETTINGS->backupEmails) {
  $em_add = '';
  $em     = '';
  if (strpos($SETTINGS->backupEmails, ',') !== false) {
    // Multiple addresses..
    $emails = array_map('trim', explode(',', $SETTINGS->backupEmails));
    // First email is main address..
    if (isset($emails[0])) {
      $em = $emails[0];
      // Now remove it from array..
      unset($emails[0]);
    }
    // Implode additional addresses..
    if (!empty($emails)) {
      $em_add = implode(',', $emails);
    }
  } else {
    // Just main address..
    $em = $SETTINGS->backupEmails;
  }
  if ($em) {
    $MSMAIL->addTag('{HELPDESK}', mswCD($SETTINGS->website));
    $MSMAIL->addTag('{DATE_TIME}', $MSDT->mswDateTimeDisplay($MSDT->mswTimeStamp(), $SETTINGS->dateformat) . ' @ ' . $MSDT->mswDateTimeDisplay($MSDT->mswTimeStamp(), $SETTINGS->timeformat));
    $MSMAIL->addTag('{VERSION}', SCRIPT_VERSION);
    $MSMAIL->addTag('{FILE}', basename($filepath));
    $MSMAIL->addTag('{SCRIPT}', SCRIPT_NAME);
    $MSMAIL->addTag('{SIZE}', mswFSC(@filesize($filepath)));
    $MSMAIL->attachments[$filepath] = basename($filepath);
    $MSMAIL->sendMSMail(array(
      'from_email' => $SETTINGS->email,
      'from_name' => $SETTINGS->website,
      'to_email' => $em,
      'to_name' => $em,
      'subject' => str_replace(array(
        '{website}'
      ), array(
        $SETTINGS->website
      ), $emailSubjects['db-backup']),
      'replyto' => array(
        'name' => $SETTINGS->website,
        'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
      ),
      'template' => PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/backup.txt',
      'language' => $SETTINGS->language,
      'alive' => ($em_add ? 'yes' : 'no'),
      'add-emails' => $em_add
    ));
    // Close connection
    $MSMAIL->smtpClose();
    // Remove file..
    @unlink($filepath);
  }
}

echo '[' . date('j F Y @ H:iA') . '] ' . $msg_script32 . PHP_EOL . str_repeat('-=', 50) . PHP_EOL;

?>