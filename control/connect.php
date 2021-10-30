<?php

/*==============================================================================================
  DATABASE CONNECTION PARAMETERS
  ==============================

  Enter your database connection parameters. If you are not sure of this, please contact
  your web host. If you get a message along the lines of 'Access denied for user..', then
  your connection information is not correct.

  Important: The prefix is for people with only a single database. If you aren`t bothered
  about the prefix, do NOT comment it out. Leave it blank if no prefix is required.

================================================================================================*/

define('DB_HOST', 'Host name goes here..');
define('DB_USER', 'Database user goes here..');
define('DB_PASS', 'Database password goes here..');
define('DB_NAME', 'Database name goes here..');
define('DB_PREFIX', 'msp_');
define('DB_CHAR_SET', 'utf8'); // Character encoding set

/*============================================================================================================
  MYSQL LOCALE
  ============

  Specify the locale for your database. Where date_format is used to convert dates, this will convert the
  date to your own locale. If you aren`t sure, leave as is.

  For more info visit:
  http://dev.mysql.com/doc/refman/5.0/en/locale-support.html (MySQL5)
  http://dev.mysql.com/doc/refman/4.1/en/locale-support.html (MySQL4)

  Examples:
  define('DB_LOCALE', 'en_GB'); = English/United Kingdom
  define('DB_LOCALE', 'en_US'); = English/United States
  define('DB_LOCALE', 'en_AU'); = English/Australia
  define('DB_LOCALE', 'fi_FI'); = Finnish/Finland

============================================================================================================*/

define('DB_LOCALE', 'en_GB');

/*============================================================================================================
  MYSQL TIMEZONE
  ==============

  For more info visit:
  https://dev.mysql.com/doc/refman/5.5/en/time-zone-support.html

  This should not be changed unless you have issues where MySQL date filtering isn`t returning
  the expected results. This offset should correspond to the timezone set in your settings.

  This can be totally deactivated by entering a blank value.

============================================================================================================*/

define('DB_TIMEZONE', '+00:00');

/*============================================================================================================
  SECRET KEY / SALT
  ==================

  Specify your own unique secret key. This is for security and helps to add an additional layer of
  security to cookies, session cookies and login passwords. The longer and more complex the better.

  This should ideally be a mix of random numbers, letters and special characters.

  Optionally you can use the MSWorld secret key service to generate a random key:
  https://www.maianscriptworld.co.uk/services/keys/

  [!] You can change this at any point in time to invalidate all existing login credentials (if applicable).

============================================================================================================*/

define('SECRET_KEY', 'secret-key-name123');

/*==============================================================================================
  COOKIE DATA - FOR ADMIN LOGIN
  =============================

  Name    = Set cookie name for security. See notes above regarding secret key.
            Leave blank to disable the 'Remember Me' option on the admin login page.
  Expiry  = Days before cookie expires
  SSL     = If set to 1, sets cookie ONLY when SSL is detected

================================================================================================*/

define('COOKIE_NAME', 'ms-cookie123');
define('COOKIE_EXPIRY_DAYS', 30);
define('COOKIE_SSL', 0);

/*=========================================================================================================
  SHOW MYSQL ERRORS
  =================

  By default MySQL errors are shown onscreen. This can be a security issue as it reveals server paths
  and sensitive data to visitors. If you are sure the system is working fine, this value should be set
  to 0 to disable mysql errors. If set to 0, specify friendly message in MYSQL_DEFAULT_ERROR.

  Note: HTML mey be used in the default error, but apostrophes MUST be escaped with a backslash. ie: \'

===========================================================================================================*/

define('ENABLE_MYSQL_ERRORS', 0);
define('MYSQL_DEFAULT_ERROR', 'Database Error - Check Back Later (If you are the site developer, enable "ENABLE_MYSQL_ERRORS" in the "control/connect.php" file)');

?>
