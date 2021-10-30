<?php

/* System - Initialisation
----------------------------------------------------------*/

if (!defined('PARENT')) {
  $HEADERS->err403();
}

//---------------------------------
// Additional options..
//---------------------------------

include(PATH . 'control/system/constants.php');
include(PATH . 'control/' . (file_exists(PATH . 'control/user-options.php') ? 'user-' : '') . 'options.php');

//-------------------------
// Database connection..
//-------------------------

include(PATH . 'control/functions.php');
include(PATH . 'control/connect.php');
include(PATH . 'control/lib/db.php');
mswSQL_connect();

//----------------------------
// Savant template engine..
//----------------------------

include(PATH . 'control/lib/Savant3.php');

//------------------------
// Other include files..
//------------------------

mswfileController();
include(PATH . 'control/system/core/sys-controller.php');
include(PATH . 'control/classes/system/class.parser.php');
include(PATH . 'control/classes/system/class.mobile-detection.php');
include(PATH . 'control/classes/mailer/class.send.php');
include(PATH . 'control/classes/system/class.datetime.php');
include(PATH . 'control/classes/class.system.php');
include(PATH . 'control/classes/class.imap.php');
include(PATH . 'control/classes/class.tickets.php');
include(PATH . 'control/classes/class.accounts.php');
include(PATH . 'control/classes/class.fields.php');
include(PATH . 'control/classes/class.faq.php');
include(PATH . 'control/classes/class.bbcode.php');
include(PATH . 'control/classes/system/class.page.php');
include(PATH . 'control/classes/system/class.json.php');
include(PATH . 'control/classes/system/class.headers.php');
include(PATH . 'control/classes/class.social.php');
include(PATH . 'control/timezones.php');

//--------------------------
// Login credentials..
//--------------------------

define('MS_PERMISSIONS', (!defined('CRON_RUN') ? mswIsUserLoggedIn($SSN) : 'guest'));

//----------------------
// Load settings..
//----------------------

$SETTINGS = @mswSQL_fetchobj(mswSQL_query("SELECT * FROM `" . DB_PREFIX . "settings` LIMIT 1", __file__, __line__));
if (!isset($SETTINGS->id)) {
  header("Location: install/index.php");
  exit;
} else {
  $mswLangSetLoader = ($SETTINGS->langSets ? unserialize($SETTINGS->langSets) : array());
}

//-----------------------
// Manual schema fix
//-----------------------

mswManSchemaFix($SETTINGS);

//--------------------------
// For search..
//--------------------------

if (isset($_GET['keys'])) {
  $_GET['p'] = 'search';
}

//----------------------
// Default vars..
//----------------------

$cmd               = (isset($_GET['p']) ? $_GET['p'] : 'home');
$page              = (isset($_GET['next']) && $_GET['next'] > 0 ? (int) $_GET['next'] : '1');
$title             = '';
$eString           = array();
$eFields           = array();
$ticketAttachments = array();
$attachString      = '';
$ticketSystemMsg   = '';
$limit             = (isset($_GET['limit']) ? (int) $_GET['limit'] : 25);
$limitvalue        = $page * $limit - ($limit);
$ms_js_css_loader  = array();
$mswUploadDropzone = array();

//------------------------
// Initiate classes..
//------------------------

$MSPARSER           = new msDataParser();
$PDTC               = new Mobile_Detect();
$MSDT               = new msDateTime();
$MSYS               = new msSystem();
$MSTICKET           = new tickets();
$MSBB               = new bbCode_Parser();
$MSFIELDS           = new customFieldManager();
$MSMAIL             = new msMail();
$FAQ                = new msFAQ();
$MSACC              = new accountSystem();
$MSJSON             = new jsonHandler();
$HEADERS            = new htmlHeaders();
$SOCIAL             = new social();
$MSPARSER->bbCode   = $MSBB;
$MSPARSER->settings = $SETTINGS;
$MSDT->settings     = $SETTINGS;
$MSTICKET->parser   = $MSPARSER;
$MSTICKET->settings = $SETTINGS;
$MSTICKET->datetime = $MSDT;
$MSTICKET->fields   = $MSFIELDS;
$MSTICKET->system   = $MSYS;
$MSYS->settings     = $SETTINGS;
$MSYS->datetime     = $MSDT;
$MSFIELDS->parser   = $MSPARSER;
$MSFIELDS->dt       = $MSDT;
$MSACC->settings    = $SETTINGS;
$MSACC->ssn         = $SSN;
$FAQ->settings      = $SETTINGS;
$FAQ->dt            = $MSDT;
$FAQ->ssn           = $SSN;
$SOCIAL->json       = $MSJSON;
$SOCIAL->settings   = $SETTINGS;

//---------------------------------
// Loaders
//---------------------------------

if ($SETTINGS->language == '' || !is_dir(PATH . 'content/language/' . $SETTINGS->language)) {
  if (is_dir(PATH . 'content/language/english')) {
    $SETTINGS->language = 'english';
    mswSQL_query("UPDATE `" . DB_PREFIX . "settings` SET `language` = 'english'", __file__, __line__);
  } else {
    die('Error: Language folder <b>' . PATH . 'content/language/' . $SETTINGS->language . '</b> does NOT exist');
  }
}

if (MS_PERMISSIONS != 'guest') {
  $LI_ACC = $MSACC->ms_user();
  define('LANG_PATH', PATH . 'content/language/' . ($LI_ACC->language && is_dir(PATH . 'content/language/' . $LI_ACC->language) ? $LI_ACC->language : $SETTINGS->language) . '/');
  define('MSTZ_SET', (in_array($LI_ACC->timezone, array_keys($timezones)) ? $LI_ACC->timezone : $SETTINGS->timezone));
  date_default_timezone_set(MSTZ_SET);
  define('MS_TEMPLATE_SET', (isset($mswLangSetLoader[$LI_ACC->language]) && is_dir(PATH . 'content/' . $mswLangSetLoader[$LI_ACC->language]) ? $mswLangSetLoader[$LI_ACC->language] : '_default_set'));
  define('MSW_LOGGED_IN', 'yes');
  // Force password update for password reset..
  if (!isset($_GET['lo']) && !isset($_GET['ajax'])) {
    if ($LI_ACC->system2 == 'forcepasschange' && $cmd != 'profile') {
      header("Location: index.php?p=profile");
      exit;
    }
  }
} else {
  define('LANG_PATH', PATH . 'content/language/' . $SETTINGS->language . '/');
  define('MSTZ_SET', $SETTINGS->timezone);
  date_default_timezone_set($SETTINGS->timezone);
  define('MS_TEMPLATE_SET', (isset($mswLangSetLoader[$SETTINGS->language]) && is_dir(PATH . 'content/' . $mswLangSetLoader[$SETTINGS->language]) ? $mswLangSetLoader[$SETTINGS->language] : '_default_set'));
  define('MSW_LOGGED_IN', 'no');
}

//-------------------------
// Load language files..
//-------------------------

include_once(LANG_PATH . 'lang1.php');
include_once(LANG_PATH . 'lang2.php');
include_once(LANG_PATH . 'lang3.php');
include_once(LANG_PATH . 'lang4.php');
include_once(LANG_PATH . 'lang5.php');
include_once(LANG_PATH . 'lang6.php');
include_once(LANG_PATH . 'lang7.php');
include_once(LANG_PATH . 'lang8.php');

//----------------------------
// Status and levels
//----------------------------

include(PATH . 'control/system/loader.php');

//----------------------------
// Callback parameters..
//----------------------------

$cmd = $MSYS->callback($cmd);
if ($SETTINGS->tawk_home == 'yes' && $cmd != 'home') {
  define('KILL_TAWK', 1);
}

//-------------------------------------------------
// Tickets by Email - Incoming from cronjob/tab
//-------------------------------------------------

if (isset($argv[1])) {
  parse_str($argv[1], $cronp);
  $cronid = ($argv[1] ? $argv[1] : (isset($cronp['pipe']) ? $cronp['pipe'] : '0'));
  if (strpos($cronid, '=') !== false) {
    $chopcron = explode('=', $cronid);
    $cronid = (isset($chopcron[1]) ? trim($chopcron[1]) : '');
  }
  define('IMAP_CRON_ID', (int) $cronid);
  if (isset($argv[2])) {
    parse_str($argv[2], $cronp2);
    $cronlg = (isset($lang[0]) ? $lang[0] : (isset($cronp2[1]) ? $cronp2[1] : ''));
    if ($cronlg) {
      if (is_dir(PATH . 'content/language/' . $cronlg)) {
        define('IMAP_CRON_LANG', $cronlg);
      } else {
        echo str_replace('{file}', PATH . 'content/language/' . $cronlg, $imap_cron_output_err2);
        exit;
      }
    }
  }
  if (IMAP_CRON_ID > 0) {
    define('CRON_RUN', 1);
    $cmd = $SETTINGS->imap_param;
  } else {
    echo $imap_cron_output_err;
    exit;
  }
}

//-----------------------------------------
// Is system disabled or account disabled
//-----------------------------------------

if ($SETTINGS->sysstatus == 'no') {
  include(PATH . 'control/system/disabled.php');
  exit;
} else {
  if (isset($LI_ACC->enabled) && $LI_ACC->enabled == 'no' && !isset($_GET['lo'])) {
    include(PATH . 'control/system/accounts/account-suspended.php');
    exit;
  }
}

//---------------------------------
// Check licence for email digest
//---------------------------------

if (isset($_SERVER['PHP_SELF']) && !defined('LIC_DEV') && basename($_SERVER['PHP_SELF']) == 'email-digest.php') {
  if (LICENCE_VER == 'locked') {
    die('Fatal Error: Email Digest Only available with a commercial licence. <a href="https://www.' . SCRIPT_URL . '/purchase.html">Purchase Licence</a>');
  }
}

/* PLATFORM DETECTION
---------------------------------------*/

define('MSW_PFDTCT', ($PDTC->isMobile() ? ($PDTC->isTablet() ? 'tablet' : 'mobile') : 'pc'));

?>