    <?php
	  $_GET['p'] = (isset($_GET['p']) ? $_GET['p'] : 'x');
    if (!defined('PARENT') || (!isset($_GET['t_status']) && !in_array($_GET['p'],array('open','close','disputes','cdisputes','search','search-fields','assign','acchistory','spam')))) { exit; }

	  //=============================
	  // ORDER BY OPTIONS
	  //=============================

	  $links = array();
	  foreach ($msg_script44 AS $k => $v) {
	    $links[] = array(
	      'link' => '?p=' . $_GET['p'] . '&amp;orderby=' . $k . mswUrlApp('dept').mswQueryParams(array('p','orderby','dept')),
	      'name' => $v,
	      'active' => (isset($_GET['orderby']) && $_GET['orderby'] == $k ? ' class="active"' : '')
	    );
	  }
	  echo $MSBOOTSTRAP->button(array(
      'text' => $msg_script45,
      'links' => $links,
      'orientation' => ' dropdown-menu-right',
      'centered' => 'no',
      'area' => 'admin',
      'icon' => 'sort',
      'param' => 'orderby'
    ));
	  ?>