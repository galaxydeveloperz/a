<?php

/* CLASS FILE
----------------------------------*/

class dbBackup {

  public $database = null;
  public $compress = false;
  public $hexValue = false;
  public $filename = null;
  public $file = null;
  public $isWritten = false;
  public $settings;
  public $dt;
  
  private $skip_head_info = 'no';

  public function __construct($filepath, $compress = false) {
    $this->compress = $compress;
    if (!dbBackup::setOutputFile($filepath)) {
      return false;
    }
    return dbBackup::setDatabase(DB_NAME);
  }

  public function setDatabase($db) {
    $this->database = $db;
    if (!((bool)mswSQL_query("USE `" . $this->database . "`", __file__, __line__))) {
      return false;
    }
    return true;
  }

  public function getDatabase() {
    return $this->database;
  }

  public function setCompress($compress) {
    if ($this->isWritten) {
      return false;
    }
    $this->compress = $compress;
    dbBackup::openFile($this->filename);
    return true;
  }

  public function getCompress() {
    return $this->compress;
  }

  public function setOutputFile($filepath) {
    if ($this->isWritten) {
      return false;
    }
    $this->filename = $filepath;
    $this->file     = dbBackup::openFile($this->filename);
    return $this->file;
  }

  public function getOutputFile() {
    return $this->filename;
  }

  public function getTableStructure($table) {
    if (!dbBackup::setDatabase($this->database)) {
      return false;
    }
    $structure = '--' . mswNL();
    $structure .= '-- Table structure for table `' . $table . '` ' . mswNL();
    $structure .= '--' . mswNL(2);
    $structure .= 'DROP TABLE IF EXISTS `' . $table . '`;' . mswNL();
    $structure .= 'CREATE TABLE `' . $table . '` (' . mswNL();
    $records = mswSQL_query('SHOW FIELDS FROM `' . $table . '`', __file__, __line__);
    if (mswSQL_numrows($records) == 0) {
      return false;
    }
    while ($record = mysqli_fetch_assoc($records)) {
      $structure .= '`' . $record['Field'] . '` ' . $record['Type'];
      if (!empty($record['Default'])) {
        $structure .= ' DEFAULT \'' . $record['Default'] . '\'';
      }
      if (strcmp($record['Null'], 'YES') != 0) {
        $structure .= ' NOT NULL';
      }
      if (!empty($record['Extra'])) {
        $structure .= ' ' . $record['Extra'];
      }
      $structure .= ',' . mswNL();
    }
    $structure = substr_replace(trim($structure), '', -1);
    $structure .= dbBackup::getSqlKeysTable($table);
    $structure .= mswNL() . ")";
    $records = mswSQL_query("SHOW TABLE STATUS LIKE '" . $table . "'", __file__, __line__);
    if ($record = mysqli_fetch_assoc($records)) {
      if (!empty($record['Engine'])) {
        $structure .= ' ENGINE=' . $record['Engine'];
      }
      if (!empty($record['Auto_increment'])) {
        $structure .= ' AUTO_INCREMENT=' . $record['Auto_increment'];
      }
    }
    $structure .= ";" . mswNL(2) . "-- --------------------------------------------------------" . mswNL(2);
    dbBackup::saveToFile($this->file, $structure);
  }

  public function mswSQL_table($table, $hexValue = true) {
    if (!dbBackup::setDatabase($this->database)) {
      return false;
    }
    $data = '--' . mswNL();
    $data .= '-- Dumping data for table `' . $table . '`' . mswNL();
    $data .= '--' . mswNL(2);
    $records    = mswSQL_query('SHOW FIELDS FROM `' . $table . '`', __file__, __line__);
    $num_fields = mswSQL_numrows($records);
    if ($num_fields == 0) {
      return false;
    }
    $selectStatement = "SELECT ";
    $insertStatement = "INSERT INTO `$table` (";
    $hexField        = array();
    for ($x = 0; $x < $num_fields; $x++) {
      $record = mysqli_fetch_assoc($records);
      if (($hexValue) && (dbBackup::isTextValue($record['Type']))) {
        $selectStatement .= 'HEX(`' . $record['Field'] . '`)';
        $hexField[$x] = true;
      } else {
        $selectStatement .= '`' . $record['Field'] . '`';
        $insertStatement .= '`' . $record['Field'] . '`';
        $insertStatement .= ", ";
        $selectStatement .= ", ";
      }
    }
    $insertStatement = substr($insertStatement, 0, -2) . ') VALUES';
    $selectStatement = substr($selectStatement, 0, -2) . ' FROM `' . $table . '`';
    $records         = mswSQL_query($selectStatement, __file__, __line__);
    $num_rows        = mswSQL_numrows($records);
    $num_fields      = (($___mysqli_tmp = mysqli_num_fields($records)) ? $___mysqli_tmp : false);
    if ($num_rows > 0) {
      $data .= $insertStatement;
      for ($i = 0; $i < $num_rows; $i++) {
        $record = mysqli_fetch_assoc($records);
        $data .= ' (';
        for ($j = 0; $j < $num_fields; $j++) {
          $field_name = ((($___mysqli_tmp = mysqli_fetch_field_direct($records,  $j)->name) && (!is_null($___mysqli_tmp))) ? $___mysqli_tmp : false);
          if (isset($hexField[$j]) && $hexField[$j] && (strlen($record[$field_name]) > 0)) {
            $data .= "0x" . $record[$field_name];
          } else {
            $data .= "'" . str_replace('\"', '"', mswSQL($record[$field_name])) . "'";
          }
          $data .= ',';
        }
        $data = substr($data, 0, -1) . ")";
        $data .= ($i < ($num_rows - 1)) ? ',' : ';';
        $data .= mswNL();
        if (strlen($data) > 1048576) {
          dbBackup::saveToFile($this->file, $data);
          $data = '';
        }
      }
      $data .= mswNL() . "-- --------------------------------------------------------" . mswNL(2);
      dbBackup::saveToFile($this->file, $data);
    }
  }

  public function getDatabaseStructure() {
    $structure    = '';
    $records      = mswSQL_query('SHOW TABLES', __file__, __line__);
    $scriptSchema = mswSQL_schema();
    if (mswSQL_numrows($records) == 0) {
      return false;
    }
    while ($record = mysqli_fetch_row($records)) {
      if (in_array($record[0], $scriptSchema)) {
        $structure .= dbBackup::getTableStructure($record[0]);
      }
    }
    return true;
  }

  public function getDatabaseData($hexValue = true) {
    $scriptSchema = mswSQL_schema();
    $records      = mswSQL_query('SHOW TABLES', __file__, __line__);
    if (mswSQL_numrows($records) == 0) {
      return false;
    }
    while ($record = mysqli_fetch_row($records)) {
      if (in_array($record[0], $scriptSchema)) {
        dbBackup::mswSQL_table($record[0], $hexValue);
      }
    }
  }

  public function getMySQLVersion() {
    $q = mswSQL_query("SELECT VERSION() AS `v`", __file__, __line__);
    $V = mswSQL_fetchobj($q);
    return (isset($V->v) ? $V->v : 'Unknown');
  }

  public function doDump() {
    if ($this->skip_head_info == 'no') {
      $header = '#--------------------------------------------------------' . mswNL();
      $header .= '# MYSQL DATABASE SCHEMATIC' . mswNL();
      $header .= '# HelpDesk: ' . mswCD($this->settings->website) . mswNL();
      $header .= '# Software Version: ' . SCRIPT_VERSION . mswNL();
      $header .= '# Date Created: ' . $this->dt->mswDateTimeDisplay(0, $this->settings->dateformat) . ' @ ' . $this->dt->mswDateTimeDisplay(0, $this->settings->timeformat) . mswNL();
      $header .= '# MySQL Version: ' . dbBackup::getMySQLVersion() . mswNL();
      $header .= '#--------------------------------------------------------' . mswNL(2);
    } else {
      $header = '';
    }
    dbBackup::saveToFile($this->file, $header . 'SET FOREIGN_KEY_CHECKS = 0;' . mswNL(2));
    dbBackup::getDatabaseStructure();
    dbBackup::getDatabaseData($this->hexValue);
    dbBackup::saveToFile($this->file, 'SET FOREIGN_KEY_CHECKS = 1;' . mswNL(2));
    dbBackup::closeFile($this->file);
    return true;
  }

  public function writeDump($filename) {
    if (!dbBackup::setOutputFile($filename)) {
      return false;
    }
    dbBackup::doDump();
    dbBackup::closeFile($this->file);
    return true;
  }

  public function getSqlKeysTable($table) {
    $primary         = '';
    $sqlKeyStatement = '';
    $unique          = array();
    $index           = array();
    $fulltext        = array();
    $results         = mswSQL_query("SHOW KEYS FROM `{$table}`", __file__, __line__);
    if (mswSQL_numrows($results) == 0) {
      return false;
    }
    while ($row = mswSQL_fetchobj($results)) {
      if (($row->Key_name == 'PRIMARY') AND ($row->Index_type == 'BTREE')) {
        if ($primary == '') {
          $primary = "  PRIMARY KEY  (`{$row->Column_name}`";
        } else {
          $primary .= ", `{$row->Column_name}`";
        }
      }
      if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '0') AND ($row->Index_type == 'BTREE')) {
        if (!isset($unique[$row->Key_name])) {
          $unique[$row->Key_name] = "  UNIQUE KEY `{$row->Key_name}` (`{$row->Column_name}`";
        } else {
          $unique[$row->Key_name] .= ", `{$row->Column_name}`";
        }
      }
      if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '1') AND ($row->Index_type == 'BTREE')) {
        if (!isset($index[$row->Key_name])) {
          $index[$row->Key_name] = "  KEY `{$row->Key_name}` (`{$row->Column_name}`";
        } else {
          $index[$row->Key_name] .= ", `{$row->Column_name}`";
        }
      }
      if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '1') AND ($row->Index_type == 'FULLTEXT')) {
        if (!isset($fulltext[$row->Key_name])) {
          $fulltext[$row->Key_name] = "  FULLTEXT `{$row->Key_name}` (`{$row->Column_name}`";
        } else {
          $fulltext[$row->Key_name] .= ", `{$row->Column_name}`";
        }
      }
    }
    if ($primary != '') {
      $sqlKeyStatement .= "," . mswNL();
      $primary .= ")";
      $sqlKeyStatement .= $primary;
    }
    if (is_array($unique)) {
      foreach ($unique AS $keyName => $keyDef) {
        $sqlKeyStatement .= "," . mswNL();
        $keyDef .= ")";
        $sqlKeyStatement .= $keyDef;
      }
    }
    if (is_array($index)) {
      foreach ($index AS $keyName => $keyDef) {
        $sqlKeyStatement .= "," . mswNL();
        $keyDef .= ")";
        $sqlKeyStatement .= $keyDef;
      }
    }
    if (is_array($fulltext)) {
      foreach ($fulltext AS $keyName => $keyDef) {
        $sqlKeyStatement .= "," . mswNL();
        $keyDef .= ")";
        $sqlKeyStatement .= $keyDef;
      }
    }
    return $sqlKeyStatement;
  }

  public function isTextValue($field_type) {
    switch ($field_type) {
      case 'tinytext':
      case 'text':
      case 'mediumtext':
      case 'longtext':
      case 'binary':
      case 'varbinary':
      case 'tinyblob':
      case 'blob':
      case 'mediumblob':
      case 'longblob':
        return true;
        break;
      default:
        return false;
    }
  }

  public function openFile($filename) {
    $file = false;
    if ($this->compress) {
      $file = gzopen($filename, 'w9');
    } else {
      $file = fopen($filename, 'ab');
    }
    return $file;
  }

  public function saveToFile($file, $data) {
    if ($this->compress) {
      if ($file) {
        gzwrite($file, $data);
      }
    } else {
      if ($file) {
        fwrite($file, $data);
      }
    }
    $this->isWritten = true;
  }

  public function closeFile($file) {
    if ($this->compress) {
      if ($file) {
        gzclose($file);
      }
    } else {
      if ($file) {
        fclose($file);
      }
    }
  }

}

?>