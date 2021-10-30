<?php

/* CONTROL BUTTONS FOR POPUPS
---------------------------------------------*/

$c_b = '';

if (!empty($json['buttons'])) {
  $c_b  = '<hr>';
  $c_b .= '<div class="controlButtons">';
  $c_b .= implode('&nbsp;&nbsp;/&nbsp;&nbsp;', $json['buttons']);
  $c_b .= '</div>';
}

?>