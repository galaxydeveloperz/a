<?php if (!defined('PARENT')) { exit; }
$SQL         = '';

if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'order_asc';
}

if (isset($_GET['orderby'])) {
  switch ($_GET['orderby']) {
    // Title (ascending)..
    case 'title_asc':
	    $orderBy = 'ORDER BY `title`';
	    break;
	  // Title (descending)..
    case 'title_desc':
	    $orderBy = 'ORDER BY `title` desc';
	    break;
	  // Order Sequence (ascending)..
    case 'order_asc':
	    $orderBy = 'ORDER BY `orderBy`';
	    break;
	  // Order Sequence (descending)..
    case 'order_desc':
	    $orderBy = 'ORDER BY `orderBy` desc';
	    break;
  }
}

if (isset($_GET['dept'])) {
  if ($_GET['dept']=='disabled') {
    $SQL          = 'WHERE `enResponse` = \'no\'';
  } else {
    $_GET['dept'] = (int) $_GET['dept'];
    $SQL          = 'WHERE FIND_IN_SET(\'' . $_GET['dept'] . '\',`departments`) > 0';
  }
}

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSQL(strtolower($_GET['keys']));
  $SQL           = 'WHERE LOWER(`title`) LIKE \'%' . $_GET['keys'] . '%\' OR LOWER(`answer`) LIKE \'%' . $_GET['keys'] . '%\'';
}

$q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS * FROM `" . DB_PREFIX . "responses`
     $SQL
		 $orderBy
		 $sqlLimStr
		 ", __file__, __line__);
$c            = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
$sqlQryRows  =  (isset($c->rows) ? $c->rows : '0');
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (isset($_GET['keys'])) {
      ?>
      <li><a href="?p=responseman"><?php echo $msg_adheader54; ?></a></li>
      <li class="active"><?php echo $mspubliclang3_7[7]; ?> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      } else {
      ?>
      <li class="active"><a href="?p=responseman"><?php echo $msg_adheader54; ?></a> (<?php echo mswNFM($sqlQryRows); ?>)</li>
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
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <?php
            if (USER_ADMINISTRATOR == 'yes' || in_array('standard-responses', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=standard-responses')"><i class="fa fa-plus fa-fw"></i></button>
            <?php
            }
            if (in_array(MSW_PFDTCT, array('mobile', 'tablet'))) {
            ?>
            <button class="btn btn-primary btn-sm" type="button" onclick="mswToggleButton('filters')"><i class="fa fa-sort-amount-asc fa-fw"></i></button>
            <button class="btn btn-info btn-sm" type="button" onclick="mswToggleButton('search')"><i class="fa fa-search fa-fw"></i></button>
            <div class="hidetkfltrs" style="display:none">
            <hr>
            <?php
            }
            // Order By..
            $links = array(
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=title_asc' . mswQueryParams(array('p','orderby')),  'name' => $msg_response23, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'title_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=title_desc' . mswQueryParams(array('p','orderby')), 'name' => $msg_response24, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'title_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_asc' . mswQueryParams(array('p','orderby')),  'name' => $msg_levels23,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'order_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_desc' . mswQueryParams(array('p','orderby')), 'name' => $msg_levels24,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'order_desc' ? ' class="active"' : ''))
            );
            echo $MSBOOTSTRAP->button(array(
              'text' => $msg_script45,
              'links' => $links,
              'orientation' => ' dropdown-menu-right',
              'centered' => 'yes',
              'area' => 'admin',
              'icon' => 'sort',
              'param' => 'orderby'
            ));
            // Filters..
            $links   = array(array('link' => '?p='.$_GET['p'].mswQueryParams(array('p','dept','next')), 'name' => $msg_response25));
            $q_dept  = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "departments` " . mswSQL_deptfilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`", __file__, __line__);
            while ($DEPT = mswSQL_fetchobj($q_dept)) {
              $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;dept='.$DEPT->id.mswQueryParams(array('p','dept','next')), 'name' => mswSH($DEPT->name),   'active' => (isset($_GET['dept']) && $_GET['dept'] == $DEPT->id ? ' class="active"' : ''));
            }
            $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;dept=disabled' . mswQueryParams(array('p','dept','next')), 'name' => $msg_response27,   'active' => (isset($_GET['dept']) && $_GET['dept'] == 'disabled' ? ' class="active"' : ''));
            echo $MSBOOTSTRAP->button(array(
              'text' => $msg_search20,
              'links' => $links,
              'orientation' => ' dropdown-menu-right',
              'centered' => 'no',
              'area' => 'admin',
              'icon' => 'cogs',
              'param' => 'dept'
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
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_customfields; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_response; ?></th>
                  <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
                </tr>
              </thead>
              <tbody>
                <?php
                if ($sqlQryRows > 0) {
                $totalR = mswSQL_rows('responses');
                while ($SR = mswSQL_fetchobj($q)) {
                ?>
                <tr id="datatr_<?php echo $SR->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes') {
                ?>
                <td>
                <input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $SR->id; ?>">
                </td>
                <?php
                }
                ?>
                <td>
                <select name="order[<?php echo $SR->id; ?>]" class="form-control">
                <?php
                for ($i=1; $i<($totalR+1); $i++) {
                ?>
                <option value="<?php echo $i; ?>" <?php echo mswSelectedItem($SR->orderBy,$i,false); ?>>
                <?php echo $i; ?>
                </option>
                <?php
                }
                ?>
                </select>
                </td>
                <td><b><?php echo mswSH($SR->title); ?></b>
                <span class="tdCellInfo">
                <?php echo ($SR->departments != null && $SR->departments != '' ? mswSrCat($SR->departments) : $msg_response6); ?>
                </span>
                </td>
                <td class="text-right">
                  <i class="fa fa-<?php echo ($SR->enResponse=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($SR->enResponse=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'srstate','<?php echo $SR->id; ?>')" title="<?php echo mswSH($msg_response28); ?>"></i>
                  <a href="?p=standard-responses&amp;edit=<?php echo $SR->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                  <a href="?p=<?php echo $_GET['p']; ?>&amp;view=<?php echo $SR->id; ?>" title="<?php echo mswSH($msg_response12); ?>" onclick="iBox.showURL(this.href,'',{width:<?php echo IBOX_RESPONSE_WIDTH; ?>,height:<?php echo IBOX_RESPONSE_HEIGHT; ?>});return false"><i class="fa fa-search fa-fw"></i></a>
                  </td>
                </tr>
                <?php
                }
                } else {
                ?>
                <tr class="warning nothing_to_see">
                <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>">
                <?php echo $msg_response9; ?>
                </td>
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
          <button onclick="mswButtonOp('srdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels9; ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('srseq')"><i class="fa fa-sort-numeric-asc fa-fw" title="<?php echo mswSH($msg_levels8); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels8; ?></span></button>
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