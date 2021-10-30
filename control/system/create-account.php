<?php

/* System - Create Account Ops
----------------------------------------------------------*/

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Account verification..
if (isset($_GET['va'])) {
  $code    = $_GET['va'];
  $message = '';
  $flag    = '';
  if ($code == '' || !ctype_alnum($code) || $SETTINGS->createAcc == 'no') {
    $HEADERS->err403();
  }
  // Get account..
  $A = mswSQL_table('portal', 'system1', mswSQL($code));
  if (!isset($A->id)) {
    $message = $msg_public_create8;
    $flag    = 'fail';
  } else {
    if ($A->verified == 'yes') {
      $message = $msg_public_create9;
      $flag    = 'exists';
    } else {
      // Load mail params
      include(PATH . 'control/classes/mailer/mail-init.php');
      // Activate..
      $pass = $MSACC->ms_generate();
      $rows = $MSACC->activate(array(
        'id' => $A->id,
        'pass' => $pass
      ));
      if ($rows > 0) {
        $flag  = 'ok';
        $MSMAIL->addTag('{NAME}', $A->name);
        $MSMAIL->addTag('{EMAIL}', $A->email);
        $MSMAIL->addTag('{PASS}', $pass);
        $MSMAIL->addTag('{LOGIN_URL}', $SETTINGS->scriptpath);
        $MSMAIL->sendMSMail(array(
          'from_email' => $SETTINGS->email,
          'from_name' => $SETTINGS->website,
          'to_email' => $A->email,
          'to_name' => $A->name,
          'subject' => str_replace(array(
            '{website}'
          ), array(
            $SETTINGS->website
          ), $emailSubjects['acc-verified']),
          'replyto' => array(
            'name' => $SETTINGS->website,
            'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
          ),
          'template' => PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/account-verified.txt',
          'language' => $SETTINGS->language
        ));
        // Admin notification..
        if ($SETTINGS->newAccNotify == 'yes') {
          $qU = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "users`
                WHERE (`id` = '1' OR `admin` = 'yes')
                AND `notify` = 'yes'
                ORDER BY `name`
                ", __file__, __line__);
          while ($ADMIN = mswSQL_fetchobj($qU)) {
            $langFile = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-account-notification.txt';
            $langSet = $SETTINGS->language;
            if ($ADMIN->language && file_exists(PATH . 'content/language/' . $ADMIN->language . '/mail-templates/new-account-notification.txt')) {
              $langSet = $ADMIN->language;
              $langFile = PATH . 'content/language/' . $ADMIN->language . '/mail-templates/new-account-notification.txt';
            }
            $MSMAIL->addTag('{IP}', mswIP());
            $MSMAIL->sendMSMail(array(
              'from_email' => $SETTINGS->email,
              'from_name' => $SETTINGS->website,
              'to_email' => $ADMIN->email,
              'to_name' => $ADMIN->name,
              'subject' => str_replace(array(
                '{website}'
              ), array(
                $SETTINGS->website
              ), $emailSubjects['new-acc-notify']),
              'replyto' => array(
                'name' => $SETTINGS->website,
                'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
              ),
              'template' => $langFile,
              'language' => $langSet,
              'add-emails' => $ADMIN->email2
            ));
          }
        }
        $MSMAIL->smtpClose();
      }
      $message = str_replace('{email}', $A->email, $msg_public_create10);
    }
  }
  // Show message..
  $title = $msg_public_create7;
  include(PATH . 'control/header.php');
  $tpl = new Savant3();
  $tpl->assign('TXT', array(
    $msg_public_create7,
    $msg_public_create,
    $message,
    $msadminlangpublic[5]
  ));
  $tpl->assign('FLAG', $flag);

  // Global vars..
  include(PATH . 'control/lib/global.php');

  // Load template..
  $tpl->display('content/' . MS_TEMPLATE_SET . '/account-verification-message.tpl.php');
  include(PATH . 'control/footer.php');
  exit;
}

$title = $msg_public_create;

// Is this option enabled?
if ($SETTINGS->createAcc == 'no') {
  $HEADERS->err403();
}

include(PATH . 'control/header.php');

// Show..
$tpl = new Savant3();
$tpl->assign('TXT', array(
  $msg_public_create,
  $msg_public_create2,
  $msg_main3,
  $msg_public_create3,
  $msg_public_create,
  $msg_public_create4,
  $msg_public_ticket9,
  $msg_public_create5,
  $msg_public_create6
));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/account-create.tpl.php');

// Load js triggers..
if (file_exists(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/auto-focus.htm')) {
  $jsHTML = str_replace(array(
    '{box}'
  ), array(
    'name'
  ), mswTmp(PATH . 'content/' . MS_TEMPLATE_SET . '/html/js/auto-focus.htm', 'ok'));
}

include(PATH . 'control/footer.php');

?>