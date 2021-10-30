<?php if (!defined('PARENT')) { exit; }
	  $_GET['p'] = (isset($_GET['p']) ? $_GET['p'] : 'x');

	  //=============================
	  // LIMIT OPTIONS
	  //=============================

	  $links = array();
	  foreach (array(10, 20, 30, 40, 50, 75, 100, 150, 200, 250, 300, 500, 'all') AS $k) {
	    $links[] = array(
	      'link' => '?p=' . $_GET['p'] . '&amp;limit=' . $k . mswQueryParams(array('p','limit','next')),
	      'name' => ($k == 'all' ? $msadminlang4_3[3] : $k . ' ' . $msg_script50),
	      'active' => (isset($_GET['limit']) && $_GET['limit'] == $k ? ' class="active"' : '')
	    );
	  }
	  echo $MSBOOTSTRAP->button(array(
      'text' => $msg_script51,
      'links' => $links,
      'orientation' => ' dropdown-menu-right',
      'centered' => 'yes',
      'area' => 'admin',
      'icon' => '',
      'param' => 'limit'
    ));
    if (!defined('SKIP_SEARCH_BOX') && MSW_PFDTCT != 'mobile') {
	  ?>
    <button class="btn btn-info btn-sm" type="button" onclick="mswToggleButton('search')"><i class="fa fa-search fa-fw"></i></button>
    <?php
    }
    ?>