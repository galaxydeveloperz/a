<?php

/* System - Accounts
----------------------------------------------------------*/

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Session vars..exist only on initial load..
$ID    = ($SSN->active('create_id') == 'yes' ? (int) $SSN->get('create_id') : '0');
$pass  = ($SSN->active('create_pass') == 'yes' ? $SSN->get('create_pass') : '');
$email = ($SSN->active('create_email') == 'yes' ? $SSN->get('create_email') : '');
$tickn = ($SSN->active('create_tickno') == 'yes' ? $SSN->get('create_tickno') : '');

if ($ID > 0) {

  $title = $msg_main2 . ' (' . $msg_public_ticket4 . ')';

  include(PATH . 'control/header.php');

  $tpl = new Savant3();
  $tpl->assign('TXT', array(
    $msg_public_ticket4,
    $msg_newticket13,
    str_replace(array(
      '{ticket}',
      '{ticket_long}'
    ), array(
      $ID,
      mswTicketNumber($ID, $SETTINGS->minTickDigits, $tickn)
    ), $msg_public_ticket5),
    $msg_public_ticket6
  ));
  $tpl->assign('ADD_TXT', ($pass ? str_replace(array(
    '{email}',
    '{pass}',
    '{url}'
  ), array(
    mswSH($email),
    mswSH($pass),
    $SETTINGS->scriptpath
  ), $msg_public_ticket7) : ''));
  $tpl->assign('ID', $ID);

  // Global vars..
  include(PATH . 'control/lib/global.php');

  $tpl->display('content/' . MS_TEMPLATE_SET . '/ticket-create-message.tpl.php');

  include(PATH . 'control/footer.php');

  // Reset session vars..
  $SSN->delete(array('create_id', 'create_email', 'create_pass', 'create_tickno'));

} else {

  $HEADERS->err403();

}

?>