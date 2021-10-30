<?php

/* Init File
----------------------------------------------------------*/

if (!defined('PARENT')) {
  die('Permission denied');
}

//---------------------------------
// Additional options..
//---------------------------------

include(BASE_PATH . 'control/system/constants.php');
include(BASE_PATH . 'control/' . (file_exists(BASE_PATH . 'control/user-options.php') ? 'user-' : '') . 'options.php');

//-------------------------
// Database connection..
//-------------------------

include(BASE_PATH . 'control/functions.php');
include(BASE_PATH . 'control/connect.php');
include(BASE_PATH . 'control/lib/db.php');
mswSQL_connect();

//------------------------
// Load files..
//------------------------

include(PATH . 'control/functions.php');
mswfileController();
include(BASE_PATH . 'control/classes/system/class.session.php');
include(BASE_PATH . 'control/system/core/sys-controller.php');
include(BASE_PATH . 'control/timezones.php');
include(BASE_PATH . 'control/classes/system/class.mobile-detection.php');
include(BASE_PATH . 'control/classes/system/class.datetime.php');
include(BASE_PATH . 'control/classes/class.system.php');
include(BASE_PATH . 'control/classes/system/class.parser.php');
include(BASE_PATH . 'control/classes/mailer/class.send.php');
include(BASE_PATH . 'control/classes/system/class.page.php');
include(PATH . 'control/classes/class.users.php');
include(PATH . 'control/classes/class.settings.php');
include(PATH . 'control/classes/class.tickets.php');
include(PATH . 'control/classes/class.fieldmanager.php');
include(BASE_PATH . 'control/classes/class.bbcode.php');
include(BASE_PATH . 'control/classes/system/class.bootstrap.php');
include(BASE_PATH . 'control/classes/system/class.json.php');
include(BASE_PATH . 'control/classes/system/class.headers.php');
include(BASE_PATH . 'control/classes/class.social.php');

//-----------------------
// Fetch settings..
//-----------------------

$SETTINGS = mswSQL_fetchobj(mswSQL_query("SELECT * FROM `" . DB_PREFIX . "settings` LIMIT 1", __file__, __line__));
$SSN = new sessHandlr();

//-------------------------------------------------------
// Check settings. If nothing, direct to installer..
//-------------------------------------------------------

if (!isset($SETTINGS->id)) {
  header("Location: ../install/index.php");
  exit;
}

//-----------------------
// Secure Tokens
//-----------------------

if ($SSN->active('csrf_token') == 'no') {
  $SSN->set(array('csrf_token' => $SSN->token()));
}

//-----------------------
// Manual schema fix
//-----------------------

mswManSchemaFix($SETTINGS);

//-------------------------------------
// Get support team information..
//-------------------------------------

$cmd = (isset($_GET['p']) ? strip_tags($_GET['p']) : 'home');
if ($cmd != 'reset') {
  if (($SSN->active('_ms_mail') == 'yes' && $SSN->active('_ms_key') == 'yes' && mswIsValidEmail($SSN->get('_ms_mail'))) || ($SSN->active_c('_msc_mail') == 'yes' && $SSN->active_c('_msc_key') == 'yes' && mswIsValidEmail($SSN->get_c('_msc_mail')))) {
    $qStaff = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "users`
              WHERE `email`  = '" . ($SSN->active('_ms_mail') == 'yes' ? $SSN->get('_ms_mail') : $SSN->get_c('_msc_mail')) . "'
              AND `accpass`  = '" . ($SSN->active('_ms_key') == 'yes' ? $SSN->get('_ms_key') : $SSN->get_c('_msc_key')) . "'
              AND `enabled`  = 'yes'
              LIMIT 1
              ", __file__, __line__);
    $MSTEAM = mswSQL_fetchobj($qStaff);
    if (!isset($MSTEAM->name) && !in_array($cmd, array(
      'login',
      'logout'
    ))) {
      $SSN->delete(array('_ms_mail', '_ms_key'));
      header("Location: index.php?p=logout");
      exit;
    }
    // Set support team timezone..
    define('MSTZ_SET', (isset($MSTEAM->timezone) && in_array($MSTEAM->timezone, array_keys($timezones)) ? $MSTEAM->timezone : $SETTINGS->timezone));
    define('MSLNG_SET', (isset($MSTEAM->language) && $MSTEAM->language && is_dir(BASE_PATH . 'content/language/' . $MSTEAM->language) ? $MSTEAM->language : $SETTINGS->language));
    date_default_timezone_set(MSTZ_SET);
  }
}

//----------------------------
// Load language files..
//----------------------------

include_once(BASE_PATH . 'content/language/' . (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language) . '/lang1.php');
include_once(BASE_PATH . 'content/language/' . (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language) . '/lang2.php');
include_once(BASE_PATH . 'content/language/' . (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language) . '/lang3.php');
include_once(BASE_PATH . 'content/language/' . (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language) . '/lang4.php');
include_once(BASE_PATH . 'content/language/' . (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language) . '/lang5.php');
include_once(BASE_PATH . 'content/language/' . (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language) . '/lang6.php');
include_once(BASE_PATH . 'content/language/' . (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language) . '/lang7.php');
include_once(BASE_PATH . 'content/language/' . (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language) . '/lang8.php');
include(PATH . 'control/arrays.php');

//---------------------------------
// Mail base path for templates..
//---------------------------------

define('LANG_PATH', BASE_PATH . 'content/language/' . (defined('MSLNG_SET') ? MSLNG_SET : $SETTINGS->language) . '/mail-templates/');
define('LANG_BASE_PATH', BASE_PATH . 'content/language/');

//---------------------
// Default vars..
//---------------------

$page       = (isset($_GET['next']) && $_GET['next'] > 0 ? (int) $_GET['next'] : '1');
$count      = 0;
$limit      = (isset($_GET['limit']) ? ($_GET['limit'] == 'all' ? 'all' : (int) $_GET['limit']) : DEFAULT_DATA_PER_PAGE);
$limitvalue = ($limit == 'all' ? 'all' : $page * $limit - ($limit));
$sqlLimStr  = ($limit != 'all' ? 'LIMIT ' . $limitvalue . ', ' . $limit : '');
$eString    = array();
$title      = '';
$tabIndex   = 0;
$attString  = array();
$attPath    = array();

//------------------------
// Pass reset check
//------------------------

if ($cmd != 'reset' && !isset($_GET['ajax']) && defined('PASS_RESET')) {
  die('<p style="color:#fff;background:#ff9999;padding:20px;border:2px solid #555">[ SYSTEM WARNING ]<br><br>PASS_RESET is defined. Possibly from password reset. This <b>MUST</b> be commented out or removed as it is a security risk.
  <br><br><a href="../docs/reset.html" style="color:#fff">Click Here for More Information</a>.<br><br>Once you have removed this, reload page.</p>');
}

//------------------------
// Timezone override
//------------------------

if (!defined('MSTZ_SET')) {
  define('MSTZ_SET', $SETTINGS->timezone);
  date_default_timezone_set(MSTZ_SET);
}

//------------------------
// Access pages
//------------------------

if (isset($MSTEAM->name) && $cmd != 'reset') {
  $userAccess = mswUserPageAccess($MSTEAM);
  include(PATH . 'control/system/team/team-perms.php');
}

//-------------------
// Load classes..
//-------------------

$MSPARSER            = new msDataParser();
$PDTC                = new Mobile_Detect();
$MSYS                = new msSystem();
$MSDT                = new msDateTime();
$MSBB                = new bbCode_Parser();
$MSMAIL              = new msMail();
$MSTICKET            = new supportTickets();
$MSUSERS             = new systemUsers();
$MSSET               = new systemSettings();
$MSFM                = new fieldManager();
$JSON                = new jsonHandler();
$MSBOOTSTRAP         = new msBootStrap();
$HEADERS             = new htmlHeaders();
$SOCIAL              = new social();
$MSSET->datetime     = $MSDT;
$MSSET->settings     = $SETTINGS;
$MSUSERS->settings   = $SETTINGS;
$MSUSERS->dt         = $MSDT;
$MSUSERS->ssn        = $SSN;
$MSTICKET->settings  = $SETTINGS;
$MSTICKET->dt        = $MSDT;
$MSPARSER->bbCode    = $MSBB;
$MSPARSER->settings  = $SETTINGS;
$MSDT->settings      = $SETTINGS;
$MSTICKET->team      = (isset($MSTEAM->id) ? $MSTEAM : '');
$SOCIAL->json        = $JSON;
$SOCIAL->settings    = $SETTINGS;

//-------------------
// Var overides.
//-------------------

$cmd = mswCallBackUrls($cmd);

// Does installer still exist..
if (!defined('LIC_DEV') && is_dir(BASE_PATH . 'install')) {
  die('Install directory exists on server. Please rename "install" directory or remove it for security, then refresh page.');
}

//---------------------------------------------------
// Set ticket id if coming from link in email..
//---------------------------------------------------

if (isset($_GET['ticket']) && REDIRECT_TO_TICKET_ON_LOGIN) {
  if (isset($MSTEAM->name)) {
    $SUPTICK = mswSQL_table('tickets', 'id', mswReverseTicketNumber($_GET['ticket']));
    if (isset($SUPTICK->id)) {
      header("Location: index.php?p=view-" . ($SUPTICK->isDisputed == 'yes' ? 'dispute' : 'ticket') . "&id=" . $SUPTICK->id);
      exit;
    }
  }
  $SSN->set(array('thisTicket' => $_GET['ticket']));
}

/* PLATFORM DETECTION
---------------------------------------*/

define('MSW_PFDTCT', ($PDTC->isMobile() ? ($PDTC->isTablet() ? 'tablet' : 'mobile') : 'pc'));

?>