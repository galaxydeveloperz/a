    <?php
		$_GET['p'] = (isset($_GET['p']) ? $_GET['p'] : 'x');
    if (!defined('PARENT') || (!isset($_GET['t_status']) && !in_array($_GET['p'],array('open','close','disputes','cdisputes','search','search-fields','assign','acchistory','spam')))) { exit; }

		//===========================================
		// DEPARTMENT FILTERS
		//===========================================

		$links   = array();
    $links[] = array('link' => '?p=' . $_GET['p'] . mswQueryParams(array('p','dept','next')),'name' => $msg_open2);
    $q_dept  = mswSQL_query("SELECT `id`,`name` FROM `" . DB_PREFIX . "departments` " . mswSQL_deptfilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`", __file__, __line__);
    while ($DEPT = mswSQL_fetchobj($q_dept)) {
		  $links[] = array(
		    'link' => '?p=' . $_GET['p'] . '&amp;dept=' . $DEPT->id . mswQueryParams(array('p','dept','next')),
		    'name' => mswCD($DEPT->name),
		    'active' => (isset($_GET['dept']) && $_GET['dept'] != '0' && $_GET['dept'] == $DEPT->id ? ' class="active"' : '')
		  );
    }

		//=========================================================
		// SHOW ALL ASSIGNED USERS IN FILTER IF PERMISSIONS ALLOW
		//=========================================================

    if ($_GET['p'] != 'assign') {
      if (!defined('HIDE_ASSIGN_FILTERS') && (USER_ADMINISTRATOR == 'yes' || in_array('assign', $userAccess))) {
        $links[] = array(
          'link' => 'sep',
          'name' => ''
        );
  		  $q_users  = mswSQL_query("SELECT `id`,`name` FROM `" . DB_PREFIX . "users` ORDER BY `name`", __file__, __line__);
        while ($U = mswSQL_fetchobj($q_users)) {
  		    $links[] = array(
  		      'link' => '?p=' . $_GET['p'] . '&amp;dept=u' . $U->id . mswQueryParams(array('p','dept','next')),
  		      'name' => $msg_open31 . ' ' . mswSH($U->name),
  		      'active' => (isset($_GET['dept']) && $_GET['dept'] != '0' && $_GET['dept'] == 'u' . $U->id ? ' class="active"' : '')
  		    );
        }
  		}
		}
		echo $MSBOOTSTRAP->button(array(
      'text' => $msg_viewticket107,
      'links' => $links,
      'orientation' => ' dropdown-menu-right',
      'centered' => 'yes',
      'area' => 'admin',
      'icon' => 'briefcase',
      'param' => 'dept'
    ));
    
    ?>