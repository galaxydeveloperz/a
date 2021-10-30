<?php

function mswCheckTable($table) {
  $q = mswSQL_query("SHOW TABLES WHERE `Tables_in_" . DB_NAME . "` = '" . DB_PREFIX . $table . "'", __file__, __line__);
  $c = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
  $f = (isset($c->rows) ? $c->rows : '0');
  return ($f > 0 ? 'yes' : 'no');
}

function mswCheckColumnType($table, $field, $string) {
  $q = mswSQL_query("SHOW FIELDS FROM `" . DB_PREFIX . $table . "` WHERE `Field` = '{$field}'", __file__, __line__);
  $R = mswSQL_fetchobj($q);
  $f = (isset($R->Type) ? strtolower($R->Type) : '');
  return (strpos($f, strtolower($string)) !== false ? 'yes' : 'no');
}

function mswCheckColumn($table, $col) {
  $q = mswSQL_query("SELECT count(*) AS `c` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = '" . DB_NAME . "'
        AND `TABLE_NAME`  = '" . DB_PREFIX . $table . "'
        AND `COLUMN_NAME` = '{$col}'
        ", __file__, __line__);
  $R = mswSQL_fetchobj($q);
  $f = (isset($R->c) ? $R->c : '0');
  return ($f > 0 ? 'yes' : 'no');
}

function mswCheckIndex($table, $index) {
  $q = mswSQL_query("SHOW INDEX FROM `" . DB_PREFIX . $table . "` WHERE `Key_name` = '$index'", __file__, __line__);
  $c = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
  $f = (isset($c->rows) ? $c->rows : '0');
  return ($f > 0 ? 'yes' : 'no');
}

function mswInsLog($table, $error = '', $code = '', $line = '', $file = '', $type = 'Create') {
  $header = '';
  if ($error = 'instruction') {
    $type = $table;
  }
  if (MSW_INSTALL_LOG) {
    if (!file_exists(BASE_PATH . 'logs/' . MSW_INSTALL_LOG_FILE)) {
      $header = 'Software: ' . SCRIPT_NAME . mswNL();
      $header .= 'Script Version: ' . SCRIPT_VERSION . mswNL();
      $header .= 'PHP Version: ' . phpVersion() . mswNL();
      $header .= 'DB Version: ' . mswSQL_version() . mswNL();
      if (isset($_SERVER['SERVER_SOFTWARE'])) {
        $header .= 'Server Software: ' . $_SERVER['SERVER_SOFTWARE'] . mswNL();
      }
      if (isset($_SERVER["HTTP_USER_AGENT"])) {
        if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'win')) {
          $platform = 'Windows';
        } else if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'mac')) {
          $platform = 'Mac';
        } else {
          $platform = 'Other';
        }
        $header .= 'Platform: ' . $platform . mswNL();
      }
      $header .= '=================================================================================' . mswNL();
    }
    if ($error != 'instruction') {
      if ($table) {
        $string = 'Table: ' . $table . mswNL();
      } else {
        $string = '';
      }
    } else {
      $string = '';
    }
    $string .= 'Operation/Detail: ' . $type . mswNL();
    if ($code && $error != 'instruction') {
      $string .= 'Error Code: ' . $code . mswNL();
    }
    if ($error && $error != 'instruction') {
      $string .= 'Error Msg: ' . $error . mswNL();
    }
    if ($line && $error != 'instruction') {
      $string .= 'On Line: ' . $line . mswNL();
    }
    if ($file && $error != 'instruction') {
      $string .= 'In File: ' . $file . mswNL();
    }
    $string .= '- - - - - - - - - - - - - - - - - - - - - ' . mswNL();
    mswFPC(BASE_PATH . 'logs/' . MSW_INSTALL_LOG_FILE, $header . $string);
  }
}

function mswUpLog($table, $error = '', $code = '', $line = '', $file = '', $type = 'Create') {
  $header = '';
  if ($error = 'instruction') {
    $type = $table;
  }
  if (MSW_UPGRADE_LOG) {
    if (!file_exists(BASE_PATH . 'logs/' . MSW_UPGRADE_LOG_FILE)) {
      $header = 'Software: ' . SCRIPT_NAME . mswNL();
      $header .= 'Script Version: ' . SCRIPT_VERSION . mswNL();
      $header .= 'PHP Version: ' . phpVersion() . mswNL();
      $header .= 'DB Version: ' . mswSQL_version() . mswNL();
      if (isset($_SERVER['SERVER_SOFTWARE'])) {
        $header .= 'Server Software: ' . $_SERVER['SERVER_SOFTWARE'] . mswNL();
      }
      if (isset($_SERVER["HTTP_USER_AGENT"])) {
        if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'win')) {
          $platform = 'Windows';
        } else if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'mac')) {
          $platform = 'Mac';
        } else {
          $platform = 'Other';
        }
        $header .= 'Platform: ' . $platform . mswNL();
      }
      $header .= '=================================================================================' . mswNL();
    }
    if ($table) {
      $string = 'Table: ' . $table . mswNL();
    } else {
      $string = '';
    }
    $string .= 'Operation/Detail: ' . $type . mswNL();
    if ($code && $error != 'instruction') {
      $string .= 'Error Code: ' . $code . mswNL();
    }
    if ($error && $error != 'instruction') {
      $string .= 'Error Msg: ' . $error . mswNL();
    }
    if ($line && $error != 'instruction') {
      $string .= 'On Line: ' . $line . mswNL();
    }
    if ($file && $error != 'instruction') {
      $string .= 'In File: ' . $file . mswNL();
    }
    $string .= '- - - - - - - - - - - - - - - - - - - - - ' . mswNL();
    mswFPC(BASE_PATH . 'logs/' . MSW_UPGRADE_LOG_FILE, $header . $string);
  }
}

// Generates 60 character product key..
$_SERVER['HTTP_HOST']   = (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : uniqid(rand(), 1));
$_SERVER['REMOTE_ADDR'] = (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : uniqid(rand(), 1));
$c1                     = sha1($_SERVER['HTTP_HOST'] . date('YmdHis') . $_SERVER['REMOTE_ADDR'] . time());
$c2                     = sha1(uniqid(rand(), 1) . time());
$prodKey                = substr($c1 . $c2, 0, 60);
$prodKey                = strtoupper($prodKey);

?>