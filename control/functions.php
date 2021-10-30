<?php

/* Functions
----------------------------------------------*/

// Clean characters..
function mswCleanFile($file) {
  return preg_replace("/[&'#]/", "_", str_replace('.php', '.phps', $file));
}

function mswJSClean($js) {
  return str_replace("'", "\'", $js);
}

// Fixes settings fields if manual schema was run..
function mswManSchemaFix($s) {
  if ($s->scriptpath == '' && $s->attachpath == '' && $s->attachhref == '') {
    $hdeskPath = 'http://www.example.com/helpdesk';
    if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['PHP_SELF'])) {
      $hdeskPath = 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, -10);
    }
    $hdeskPathAtt = $hdeskPath . '/content/attachments';
    $hdeskPathFaq = $hdeskPath . '/content/attachments-faq';
    $attachPath   = mswSQL(PATH . 'content/attachments');
    $attFaqPath   = mswSQL(PATH . 'content/attachments-faq');
    $apiKey       = strtoupper(substr(md5(uniqid(rand(), 1)), 3, 10) . '-' . substr(md5(uniqid(rand(), 1)), 3, 8));
    mswSQL_query("UPDATE `" . DB_PREFIX . "settings` SET
    `website`             = 'My HelpDesk',
    `timezone`            = 'Europe/London',
    `scriptpath`          = '{$hdeskPath}',
    `attachpath`          = '{$attachPath}',
	  `attachhref`          = '{$hdeskPathAtt}',
	  `attachpathfaq`       = '{$attFaqPath}',
	  `attachhreffaq`       = '{$hdeskPathFaq}',
    `langSets`            = '" . mswSQL('a:1:{s:7:"english";s:12:"_default_set";}') . "',
    `adminFooter`         = 'To add your own footer code, click &quot;Settings &amp; Tools > Settings > Other Options > Edit Footers&quot;',
    `publicFooter`        = 'To add your own footer code, click &quot;Settings &amp; Tools > Settings > Other Options > Edit Footers&quot;',
    `prodKey`             = '" . mswProdKeyGen() . "',
    `encoderVersion`      = 'msw',
    `softwareVersion`     = '" . SCRIPT_VERSION . "',
	  `apiKey`              = '{$apiKey}',
    `defKeepLogs`         = '" . mswSQL('a:2:{s:4:"user";s:2:"50";s:3:"acc";s:2:"50";}') . "'
    ", __file__, __line__);
    // Insert user..
    mswSQL_query("UPDATE `" . DB_PREFIX . "users` SET
    `accpass` = '" . mswPassHash(array('type' => 'add', 'pass' => 'admin')) . "'
    ", __file__, __line__);
    // Page reload..
    header("Location: index.php");
    exit;
  }
}

// Generates 60 character product key..
function mswProdKeyGen() {
  $_SERVER['HTTP_HOST']   = (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : uniqid(rand(), 1));
  $_SERVER['REMOTE_ADDR'] = (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : uniqid(rand(), 1));
  if (function_exists('sha1')) {
    $c1      = sha1($_SERVER['HTTP_HOST'] . date('YmdHis') . $_SERVER['REMOTE_ADDR'] . time());
    $c2      = sha1(uniqid(rand(), 1) . time());
    $prodKey = substr($c1 . $c2, 0, 60);
  } elseif (function_exists('md5')) {
    $c1      = md5($_SERVER['HTTP_POST'] . date('YmdHis') . $_SERVER['REMOTE_ADDR'] . time());
    $c2      = md5(uniqid(rand(), 1), time());
    $prodKey = substr($c1 . $c2, 0, 60);
  } else {
    $c1      = str_replace('.', '', uniqid(rand(), 1));
    $c2      = str_replace('.', '', uniqid(rand(), 1));
    $c3      = str_replace('.', '', uniqid(rand(), 1));
    $prodKey = substr($c1 . $c2 . $c3, 0, 60);
  }
  return strtoupper($prodKey);
}

// Login credentials..
function mswIsUserLoggedIn($ssn) {
  if (method_exists($ssn, 'get')) {
    return ($ssn->active('_msw_support') == 'yes' &&
      mswIsValidEmail($ssn->get('_msw_support')) &&
      mswSQL_rows('portal WHERE `email` = \'' . mswSQL($ssn->get('_msw_support')) . '\' AND `verified` = \'yes\'') > 0 ?
      $ssn->get('_msw_support') :
      'guest'
    );
  }
  return 'guest';
}

// Check valid email..
function mswIsValidEmail($em) {
  if (function_exists('filter_var') && filter_var($em, FILTER_VALIDATE_EMAIL)) {
    return true;
  }
  if (preg_match('/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}' .
    '[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD', $em)) {
    return true;
  }
  return false;
}

// New line to break..
function mswNL2BR($text) {
  return nl2br($text, false);
}

function mswNFM($num, $dec = 0) {
  return @number_format($num, $dec);
}

function mswNFMDec($num, $dec = 0) {
  return @number_format($num, $dec, '.', '');
}

// Detect SSL..
function mswSSL() {
  return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'yes' : 'no');
}

// Variable sanitation..
function mswDigit($no) {
  return (int) $no;
}

// File size..
function mswFSC($size, $precision = 2) {
  if ($size > 0) {
    $base     = log($size) / log(1024);
    $suffixes = array(
      'Bytes',
      'KB',
      'MB',
      'GB',
      'TB'
    );
    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
  } else {
    return '0Bytes';
  }
}

// Check valid query..
function mswVLQY($data) {
  if (!isset($data->id)) {
    die('Invalid page or parameter');
  }
}

// Digit check..
function mswVLDG($id, $admin = false) {
  if ((int) $id == 0) {
    if (class_exists('htmlHeaders')) {
      htmlHeaders::err404($admin);
    } else {
      header('HTTP/1.0 404 Not Found');
      header('Content-type: text/plain; charset=utf-8');
      echo '<h1>404, Invalid Page</h1>';
    }
    exit;
  }
}

// Ticket numbers...
function mswTicketNumber($num, $min, $rand = '') {
  $cn = ($min - strlen($num));
  return ($rand ? $rand : ($cn > 0 ? str_repeat(0, $cn) . $num : $num));
}

function mswRandTicket($id) {
  $cn = (6 - strlen($id));
  return substr(time(), -2) . mt_rand(111, 999) . '-' . ($cn > 0 ? substr(sha1($id . time()), 0, $cn) . $id : $id);
}

function mswReverseTicketNumber($num, $rand = '') {
  return ($rand ? $rand : ltrim($num, '0'));
}

// Yes/No..
function mswYN($flag, $y, $n) {
  return ($flag == 'yes' ? $y : $n);
}

// Clean data..
function mswCD($data) {
  if (version_compare(PHP_VERSION, '5.4', '<') && function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
    $sybase = strtolower(@ini_get('magic_quotes_sybase'));
    if (empty($sybase) || $sybase == 'off') {
      // Fixes issue of new line chars not parsing between single quotes..
      $data = str_replace('\n', '\\\n', $data);
      return stripslashes($data);
    }
  }
  return trim($data);
}

// Gets visitor IP address..
function mswIP() {
  $ips = array();
  $types = array(
    'HTTP_CLIENT_IP',
    'HTTP_X_FORWARDED_FOR',
    'HTTP_X_FORWARDED',
    'HTTP_X_CLUSTER_CLIENT_IP',
    'HTTP_FORWARDED_FOR',
    'HTTP_FORWARDED',
    'REMOTE_ADDR'
  );
  foreach ($types AS $key) {
    if (array_key_exists($key, $_SERVER) === true) {
      foreach (array_map('trim', explode(',', $_SERVER[$key])) AS $ipA) {
        if (!in_array($ipA, $ips) && filter_var($ipA, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
          $ips[] = $ipA;
        } else {
          // Double check for localhost..
          if (!in_array($ipA, $ips) && in_array($ipA, array('::1','127.0.0.1'))) {
            $ips[] = $ipA;
          }
        }
      }
    }
  }
  return (!empty($ips) ? implode(',', $ips) : '');
}

// Define newline..
function mswNL($br = 1) {
  if (defined('PHP_EOL')) {
    if ($br > 1) {
      return str_repeat(PHP_EOL, $br);
    }
    return PHP_EOL;
  }
  $nl = "\r\n";
  if (isset($_SERVER["HTTP_USER_AGENT"]) && strstr(strtolower($_SERVER["HTTP_USER_AGENT"]), 'win')) {
    $nl = "\r\n";
  } else if (isset($_SERVER["HTTP_USER_AGENT"]) && strstr(strtolower($_SERVER["HTTP_USER_AGENT"]), 'mac')) {
    $nl = "\r";
  } else {
    $nl = "\n";
  }
  if ($br > 1) {
    return str_repeat($nl, $br);
  }
  return $nl;
}

// Append url parameter..
function mswUrlApp($var, $ampersand = true) {
  return (isset($_GET[$var]) ? ($ampersand ? '&amp;' : '') . $var . '=' . mswCD($_GET[$var]) : '');
}

// Return selected option..
function mswSelectedItem($var, $compare, $get = false, $checked = false) {
  if ($get) {
    return (isset($_GET[$var]) && $_GET[$var] == $compare ? ($checked ? ' checked="checked"' : ' selected="selected"') : '');
  } else {
    return (trim($var) == trim($compare) ? ($checked ? ' checked="checked"' : ' selected="selected"') : '');
  }
}

// Check encoding..
function mswUTF8($in, $encoding) {
  $encoding = strtoupper($encoding);
  switch ($encoding) {
    case 'UTF-8':
      return $in;
      break;
    case 'ISO-8859-1':
      return utf8_encode($in);
      break;
    default:
      return iconv($encoding, 'UTF-8', $in);
      break;
  }
}

// Return checked option based on array..
function mswCheckedArrItem($arr, $value) {
  return (in_array($value, $arr) ? ' checked="checked"' : '');
}

// Parse url for query string params..
function mswQueryParams($skip = array(), $start = 'no', $escape = 'yes') {
  $s = '';
  if (!empty($_GET)) {
    foreach ($_GET AS $gK => $gV) {
      // Check for array elements in query string..
      if (is_array($gV)) {
        foreach ($gV AS $gKA => $gVA) {
          if (!in_array($gK, $skip)) {
            $s .= ($escape == 'yes' ? '&amp;' : '&') . $gK . '[]=' . urlencode(mswCD($gVA));
          }
        }
      } else {
        if (!in_array($gK, $skip)) {
          $s .= ($escape == 'yes' ? '&amp;' : '&') . $gK . '=' . urlencode(mswCD($gV));
        }
      }
    }
  }
  return ($start == 'yes' ? substr($s, 5) : $s);
}

// Encryption method
function mswEncrypt($data) {
  return (function_exists('sha1') ? sha1($data) : md5($data));
}

// Convert bad multibyte chars..
function mswStripMultibyteChars($str) {
  $result = '';
  $length = strlen($str);
  for ($i = 0; $i < $length; $i++) {
    $ord = ord($str[$i]);
    if ($ord >= 240 && $ord <= 244) {
      $result .= '?';
      $i += 3;
    } else {
      $result .= $str[$i];
    }
  }
  return $result;
}

// Special char..
function mswSH($data, $entities = true) {
  if (!$entities) {
    return mswCD($data);
  }
  return htmlspecialchars(mswCD($data));
}

// Recursive way of handling multi dimensional arrays..
function mswMDAM($func, $arr) {
  $newArr = array();
  if (!empty($arr)) {
    foreach ($arr AS $key => $value) {
      $newArr[$key] = (is_array($value) ? mswMDAM($func, $value) : $func($value));
    }
  }
  return $newArr;
}

// Controller
function mswfileController() {
  if (!file_exists((defined('BASE_PATH') ? BASE_PATH : PATH) . 'control/system/core/sys-controller.php')) {
    die('[FATAL ERROR] The "control/system/core/sys-controller.php" file does NOT exist in your installation.');
  }
}

function mswResData($data, $limit) {
  if ($limit > 0) {
    return substr($data, 0, $limit) . (strlen($data) > $limit ? '...' : '');
  }
  return $data;
}

function mswFPC($file, $data) {
  file_put_contents($file, $data, FILE_APPEND);
}

// Template loader
function mswTmp($path, $loaded = 'no') {
  switch($loaded) {
    case 'ok':
      return file_get_contents($path);
      break;
    case 'no':
      return (file_exists($path) ? file_get_contents($path) : die('Template file "' . $path . '" missing!'));
      break;
  }
}

// Password hashing..
function mswPassHash($d = array()) {
  switch($d['type']) {
    case 'add':
      return password_hash($d['pass'], PASSWORD_BCRYPT);
      break;
    case 'calc':
      return password_verify($d['val'], $d['hash']);
      break;
  }
}

// Global filtering on post and get inputs using callback mechanism..
$_GET  = mswMDAM('htmlspecialchars', $_GET);
$_POST = mswMDAM('trim', $_POST);

?>