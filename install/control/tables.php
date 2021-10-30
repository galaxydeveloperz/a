<?php

/* INSTALLER TABLES
---------------------------------*/

if (!defined('PARENT') && defined('INSTALL_RUN')) {
  exit;
}

if (is_dir(PATH . 'control/sql/tables')) {
  $dir = opendir(PATH . 'control/sql/tables');
  while (false !== ($read = readdir($dir))) {
    if (substr(strtolower($read), -4) == '.sql') {
      $table = substr($read, 0, -4);
      $tbdta = str_replace(array('{prefix}', '{engine}'), array(DB_PREFIX, $tableType), mswTmp(PATH . 'control/sql/tables/' . $read, 'ok'));
      mswSQL_query("drop table if exists `" . DB_PREFIX . $table . "`");
      $query = mswSQL_query($tbdta);
      if ($query === 'err') {
        $tableD[] = DB_PREFIX . $table;
        $ERR      = mswSQL_error(true);
        mswInsLog(DB_PREFIX . $table, $ERR[1], $ERR[0], __LINE__, __FILE__);
        ++$count;
      } else {
        $sTables[] = $table;
        mswInsLog('Table added: ' . $table, 'instruction');
      }
    }
  }
  closedir($dir);
}

?>