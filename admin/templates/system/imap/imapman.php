<?php if (!defined('PARENT')) { exit; }
$SQL           = '';

if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'user_asc';
}

if (isset($_GET['orderby'])) {
  switch ($_GET['orderby']) {
    // Protocol (ascending)..
    case 'host_asc':
	    $orderBy = 'ORDER BY `im_host`';
	    break;
	  // Protocol (descending)..
    case 'host_desc':
	    $orderBy = 'ORDER BY `im_host` desc';
	    break;
	  // Mailbox User (ascending)..
    case 'user_asc':
	    $orderBy = 'ORDER BY `im_user`';
	    break;
	  // Mailbox User (descending)..
    case 'user_desc':
	    $orderBy = 'ORDER BY `im_user` desc';
	    break;
  }
}

if (isset($_GET['filter'])) {
  $SQL  = 'WHERE `im_piping` = \'no\'';
}

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSQL(strtolower($_GET['keys']));
  $SQL           = 'WHERE LOWER(`im_host`) LIKE \'%' . $_GET['keys'] . '%\' OR LOWER(`im_user`) LIKE \'%' . $_GET['keys'] . '%\'';
}

$q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS * FROM `" . DB_PREFIX . "imap`
     $SQL
		 $orderBy
		 $sqlLimStr
		 ", __file__, __line__)
                ;
$c             = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
$sqlQryRows   = (isset($c->rows) ? $c->rows : '0');
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (isset($_GET['keys'])) {
      ?>
      <li><a href="?p=imapman"><?php echo $msadminlang3_1[4]; ?></a></li>
      <li class="active"><?php echo $mspubliclang3_7[7]; ?> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      } else {
      ?>
      <li class="active"><a href="?p=imapman"><?php echo $msadminlang3_1[4]; ?></a> (<?php echo mswNFM($sqlQryRows); ?>)</li>
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
            if (USER_ADMINISTRATOR == 'yes' || in_array('imap', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=imap')"><i class="fa fa-plus fa-fw"></i></button>
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
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=host_asc' . mswQueryParams(array('p','orderby')),  'name' => $msg_imap35, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'host_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=host_desc' . mswQueryParams(array('p','orderby')), 'name' => $msg_imap36, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'host_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=user_asc' . mswQueryParams(array('p','orderby')),  'name' => $msg_imap37, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'user_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=user_desc' . mswQueryParams(array('p','orderby')), 'name' => $msg_imap38, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'user_desc' ? ' class="active"' : ''))
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
            // Filter By..
            $links = array(
              array('link' => '?p=' . $_GET['p'] . mswQueryParams(array('p','filter','next')),  'name' => $msg_imap39),
              array('link' => '?p=' . $_GET['p'] . '&amp;filter=disabled' . mswQueryParams(array('p','filter','next')), 'name' => $msg_response27,   'active' => (isset($_GET['filter']) && $_GET['filter'] == 'disabled' ? ' class="active"' : ''))
            );
            echo $MSBOOTSTRAP->button(array(
              'text' => $msg_search20,
              'links' => $links,
              'orientation' => ' dropdown-menu-right',
              'centered' => 'no',
              'area' => 'admin',
              'icon' => 'cogs',
              'param' => 'filter'
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
                   <th><?php echo TABLE_HEAD_DECORATION . $msg_imap7; ?></th>
                   <th><?php echo TABLE_HEAD_DECORATION . $msg_imap8; ?></th>
                   <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
                 </tr>
               </thead>
               <tbody>
               <?php
               if ($sqlQryRows > 0) {
               while ($IMAP = mswSQL_fetchobj($q)) {
               ?>
               <tr id="datatr_<?php echo $IMAP->id; ?>">
               <?php
               if (USER_DEL_PRIV == 'yes') {
               ?>
               <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $IMAP->id; ?>"></td>
               <?php
               }
               ?>
               <td><?php echo $IMAP->id; ?></td>
               <td><b><?php echo mswSH($IMAP->im_host); ?></b></td>
               <td><?php echo mswSH($IMAP->im_user); ?></td>
               <td class="text-right">
                 <i class="fa fa-<?php echo ($IMAP->im_piping=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($IMAP->im_piping=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'imstate','<?php echo $IMAP->id; ?>')" title="<?php echo mswSH($msg_response28); ?>"></i>
                 <a href="?p=imap&amp;edit=<?php echo $IMAP->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                 <a href="#" onclick="mswImapCheckMail('../?<?php echo $SETTINGS->imap_param . '=' . $IMAP->id; ?>&amp;output=html','','{width:<?php echo IBOX_IMAP_WIDTH; ?>,height:<?php echo IBOX_IMAP_HEIGHT; ?>}')" title="<?php echo mswSH($msg_imap29); ?>" onclick="window.open(this);return false"><i class="fa fa-envelope-o fa-fw"></i></a>
               </td>
               </tr>
               <?php
               }
               } else {
               ?>
               <tr class="warning nothing_to_see">
                 <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>"><?php echo $msg_imap21; ?></td>
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
          <button onclick="mswButtonOp('imdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels9; ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
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