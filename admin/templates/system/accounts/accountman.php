<?php if (!defined('PARENT')) { exit; }
$SQL           = '';
if (!isset($_GET['orderby'])) {
  $_GET['orderby'] = 'order_asc';
}
$orderBy = 'ORDER BY `name`';

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
	  // Email Address (ascending)..
    case 'email_asc':
	    $orderBy = 'ORDER BY `email`';
	    break;
	  // Email Address (descending)..
    case 'email_desc':
	    $orderBy = 'ORDER BY `email` desc';
	    break;
    // Date created (ascending)..
    case 'date_asc':
	    $orderBy = 'ORDER BY `ts`';
	    break;
	  // Date created (descending)..
    case 'date_desc':
	    $orderBy = 'ORDER BY `ts` desc';
	    break;
	  // Most tickets..
    case 'tickets_asc':
	    $orderBy = 'ORDER BY `tickCount` desc';
	    break;
	  // Least tickets..
    case 'tickets_desc':
	    $orderBy = 'ORDER BY `tickCount`';
	    break;
  }
}

if (isset($_GET['filter'])) {
  switch ($_GET['filter']) {
    case 'disabled':
      $SQL = 'WHERE `enabled` = \'no\' AND `verified` = \'yes\'';
      break;
    case 'verified':
      $SQL = 'WHERE `verified` = \'no\'';
      break;
  }
} else {
  $SQL = 'WHERE `enabled` IN(\'no\',\'yes\')';
}

if (isset($_GET['keys'])) {
  // Filters..
  $filters = array();
  if ($_GET['keys']) {
    $_GET['keys']  = mswSQL(strtolower($_GET['keys']));
    $filters[]     = "LOWER(`" . DB_PREFIX . "portal`.`name`) LIKE '%" . $_GET['keys'] . "%' OR LOWER(`" . DB_PREFIX . "portal`.`email`) LIKE '%" . $_GET['keys'] . "%' OR LOWER(`" . DB_PREFIX . "portal`.`ip`) LIKE '%" . $_GET['keys'] . "%'  OR LOWER(`" . DB_PREFIX . "portal`.`notes`) LIKE '%" . $_GET['keys'] . "%'";
  }
  if (isset($_GET['from'],$_GET['to']) && $_GET['from'] && $_GET['to']) {
    $from  = $MSDT->mswDatePickerFormat($_GET['from']);
    $to    = $MSDT->mswDatePickerFormat($_GET['to']);
    $filters[]     = "DATE(FROM_UNIXTIME(`ts`)) BETWEEN '{$from}' AND '{$to}'";
  }
  // Build search string..
  if (!empty($filters)) {
    for ($i=0; $i<count($filters); $i++) {
      $SQL .= 'AND (' . $filters[$i] . ')';
    }
  }
}

// Are we querying for disputes..
$sqlDisputes = '';
if ($SETTINGS->disputes == 'yes') {
  $sqlDisputes = ',
   (SELECT count(*) FROM `' . DB_PREFIX . 'disputes`
    WHERE `' . DB_PREFIX . 'portal`.`id` = `' . DB_PREFIX . 'disputes`.`visitorID`
   ) AS `dispCount`';
}

$q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS *,
     (SELECT count(*) FROM `" . DB_PREFIX . "tickets`
      WHERE `" . DB_PREFIX . "portal`.`id` = `" . DB_PREFIX . "tickets`.`visitorID`
      AND `spamFlag`   = 'no'
      AND `isDisputed` = 'no'
      ) AS `tickCount`
      $sqlDisputes
      FROM `" . DB_PREFIX . "portal`
      $SQL
      $orderBy
      $sqlLimStr
      ", __file__, __line__);
$c            = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
$sqlQryRows  = (isset($c->rows) ? $c->rows : '0');
define('LOAD_DATE_PICKERS', 1);
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (isset($_GET['keys'])) {
      ?>
      <li><a href="?p=accountman"><?php echo $msg_adheader40; ?></a></li>
      <li class="active"><?php echo $mspubliclang3_7[7]; ?> (<?php echo $sqlQryRows; ?>)</li>
      <?php
      } else {
      ?>
      <li class="active"><a href="?p=accountman"><?php echo $msg_adheader40; ?></a> (<?php echo $sqlQryRows; ?>)</li>
      <?php
      }
      ?>
    </ol>

    <?php
    if ($SETTINGS->accautodel > 0) {
      include(PATH . 'control/classes/class.accounts.php');
      $MSPTL           = new accounts();
      $MSPTL->settings = $SETTINGS;
      $MSPTL->ssn      = $SSN;
      $gone            = $MSPTL->autoDel();
      if ($gone > 0) {
        ?>
        <div class="alert alert-warning alert-dismissable border_2x">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="fa fa-times fa-fw"></i></button>
          <?php echo str_replace(array('{count}','{days}'), array($gone, $SETTINGS->accautodel), $msadminlang_accounts_3_7[1]); ?>
        </div>
        <?php
      }
    }

    // Search..
    include(PATH . 'templates/system/bootstrap/search-accounts.php');
    ?>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <?php
            if (USER_ADMINISTRATOR == 'yes' || in_array('accounts', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=accounts')"><i class="fa fa-plus fa-fw"></i></button>
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
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_asc' . mswQueryParams(array('p','orderby')),    'name' => $msg_levels21,      'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'name_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=name_desc' . mswQueryParams(array('p','orderby')),   'name' => $msg_levels22,      'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'name_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=email_asc' . mswQueryParams(array('p','orderby')),   'name' => $msg_accounts9,     'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'email_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=email_desc' . mswQueryParams(array('p','orderby')),  'name' => $msg_accounts10,    'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'email_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=date_asc' . mswQueryParams(array('p','orderby')),    'name' => $msadminlang4_2[1], 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'date_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=date_desc' . mswQueryParams(array('p','orderby')),   'name' => $msadminlang4_2[2], 'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'date_desc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=tickets_asc' . mswQueryParams(array('p','orderby')), 'name' => $msg_accounts11,    'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'tickets_asc' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;orderby=tickets_desc' . mswQueryParams(array('p','orderby')),'name' => $msg_accounts12,    'active' => (isset($_GET['orderby']) && $_GET['orderby'] == 'tickets_desc' ? ' class="active"' : ''))
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
              array('link' => '?p='.$_GET['p'].mswQueryParams(array('p','orderby','filter')),                       'name' => $msg_accounts14),
              array('link' => '?p=' . $_GET['p'] . '&amp;filter=disabled' . mswQueryParams(array('p','filter')), 'name' => $msg_response27,   'active' => (isset($_GET['filter']) && $_GET['filter'] == 'disabled' ? ' class="active"' : '')),
              array('link' => '?p=' . $_GET['p'] . '&amp;filter=verified' . mswQueryParams(array('p','filter')), 'name' => $msadminlang_accounts_3_7[0],   'active' => (isset($_GET['filter']) && $_GET['filter'] == 'verified' ? ' class="active"' : ''))
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
                 <th><?php echo TABLE_HEAD_DECORATION . str_replace('/', ', ', $msg_accounts); ?></th>
                 <th><?php echo TABLE_HEAD_DECORATION . $msg_accounts2; ?></th>
                 <th><?php echo TABLE_HEAD_DECORATION . str_replace('/', ', ', ($SETTINGS->disputes == 'yes' ? $msg_accounts38 : $msg_accounts3)); ?></th>
                 <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
               </tr>
              </thead>
              <tbody>
              <?php
              if ($sqlQryRows > 0) {
                while ($ACC = mswSQL_fetchobj($q)) {
                if (isset($ACC->dispCount)) {
                  $dCStart        = mswSQL_rows('tickets WHERE `visitorID` = \''.$ACC->id.'\' AND `isDisputed` = \'yes\' AND `spamFlag` = \'no\'');
                  $ACC->dispCount = ($ACC->dispCount+$dCStart);
                }
                ?>
                <tr id="datatr_<?php echo $ACC->id; ?>">
                <?php
                if (USER_DEL_PRIV == 'yes') {
                ?>
                <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $ACC->id; ?>"></td>
                <?php
                }
                ?>
                <td><b><?php echo ($ACC->name ? mswSH($ACC->name) : $msg_script17); ?></b>
                <span class="tdCellInfo">
                  <?php echo $msadminlang4_2[0]; ?>: <?php echo $MSDT->mswDateTimeDisplay($ACC->ts, $SETTINGS->dateformat); ?>
                </span>
                </td>
                <td><?php echo mswSH($ACC->email); ?></td>
                <?php
                if ($SETTINGS->disputes == 'yes') {
                ?>
                <td><a href="?p=acchistory&amp;id=<?php echo $ACC->id; ?>" title="<?php echo mswNFM($ACC->tickCount); ?>"><?php echo mswNFM($ACC->tickCount); ?></a> / <a href="?p=acchistory&amp;id=<?php echo $ACC->id; ?>&amp;disputes=yes" title="<?php echo mswNFM($ACC->dispCount); ?>"><?php echo mswNFM($ACC->dispCount); ?></a></td>
                <?php
                } else {
                ?>
                <td><a href="?p=acchistory&amp;id=<?php echo $ACC->id; ?>" title="<?php echo mswNFM($ACC->tickCount); ?>"><?php echo mswNFM($ACC->tickCount); ?></a></td>
                <?php
                }
                $appendDisUrl = '';
                if ($SETTINGS->disputes == 'yes' && isset($ACC->dispCount) && $ACC->dispCount>0) {
                  $appendDisUrl = '&amp;disputes=yes';
                }
                ?>
                <td class="text-right">
                  <i class="fa fa-<?php echo ($ACC->enabled=='yes' ? 'flag' : 'flag-o'); ?> fa-fw<?php echo ($ACC->enabled=='yes' ? ' msw-green' : ''); ?> cursor_pointer" onclick="mswEnableDisable(this,'accstate','<?php echo $ACC->id; ?>')" title="<?php echo mswSH($msg_response28); ?>"></i>
                  <a href="?p=accounts&amp;edit=<?php echo $ACC->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a>
                  <a href="?p=acchistory&amp;id=<?php echo $ACC->id.$appendDisUrl; ?>&amp;all=yes" title="<?php echo mswSH($msg_accounts13); ?>"><i class="fa fa-calendar fa-fw"></i></a>
                </td>
                </tr>
                <?php
                }
                } else {
                ?>
                <tr class="warning nothing_to_see">
                  <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>"><?php echo $msg_accounts5; ?></td>
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
          foreach ($_GET AS $k => $v) {
          if (!in_array($k, array('p','next'))) {
          ?>
          <input type="hidden" name="<?php echo mswSH($k); ?>" value="<?php echo mswSH($v); ?>">
          <?php
          }
          }
	        if (USER_DEL_PRIV == 'yes') {
	        ?>
          <button onclick="mswButtonOp('accdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels9; ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
	        <button class="btn btn-primary" type="button" onclick="mswProcess('accexp')"><i class="fa fa-download fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_accounts36; ?></span></button>
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