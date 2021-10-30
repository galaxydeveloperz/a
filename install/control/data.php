<?php

if (!defined('PARENT')) {
  exit;
}

$dataE = array();
$dtcount = 0;

// Path / zone checks..
$root = 'http://www.example.com/helpdesk';
$zone = (isset($_POST['timezone']) ? $_POST['timezone'] : 'Europe/London');
if (isset($_SERVER['HTTP_HOST'], $_SERVER['PHP_SELF'])) {
  $root = 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'install') - 1);
}
if (!isset($_POST['timezone'])) {
  if (function_exists('date_default_timezone_get')) {
    $zone = date_default_timezone_get();
  }
  if ($zone == '' & @ini_get('date.timezone')) {
    $zone = @ini_get('date.timezone');
  }
}

// Find / replace tags..
$f_r = array(
  '{prefix}' => DB_PREFIX,
  '{date}' => date('Y-m-d'),
  '{script}' => SCRIPT_NAME,
  '{version}' => SCRIPT_VERSION,
  '{ts}' => strtotime(date('Y-m-d H:i:s')),
  '{rss}' => date('r'),
  '{path}' => mswSQL($root),
  '{zone}' => mswSQL($zone),
  '{name}' => (isset($_POST['nm']) ? mswSQL($_POST['nm']) : 'My Helpdesk'),
  '{email}' => (isset($_POST['em']) && mswIsValidEmail($_POST['em']) ? mswSQL($_POST['em']) : 'admin@example.com'),
  '{pass}' => (isset($_POST['pw']) && $_POST['pw'] ? mswPassHash(array('type' => 'add', 'pass' => $_POST['pw'])) : ''),
  '{key}' => $prodKey,
  '{attpath}' => $root . '/content/attachments',
  '{attfaqpath}' => $root . '/content/attachments-faq',
  '{attpath-server}' => mswSQL(BASE_PATH . 'content/attachments'),
  '{attfaqpath-server}' => mswSQL(BASE_PATH . 'content/attachments-faq'),
  '{deflogs}' => mswSQL('a:2:{s:4:"user";s:2:"50";s:3:"acc";s:2:"50";}'),
  '{langsets}' => mswSQL('a:1:{s:7:"english";s:12:"_default_set";}'),
  '{apikey}' => strtoupper(substr(md5(uniqid(rand(), 1)), 3, 10) . '-' . substr(md5(uniqid(rand(), 1)), 3, 8))
);

// Data..
$sTables = array(
  'departments', 'imapban', 'levels', 'settings', 'users', 'categories', 'faq', 'statuses'
);

// Clear existing data..
mswSQL_truncate($sTables);

// Import new data..
foreach ($sTables AS $sql_file) {
  if (file_exists(PATH . 'control/sql/' . $sql_file . '.sql')) {
    $q = mswSQL_query(strtr(@mswTmp(PATH . 'control/sql/' . $sql_file . '.sql'), $f_r));
    if ($q === 'err') {
      $ERR = mswSQL_error(true);
      mswInsLog(DB_PREFIX . $sql_file, $ERR[1], $ERR[0], __LINE__, __FILE__, 'Insert Standard Data (' . $sql_file . '.sql)');
      ++$dtcount;
    }
  } else {
    mswInsLog(DB_PREFIX . $sql_file, $sql_file . '.sql - file does not exist', 0, __LINE__, __FILE__, 'Insert Standard Data (' . $sql_file . '.sql)');
    ++$dtcount;
  }
}

?>