<?php

/* CLASS FILE
----------------------------------*/

class social {

  public $json;
  public $settings;

  public function params($flag = 'all') {
    $arr = array();
    switch($flag) {
      case 'all':
        $Q   = mswSQL_query("SELECT `desc`, `param`, `value` FROM `" . DB_PREFIX . "social`", __file__, __line__);
        break;
      default:
        $Q   = mswSQL_query("SELECT `desc`, `param`, `value` FROM `" . DB_PREFIX . "social` WHERE `desc` = '{$flag}'", __file__, __line__);
        break;
    }
    while ($PAR = mswSQL_fetchobj($Q)) {
      $arr[$PAR->desc][$PAR->param] = $PAR->value;
    }
    return $arr;
  }
}

?>