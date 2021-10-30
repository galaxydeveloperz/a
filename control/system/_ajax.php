<?php

/* System - Ajax Ops
----------------------------------------------------------*/

// Check var and parent load..
if (!defined('PARENT') || !defined('MS_PERMISSIONS') || !isset($_GET['ajax'])) {
  exit;
}

define('AJAX_HANDLER', 1);

// Define array for json response..
$json = array();

include(PATH . 'control/classes/class.upload.php');
$MSUPL  = new msUpload();

// Load mail params
include(PATH . 'control/classes/mailer/mail-init.php');

// Handle request..
switch ($_GET['ajax']) {

  //========================
  // Ticket Reply
  //========================

  case 'tickreply':
    include(PATH . 'control/system/accounts/account-ticket-reply.php');
    break;

  //========================
  // Create Ticket
  //========================

  case 'create-ticket':
    include(PATH . 'control/system/accounts/account-ticket-create.php');
    break;

  //========================
  // Create account..
  //========================

  case 'create':
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
      'enabled' => (isset($aspam_api['ctalk']['enableaccs']) ? 'yes' : 'no'),
      'log' => (isset($aspam_api['ctalk']['log']) ? 'yes' : 'no'),
      'name' => 'cleanTalk'
    );
    // Is spam system enabled?
    if ($spamParams['key'] && $spamParams['enabled'] == 'yes') {
      define('MSW_ANTI_SPAM_ENABLED', 1);
    }
    if ($SETTINGS->createAcc == 'yes' && isset($_POST['name']) && isset($_POST['email']) && isset($_POST['email2'])) {
      if ($_POST['name'] == '') {
        $eFields[] = $msadminlangpublic[3];
      }
      if (!mswIsValidEmail($_POST['email'])) {
        $eFields[] = $msg_public_create5;
      } else {
        if (strtolower($_POST['email']) != strtolower($_POST['email2'])) {
          $eFields[] = $msadminlangpublic[2];
        } else {
          if (mswSQL_rows('portal WHERE LOWER(`email`) = \'' . mswSQL(strtolower($_POST['email'])) . '\'') > 0) {
            $eFields[] = $msg_public_create6;
          }
        }
      }
      // Show errors..
      if (!empty($eFields)) {
        $json = array(
          'status' => 'err',
          'field' => implode(',', $eFields),
          'msg' => implode('<br>', $eFields)
        );
      } else {
        // Spam checks..
        if (defined('MSW_ANTI_SPAM_ENABLED')) {
          $ctkc = $CTALK->check(array(
            'email' => (isset($_POST['email']) ? $_POST['email'] : ''),
            'name' => (isset($_POST['name']) ? $_POST['name'] : ''),
            'ct_ts' => (isset($_POST['js_ts']) ? $_POST['js_ts']: '')
          ));
          if (!isset($ctkc['allow']) || $ctkc['allow'] == 0) {
            $eFields[] = $msadminlang_public_3_7[1];
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
        // Create account..
        $pass   = $MSACC->ms_generate();
        $code   = substr(md5(uniqid(rand(), 1)), 3, 23);
        $userID = $MSACC->add(array(
          'name' => $_POST['name'],
          'email' => $_POST['email'],
          'pass' => $pass,
          'enabled' => 'no',
          'verified' => 'no',
          'timezone' => '',
          'ip' => mswSQL(mswIP()),
          'notes' => '',
          'language' => $SETTINGS->language,
          'system1' => $code
        ));
        // Send verification email..
        if ($userID > 0) {
          $MSMAIL->addTag('{NAME}', $_POST['name']);
          $MSMAIL->addTag('{LOGIN_URL}', $SETTINGS->scriptpath);
          $MSMAIL->addTag('{CODE}', $code);
          $MSMAIL->sendMSMail(array(
            'from_email' => $SETTINGS->email,
            'from_name' => $SETTINGS->website,
            'to_email' => $_POST['email'],
            'to_name' => $_POST['name'],
            'subject' => str_replace(array(
              '{website}'
            ), array(
              $SETTINGS->website
            ), $emailSubjects['acc-verify']),
            'replyto' => array(
              'name' => $SETTINGS->website,
              'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
            ),
            'template' => PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/account-verification.txt',
            'language' => $SETTINGS->language
          ));
          $MSMAIL->smtpClose();
        }
        $json = array(
          'status' => 'ok-dialog',
          'field' => 'msg',
          'msg' => str_replace('{email}', $_POST['email'], $msg_script_action8)
        );
        if ($SSN->active('ggrcver') == 'yes') {
          $SSN->delete(array('ggrcver'));
        }
      }
    }
    break;

  //========================
  // Update profile
  //========================

  case 'profile':
    if (!isset($LI_ACC->id)) {
      exit;
    }
    if (isset($_POST['email']) && $_POST['email']) {
      // Is email same as current = error..
      if (strtolower($_POST['email']) == $LI_ACC->email) {
        $json = array(
          'status' => 'err',
          'field' => 'email',
          'tab' => 'two',
          'msg' => $msg_portal31
        );
      } else {
        // Is email2 field blank = error..
        if ($_POST['email2'] == '') {
          $json = array(
            'status' => 'err',
            'field' => 'email2',
            'tab' => 'two',
            'msg' => $msg_portal30
          );
        } else {
          // Is new email valid = error..
          if (!mswIsValidEmail($_POST['email'])) {
            $json = array(
              'status' => 'err',
              'field' => 'email',
              'tab' => 'two',
              'msg' => $msg_portal30
            );
          } else {
            // Do mail fields match = error..
            if (strtolower($_POST['email']) != strtolower($_POST['email2'])) {
              $json = array(
                'status' => 'err',
                'field' => 'email',
                'tab' => 'two',
                'msg' => $msg_public_profile
              );
            } else {
              // Does new email exist somewhere else = error..
              if (mswSQL_rows('portal WHERE LOWER(`email`) = \'' . mswSQL(strtolower($_POST['email'])) . '\' AND `id` != \'' . $LI_ACC->id . '\'') > 0) {
                $json = array(
                  'status' => 'err',
                  'field' => 'email',
                  'tab' => 'two',
                  'msg' => $msg_public_profile5
                );
              }
              $newEmailConfirmed = $_POST['email'];
            }
          }
        }
      }
    }
    // What about password..
    if ($LI_ACC->system2 == 'forcepasschange' && $_POST['curpass'] == '') {
      $json = array(
        'status' => 'err',
        'field' => 'curpass',
        'tab' => 'three',
        'msg' => $msadminlang_user_accs_3_7[0]
      );
    } else {
      if (isset($_POST['curpass']) && $_POST['curpass']) {
        if (!mswPassHash(array('type' => 'calc', 'val' => $_POST['curpass'], 'hash' => $LI_ACC->userPass))) {
          $json = array(
            'status' => 'err',
            'field' => 'curpass',
            'tab' => 'three',
            'msg' => $msg_public_profile10
          );
        } else {
          if ($_POST['newpass'] == '' || $_POST['newpass2'] == '') {
            $json = array(
              'status' => 'err',
              'field' => 'newpass',
              'tab' => 'three',
              'msg' => $msg_public_profile11
            );
          } else {
            if ($_POST['newpass'] == $_POST['curpass']) {
              $json = array(
                'status' => 'err',
                'field' => 'newpass',
                'tab' => 'three',
                'msg' => $msadminlang_user_accs_3_7[1]
              );
            } else {
              if ($_POST['newpass'] != $_POST['newpass2']) {
                $json = array(
                  'status' => 'err',
                  'field' => 'newpass',
                  'tab' => 'three',
                  'msg' => $msg_public_profile12
                );
              } else {
                if (strlen($_POST['newpass']) < $SETTINGS->minPassValue) {
                  $json = array(
                    'status' => 'err',
                    'field' => 'newpass',
                    'tab' => 'three',
                    'msg' => str_replace('{min}', $SETTINGS->minPassValue, $msg_public_profile13)
                  );
                } else {
                  $newPassConfirmed = mswPassHash(array('type' => 'add', 'pass' => $_POST['newpass']));
                }
              }
            }
          }
        }
      }
    }
    // If ok, update..
    if (!isset($json['status'])) {
      // Update profile..
      $rows = $MSACC->ms_update(array(
        'id' => $LI_ACC->id,
        'name' => (isset($_POST['name']) && $_POST['name'] ? substr($_POST['name'], 0, 200) : $LI_ACC->name),
        'email' => (isset($newEmailConfirmed) ? $newEmailConfirmed : $LI_ACC->email),
        'pass' => (isset($newPassConfirmed) ? $newPassConfirmed : $LI_ACC->userPass),
        'timezone' => (isset($_POST['timezone']) && $_POST['timezone'] != '0' ? $_POST['timezone'] : $LI_ACC->timezone),
        'language' => (isset($_POST['language']) ? $_POST['language'] : $LI_ACC->language)
      ));
      // Send email notification if something got updated..
      if ($rows > 0 && $SETTINGS->accProfNotify == 'yes') {
        // Send mail..
        $MSMAIL->addTag('{NAME}', $LI_ACC->name);
        // Check template..
        if ($LI_ACC->language && file_exists(PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/profile-updated.txt')) {
          $mailT = PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/profile-updated.txt';
          $pLang = $LI_ACC->language;
        } else {
          $mailT = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/profile-updated.txt';
        }
        $MSMAIL->sendMSMail(array(
          'from_email' => $SETTINGS->email,
          'from_name' => $SETTINGS->website,
          'to_email' => $LI_ACC->email,
          'to_name' => $LI_ACC->name,
          'subject' => str_replace(array(
            '{website}'
          ), array(
            $SETTINGS->website
          ), $emailSubjects['profile-update']),
          'replyto' => array(
            'name' => $SETTINGS->website,
            'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
          ),
          'template' => $mailT,
          'language' => (isset($pLang) ? $pLang : $SETTINGS->language)
        ));
        $MSMAIL->smtpClose();
      }
      // We are done..
      $json = array(
        'status' => 'ok',
        'field' => 'msg',
        'msg' => $msg_public_profile2
      );
    }
    break;

  //========================
  // New pass request
  //========================

  case 'newpass':
    if (isset($_POST['email']) && $_POST['email']) {
      if (!mswIsValidEmail($_POST['email'])) {
        $json = array(
          'status' => 'err',
          'field' => 'email',
          'msg' => $msg_script_action6
        );
      } else {
        $ACC = mswSQL_table('portal', 'email', mswSQL(strtolower($_POST['email'])), 'AND `verified` = \'yes\'');
        if (!isset($ACC->id)) {
          $json = array(
            'status' => 'err',
            'field' => 'email',
            'msg' => $msg_script_action7
          );
        } else {
          // Create new password...
          $newPass = $MSACC->ms_password($ACC->email);
          // Send mail..
          $MSMAIL->addTag('{PASSWORD}', $newPass);
          $MSMAIL->addTag('{NAME}', $ACC->name);
          $MSMAIL->addTag('{EMAIL}', $ACC->email);
          // Check template..
          if ($ACC->language && file_exists(PATH . 'content/language/' . $ACC->language . '/mail-templates/new-password.txt')) {
            $mailT = PATH . 'content/language/' . $ACC->language . '/mail-templates/new-password.txt';
            $pLang = $ACC->language;
          } else {
            $mailT = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-password.txt';
          }
          $MSMAIL->sendMSMail(array(
            'from_email' => $SETTINGS->email,
            'from_name' => $SETTINGS->website,
            'to_email' => $ACC->email,
            'to_name' => $ACC->name,
            'subject' => str_replace(array(
              '{website}'
            ), array(
              $SETTINGS->website
            ), $emailSubjects['new-password']),
            'replyto' => array(
              'name' => $SETTINGS->website,
              'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
            ),
            'template' => $mailT,
            'language' => (isset($pLang) ? $pLang : $SETTINGS->language)
          ));
          $MSMAIL->smtpClose();
          $json = array(
            'status' => 'ok-dialog',
            'field' => 'msg',
            'msg' => str_replace('{email}', $ACC->email, $mspubliclang4_2[5])
          );
        }
      }
    }
    break;

  //========================
  // Account login
  //========================

  case 'login':
    $redr = 'index.php?p=dashboard';
    // If login limit and ban time is enabled, check first..
    if ($SETTINGS->loginLimit > 0) {
      $ban = $MSACC->checkban($SETTINGS, $MSDT);
      if ($ban == 'fail') {
        $json = array(
          'status' => 'err',
          'field' => 'email',
          'msg' => $msg_public_login4
        );
      }
    }
    if (!isset($json['status']) && isset($_POST['email'], $_POST['pass']) && $_POST['email'] && $_POST['pass']) {
      // Check for valid email..
      if (!mswIsValidEmail($_POST['email'])) {
        $json = array(
          'status' => 'err',
          'field' => 'email',
          'msg' => $msg_main13
        );
      } else {
        // Now check account..
        $ACC = mswSQL_table('portal', 'email', mswSQL(strtolower($_POST['email'])), 'AND `verified` = \'yes\'');
        // Check access..
        if (isset($ACC->email) && mswPassHash(array('type' => 'calc', 'val' => $_POST['pass'], 'hash' => $ACC->userPass))) {
          if ($ACC->enabled == 'yes') {
            $SSN->set(array('_msw_support' => $ACC->email));
            // Ticket/dispute redirection..
            if ($SSN->active('ticketAccessID') == 'yes') {
              $redr = 'index.php?t=' . $SSN->get('ticketAccessID');
              $SSN->delete(array('ticketAccessID'));
            }
            if ($SSN->active('disputeAccessID') == 'yes') {
              $redr = 'index.php?d=' . $SSN->get('disputeAccessID');
              $SSN->delete(array('disputeAccessID'));
            }
            if ($SSN->active('redirectPage') == 'yes') {
              $redr = 'index.php?p=open';
              $SSN->delete(array('redirectPage'));
            }
            if ($ACC->system2 == 'forcepasschange') {
              $redr = 'index.php?p=profile';
            }
            // Add entry log..
            if ($ACC->enableLog == 'yes') {
              $MSACC->log($ACC->id);
            }
            // Clear any ban logs..
            $MSACC->clearban();
            // Update IP if blank (eg: admin added)
            if (mswIP() != $ACC->ip) {
              $MSACC->updateIP($ACC->id);
            } else {
              // Clear system flags..
              if (!in_array($ACC->system2, array('forcepasschange'))) {
                $MSACC->clearSystemFlags($ACC->id);
              }
            }
            $json = array(
              'status' => 'ok',
              'field' => 'redirect',
              'msg' => $redr
            );
          } else {
            $SSN->set(array('_msw_support' => $ACC->email));
            $json = array(
              'status' => 'ok',
              'field' => 'redirect',
              'msg' => 'index.php'
            );
          }
        } else {
          // Is max attempts and ban time enabled?
          if ($SETTINGS->loginLimit > 0) {
            $MSACC->ban();
          }
          $json = array(
            'status' => 'err',
            'field' => 'email',
            'msg' => $msg_main8
          );
        }
      }
    }
    break;

  //========================
  // Resend Confirmation
  //========================

  case 'resend':
    $json = array(
      'status' => 'err',
      'msg' => $msadminlangpublic[6]
    );
    if (isset($_POST['code']) && ctype_alnum($_POST['code'])) {
      $A = mswSQL_table('portal', 'system1', mswSQL($_POST['code']));
      if (isset($A->id) && $A->verified == 'yes') {
        $pass = $MSACC->ms_generate();
        $MSACC->ms_update(array(
          'id' => $A->id,
          'name' => $A->name,
          'email' => $A->email,
          'pass' => mswPassHash(array('type' => 'add', 'pass' => $pass)),
          'timezone' => $A->timezone,
          'language' => $A->language,
          'nologin' => 'yes'
        ));
        $langFile = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/account-verified.txt';
        $langSet = $SETTINGS->language;
        if ($A->language && file_exists(PATH . 'content/language/' . $A->language . '/mail-templates/account-verified.txt')) {
          $langSet = $A->language;
          $langFile = PATH . 'content/language/' . $A->language . '/mail-templates/account-verified.txt';
        }
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
          'template' => $langFile,
          'language' => $langSet
        ));
        $MSMAIL->smtpClose();
        // We are done..
        $json = array(
          'status' => 'ok-dialog',
          'field' => 'msg',
          'msg' => str_replace('{email}', $A->email, $msg_script_action8)
        );
      }
    }
    break;

  //========================
  // Voting..
  //========================

  case 'voting':
    $json = array(
      'status' => 'err',
      'msg' => $msadminlangpublic[6]
    );
    if ($SETTINGS->enableVotes == 'yes' && isset($_GET['id']) && isset($_GET['vote'])) {
      if ($SETTINGS->multiplevotes == 'no') {
        if ($SSN->active_c(COOKIE_NAME) == 'yes') {
          $votes = unserialize($SSN->get_c(COOKIE_NAME));
          if (in_array($_GET['id'], $votes)) {
            define('VOTING_STOP', 1);
            $json['msg'] = $msadminlang3_1faq[10];
          }
        }
      }
      if (!defined('VOTING_STOP')) {
        $ret = $FAQ->vote();
        if ($ret == 'ok') {
          $sofar          = $FAQ->stats($_GET['id']);
          $json['status'] = 'ok';
          $json['yes']    = $sofar[0];
          $json['no']     = $sofar[1];
          $json['total']  = $sofar[2];
        }
      }
    }
    break;

  //==============================
  // Attachment download..
  //==============================

  case 'dl':
  case 'token':
    $json = array(
      'status' => 'err',
      'msg' => $mspubliclang3_7[6]
    );
    switch ($_GET['ajax']) {
      case 'dl':
        if (isset($_GET['id'], $_GET['ad'])) {
          $A = mswSQL_table('faqattach', 'id', (int) $_GET['id'], ' AND `enAtt` = \'yes\'');
          if (isset($A->id)) {
            // Security checks..
            $Q = mswSQL_table('faq', 'id', (int) $_GET['ad'], ' AND `enFAQ` = \'yes\'');
            $restr_cats = $FAQ->catrestr((isset($LI_ACC->id) ? $LI_ACC->id : '0'));
            if (empty($restr_cats) || !in_array($Q->cat, $restr_cats)) {
              if ($A->remote) {
                $json['status'] = 'remote';
                $json['remote'] = $A->remote;
              } else {
                // Generate token..
                if ($A->path && @file_exists($SETTINGS->attachpathfaq . '/' . $A->path)) {
                  $token = $FAQ->token('create', $A->id);
                  if ($token) {
                    $json['status'] = 'token';
                    $json['token'] = $token;
                  }
                }
              }
            }
          }
        }
        break;
      case 'token':
        if (isset($_GET['cde'])) {
          $TK = mswSQL_table('faqdl', 'token', mswSQL($_GET['cde']));
          if (isset($TK->id)) {
            // Clear token..
            $FAQ->token('clear', $TK->id);
            $A = mswSQL_table('faqattach', 'id', $TK->question, ' AND `enAtt` = \'yes\'');
            if (isset($A->id)) {
              include(PATH . 'control/classes/system/class.download.php');
              $D = new msDownload();
              $m = $D->mime($SETTINGS->attachpathfaq . '/' . $A->path, $A->mimeType);
              $D->dl($SETTINGS->attachpathfaq . '/' . $A->path, $m, 'no');
              exit;
            }
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
    if (MS_PERMISSIONS != 'guest' && MSW_LOGGED_IN == 'yes' && isset($LI_ACC->id)) {
      switch ($_GET['ajax']) {
        case 'dla':
          if (isset($_GET['id'])) {
            $A = mswSQL_table('attachments', 'id', (int) $_GET['id'], '', '*,DATE(FROM_UNIXTIME(`ts`)) AS `addDate`');
            if (isset($A->id)) {
              $allow = 'no';
              // Is the ticket that this attachment relates to a ticket belonging to logged in user?
              // If not, does this person have access to the ticket because of a dispute?
              $T = mswSQL_table('tickets', 'id', $A->ticketID, 'AND `visitorID` = \'' . $LI_ACC->id . '\' AND `spamFlag` = \'no\'');
              if (isset($T->ts)) {
                $allow = 'yes';
              } else {
                $DS = mswSQL_table('disputes', 'ticketID', $A->ticketID, 'AND `visitorID` = \'' . $LI_ACC->id . '\'');
                if (isset($DS->ticketID)) {
                  $allow = 'yes';
                }
              }
              // If allowed, download..
              if ($allow == 'yes') {
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
          }
          break;
        case 'tokena':
          if (isset($_GET['cde'])) {
            $A = mswSQL_table('attachments', 'id', (int) $_GET['cde'], '', '*,DATE(FROM_UNIXTIME(`ts`)) AS `addDate`');
            if (isset($A->id)) {
              $allow = 'no';
              // Is the ticket that this attachment relates to a ticket belonging to logged in user?
              // If not, does this person have access to the ticket because of a dispute?
              $T = mswSQL_table('tickets', 'id', $A->ticketID, 'AND `visitorID` = \'' . $LI_ACC->id . '\' AND `spamFlag` = \'no\'');
              if (isset($T->ts)) {
                $allow = 'yes';
              } else {
                $DS = mswSQL_table('disputes', 'ticketID', $A->ticketID, 'AND `visitorID` = \'' . $LI_ACC->id . '\'');
                if (isset($DS->ticketID)) {
                  $allow = 'yes';
                }
              }
              // If allowed, download..
              if ($allow == 'yes') {
                $split = explode('-', $A->addDate);
                $base  = $SETTINGS->attachpath . '/';
                // Check for newer folder structure..
                // Earlier versions had no sub folders..
                if (@file_exists($SETTINGS->attachpath . '/' . $split[0] . '/' . $split[1] . '/' . $A->fileName)) {
                  $base = $SETTINGS->attachpath . '/' . $split[0] . '/' . $split[1] . '/';
                }
                include(PATH . 'control/classes/system/class.download.php');
                $D = new msDownload();
                $m = $D->mime($base . $A->fileName, $A->mimeType);
                $D->dl($base . $A->fileName, $m, 'no');
                exit;
              } else {
                $HEADERS->err403();
                exit;
              }
            }
          }
          break;
      }
    }
    break;

  //========================
  // Department loader..
  //========================

  case 'dept':
    $pre  = array(
      'sub' => '',
      'msg' => '',
      'prt' => $SETTINGS->defprty
    );
    $flds = '';
    if (isset($_GET['dp'])) {
      $dep = (int) $_GET['dp'];
      $acc = (MS_PERMISSIONS != 'guest' && MSW_LOGGED_IN == 'yes' && isset($LI_ACC->id) ? $LI_ACC->id : '0');
      if ($dep > 0) {
        $pre  = $MSTICKET->preFill($dep);
        $flds = $MSFIELDS->build('ticket', $dep, $acc);
      }
    }
    $json = array(
      'subject' => $pre['sub'],
      'comments' => $pre['msg'],
      'priority' => $pre['prt'],
      'fields' => $flds
    );
    break;

  //======================
  // Menu Panel Stats
  //======================

  case 'menu-panel':
    $SSN->set(array('vis_menu_panel' => preg_replace('/[^0-9a-zA-Z]/', '', $_GET['pnl'])));
    $json['status'] = 'ok';
    break;

  //======================
  // Close Account
  //======================

  case 'closeaccount':
    if (isset($LI_ACC->id) && $SETTINGS->visclose == 'yes') {
      // Clear data..
      $MSACC->close($LI_ACC->id);
      // Remove sessions..
      $SSN->delete(array('_msw_support', 'portalEmail'));
      // Done..
      $json = array(
        'status' => 'ok',
        'rdr' => $SETTINGS->scriptpath,
        'msg' => $mspubliclang4_2[4]
      );
    } else {
      $json = array(
        'status' => 'err',
        'msg' => $msadminlang3_1[3]
      );
    }
    break;
    
  //======================
  // Ticket Draft
  //======================
  
  case 'tickdraft-save':
    if (isset($_POST['id'],$_POST['draft'])) {
      $time = str_replace(
        array('{date}','{time}'), 
        array(
          $MSDT->mswDateTimeDisplay($MSDT->mswTimeStamp(), $SETTINGS->dateformat),
          $MSDT->mswDateTimeDisplay($MSDT->mswTimeStamp(), $SETTINGS->timeformat)
        ), $mssuptickets4_3[6]
      );
      if ($SSN->active('fe_draft_' . $_POST['id']) == 'yes') {
        $SSN->delete(array(
          'fe_draft_' . $_POST['id'],
          'fe_time_' . $_POST['id']
        ));
      }
      $SSN->set(array(
        'fe_draft_' . $_POST['id'] => $_POST['draft'],
        'fe_time_' . $_POST['id'] => $time
      ));
      $json = array(
        'msg' => 'saved',
        'text' => $time
      );
    }
    echo $MSJSON->encode($json);
    exit;
    break;
  case 'tickdraft-load':
    if (isset($_GET['id'])) {
      if ($SSN->active('fe_draft_' . $_GET['id']) == 'yes') {
        $draft = $SSN->get('fe_draft_' . $_GET['id']);
        $time = $SSN->get('fe_time_' . $_GET['id']);
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
    echo $MSJSON->encode($json);
    exit;
    break;

}

// For version 3.1+
$other = array(
  'sys' => $msadminlang3_1[2]
);

// We are done..
echo $MSJSON->encode(array_merge($json, $other));
exit;

?>