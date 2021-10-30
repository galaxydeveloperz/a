<?php if (!defined('PARENT')) { exit; }
$SQL = '';

if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'order_asc';
}

if (isset($_GET['orderby'])) {
  switch ($_GET['orderby']) {
    // Title (ascending)..
    case 'title_asc':
	    $orderBy = 'ORDER BY `fieldInstructions`';
	    break;
	  // Title (descending)..
    case 'title_desc':
	    $orderBy = 'ORDER BY `fieldInstructions` desc';
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
  switch ($_GET['dept']) {
    case 'disabled':
      $SQL          = 'WHERE `enField` = \'no\'';
      break;
	  case 'required':
      $SQL          = 'WHERE `fieldReq` = \'yes\'';
      break;
	  case 'ticket':
    case 'reply':
    case 'admin':
      $SQL          = 'WHERE FIND_IN_SET(\''.$_GET['dept'].'\',`fieldLoc`) > 0';
      break;
    default:
      $_GET['dept'] = (int)$_GET['dept'];
      $SQL          = 'WHERE FIND_IN_SET(\''.$_GET['dept'].'\',`departments`)>0';
	    break;
  }
}

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSQL(strtolower($_GET['keys']));
  $SQL           = 'WHERE LOWER(`fieldInstructions`) LIKE \'%' . $_GET['keys'] . '%\' OR LOWER(`fieldOptions`) LIKE \'%' . $_GET['keys'] . '%\'';
}

$q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS * FROM `" . DB_PREFIX . "cusfields`
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
      <li><a href="?p=fieldsman"><?php echo $msg_adheader43; ?></a></li>
      <li class="active"><?php echo $mspubliclang3_7[7]; ?> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      } else {
      ?>
      <li class="active"><a href="?p=fieldsman"><?php echo $msg_adheader43; ?></a> (<?php echo mswNFM($sqlQryRows); ?>)</li>
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
            if (USER_ADMINISTRATOR == 'yes' || in_array('fields', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=fields')"><i class="fa fa-plus fa-fw"></i></button>
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
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=title_asc' . mswQueryParams(array('p','orderby','next')),  'name' => $msg_customfields37, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'title_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=title_desc' . mswQueryParams(array('p','orderby','next')), 'name' => $msg_customfields38, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'title_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_asc' . mswQueryParams(array('p','orderby','next')),  'name' => $msg_levels23,       'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'order_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_desc' . mswQueryParams(array('p','orderby','next')), 'name' => $msg_levels24,       'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'order_desc' ? ' class="active"' : ''))
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
            // Order By..
            $links   = array(array('link' => '?p='.$_GET['p'].mswQueryParams(array('p','dept')),  'name' => $msg_customfields39));
            $q_dept  = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "departments` " . mswSQL_deptfilter($mswDeptFilterAccess,'WHERE') . " ORDER BY `orderBy`", __file__, __line__);
              while ($DEPT = mswSQL_fetchobj($q_dept)) {
              $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;dept='.$DEPT->id.mswQueryParams(array('p','dept')), 'name' => mswCD($DEPT->name),   'active' => (isset($_GET['dept']) && $_GET['dept'] == $DEPT->id ? ' class="active"' : ''));
            }
            $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;dept=disabled' . mswQueryParams(array('p','dept')), 'name' => $msg_response27,     'active' => (isset($_GET['dept']) && $_GET['dept'] == 'disabled' ? ' class="active"' : ''));
            $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;dept=required' . mswQueryParams(array('p','dept')), 'name' => $msg_customfields43, 'active' => (isset($_GET['dept']) && $_GET['dept'] == 'required' ? ' class="active"' : ''));
            $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;dept=ticket' . mswQueryParams(array('p','dept')),   'name' => $msg_customfields44, 'active' => (isset($_GET['dept']) && $_GET['dept'] == 'ticket' ? ' class="active"' : ''));
            $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;dept=reply' . mswQueryParams(array('p','dept')),    'name' => $msg_customfields45, 'active' => (isset($_GET['dept']) && $_GET['dept'] == 'reply' ? ' class="active"' : ''));
            $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;dept=admin' . mswQueryParams(array('p','dept')),    'name' => $msg_customfields46, 'active' => (isset($_GET['dept']) && $_GET['dept'] == 'admin' ? ' class="active"' : ''));
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
                  <th><?php echo TABLE_HEAD_DECORATION . $msadminlang3_7prlevels[3]; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_customfields; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_customfields4 . ', ' . str_replace('/', ', ', $msg_customfields3); ?></th>
                  <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
                </tr>
              </thead>
              <tbody>
                <?php
                if ($sqlQryRows > 0) {
                while ($FIELD = mswSQL_fetchobj($q)) {
                ?>
                <tr id="datatr_<?php echo $FIELD->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes') {
                ?>
                <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $FIELD->id; ?>"></td>
		            <?php
		            }
		            ?>
		            <td><?php echo $FIELD->id; ?></td>
                <td><select name="order[<?php echo $FIELD->id; ?>]" class="form-control">
                <?php
                for ($i=1; $i<($sqlQryRows+1); $i++) {
                ?>
                <option value="<?php echo $i; ?>"<?php echo mswSelectedItem($FIELD->orderBy,$i); ?>><?php echo $i; ?></option>
                <?php
                }
                ?>
                </select>
                </td>
                <td>
                <b><?php echo mswSH($FIELD->fieldInstructions); ?></b>
                <span class="tdCellInfo">
                <?php
                include_once(PATH . 'control/classes/class.fields.php');
                $MSFIELDS = new fields();
                $accRes = $MSFIELDS->fieldAccounts($FIELD->accounts);
                echo str_replace(
                 array('{type}','{required}','{depts}','{display}'),
                 array(
                   ucfirst($FIELD->fieldType) . (in_array($FIELD->fieldType, array('checkbox','select')) ? ' (' . count(explode(mswNL(), $FIELD->fieldOptions)) . ')' : ''),
                   mswYN($FIELD->fieldReq, $msg_script4, $msg_script5),
                   mswSrCat($FIELD->departments),
                   mswFieldDisplayInformation(array(
                     'loc' => $FIELD->fieldLoc,
                     'l' => array($msg_customfields40, $msg_customfields41, $msg_customfields42)
                   ))
                 ),
                 $msg_customfields33 . '<br>' . str_replace('{accounts}', ($accRes ? $accRes : $msg_customfields32), $mscsfields4_3[3])
                );
                ?>
                </span>
                </td>
                <td class="text-right">
                <i class="fa fa-<?php echo ($FIELD->enField=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($FIELD->enField=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'fldstate','<?php echo $FIELD->id; ?>')" title="<?php echo mswSH($msg_response28); ?>"></i>
                <a href="?p=fields&amp;edit=<?php echo $FIELD->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                </td>
               </tr>
               <?php
               }
               } else {
               ?>
               <tr class="warning nothing_to_see">
                <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>"><?php echo $msg_customfields16; ?></td>
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
          <button onclick="mswButtonOp('flddel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels9; ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('fldseq')"><i class="fa fa-sort-numeric-asc fa-fw" title="<?php echo mswSH($msg_levels8); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels8; ?></span></button>
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