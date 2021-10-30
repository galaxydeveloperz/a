<?php if (!defined('PARENT')) { exit; }
$SQL         = '';

if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'order_asc';
}
$orderBy      = 'ORDER BY `orderBy`';

if (isset($_GET['orderby'])) {
  switch ($_GET['orderby']) {
    // Name (ascending)..
    case 'name_asc':
	    $orderBy = 'ORDER BY `name`';
	    break;
	  // Name (descending)..
    case 'name_desc':
	    $orderBy = 'ORDER BY `name` desc';
	    break;
	  // Order Sequence (ascending)..
    case 'order_asc':
	    $orderBy = 'ORDER BY `orderBy`';
	    break;
	  // Order Sequence (descending)..
    case 'order_desc':
	    $orderBy = 'ORDER BY `orderBy` desc';
	    break;
	  // Manually Assign (ascending)..
    case 'man_asc':
	    $orderBy = 'ORDER BY FIELD(`manual_assign`,\'yes\',\'no\')';
	    break;
	  // Manually Assign (descending)..
    case 'man_desc':
	    $orderBy = 'ORDER BY FIELD(`manual_assign`,\'no\',\'yes\')';
	    break;
	  // Visibility (ascending)..
    case 'vis_asc':
	    $orderBy = 'ORDER BY FIELD(`showDept`,\'yes\',\'no\')';
	    break;
	  // Visibility (descending)..
    case 'vis_desc':
	    $orderBy = 'ORDER BY FIELD(`showDept`,\'no\',\'yes\')';
	    break;
	  // Most tickets..
    case 'tickets_desc':
	    $orderBy = 'ORDER BY `tickCount` desc';
	    break;
	  // Least tickets..
    case 'tickets_asc':
	    $orderBy = 'ORDER BY `tickCount`';
	    break;
  }
}

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSQL(strtolower($_GET['keys']));
  $SQL           = (mswSQL_deptfilter($mswDeptFilterAccess,'WHERE') ? ' AND ' : 'WHERE ').' LOWER(`name`) LIKE \'%' . $_GET['keys'] . '%\'';
}

$q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS *,
     (SELECT count(*) FROM `" . DB_PREFIX . "tickets`
		 WHERE `" . DB_PREFIX . "departments`.`id` = `" . DB_PREFIX . "tickets`.`department`
		 AND `spamFlag` = 'no'
     ) AS `tickCount`
     FROM `" . DB_PREFIX . "departments` " . mswSQL_deptfilter($mswDeptFilterAccess, 'WHERE') . "
     $SQL
     $orderBy
     $sqlLimStr
     ", __file__, __line__);
$c            = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
$sqlQryRows  = (isset($c->rows) ? $c->rows : '0');
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (isset($_GET['keys'])) {
      ?>
      <li><a href="?p=deptman"><?php echo $msg_dept9; ?></a></li>
      <li class="active"><?php echo $mspubliclang3_7[7]; ?> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      } else {
      ?>
      <li class="active"><a href="?p=deptman"><?php echo $msg_dept9; ?></a> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      }
      ?>
    </ol>

    <?php
    // Search..
    include(PATH . 'templates/system/bootstrap/search-box.php');
    ?>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <?php
            if (USER_ADMINISTRATOR == 'yes' || in_array('dept', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=dept')"><i class="fa fa-plus fa-fw"></i></button>
            <?php
            }
            // Order By..
            // For small screen devices, we hide the filters to give us more screen space..
            if (in_array(MSW_PFDTCT, array('mobile', 'tablet'))) {
            ?>
            <button class="btn btn-primary btn-sm" type="button" onclick="mswToggleButton('filters')"><i class="fa fa-sort-amount-asc fa-fw"></i></button>
            <button class="btn btn-info btn-sm" type="button" onclick="mswToggleButton('search')"><i class="fa fa-search fa-fw"></i></button>
            <div class="hidetkfltrs" style="display:none">
            <hr>
            <?php
            }
            $links = array(
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_asc' . mswQueryParams(array('p','orderby')),     'name' => $msg_levels21,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'name_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_desc' . mswQueryParams(array('p','orderby')),    'name' => $msg_levels22,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'name_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_asc' . mswQueryParams(array('p','orderby')),    'name' => $msg_levels23,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'order_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_desc' . mswQueryParams(array('p','orderby')),   'name' => $msg_levels24,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'order_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=man_asc' . mswQueryParams(array('p','orderby')),      'name' => $msg_dept26,     'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'man_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=man_desc' . mswQueryParams(array('p','orderby')),     'name' => $msg_dept27,     'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'man_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=vis_asc' . mswQueryParams(array('p','orderby')),      'name' => $msg_dept28,     'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'vis_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=vis_desc' . mswQueryParams(array('p','orderby')),     'name' => $msg_dept29,     'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'vis_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=tickets_desc' . mswQueryParams(array('p','orderby')), 'name' => $msg_accounts11, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'tickets_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=tickets_asc' . mswQueryParams(array('p','orderby')),  'name' => $msg_accounts12, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'tickets_asc' ? ' class="active"' : ''))
            );
            echo $MSBOOTSTRAP->button(array(
              'text' => $msg_script45,
              'links' => $links,
              'orientation' => '',
              'centered' => 'no',
              'area' => 'admin',
              'icon' => '',
              'param' => 'orderby'
            ));
            // Page filter..
            include(PATH . 'templates/system/bootstrap/page-filter.php');
            if (in_array(MSW_PFDTCT, array('mobile', 'tablet'))) {
            ?>
            </div>
            <?php
            }
            ?>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <?php
                  if (USER_DEL_PRIV == 'yes') {
                  ?>
                  <th style="width:5%">
                    <input type="checkbox" onclick="mswCheckBoxes(this.checked,'.panel-body');mswCheckCount('panel-body','delButton','mswCVal')">
                  </th>
                  <?php
                  }
                  ?>
                  <th><?php echo TABLE_HEAD_DECORATION . $msadminlang3_7prlevels[3]; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_customfields; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_dept19; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_accounts3; ?></th>
                  <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
                </tr>
              </thead>
              <tbody>
                <?php
                if ($sqlQryRows > 0) {
                while ($DEPT = mswSQL_fetchobj($q)) {
                ?>
                <tr id="datatr_<?php echo $DEPT->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes' && mswSQL_rows('tickets WHERE `department` = \'' . $DEPT->id . '\'')==0) {
                ?>
                <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $DEPT->id; ?>"></td>
                <?php
                } else {
                ?>
                <td>&nbsp;</td>
                <?php
                }
                ?>
                <td><?php echo $DEPT->id; ?></td>
                <td><select name="order[<?php echo $DEPT->id; ?>]" class="form-control">
                <?php
                for ($i=1; $i<($sqlQryRows+1); $i++) {
                ?>
                <option value="<?php echo $i; ?>"<?php echo mswSelectedItem($DEPT->orderBy, $i, false); ?>><?php echo $i; ?></option>
                <?php
                }
                ?>
                </select></td>
                <td><b><?php echo mswSH($DEPT->name,false); ?></b>
                <span class="tdCellInfo">
                <?php
                $whatsOn = array($msg_script5,$msg_script5);
                if ($DEPT->showDept=='yes') {
                  $whatsOn[0] = $msg_script4;
                }
                if ($DEPT->manual_assign=='yes') {
                  $whatsOn[1] = $msg_script4;
                }
                echo str_replace(array('{manual}','{visible}'),array($whatsOn[1],$whatsOn[0]),$msg_dept23); ?>
                </span>
                </td>
                <td><a href="?p=search&amp;keys=&amp;dept=<?php echo $DEPT->id; ?>" title="<?php echo mswNFM($DEPT->tickCount); ?>"><?php echo mswNFM($DEPT->tickCount); ?></a></td>
                <td class="text-right">
                  <a href="?p=dept&amp;edit=<?php echo $DEPT->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                </td>
               </tr>
               <?php
               }
               } else {
               ?>
               <tr class="warning nothing_to_see">
                <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '6' : '5'); ?>"><?php echo $msg_dept8; ?></td>
               </tr>
               <?php
               }
               ?>
              </tbody>
              </table>
            </div>

          </div>

          <?php
	        if ($sqlQryRows > 0) {
          ?>
          <div class="panel-footer">
          <?php
	        if (USER_DEL_PRIV == 'yes') {
	        ?>
          <button onclick="mswButtonOp('depdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels9; ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('deptseq')"><i class="fa fa-sort-numeric-asc fa-fw" title="<?php echo mswSH($msg_levels8); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels8; ?></span></button>
          </div>
	        <?php
	        }
          ?>
        </div>

        <?php
        if ($sqlLimStr && $sqlQryRows > 0 && $sqlQryRows > $limit) {
          define('PER_PAGE', $limit);
          $PGS = new pagination(array($sqlQryRows, $msg_script42, $page),'?p=' . $_GET['p'] . '&amp;next=');
          echo $PGS->display();
        }
        ?>

      </div>
    </div>
    </form>

  </div>