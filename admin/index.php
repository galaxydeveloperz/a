<?php

/* SOFTWARE LOADER
   DO NOT change this file
----------------------------------*/

header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
header('Content-type: text/html; charset=utf-8');

@ini_set('session.cookie_httponly', 1);
@session_start();

// PREVENT DATE ERRORS
date_default_timezone_set('UTC');

define('ADMIN_PANEL', 1);
define('PARENT', 1);

// SET PATHS
define('PATH', dirname(__file__) . '/');
define('ADM_FLDR', basename(PATH));
define('BASE_PATH', substr(PATH, 0, -strlen(ADM_FLDR)-2) . '/');

// ERROR HANDLER
include(BASE_PATH . 'control/classes/system/class.errors.php');
if (ERR_HANDLER_ENABLED) {
  register_shutdown_function('msFatalErr');
  set_error_handler('msErrorhandler');
}

// INITIALISE..
include(PATH . 'control/system/init.php');
include(PATH . 'control/index-parser.php');

?>