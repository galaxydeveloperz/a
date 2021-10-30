    <?php
	  $_GET['p'] = (isset($_GET['p']) ? $_GET['p'] : 'x');
    if (!defined('PARENT') || (!isset($_GET['t_status']) && !in_array($_GET['p'],array('open','close','disputes','cdisputes','search','search-fields','assign','acchistory','spam')))) { exit; }

	  //=============================
	  // ORDER BY OPTIONS
	  //=============================

	  $links = array(array('link' => '?p='.$_GET['p'].mswUrlApp('dept').mswQueryParams(array('p','dept','priority','status')),  'name' => $msg_open3));
	  foreach ($ticketLevelSel AS $k => $v) {
	    $links[] = array(
	      'link' => '?p=' . $_GET['p'] . '&amp;priority=' . $k . mswUrlApp('dept').mswQueryParams(array('p','dept','priority','status')),
	      'name' => $v,
	      'active' => (isset($_GET['priority']) && $_GET['priority'] == $k ? ' class="active"' : '')
	    );
    }
    
    //=============================
	  // SHOW STATUS OPTIONS
	  //=============================

	  if (in_array($_GET['p'],array('open','search','acchistory','search-fields'))) {
	    $links[] = array(
        'link' => 'sep',
        'name' => ''
      );
      foreach ($ticketStatusSel AS $sk => $sv) {
        $links[] = array(
    		  'link' => '?p=' . $_GET['p'] . '&amp;status=' . $sk . mswQueryParams(array('p','status')),
    		  'name' => $sv[0],
    		  'active' => (isset($_GET['status']) && $_GET['status'] == $sk ? ' class="active"' : '')
    		);
      }
    }
	  echo $MSBOOTSTRAP->button(array(
      'text' => $msg_search20,
      'links' => $links,
      'orientation' => ' dropdown-menu-right',
      'centered' => 'no',
      'area' => 'admin',
      'icon' => 'hourglass',
      'param' => 'status',
      'param2' => 'priority'
    ));
	  ?>