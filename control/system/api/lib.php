<?php

/* Incoming data
------------------------------------------------------*/

if (!defined('API_LOADER')) {
  exit;
}

$data = urldecode(file_get_contents('php://input'));

?>