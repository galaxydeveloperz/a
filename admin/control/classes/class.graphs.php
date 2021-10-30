<?php

/* CLASS FILE
----------------------------------*/

class graphs {

  public $settings;
  public $datetime;
  public $team;
  public $years;
  public $range = array();
  protected $date_format = '%c/%e/%Y'; // DO NOT change..

  // Data for responses..
  public function responses($filter, $id) {
    $arr = array(array(0,0,0,0,0,0,0,0,0,0,0,0),array(0,0,0,0,0,0,0,0,0,0,0,0));
    $q = mswSQL_query("SELECT
         count(*) AS `dC`,
         MONTH(FROM_UNIXTIME(`ts`)) AS `dM`,
         YEAR(FROM_UNIXTIME(`ts`)) AS `dY`
         FROM `" . DB_PREFIX . "replies`
         WHERE YEAR(FROM_UNIXTIME(`ts`)) IN('{$this->years[0]}','{$this->years[1]}')
         AND `replyType` = 'admin'
		     AND `replyUser` = '{$id}'
		     GROUP BY YEAR(FROM_UNIXTIME(`ts`)), MONTH(FROM_UNIXTIME(`ts`))
         ", __file__, __line__);
    while ($G = mswSQL_fetchobj($q)) {
      switch($G->dY) {
        case $this->years[0]:
          $arr[0][($G->dM - 1)] = mswNFM($G->dC);
          break;
        case $this->years[1]:
          $arr[1][($G->dM - 1)] = mswNFM($G->dC);
          break;
      }
    }
    return array(
      implode(',', $arr[0]),
      implode(',', $arr[1])
    );
  }

  public function home($filter) {
    $arr = array(array(0,0,0,0,0,0,0,0,0,0,0,0),array(0,0,0,0,0,0,0,0,0,0,0,0));
    $q = mswSQL_query("SELECT
         count(*) AS `dC`,
         MONTH(FROM_UNIXTIME(`ts`)) AS `dM`,
         YEAR(FROM_UNIXTIME(`ts`)) AS `dY`
         FROM `" . DB_PREFIX . "tickets`
         WHERE YEAR(FROM_UNIXTIME(`ts`)) IN('{$this->years[0]}','{$this->years[1]}')
         AND `assignedto` != 'waiting'
	       AND `spamFlag` = 'no'
		     " . mswSQL_deptfilter($filter) . "
		     GROUP BY YEAR(FROM_UNIXTIME(`ts`)), MONTH(FROM_UNIXTIME(`ts`))
         ", __file__, __line__);
    while ($G = mswSQL_fetchobj($q)) {
      switch($G->dY) {
        case $this->years[0]:
          $arr[0][($G->dM - 1)] = mswNFM($G->dC);
          break;
        case $this->years[1]:
          $arr[1][($G->dM - 1)] = mswNFM($G->dC);
          break;
      }
    }
    return array(
      implode(',', $arr[0]),
      implode(',', $arr[1])
    );
  }

}

?>