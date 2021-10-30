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

if (isset($_GET['cat'])) {
  define('DISABLED_CATS',1);
}

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSQL(strtolower($_GET['keys']));
  $SQL           = 'AND (LOWER(`name`) LIKE \'%' . $_GET['keys'] . '%\' OR LOWER(`summary`) LIKE \'%' . $_GET['keys'] . '%\')';
}

$q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS *,
     (SELECT count(*) FROM `" . DB_PREFIX . "faq`
      WHERE (`" . DB_PREFIX . "faq`.`cat` = `" . DB_PREFIX . "categories`.`id`)
		 ) AS `queCount`
		 FROM `" . DB_PREFIX . "categories`
     WHERE `subcat` = '0'
		 $SQL
		 $orderBy
		 $sqlLimStr
		 ", __file__, __line__);
$c            = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
$sqlQryRows  = (isset($c->rows) ? $c->rows : '0');
$totalCats    = mswSQL_rows('categories');
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (isset($_GET['keys'])) {
      ?>
      <li><a href="?p=faq-catman"><?php echo $msg_adheader45; ?></a></li>
      <li class="active"><?php echo $mspubliclang3_7[7]; ?> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      } else {
      ?>
      <li class="active"><a href="?p=faq-catman"><?php echo $msg_adheader45; ?></a> (<?php echo mswNFM($totalCats); ?>)</li>
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
            if (USER_ADMINISTRATOR == 'yes' || in_array('faq-cat', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=faq-cat')"><i class="fa fa-plus fa-fw"></i></button>
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
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_asc' . mswQueryParams(array('p','orderby','next')),       'name' => $msg_kbase43,  'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'name_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_desc' . mswQueryParams(array('p','orderby','next')),      'name' => $msg_kbase44,  'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'name_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_asc' . mswQueryParams(array('p','orderby','next')),      'name' => $msg_levels23, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'order_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=order_desc' . mswQueryParams(array('p','orderby','next')),     'name' => $msg_levels24, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'order_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=questions_desc' . mswQueryParams(array('p','orderby','next')), 'name' => $msg_kbase58,  'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'questions_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=questions_asc' . mswQueryParams(array('p','orderby','next')),  'name' => $msg_kbase57,  'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'questions_asc' ? ' class="active"' : ''))
            );
            echo $MSBOOTSTRAP->button(array(
              'text' => $msg_search20,
              'links' => $links,
              'orientation' => '',
              'centered' => 'no',
              'area' => 'admin',
              'icon' => '',
              'param' => 'orderby'
            ));
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
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_kbase17; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_kbase56; ?></th>
                  <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
                 </tr>
                </thead>
              <tbody>
                <?php
                if ($sqlQryRows > 0) {
                while ($CAT = mswSQL_fetchobj($q)) {
                ?>
                <tr id="datatr_<?php echo $CAT->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes') {
                ?>
                <td><input type="checkbox" onclick="mswCheckRange(this.checked,'subcat_<?php echo $CAT->id; ?>');mswCheckCount('panel-body','delButton','mswCVal');" name="del[]" value="<?php echo $CAT->id; ?>"></td>
                <?php
                }
                ?>
                <td>
                 <select name="order[<?php echo $CAT->id; ?>]" class="form-control">
                 <?php
                 for ($i=1; $i<($sqlQryRows+1); $i++) {
                 ?>
                 <option value="<?php echo $i; ?>"<?php echo mswSelectedItem($CAT->orderBy,$i); ?>><?php echo $i; ?></option>
                 <?php
                 }
                 ?>
                 </select>
                </td>
                <td>
                <?php echo ($CAT->private == 'yes' ? '<i class="fa fa-lock fa-fw" title="' . mswSH($msadminlang3_1faq[3]) . '"></i> ' . (!in_array($CAT->accounts, array('','all',null)) ? '<a href="#" onclick="iBox.showURL(\'?p=faq-catman&amp;pr_acc=' . $CAT->id . '\',\'\',{width:' . IBOX_FQACC_WIDTH . ',height:' . IBOX_FQACC_HEIGHT . '});return false"><i class="fa fa-user fa-fw" title="' . mswSH($msadminlang_faq_3_7[3]) . '"></i></a> ' : '')  : '') . '<b>' . mswSH($CAT->name); ?></b>
                <span class="tdCellInfo">
                <?php echo (strlen($CAT->summary)>CATEGORIES_SUMMARY_TEXT_LIMIT ? substr(mswSH($CAT->summary),0,CATEGORIES_SUMMARY_TEXT_LIMIT).'..' : mswSH($CAT->summary)); ?>
                </span>
                </td>
                <td><a href="?p=faqman&amp;cat=<?php echo $CAT->id; ?>" title="<?php echo mswNFM($CAT->queCount); ?>"><?php echo mswNFM($CAT->queCount); ?></a></td>
                <td class="text-right">
                  <i class="fa fa-<?php echo ($CAT->enCat=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($CAT->enCat=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'faqcatstate','<?php echo $CAT->id; ?>')" title="<?php echo mswSH($msg_response28); ?>"></i>
                  <a href="?p=faq-cat&amp;edit=<?php echo $CAT->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                </td>
                </tr>
                <?php

                //============================
                // SUB CATEGORIES
                //============================

                $q2  = mswSQL_query("SELECT *,
                       (SELECT count(*) FROM `" . DB_PREFIX . "faq`
                        WHERE (`" . DB_PREFIX . "faq`.`cat` = `" . DB_PREFIX . "categories`.`id`)
                       ) AS `queCount`
                       FROM `" . DB_PREFIX . "categories`
                       WHERE `subcat` = '{$CAT->id}'
                       " . (defined('DISABLED_CATS') ? 'AND `enCat` = \'no\'' : '') . "
                       " . $SQL." " . $orderBy, __file__, __line__);
               $subCount = mswSQL_numrows($q2);
               if ($subCount>0) {
               while ($SUB = mswSQL_fetchobj($q2)) {
		           ?>
               <tr id="datatr_<?php echo $SUB->enCat; ?>">
               <?php
               if (USER_DEL_PRIV == 'yes') {
               ?>
               <td style="padding-left:15px" class="subcat_<?php echo $CAT->id; ?>"><input type="checkbox" onclick="if(!this.checked){mswUncheck('cat_<?php echo $CAT->id; ?>')};mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $SUB->id; ?>"></td>
               <?php
               }
               ?>
               <td style="padding-left:15px">
                <select name="orderSub[<?php echo $SUB->id; ?>]" class="form-control">
                <?php
                for ($i=1; $i<($subCount+1); $i++) {
                ?>
                <option value="<?php echo $i; ?>"<?php echo mswSelectedItem($SUB->orderBy,$i); ?>><?php echo $i; ?></option>
                <?php
                }
                ?>
                </select>
               </td>
               <td style="padding-left:15px">
               <?php echo ($CAT->private == 'yes' ? '<i class="fa fa-lock fa-fw" title="' . mswSH($msadminlang3_1faq[3]) . '"></i> ' . (!in_array($CAT->accounts, array('','all',null)) ? '<a href="#" onclick="iBox.showURL(\'?p=faq-catman&amp;pr_acc=' . $CAT->id . '\',\'\',{width:' . IBOX_FQACC_WIDTH . ',height:' . IBOX_FQACC_HEIGHT . '});return false"><i class="fa fa-user fa-fw" title="' . mswSH($msadminlang_faq_3_7[3]) . '"></i></a> ' : '')  : '') . '<b>' . mswSH($SUB->name); ?></b>
                <span class="tdCellInfo">
               <?php echo (strlen($SUB->summary)>CATEGORIES_SUMMARY_TEXT_LIMIT ? substr(mswSH($SUB->summary),0,CATEGORIES_SUMMARY_TEXT_LIMIT).'..' : mswSH($SUB->summary)); ?>
               </span>
               </td>
               <td><a href="?p=faqman&amp;cat=<?php echo $SUB->id; ?>" title="<?php echo mswNFM($SUB->queCount); ?>"><?php echo mswNFM($SUB->queCount); ?></a></td>
               <td class="text-right">
                 <i class="fa fa-<?php echo ($SUB->enCat=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($SUB->enCat=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'faqcatstate','<?php echo $SUB->id; ?>')" title="<?php echo mswSH($msg_response28); ?>"></i>
                 <a href="?p=faq-cat&amp;edit=<?php echo $SUB->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
               </td>
               </tr>
               <?php
               }
               }

               //============================
               // END SUB CATEGORIES
               //============================

               }
               } else {
               ?>
               <tr class="warning nothing_to_see">
                <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>"><?php echo $msg_kbasecats8; ?></td>
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
          <button onclick="mswButtonOp('faqcatdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels9; ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('faqcatseq')"><i class="fa fa-sort-numeric-asc fa-fw" title="<?php echo mswSH($msg_levels8); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels8; ?></span></button>
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