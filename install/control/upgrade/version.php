<?php

/* Version Update
-------------------------------------------*/

$query = mswSQL_query("UPDATE `" . DB_PREFIX . "settings` SET `softwareVersion` = '" . SCRIPT_VERSION . "'", __file__, __line__);
if ($query === 'err') {
  $ERR      = mswSQL_error(true);
  mswUpLog(DB_PREFIX . 'settings', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
}

?>