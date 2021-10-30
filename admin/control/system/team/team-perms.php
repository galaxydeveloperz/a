<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT') || !isset($MSTEAM->id) || !isset($userAccess)) {
  $HEADERS->err403(true);
}

define('USER_ADMINISTRATOR', ($MSTEAM->id == '1' || $MSTEAM->admin == 'yes' ? 'yes' : 'no'));

$userDeptAccess      = mswGetDepartmentAccess($MSTEAM->id);
$mswDeptFilterAccess = mswDeptFilterAccess($MSTEAM, $userDeptAccess, 'department');
$ticketFilterAccess  = mswDeptFilterAccess($MSTEAM, $userDeptAccess, 'tickets');
$ePerms              = ($MSTEAM->editperms ? unserialize($MSTEAM->editperms) : array());

define('USER_DEL_PRIV', ($MSTEAM->id == '1' || $MSTEAM->admin == 'yes' ? 'yes' : $MSTEAM->delPriv));
define('USER_EDIT_T_PRIV', ($MSTEAM->id == '1' || $MSTEAM->admin == 'yes' || in_array('ticket', $ePerms) ? 'yes' : 'no'));
define('USER_EDIT_R_PRIV', ($MSTEAM->id == '1' || $MSTEAM->admin == 'yes' || in_array('reply', $ePerms) ? 'yes' : 'no'));
define('USER_CLOSE_PRIV', ($MSTEAM->id == '1' || $MSTEAM->admin == 'yes' || $MSTEAM->close == 'yes' ? 'yes' : 'no'));
define('USER_LOCK_PRIV', ($MSTEAM->id == '1' || $MSTEAM->admin == 'yes' || $MSTEAM->lock == 'yes' ? 'yes' : 'no'));

?>