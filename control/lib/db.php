<?php

/* Database functions
----------------------------------------*/

define('DB_ERR_LOG_NAME', 'mysqli-err-log.log');

function mswSQL_connect() {
  if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
    $connect = @($GLOBALS["___mysqli_ston"] = mysqli_connect(trim(DB_HOST),  trim(DB_USER),  trim(DB_PASS)));
    if (!$connect) {
      mswSQL_err(mysqli_connect_errno(), mysqli_connect_error(), __file__, __line__);
      exit;
    }
    if ($connect && !((bool)mswSQL_query('USE `' . DB_NAME . '`'))) {
      mswSQL_err(mysqli_connect_errno(), mysqli_connect_error(), __file__, __line__);
      exit;
    }
    if ($connect) {
      // Character set..
      if (defined('DB_CHAR_SET') && DB_CHAR_SET) {
        if (strtolower(DB_CHAR_SET) == 'utf-8') {
          $change = 'utf8';
        }
        mswSQL_query("SET CHARACTER SET '" . (isset($change) ? $change : DB_CHAR_SET) . "'", __file__, __line__);
        mswSQL_query("SET NAMES '" . (isset($change) ? $change : DB_CHAR_SET) . "'", __file__, __line__);
        if (defined('DB_TIMEZONE') && DB_TIMEZONE != '') {
          mswSQL_query("SET `time_zone` = '" . DB_TIMEZONE . "'", __file__, __line__);
        }
      }
      // Locale..
      if (defined('DB_LOCALE')) {
        if (DB_CHAR_SET && DB_LOCALE) {
          mswSQL_query("SET `lc_time_names` = '" . DB_LOCALE . "'", __file__, __line__);
        }
      }
      mswSQL_query("SET `sql_mode` = 'IGNORE_SPACE,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'", __file__, __line__);
    }
  } else {
    die('Database parameters edited incorrectly in "control/connect.php" file. Please try again');
  }
}

function mswSQL($data) {
  if (version_compare(PHP_VERSION, '5.4', '<') && function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
    $sybase = strtolower(@ini_get('magic_quotes_sybase'));
    if (empty($sybase) || $sybase == 'off') {
      $data = stripslashes($data);
    } else {
      $data = mswSQL_doubleapos($data);
    }
  }
  // Strip bad multibyte characters and replace with ?.
  // Skip if value is an integer
  if (DB_CHAR_SET == 'utf8') {
    $q  = mswSQL_query("SELECT VERSION() AS `v`");
    $VS = @mswSQL_fetchobj($q);
    if (isset($VS->v) && $VS->v <= '5.5.3' && (int) $data == 0) {
      $data = mswStripMultibyteChars($data);
    }
  }
  // Fix microsoft word smart quotes..
  $data = mswSQL_smartquotes($data);
  return ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $data) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
}

function mswSQL_table($table, $row, $val, $and = '', $params = '*') {
  $q = mswSQL_query("SELECT $params FROM `" . DB_PREFIX . $table . "`
       WHERE `" . $row . "`  = '{$val}'
       $and
       LIMIT 1
       ");
  return mswSQL_fetchobj($q);
}

function mswSQL_rows($table, $where = '', $format = true) {
  $q = mswSQL_query("SELECT count(*) AS `r_count` FROM " . DB_PREFIX . $table . $where);
  $r = mswSQL_fetchobj($q);
  if ($format) {
    return mswNFM($r->r_count);
  } else {
    return $r->r_count;
  }
}

function mswSQL_schema() {
  $tbl = array();
  if (strlen(DB_PREFIX) > 0) {
    $q = mswSQL_query("SHOW TABLES WHERE SUBSTRING(`Tables_in_" . DB_NAME . "`,1," . strlen(DB_PREFIX) . ") = '" . DB_PREFIX . "'");
  } else {
    $q = mswSQL_query("SHOW TABLES");
  }
  while ($TABLES = mswSQL_fetchobj($q)) {
    $field = 'Tables_in_' . DB_NAME;
    $tbl[] = $TABLES->{$field};
  }
  return $tbl;
}

function mswSQL_query($q, $file = '', $line = '', $no = '', $code = '', $err = 'no') {
  $qy = mysqli_query($GLOBALS["___mysqli_ston"], $q);
  if ($qy) {
    return $qy;
  } else {
    if (defined('INS_ROUTINE')) {
      return 'err';
    } elseif ($err == 'yes') {
      return 'err';
    } {
      mswSQL_err(
        ($no ? $no : mysqli_errno($GLOBALS["___mysqli_ston"])),
        ($code ? $code : mysqli_error($GLOBALS["___mysqli_ston"])),
        $file,
        $line,
        $q
      );
    }
  }
}

function mswSQL_fetchobj($q) {
  return mysqli_fetch_object($q);
}

function mswSQL_numrows($q) {
  return mysqli_num_rows($q);
}

function mswSQL_affrows() {
  return mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
}

function mswSQL_insert_id() {
  return mysqli_insert_id($GLOBALS["___mysqli_ston"]);
}

function mswSQL_smartquotes($string) {
  return $string;
  // Uncomment to use. Not 100% reliable.
  //$search   = array(chr(145),chr(146),chr(147),chr(148),chr(151));
  //$replace  = array("'","'",'"','"','-');
  //return str_replace($search,$replace,$string);
}

function mswSQL_doubleapos($data) {
  return str_replace("''", "'", $data);
}

function mswSQL_version() {
  $q = mswSQL_query("SELECT VERSION() AS `v`");
  $V = mswSQL_fetchobj($q);
  return (isset($V->v) ? $V->v : 'Unknown');
}

function mswSQL_charsets() {
  $cSets    = array();
  $DCHARSET = mswSQL_query("SHOW CHARACTER SET");
  while ($CH = mswSQL_fetchobj($DCHARSET)) {
    if (is_object($CH)) {
      $CH_SET = (array) $CH;
      if (isset($CH_SET['Charset'])) {
        $DCOLL = mswSQL_query("SHOW COLLATION LIKE '" . $CH_SET['Charset'] . "%'");
        while ($COL = mswSQL_fetchobj($DCOLL)) {
          if (is_object($COL)) {
            $COL_SET = (array) $COL;
            if (isset($COL_SET['Collation'])) {
              $cSets[] = $COL_SET['Collation'];
            }
          }
        }
      }
    }
  }
  return $cSets;
}

function mswSQL_error($raw = false, $query='') {
  if ($raw) {
    return array(
      ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)),
      ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))
    );
  }
  return (db::db_error_log(((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), $query));
}

function mswSQL_truncate($tables = array(), $force = false) {
  if (!empty($tables)) {
    foreach ($tables AS $t) {
      if (mswSQL_rows($t) == 0 || $force) {
        mswSQL_query("TRUNCATE TABLE `" . DB_PREFIX . $t . "`");
      }
    }
  }
}

function mswSQL_err($code, $error, $file, $line, $query = '') {
  // If ajax queries are present, log silently..
  if (isset($_GET['ajax'])) {
    $str  = 'MySQLi Error on ' . date('j F Y') . ' @ ' . date('H:iA') . mswNL();
    $str .= 'Code' . ': ' . $code . mswNL();
    $str .= 'Error' . ': ' . $error . mswNL();
    $str .= 'File' . ': ' . $line . mswNL();
    $str .= 'Line' . ': ' . $file . mswNL();
    if ($query) {
      $str .= 'DB Query' . ': ' . mswNL(2) . $query . mswNL();
    }
    $str .= '- - - - - - - - - - - - - - - - - - - - - - - -' . mswNL();
    if (defined('BASE_PATH') || defined('PATH')) {
      if (is_dir((defined('BASE_PATH') ? BASE_PATH : PATH) . 'logs')) {
        mswFPC((defined('BASE_PATH') ? BASE_PATH : PATH) . 'logs/' . DB_ERR_LOG_NAME, $str);
      }
    }
  } else {
    if (ENABLE_MYSQL_ERRORS) {
      if (!function_exists('mswSH')) {
        function mswSH($d) {
          return htmlspecialchars($d);
        }
      }
      echo '<div style="margin:20px;padding:20px;background: #ff9999;-webkit-border-radius: 5px;border-radius: 5px;border:1px solid #555">';
      echo '<b>MYSQLi DATABASE ERROR:</b><br><br>';
      echo '<b>Code</b>: ' . $code . '<br>';
      echo '<b>Error</b>: ' . mswSH($error) . '<br>';
      echo '<b>File</b>: ' . $file . '<br>';
      echo '<b>Line</b>: ' . $line;
      if ($query) {
        echo '<hr style="border:0;border-bottom:1px solid #fff"><b>DB Query</b>:<br><br>' . mswSH($query) . '<hr style="border:0;border-bottom:1px solid #fff">';
      }
      echo '</div>';
    } else {
      echo MYSQL_DEFAULT_ERROR;
    }
  }
  exit;
}

?>