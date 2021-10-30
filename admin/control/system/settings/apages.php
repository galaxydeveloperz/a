<?php

/* Admin - System Module
----------------------------------------------------------*/

if (!defined('PARENT')) {
  $HEADERS->err403(true);
}

// View
if (isset($_GET['view'])) {
  $PG = mswSQL_table('admin_pages', 'id', (int) $_GET['view']);
  if (isset($PG->id)) {
    if (USER_ADMINISTRATOR == 'yes' ||
        in_array('apages&view=' . $PG->id, $userAccess) ||
        in_array('apages&amp;view=' . $PG->id, $userAccess)
        ) {
      if ($PG->enPage == 'yes') {
        // Check permissions..
        $perms = explode(',', $PG->accounts);
        if (USER_ADMINISTRATOR == 'yes' || in_array('all', $perms) || in_array($MSTEAM->id, $perms)) {
          $title = mswSH($PG->title);
          include(PATH . 'templates/header.php');
          if ($PG->tmp && file_exists(PATH . 'templates/admin-pages/' . $PG->tmp)) {
            include(PATH . 'templates/admin-pages/' . $PG->tmp);
          } else {
            include(PATH . 'templates/system/settings/apages-view.php');
          }
          include(PATH . 'templates/footer.php');
        } else {
          $HEADERS->err403(true);
        }
      } else {
        $HEADERS->err403(true);
      }
    } else {
      $HEADERS->err403(true);
    }
  }
  exit;
}

// Access..
if (!in_array($cmd, $userAccess) && USER_ADMINISTRATOR != 'yes') {
  $HEADERS->err403(true);
}

$title           = (isset($_GET['edit']) ? $msadminpages4_3[1] : $msadminpages4_3[0]);
$textareaFullScr = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/settings/apages.php');
include(PATH . 'templates/footer.php');

?>