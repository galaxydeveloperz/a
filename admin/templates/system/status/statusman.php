<?php if (!defined('PARENT')) { exit; }
$SQL = '';

if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'order_asc';
}
$orderBy  = 'ORDER BY `orderBy`';

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
  $SQL           = 'WHERE LOWER(`name`) LIKE \'%' . $_GET['keys'] . '%\'';
}

$q  = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS *,
      (SELECT count(*) FROM `" . DB_PREFIX . "tickets`
			WHERE (`" . DB_PREFIX . "statuses`.`id` = `" . DB_PREFIX . "tickets`.`ticketStatus` AND `spamFlag` = 'no')
			OR (`" . DB_PREFIX . "statuses`.`marker` = `" . DB_PREFIX . "tickets`.`ticketStatus` AND `spamFlag` = 'no')
			) AS `tickCount`
      FROM `" . DB_PREFIX . "statuses`
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
      <li><a href="?p=statusman"><?php echo $msticketstatuses4_3[2]; ?></a></li>
      <li class="active"><?php echo $mspubliclang3_7[7]; ?> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      } else {
      ?>
      <li class="active"><a href="?p=statusman"><?php echo $msticketstatuses4_3[2]; ?></a> (<?php echo mswNFM($sqlQryRows); ?>)</li>
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
            if (USER_ADMINISTRATOR == 'yes' || in_array('status', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=status')"><i class="fa fa-plus fa-fw"></i></button>
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
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_asc' . mswQueryParams(array('p','orderby','next')),     'name' => $msg_levels21,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'name_asc' ? ' class="active"' : '')),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_desc' . mswQueryParams(array('p','orderby','next')),    'name' => $msg_levels22,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'name_desc' ? ' class="active"' : '')),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_asc' . mswQueryParams(array('p','orderby','next')),    'name' => $msg_levels23,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'order_asc' ? ' class="active"' : '')),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_desc' . mswQueryParams(array('p','orderby','next')),   'name' => $msg_levels24,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'order_desc' ? ' class="active"' : '')),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=tickets_desc' . mswQueryParams(array('p','orderby','next')), 'name' => $msg_accounts11, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'tickets_desc' ? ' class="active"' : '')),
             array('link' => '?p=' . $_GET['p'] . '&amp;orderby=tickets_asc' . mswQueryParams(array('p','orderby','next')),  'name' => $msg_accounts12, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'tickets_asc' ? ' class="active"' : ''))
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
                 <th><?php echo TABLE_HEAD_DECORATION . $msticketstatuses4_3[5]; ?></th>
                 <th><?php echo TABLE_HEAD_DECORATION . $msg_accounts3; ?></th>
                 <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
                <?php
                if ($sqlQryRows > 0) {
                while ($STATUS = mswSQL_fetchobj($q)) {
                ?>
                <tr id="datatr_<?php echo $STATUS->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes') {
                if ($STATUS->id>3) {
                ?>
                <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $STATUS->id; ?>"></td>
                <?php
                } else {
                ?>
                <td>&nbsp;</td>
                <?php
                }
                }
                ?>
                <td><?php echo mswPRMarker(($STATUS->marker ? $STATUS->marker : $STATUS->id), $STATUS->colors); ?></td>
                <td>
                <select name="order[<?php echo $STATUS->id; ?>]" class="form-control">
                  <?php
                  for ($i=1; $i<($sqlQryRows+1); $i++) {
                  ?>
                  <option value="<?php echo $i; ?>"<?php echo mswSelectedItem($STATUS->orderBy,$i,false); ?>><?php echo $i; ?></option>
                  <?php
                  }
                  ?>
                  </select>
                </td>
                <td><b><?php echo mswSH($STATUS->name); ?></b>
                <span class="tdCellInfo">
                  <?php echo ($STATUS->id>3 ? $msticketstatuses4_3[13] . ': ' . ($STATUS->visitor=='yes' ? $msg_script4 : $msg_script5) : '--'); ?>
                </span>
                </td>
                <td><a href="?p=search&amp;keys=&amp;status=<?php echo ($STATUS->marker ? $STATUS->marker : $STATUS->id); ?>" title="<?php echo mswNFM($STATUS->tickCount); ?>"><?php echo mswNFM($STATUS->tickCount); ?></a></td>
                <td class="text-right">
                  <a href="?p=status&amp;edit=<?php echo $STATUS->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                </td>
                </tr>
                <?php
                }
                } else {
                ?>
                <tr class="warning nothing_to_see">
                 <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '6' : '5'); ?>"><?php echo $msticketstatuses4_3[9]; ?></td>
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
          <button onclick="mswButtonOp('statdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels9; ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('statseq')"><i class="fa fa-sort-numeric-asc fa-fw" title="<?php echo mswSH($msg_levels8); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels8; ?></span></button>
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