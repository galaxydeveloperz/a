<?php

/* Fixes for skipped versions
-------------------------------------------------------*/

if (mswCheckColumn('departments', 'days') == 'yes') {
  $query = mswSQL_query("update `" . DB_PREFIX . "departments` set `days` = replace(`days`,'Thur','Thu')", __file__, __line__);
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'departments', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in departments: days', 'instruction');
  }
}

?>