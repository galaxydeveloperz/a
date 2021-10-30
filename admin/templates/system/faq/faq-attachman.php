<?php if (!defined('PARENT')) { exit; }
$SQL = '';

if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'order_asc';
}
$orderBy = 'ORDER BY `orderBy`';

if (isset($_GET['orderby'])) {
  switch ($_GET['orderby']) {
    // Cat Name (ascending)..
    case 'name_asc':
	    $orderBy = 'ORDER BY `name`';
	    break;
	  // Cat Name (descending)..
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
	  // Most questions..
    case 'questions_desc':
	    $orderBy = 'ORDER BY `queCount` desc';
	    break;
	  // Least questions..
    case 'questions_asc':
	    $orderBy = 'ORDER BY `queCount`';
	    break;
  }
}

if (isset($_GET['opt'])) {
  switch ($_GET['opt']) {
    case 'disabled':
	    $SQL = 'WHERE `enAtt` = \'no\'';
	    break;
	  case 'remote':
      $SQL = 'WHERE `path` = \'\'';
	    break;
  }
}

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSQL(strtolower($_GET['keys']));
  $SQL           = 'WHERE LOWER(`name`) LIKE \'%' . $_GET['keys'] . '%\' OR LOWER(`remote`) LIKE \'%' . $_GET['keys'] . '%\' OR LOWER(`path`) LIKE \'%' . $_GET['keys'] . '%\'';
} else {
  // Are we showing attachments only allocated to a question?
  if (isset($_GET['question'])) {
    $_GET['question'] = (int)$_GET['question'];
	  $attachIDs  = array();
	  $qA = mswSQL_query("SELECT `itemID` FROM `" . DB_PREFIX . "faqassign`
	        WHERE `question` = '{$_GET['question']}'
          AND `desc`       = 'attachment'
          GROUP BY `itemID`
          ", __file__, __line__);
    while ($AA = mswSQL_fetchobj($qA)) {
	    $attachIDs[] = $AA->itemID;
	  }
	  if (!empty($attachIDs)) {
	    $SQL = 'WHERE `id` IN(' . mswSQL(implode(',',$attachIDs)) . ')';
	  } else {
	    $SQL = 'WHERE `id` IN(0)';
	  }
  }
}

$q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS *,
     (SELECT count(*) FROM `" . DB_PREFIX . "faqassign`
		  WHERE (`" . DB_PREFIX . "faqassign`.`itemID` = `" . DB_PREFIX . "faqattach`.`id`)
			AND `" . DB_PREFIX . "faqassign`.`desc`     = 'attachment'
		 ) AS `queCount`
		 FROM `" . DB_PREFIX . "faqattach`
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
      <li><a href="?p=attachman"><?php echo $msg_adheader49; ?></a></li>
      <li class="active"><?php echo $mspubliclang3_7[7]; ?> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      } else {
      ?>
      <li class="active"><a href="?p=attachman"><?php echo $msg_adheader49; ?></a> (<?php echo mswNFM($sqlQryRows); ?>)</li>
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
            if (USER_ADMINISTRATOR == 'yes' || in_array('attachments', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=attachments')"><i class="fa fa-plus fa-fw"></i></button>
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
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_asc' . mswQueryParams(array('p','orderby','next')),       'name' => $msg_attachments17, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'name_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_desc' . mswQueryParams(array('p','orderby','next')),      'name' => $msg_attachments18, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'name_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_asc' . mswQueryParams(array('p','orderby','next')),      'name' => $msg_levels23,      'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'order_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_desc' . mswQueryParams(array('p','orderby','next')),     'name' => $msg_levels24,      'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'order_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=questions_desc' . mswQueryParams(array('p','orderby','next')), 'name' => $msg_kbase58,       'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'questions_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=questions_asc' . mswQueryParams(array('p','orderby','next')),  'name' => $msg_kbase57,       'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'questions_asc' ? ' class="active"' : ''))
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
            $links = array(
              array('link' => '?p='.$_GET['p'].mswQueryParams(array('p','opt','next')),                     'name' => $msg_attachments20),
              array('link' => '?p=' . $_GET['p'] . '&amp;opt=disabled' . mswQueryParams(array('p','opt','next')), 'name' => $msg_response27, 'active' => (isset($_GET['opt']) && $_GET['opt'] == 'disabled' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;opt=remote' . mswQueryParams(array('p','opt','next')),   'name' => $msg_attachments21, 'active' => (isset($_GET['opt']) && $_GET['opt'] == 'remote' ? ' class="active"' : ''))
            );
            echo $MSBOOTSTRAP->button(array(
              'text' => $msg_search20,
              'links' => $links,
              'orientation' => ' dropdown-menu-right',
              'centered' => 'no',
              'area' => 'admin',
              'icon' => 'cogs',
              'param' => 'opt'
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
                <th style="width:6%">
                  <input type="checkbox" onclick="mswCheckBoxes(this.checked,'.panel-body');mswCheckCount('panel-body','delButton','mswCVal')">
                </th>
                <?php
                }
                ?>
                <th><?php echo TABLE_HEAD_DECORATION . $msg_customfields; ?></th>
                <th><?php echo TABLE_HEAD_DECORATION . str_replace('/', ', ', $msg_attachments16); ?></th>
                <th><?php echo TABLE_HEAD_DECORATION . $msg_kbase56; ?></th>
                <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
                <?php
                if ($sqlQryRows > 0) {
                while ($ATT = mswSQL_fetchobj($q)) {
                ?>
                <tr id="datatr_<?php echo $ATT->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes') {
                ?>
                <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal');" name="del[]" value="<?php echo $ATT->id; ?>"></td>
                <?php
                }
                ?>
                <td><select name="order[<?php echo $ATT->id; ?>]" class="form-control">
                <?php
                for ($i=1; $i<($sqlQryRows+1); $i++) {
                ?>
                <option value="<?php echo $i; ?>"<?php echo mswSelectedItem($ATT->orderBy,$i); ?>><?php echo $i; ?></option>
                <?php
                }
                ?>
                </select></td>
                <td><b><?php echo ($ATT->name ? mswSH($ATT->name) : ($ATT->remote ? $ATT->remote : $ATT->path)); ?></b>
                <span class="tdCellInfo">
                <?php echo str_replace(
                 array(
                  '{type}',
                  '{size}'
                 ),
                 array(
                  strtoupper(substr(strrchr(strtolower(($ATT->remote ? $ATT->remote : $ATT->path)),'.'),1)),
                  ($ATT->size>0 ? mswFSC($ATT->size) : $msg_script17)
                 ),
                 $msg_attachments11) . (ONE_CLICK_IMG_VIEWER && substr($ATT->mimeType, 0, 6) == 'image/' ? ' (<a href="' . ($ATT->remote ? $ATT->remote : $SETTINGS->attachhreffaq . '/' . $ATT->path) . '" onclick="iBox.showURL(this.href,\'\');return false">' . $msg_script10 . '</a>)' : '');
                ?>
                </span>
                </td>
                <td><a href="?p=faqman&amp;attached=<?php echo $ATT->id; ?>"><?php echo mswNFM($ATT->queCount); ?></a></td>
                <td class="text-right">
                  <i class="fa fa-<?php echo ($ATT->enAtt=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($ATT->enAtt=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'faqattachstate','<?php echo $ATT->id; ?>')" title="<?php echo mswSH($msg_response28); ?>"></i>
                  <a href="?p=attachments&amp;edit=<?php echo $ATT->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                  <a href="#" onclick="mswDL('<?php echo $ATT->id; ?>','dl');return false" title="<?php echo mswSH($msg_viewticket50); ?>"><i class="fa fa-download fa-fw"></i></a>
                </td>
                </tr>
                <?php
                }
                } else {
                ?>
                <tr class="warning nothing_to_see">
                  <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>"><?php echo $msg_attachments9; ?></td>
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
          <button onclick="mswButtonOp('faqattachdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels9; ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('faqattachseq')"><i class="fa fa-sort-numeric-asc fa-fw" title="<?php echo mswSH($msg_levels8); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels8; ?></span></button>
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