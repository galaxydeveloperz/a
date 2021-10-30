<?php

/* NAVIGATION
----------------------------------------------------------*/

if (!defined('PARENT')) {
	$HEADERS->err403(true);
}

$slidePanelLeftMenu = array();
$footerSlideMenu    = '';
$msTopMenu          = array();
$defNavOrder        = array(
  'tickets','dept','status','levels','fields','imap','staff',
  'accounts','stanresp','pages','faq','apages','settings'
);

/* TOP MENU BAR
========================================================*/

if (LICENCE_VER == 'locked' || defined('LIC_DEV')) {
  $msTopMenu[] = array(
    'url' => 'index.php?p=purchase',
    'icon' => 'fa-shopping-basket',
    'text' => 'Purchase',
    'class' => 'hidden-sm hidden-xs',
    'ext' => 'no'
  );
}

$msTopMenu[] = array(
  'url' => 'index.php',
  'icon' => 'fa-dashboard',
  'text' => $msg_adheader11,
  'class' => 'hidden-sm hidden-xs',
  'ext' => 'no'
);

if ($MSTEAM->mailbox == 'yes') {
  $msTopMenu[] = array(
    'url' => 'index.php?p=mailbox',
    'icon' => 'fa-envelope',
    'text' => $msg_adheader61,
    'class' => 'hidden-sm hidden-xs',
    'ext' => 'no'
  );
}

if (USER_ADMINISTRATOR == 'yes' || $MSTEAM->helplink == 'yes') {
  $msTopMenu[] = array(
    'url' => '../docs/' . (isset($_GET['t_status']) ? 'statuspage.html' : (isset($_GET['p']) ? helpPageLoader($_GET['p']) . '.html' : 'admin-home.html')),
    'icon' => 'fa-question-circle',
    'text' => $msg_adheader12,
    'class' => 'hidden-sm hidden-xs',
    'ext' => 'yes'
  );
}

$msTopMenu[] = array(
  'url' => 'index.php?p=logout',
  'icon' => 'fa-unlock',
  'text' => $msg_adheader10,
  'class' => 'hidden-sm hidden-xs',
  'ext' => 'no'
);


/* NAVIGATIONAL MENU
========================================================*/

$nMenu = ($SETTINGS->navmenu ? unserialize($SETTINGS->navmenu) : $defNavOrder);

if (!empty($nMenu)) {
  foreach (($SETTINGS->navmenu ? array_keys($nMenu) : $nMenu) AS $load_menu_file) {
    if (file_exists(PATH . 'control/nav/' . $load_menu_file . '.php')) {
      include_once(PATH . 'control/nav/' . $load_menu_file . '.php');
    }
  }
}

?>