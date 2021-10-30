<?php

/* UPGRADE
------------------------------------------------*/

header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
header('Content-type: text/html; charset=utf-8');

@ini_set('session.cookie_httponly', 1);

if (function_exists('date_default_timezone_get') && @date_default_timezone_get()) {
  date_default_timezone_set(@date_default_timezone_get());
}

if (!function_exists('mysqli_connect')) {
  die('!!! <b>The mysqli functions are not enabled on your server. Your must enable these functions before you can continue.</b><br><br>
  <a href="https://php.net/manual/en/book.mysqli.php">https://php.net/manual/en/book.mysqli.php</a>');
}

define('PARENT', 1);
define('PATH', dirname(__file__) . '/');
define('BASE_PATH', substr(PATH, 0, strpos(PATH, 'install') - 1) . '/');
define('INS_ROUTINE', 1);

include(PATH . 'control/config.php');
include(BASE_PATH . 'control/options.php');
include(BASE_PATH . 'control/functions.php');
include(BASE_PATH . 'control/timezones.php');
include(BASE_PATH . 'control/connect.php');
include(BASE_PATH . 'control/lib/db.php');
mswSQL_connect();
include(PATH . 'control/functions.php');
include(BASE_PATH . 'control/system/constants.php');
include(BASE_PATH . 'control/classes/system/class.json.php');

//---------------------------------------------------
// Error reporting
//---------------------------------------------------

include(BASE_PATH . 'control/classes/system/class.errors.php');
if (ERR_HANDLER_ENABLED) {
  register_shutdown_function('msFatalErr');
  set_error_handler('msErrorhandler');
}

include(PATH . 'control/arrays.php');

$JSON     = new jsonHandler();
$SETTINGS = @mswSQL_fetchobj(mswSQL_query("SELECT * FROM `" . DB_PREFIX . "settings` LIMIT 1", __file__, __line__));
$cmd      = (isset($_GET['s']) ? $_GET['s'] : '1');
$title    = SCRIPT_NAME . ': Upgrade';
$count    = 0;
$defChar  = 'utf8mb4_general_ci';
$sqlVer   = mswSQL_version();

// Legacy version..
if (!isset($SETTINGS->id)) {
  die('<div style="font:15px arial;background:#ff9999;color:#fff;padding:20px;border:2px dashed #555">[ FATAL ERROR ] Invalid database connection, please check your connection parameters.</div>');
} else {
  if (!isset($SETTINGS->encoderVersion)) {
    die('<div style="font:15px arial;background:#ff9999;color:#fff;padding:20px;border:2px dashed #555">
    Your version of ' . SCRIPT_NAME . ' appears to be older than v2.0, so an upgrade is not possible.<br><br>
    Please install a fresh copy of the latest version of ' . SCRIPT_NAME . '.<br><br>
    <a href="https://www.' . SCRIPT_URL . '/download.html">https://www.' . SCRIPT_URL . '/download.html</a><br><br>
    Thank you and sorry for any inconvenience.</div>');
  }
}

// v2.0..
if (!isset($SETTINGS->softwareVersion)) {
  $SETTINGS->softwareVersion = '2.0';
}

// Set limits
if (MS_SET_MEM_ALLOCATION_LIMIT) {
  @ini_set('memory_limit', MS_SET_MEM_ALLOCATION_LIMIT);
}
@set_time_limit(MS_SET_TIME_OUT_LIMIT);

if (isset($_GET['ajax-ops'])) {
  include(PATH . 'control/_ajax.php');
  exit;
}

include(PATH . 'content/header.php');
include(PATH . 'content/upgrade.php');
include(PATH . 'content/footer.php');

?>