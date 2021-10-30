<?php if (!defined('PARENT')) { exit; }
$SQL = '';

if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'order_asc';
}
$orderBy = 'ORDER BY `orderBy`';

if (isset($_GET['orderby'])) {
  switch ($_GET['orderby']) {
    // Question (ascending)..
    case 'que_asc':
	    $orderBy = 'ORDER BY `question`';
	    break;
	  // Question (descending)..
    case 'que_desc':
	    $orderBy = 'ORDER BY `question` desc';
	    break;
	  // Order Sequence (ascending)..
    case 'order_asc':
	    $orderBy = 'ORDER BY `orderBy`';
  	  break;
	  // Order Sequence (descending)..
    case 'order_desc':
	    $orderBy = 'ORDER BY `orderBy` desc';
	    break;
	  // Most attachments..
    case 'att_desc':
	    $orderBy = 'ORDER BY `attCount` desc';
	    break;
	  // Least attachments..
    case 'att_asc':
	    $orderBy = 'ORDER BY `attCount`';
	    break;
  }
}

if (isset($_GET['cat'])) {
  switch ($_GET['cat']) {
    case 'disabled':
	    $SQL          = 'WHERE `enFaq` = \'no\'';
	    break;
	  default:
      $_GET['cat']  = (int)$_GET['cat'];
      $SQL          = 'WHERE `cat` = \'' . (int) $_GET['cat'] . '\'';
	    break;
  }
}

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSQL(strtolower($_GET['keys']));
  $SQL           = 'WHERE LOWER(`question`) LIKE \'%' . $_GET['keys'] . '%\' OR LOWER(`answer`) LIKE \'%' . $_GET['keys'] . '%\'';
} else {
  // Are we showing questions only allocated to an attachment?
  if (isset($_GET['attached'])) {
    $_GET['attached'] = (int)$_GET['attached'];
	  $attachIDs        = array();
	  $qA               = mswSQL_query("SELECT `question` FROM `" . DB_PREFIX . "faqassign`
                        WHERE `itemID` = '{$_GET['attached']}'
						            AND `desc`     = 'attachment'
						            GROUP BY `question`
						            ", __file__, __line__);
    while ($AA = mswSQL_fetchobj($qA)) {
	    $attachIDs[] = $AA->question;
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
      WHERE (`" . DB_PREFIX . "faqassign`.`question` = `" . DB_PREFIX . "faq`.`id`)
      AND `" . DB_PREFIX . "faqassign`.`desc`       = 'attachment'
     ) AS `attCount`,
     (SELECT `name` FROM `" . DB_PREFIX . "categories`
      WHERE `" . DB_PREFIX . "categories`.`id` = `cat`
     ) AS `catName`
     FROM `" . DB_PREFIX . "faq`
     $SQL
     $orderBy
     $sqlLimStr
     ", __file__, __line__);
$c              = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
$sqlQryRows    =  (isset($c->rows) ? $c->rows : '0');
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (isset($_GET['keys'])) {
      ?>
      <li><a href="?p=faqman"><?php echo $msg_adheader47; ?></a></li>
      <li class="active"><?php echo $mspubliclang3_7[7]; ?> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      } else {
      ?>
      <li class="active"><a href="?p=faqman"><?php echo $msg_adheader47; ?></a> (<?php echo mswNFM($sqlQryRows); ?>)</li>
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
            if (USER_ADMINISTRATOR == 'yes' || in_array('faq', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=faq')"><i class="fa fa-plus fa-fw"></i></button>
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
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=que_asc' . mswQueryParams(array('p','orderby','next')),  'name' => $msg_kbase46,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'que_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=que_desc' . mswQueryParams(array('p','orderby','next')), 'name' => $msg_kbase47,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'que_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_asc' . mswQueryParams(array('p','orderby','next')), 'name' => $msg_levels23, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'order_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_desc' . mswQueryParams(array('p','orderby','next')),'name' => $msg_levels24, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'order_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=att_desc' . mswQueryParams(array('p','orderby','next')), 'name' => $msg_kbase62,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'att_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=att_asc' . mswQueryParams(array('p','orderby','next')),  'name' => $msg_kbase61,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'att_asc' ? ' class="active"' : ''))
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
            $links   = array(array('link' => '?p=' . $_GET['p'] . mswQueryParams(array('p','cat','next')),  'name' => $msg_kbase48));
            $q_c     = mswSQL_query("SELECT `id`,`name`,`private`,`accounts` FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '0' ORDER BY `name`", __file__, __line__);
            while ($CAT = mswSQL_fetchobj($q_c)) {
              $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;cat=' . $CAT->id . mswQueryParams(array('p','cat','next')),'name' => mswCD($CAT->name),   'active' => (isset($_GET['cat']) && $_GET['cat'] == $CAT->id ? ' class="active"' : ''));
              $q_c2    = mswSQL_query("SELECT `id`,`name` FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '{$CAT->id}' ORDER BY `name`", __file__, __line__);
              while ($SUB = mswSQL_fetchobj($q_c2)) {
                $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;cat=' . $SUB->id . mswQueryParams(array('p','cat')),'name' => '&nbsp;&nbsp;' . mswCD($SUB->name),'active' => (isset($_GET['cat']) && $_GET['cat'] == $SUB->id ? ' class="active"' : ''));
              }
            }
            $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;cat=disabled' . mswQueryParams(array('p','cat')),'name' => $msg_response27);
            echo $MSBOOTSTRAP->button(array(
              'text' => $msg_search20,
              'links' => $links,
              'orientation' => ' dropdown-menu-right',
              'centered' => 'yes',
              'area' => 'admin',
              'icon' => '',
              'param' => 'cat'
            ));
            // Page filter..
            if (isset($_GET['attached'])) {
              define('SKIP_SEARCH_BOX', 1);
            }
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
                  <input type="checkbox" onclick="mswCheckBoxes(this.checked,'.panel-body');mswCheckCount('panel-body','delButton','mswCVal');mswCheckCount('panel-body','delButton2','mswCVal2')">
                </th>
                <?php
                }
                ?>
                <th><?php echo TABLE_HEAD_DECORATION . $msg_customfields; ?></th>
                <th><?php echo TABLE_HEAD_DECORATION . $msg_kbase; ?></th>
                <th><?php echo TABLE_HEAD_DECORATION . $msg_kbase51; ?></th>
                <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
               <?php
               if ($sqlQryRows > 0) {
               $totalR = mswSQL_rows('faq');
               while ($QUE = mswSQL_fetchobj($q)) {
               if ($SETTINGS->enableVotes=='yes') {
                 $yes  = ($QUE->kviews>0 ? mswNFM(($QUE->kuseful / $QUE->kviews) * 100, 2) : '0.00');
                 $no   = ($QUE->kviews>0 ? mswNFM(($QUE->knotuseful / $QUE->kviews) * 100, 2) : '0.00');
                 if (substr($yes, -3) == '.00') {
                   $yes = substr($yes, 0, -3);
                 }
                 if (substr($no, -3) == '.00') {
                   $no = substr($no, 0, -3);
                 }
               }
               ?>
               <tr id="datatr_<?php echo $QUE->id; ?>">
               <?php
               if (USER_DEL_PRIV == 'yes') {
               ?>
               <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal');mswCheckCount('panel-body','delButton2','mswCVal2')" name="del[]" value="<?php echo $QUE->id; ?>"></td>
               <?php
               }
               ?>
               <td><select name="order[<?php echo $QUE->id; ?>]" class="form-control">
               <?php
               for ($i=1; $i<($totalR+1); $i++) {
               ?>
               <option value="<?php echo $i; ?>"<?php echo mswSelectedItem($QUE->orderBy,$i); ?>><?php echo $i; ?></option>
               <?php
               }
               ?>
               </select></td>
               <td>
               <b><?php echo mswSH($QUE->question); ?></b>
               <span class="tdCellInfo">
               <?php
               echo ($QUE->catName ? $QUE->catName : '<span class="unassigned"><i class="fa fa-warning fa-fw ms_red"></i> '.$msg_kbase63.'</span>');
               ?>
               </span>
               <span class="tdCellInfo">
               <?php
               echo ($SETTINGS->enableVotes=='yes' ? str_replace(array('{count}','{helpful}','{nothelpful}'),array($QUE->kviews,$yes,$no),$msg_kbase18) : '');
               ?>
               </span>
               </td>
               <td><a href="?p=attachman&amp;question=<?php echo $QUE->id; ?>" title="<?php echo $QUE->attCount; ?>"><?php echo $QUE->attCount; ?></a></td>
               <td class="text-right">
                 <i class="fa fa-<?php echo ($QUE->enFaq=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($QUE->enFaq=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'faqstate','<?php echo $QUE->id; ?>')" title="<?php echo mswSH($msg_response28); ?>"></i>
                 <a href="?p=faq&amp;edit=<?php echo $QUE->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                 <a href="?p=<?php echo $_GET['p']; ?>&amp;view=<?php echo $QUE->id; ?>" onclick="iBox.showURL(this.href,'',{width:<?php echo IBOX_FAQ_WIDTH; ?>,height:<?php echo IBOX_FAQ_HEIGHT; ?>});return false" title="<?php echo mswSH($msg_kbase12); ?>"><i class="fa fa-search fa-fw"></i></a>
               </td>
               </tr>
               <?php
               }
               } else {
               ?>
               <tr class="warning nothing_to_see">
                <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>"><?php echo $msg_kbase9; ?></td>
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
          <button onclick="mswButtonOp('faqdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels9; ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
          if ($SETTINGS->enableVotes=='yes') {
          ?>
          <button onclick="mswButtonOp('faqreset');return false;" class="btn btn-warning button_margin_right20" disabled="disabled" type="button" id="delButton2"><i class="fa fa-refresh fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_kbase27; ?></span> <span id="mswCVal2">(0)</span></button>
          <?php
          }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('faqseq')"><i class="fa fa-sort-numeric-asc fa-fw" title="<?php echo mswSH($msg_levels8); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels8; ?></span></button>
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