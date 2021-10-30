<?php if (!defined('PARENT')) { exit; }
$SQL = '';
if ($MSTEAM->id != '1') {
  $SQL = 'WHERE `id` > 1';
}

if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'name_asc';
}
$orderBy = 'ORDER BY `name`';

if (isset($_GET['orderby'])) {
  switch ($_GET['orderby']) {
    // Name (ascending)..
    case 'name_asc':
	    $orderBy = 'ORDER BY FIELD(`id`, 1) DESC, `name`';
	    break;
	  // Name (descending)..
    case 'name_desc':
	    $orderBy = 'ORDER BY `name` desc';
	    break;
	  // Email Address (ascending)..
    case 'email_asc':
	    $orderBy = 'ORDER BY `email`';
	    break;
	  // Email Address (descending)..
    case 'email_desc':
	    $orderBy = 'ORDER BY `email` desc';
	    break;
	  // Most responses..
    case 'resp_asc':
	    $orderBy = 'ORDER BY `respCount` desc';
	    break;
	  // Least tickets..
    case 'resp_desc':
	    $orderBy = 'ORDER BY `respCount`';
	    break;
  }
}

if (isset($_GET['filter'])) {
  switch ($_GET['filter']) {
    case 'disabled':
      $SQL = 'WHERE `enabled` = \'no\'';
      break;
	  case 'notify':
      $SQL = 'WHERE `notify` = \'no\'';
      break;
	  case 'delpriv':
      $SQL = 'WHERE `delPriv` = \'yes\'';
      break;
	  case 'notepad':
      $SQL = 'WHERE `notePadEnable` = \'yes\'';
      break;
	  case 'assigned':
      $SQL = 'WHERE `assigned` = \'yes\'';
      break;
    case 'mergepriv':
      $SQL = 'WHERE `mergeperms` = \'yes\'';
      break;
    case 'closepriv':
      $SQL = 'WHERE `close` = \'yes\'';
      break;
    case 'lockpriv':
      $SQL = 'WHERE `lock` = \'yes\'';
      break;
    case 'mailbox':
      $SQL = 'WHERE `mailbox` = \'yes\'';
      break;
    case 'admin':
      $SQL = 'WHERE `admin` = \'yes\'';
      break;
  }
}

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSQL(strtolower($_GET['keys']));
  $SQL           = 'WHERE LOWER(`name`) LIKE \'%' . $_GET['keys'] . '%\' OR LOWER(`email`) LIKE \'%' . $_GET['keys'] . '%\'';
}

$q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS *,
     (SELECT count(*) FROM `" . DB_PREFIX . "replies`
		 WHERE `" . DB_PREFIX . "replies`.`replyUser` = `" . DB_PREFIX . "users`.`id`
		 AND `" . DB_PREFIX . "replies`.`replyType` = 'admin'
		 ) AS `respCount`
		 FROM `" . DB_PREFIX . "users`
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
      <li><a href="?p=teamman"><?php echo $msg_adheader58; ?></a></li>
      <li class="active"><?php echo $mspubliclang3_7[7]; ?> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      } else {
      ?>
      <li class="active"><a href="?p=teamman"><?php echo $msg_adheader58; ?></a> (<?php echo mswNFM($sqlQryRows); ?>)</li>
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
            if (USER_ADMINISTRATOR == 'yes' || in_array('team', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=team')"><i class="fa fa-plus fa-fw"></i></button>
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
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_asc' . mswQueryParams(array('p','orderby')),  'name' => $msg_levels21,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'name_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_desc' . mswQueryParams(array('p','orderby')), 'name' => $msg_levels22,   'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'name_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=email_asc' . mswQueryParams(array('p','orderby')), 'name' => $msg_accounts9,  'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'email_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=email_desc' . mswQueryParams(array('p','orderby')),'name' => $msg_accounts10, 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'email_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=resp_asc' . mswQueryParams(array('p','orderby')),  'name' => $msg_user78,     'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'resp_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=resp_desc' . mswQueryParams(array('p','orderby')), 'name' => $msg_user79,     'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'resp_desc' ? ' class="active"' : ''))
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
              array('link' => '?p='.$_GET['p'].mswQueryParams(array('p','filter')),                               'name' => $msg_accounts14),
              array('link' => '?p=' . $_GET['p'] . '&amp;filter=disabled' . mswQueryParams(array('p','filter')),  'name' => $msg_response27,           'active' => (isset($_GET['filter']) && $_GET['filter'] == 'disabled' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;filter=admin' . mswQueryParams(array('p','filter')),     'name' => $msadminlang_user_3_7[17], 'active' => (isset($_GET['filter']) && $_GET['filter'] == 'admin' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;filter=mailbox' . mswQueryParams(array('p','filter')),   'name' => $msadminlang_user_3_7[18], 'active' => (isset($_GET['filter']) && $_GET['filter'] == 'mailbox' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;filter=notify' . mswQueryParams(array('p','filter')),    'name' => $msg_user80,               'active' => (isset($_GET['filter']) && $_GET['filter'] == 'notify' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;filter=mergepriv' . mswQueryParams(array('p','filter')), 'name' => $msadminlang_user_3_7[14], 'active' => (isset($_GET['filter']) && $_GET['filter'] == 'mergepriv' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;filter=closepriv' . mswQueryParams(array('p','filter')), 'name' => $msadminlang_user_3_7[15], 'active' => (isset($_GET['filter']) && $_GET['filter'] == 'closepriv' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;filter=lockpriv' . mswQueryParams(array('p','filter')),  'name' => $msadminlang_user_3_7[16], 'active' => (isset($_GET['filter']) && $_GET['filter'] == 'lockpriv' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;filter=delpriv' . mswQueryParams(array('p','filter')),   'name' => $msg_user81,               'active' => (isset($_GET['filter']) && $_GET['filter'] == 'delpriv' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;filter=notepad' . mswQueryParams(array('p','filter')),   'name' => $msg_user82,               'active' => (isset($_GET['filter']) && $_GET['filter'] == 'notepad' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;filter=assigned' . mswQueryParams(array('p','filter')),  'name' => $msg_user83,               'active' => (isset($_GET['filter']) && $_GET['filter'] == 'assigned' ? ' class="active"' : ''))
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
                <th style="width:5%"><?php echo TABLE_HEAD_DECORATION . $msadminlang3_7prlevels[3]; ?></th>
                <th><?php echo TABLE_HEAD_DECORATION . str_replace('/', ', ', $msg_user); ?></th>
                <th><?php echo TABLE_HEAD_DECORATION . $msg_user4; ?></th>
                <th><?php echo TABLE_HEAD_DECORATION . $msg_user77; ?></th>
                <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
                <?php
                if ($sqlQryRows > 0) {
                while ($USER = mswSQL_fetchobj($q)) {
                ?>
                <tr id="datatr_<?php echo $USER->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes') {
                if ($USER->id > 1) {
                ?>
                <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $USER->id; ?>"></td>
                <?php
                } else {
                ?>
                <td>&nbsp;</td>
                <?php
                }
                }
                ?>
                <td><?php echo $USER->id; ?></td>
                <td><b><?php echo mswSH($USER->name); ?></b><span class="tdCellInfo"><?php echo $msadminlang_user_3_7[13]; ?>: <?php echo ($USER->id == '1' ? $msg_script4 : mswYN($USER->admin, $msg_script4, $msg_script5)); ?></span></td>
                <td><?php echo mswSH($USER->email); ?></td>
                <td><a href="?p=responses&amp;id=<?php echo $USER->id; ?>" title=""><?php echo mswNFM($USER->respCount); ?></a></td>
                <td class="text-right">
                <i class="fa fa-<?php echo ($USER->enabled=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($USER->enabled=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'tmstate','<?php echo $USER->id; ?>')" title="<?php echo mswSH($msg_response28); ?>"></i>
                <a href="?p=team&amp;edit=<?php echo $USER->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                <a href="?p=responses&amp;id=<?php echo $USER->id; ?>" title="<?php echo mswSH($msg_user25); ?>"><i class="fa fa-comments-o fa-fw"></i></a>
                <a href="?p=graph&amp;id=<?php echo $USER->id; ?>" title="<?php echo mswSH($msg_user31); ?>"><i class="fa fa-bar-chart fa-fw"></i></a>
                </td>
                </tr>
                <?php
                }
                } else {
                ?>
                <tr class="warning nothing_to_see">
                 <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '6' : '5'); ?>"><?php echo $msg_user11; ?></td>
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
          <button onclick="mswButtonOp('tmdel');return false;" class="btn btn-danger" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels9; ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('tmrep')"><i class="fa fa-save fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msadminlang_user_3_7[19]; ?></span></button>
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