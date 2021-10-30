<?php

/* CLASS FILE
----------------------------------*/

class msDateTime {

  public $settings;

  // Work time..
  public function worktime($time, $abb) {
    $t = explode(':', $time);
    $a = explode(',', $abb);
    return (isset($t[0], $t[1], $t[2], $a[0], $a[1], $a[2]) ? '<span class="bold">' . $t[0] . '</span>' . $a[0] . ' <span class="bold">' . $t[1] . '</span>' . $a[1] . ' <span class="bold">' . $t[2] . '</span>' . $a[2] : $time);
  }

  public function secToTime($sec) {
	  $time = array(0, 0, intval($sec));
    if ($time[2] == 0) {
		  return '00:00:00';
	  }
    if ($time[2] > 59) {
    	$time[1] = floor($time[2] / 60) + $time[1];
      $time[2] = intval($time[2] % 60);
    }
    if ($time[1] > 59) {
    	$time[0] = floor($time[1] / 60) + $time[0];
      $time[1] = intval($time[1] % 60);
    }
    return str_pad($time[0], 2, '0', STR_PAD_LEFT) . ':' . str_pad($time[1], 2, '0', STR_PAD_LEFT) . ':' . str_pad($time[2], 2, '0', STR_PAD_LEFT);
  }

  // Convert us date to specified date..
  public function mswConvertMySQLDate($sql) {
    $split = explode('-', $sql);
    switch ($this->settings->jsDateFormat) {
      case 'DD-MM-YYYY':
        return $split[2] . '-' . $split[1] . '-' . $split[0];
        break;
      case 'DD/MM/YYYY':
        return $split[2] . '/' . $split[1] . '/' . $split[0];
        break;
      case 'YYYY-MM-DD':
        return $sql;
        break;
      case 'YYYY/MM/DD':
        return str_replace('-', '/', $sql);
        break;
      case 'MM-DD-YYYY':
        return $split[1] . '-' . $split[2] . '-' . $split[0];
        break;
      case 'MM/DD/YYYY':
        return $split[1] . '/' . $split[2] . '/' . $split[0];
        break;
    }
  }

  // Calendar picker format..
  public function mswDatePickerFormat($sql = '') {
    // Convert into js format dates..
    switch ($this->settings->jsDateFormat) {
      case 'DD-MM-YYYY':
        $formatJS = ($sql ? substr($sql, 6, 4) . '-' . substr($sql, 3, 2) . '-' . substr($sql, 0, 2) : 'dd-mm-yy');
        break;
      case 'DD/MM/YYYY':
        $formatJS = ($sql ? substr($sql, 6, 4) . '-' . substr($sql, 3, 2) . '-' . substr($sql, 0, 2) : 'dd/mm/yy');
        break;
      case 'YYYY-MM-DD':
        $formatJS = ($sql ? $sql : 'yy-mm-dd');
        break;
      case 'YYYY/MM/DD':
        $formatJS = ($sql ? str_replace('/', '-', $sql) : 'yy/mm/dd');
        break;
      case 'MM-DD-YYYY':
        $formatJS = ($sql ? substr($sql, 6, 4) . '-' . substr($sql, 0, 2) . '-' . substr($sql, 3, 2) : 'mm-dd-yy');
        break;
      case 'MM/DD/YYYY':
        $formatJS = ($sql ? substr($sql, 6, 4) . '-' . substr($sql, 0, 2) . '-' . substr($sql, 3, 2) : 'mm/dd/yy');
        break;
      default:
        $formatJS = ($sql ? mswSQLDate() : 'dd/mm/yy');
        break;
    }
    return $formatJS;
  }

  public function mswTimeZone($timezone, $zone = 0) {
    if (function_exists('date_default_timezone_set')) {
      //date_default_timezone_set(($zone!='0' ? $zone : $timezone));
    }
  }

  public function mswUTC() {
    return (date('I') ? strtotime(date('Y-m-d H:i:s', strtotime('-1 hour'))) : strtotime(date('Y-m-d H:i:s')));
  }

  public function mswTimeStamp() {
    return time();
  }

  public function mswDateTimeDisplay($ts = 0, $format, $zone = '') {
    if ($ts == 0) {
      $ts = msDateTime::mswTimeStamp();
    }
    if (!defined('MSTZ_SET')) {
      define('MSTZ_SET', $this->settings->timezone);
    }
    $dt = new DateTime(date('Y-m-d H:i:s', $ts));
    $dt->setTimezone(new DateTimeZone(($zone ? $zone : MSTZ_SET)));
    return $dt->format($format);
  }

  public function mswGMTDateTime() {
    $ts = time() + date('Z');
    return strtotime(gmdate('Y-m-d H:i:s', $ts));
  }

  public function mswSQLDate() {
    return date('Y-m-d');
  }

  public function microtimeFloat() {
    list($usec, $sec) = explode(' ', microtime());
    return ((float) $usec + (float) $sec);
  }

}

?>