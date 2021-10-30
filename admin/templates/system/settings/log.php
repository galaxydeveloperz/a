<?php if (!defined('PARENT')) { exit; }
$from        = (isset($_GET['from']) && $MSDT->mswDatePickerFormat($_GET['from'])!='0000-00-00' ? $_GET['from'] : '');
$to          = (isset($_GET['to']) && $MSDT->mswDatePickerFormat($_GET['to'])!='0000-00-00' ? $_GET['to'] : '');
$keys        = '';
$where       = array();
if (isset($_GET['keys']) && $_GET['keys']) {
  $chop  = explode(' ',$_GET['keys']);
  $words = '';
  for ($i=0; $i<count($chop); $i++) {
    $words .= ($i ? 'OR ' : 'WHERE (') . "`" . DB_PREFIX . "portal`.`name` LIKE '%" . mswSQL($chop[$i]) . "%' OR `" . DB_PREFIX . "users`.`name` LIKE '%" . mswSQL($chop[$i]) . "%' ";
  }
  if ($words) {
    $where[] = $words.')';
  }
}
if ($from && $to) {
  $where[]  = (!empty($where) ? 'AND ' : 'WHERE ').'DATE(FROM_UNIXTIME(`' . DB_PREFIX . 'log`.`ts`)) BETWEEN \''.$MSDT->mswDatePickerFormat($from).'\' AND \''.$MSDT->mswDatePickerFormat($to).'\'';
}
$q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS *,
     `" . DB_PREFIX . "log`.`ts` AS `lts`,
     `" . DB_PREFIX . "log`.`id` AS `logID`,
     `" . DB_PREFIX . "log`.`userID` AS `personID`,
     `" . DB_PREFIX . "log`.`ip` AS `entryLogIP`,
     `" . DB_PREFIX . "portal`.`name` AS `portalName`,
     `" . DB_PREFIX . "users`.`name` AS `userName`
     FROM `" . DB_PREFIX . "log`
     LEFT JOIN `" . DB_PREFIX . "users`
     ON `" . DB_PREFIX . "log`.`userID` = `" . DB_PREFIX . "users`.`id`
     LEFT JOIN `" . DB_PREFIX . "portal`
     ON `" . DB_PREFIX . "log`.`userID` = `" . DB_PREFIX . "portal`.`id`
     " . (!empty($where) ? implode(mswNL(),$where) : '') . "
     ORDER BY `" . DB_PREFIX . "log`.`id` DESC
     $sqlLimStr
     ", __file__, __line__);
$c            = mswSQL_fetchobj(mswSQL_query("SELECT FOUND_ROWS() AS `rows`", __file__, __line__));
$sqlQryRows  =  (isset($c->rows) ? $c->rows : '0');
$actualRows   = mswSQL_rows('log');
define('LOAD_DATE_PICKERS', 1);
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('settings', $userAccess) || USER_ADMINISTRATOR == 'yes') {
      ?>
      <li><a href="index.php?p=settings"><?php echo $msg_adheader2; ?></a></li>
      <?php
      }
      if (isset($_GET['keys'])) {
      ?>
      <li><a href="?p=log"><?php echo $msg_adheader20; ?></a></li>
      <li class="active"><?php echo $mspubliclang3_7[7]; ?> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      } else {
      ?>
      <li class="active"><a href="?p=log"><?php echo $msg_adheader20; ?></a> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      }
      ?>
    </ol>

    <?php
    // Search..
    include(PATH . 'templates/system/bootstrap/search-accounts.php');
    ?>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right">
            <?php
            if (in_array(MSW_PFDTCT, array('mobile', 'tablet'))) {
            ?>
            <button class="btn btn-primary btn-sm" type="button" onclick="mswToggleButton('filters')"><i class="fa fa-sort-amount-asc fa-fw"></i></button>
            <button class="btn btn-info btn-sm" type="button" onclick="mswToggleButton('search')"><i class="fa fa-search fa-fw"></i></button>
            <div class="hidetkfltrs" style="display:none">
            <hr>
            <?php
            }
            // Filters..
            $links   = array(array('link' => '?p='.$_GET['p'].mswQueryParams(array('p','type','from','to','q')),  'name' => $msg_log11));
            $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;type=user' . mswQueryParams(array('p','type','next')),'name' => $msg_log13, 'active' => (isset($_GET['type']) && $_GET['type'] == 'user' ? ' class="active"' : ''));
            $links[] = array('link' => '?p=' . $_GET['p'] . '&amp;type=acc' . mswQueryParams(array('p','type','next')),'name' => $msg_log12,  'active' => (isset($_GET['type']) && $_GET['type'] == 'acc' ? ' class="active"' : ''));
            echo $MSBOOTSTRAP->button(array(
              'text' => $msg_search20,
              'links' => $links,
              'orientation' => '',
              'centered' => 'no',
              'area' => 'admin',
              'icon' => '',
              'param' => 'type'
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
                  <th style="width:5%">
                    <input type="checkbox" onclick="mswCheckBoxes(this.checked,'.panel-body');mswCheckCount('panel-body','delButton','mswCVal')">
                  </th>
                  <?php
                  }
                  ?>
                  <th><?php echo TABLE_HEAD_DECORATION . str_replace('/', ', ', $msg_log); ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_log16; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $msg_log8; ?></th>
                  <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_log7; ?></th>
               </tr>
              </thead>
              <tbody>
               <?php
               if (mswSQL_numrows($q)>0) {
               while ($LOG = mswSQL_fetchobj($q)) {
               // IP entry..
               $ips_html = '';
               if (strpos($LOG->entryLogIP,',')!==false) {
                 $ips = array_map('trim',explode(',', $LOG->entryLogIP));
                 foreach ($ips AS $ipA) {
                   $ips_html .= $ipA.' <a href="'.str_replace('{ip}',$ipA,IP_LOOKUP).'" onclick="window.open(this);return false"><i class="fa fa-external-link fa-fw"></i></a><br>';
                 }
               } else {
                 $ips_html = $LOG->entryLogIP.' <a href="'.str_replace('{ip}',$LOG->entryLogIP,IP_LOOKUP).'" onclick="window.open(this);return false"><i class="fa fa-external-link fa-fw"></i></a>';
               }
               ?>
               <tr id="datatr_<?php echo $LOG->logID; ?>">
               <?php
               $name = ($LOG->type=='acc' ? $LOG->portalName : $LOG->userName);
               if (USER_DEL_PRIV == 'yes') {
               ?>
               <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal')" name="del[]" value="<?php echo $LOG->logID; ?>"></td>
               <?php
               }
               ?>
               <td><b><?php echo mswSH(($name ? $name : $msg_script17)); ?></b> <a href="?p=<?php echo ($LOG->type=='acc' ? 'accounts&amp;edit='.$LOG->personID : 'team&amp;edit='.$LOG->personID); ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-edit fa-fw"></i></a></td>
               <td><?php echo ($LOG->type=='user' ? $msg_log15 : $msg_log14); ?></td>
               <td><?php echo $ips_html; ?></td>
               <td class="text-right"><?php echo $MSDT->mswDateTimeDisplay($LOG->lts,$SETTINGS->dateformat).' / '.$MSDT->mswDateTimeDisplay($LOG->lts,$SETTINGS->timeformat); ?></td>
               </tr>
               <?php
               }
               } else {
               ?>
               <tr class="warning nothing_to_see">
                <td colspan="<?php echo (USER_DEL_PRIV == 'yes' ? '5' : '4'); ?>"><?php echo $msg_log4; ?></td>
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
            <input type="hidden" name="from" value="<?php echo mswSH($from); ?>">
            <input type="hidden" name="to" value="<?php echo mswSH($to); ?>">
            <input type="hidden" name="keys" value="<?php echo (isset($_GET['keys']) ? $_GET['keys'] : ''); ?>">
            <?php
            if (USER_DEL_PRIV == 'yes') {
            ?>
            <button onclick="mswButtonOp('logdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels9; ?></span> <span id="mswCVal">(0)</span></button>
            <?php
            }
            ?>
            <button class="btn btn-primary button_margin_right20" type="button" onclick="mswProcess('log')"><i class="fa fa-save fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_log3; ?></span></button>
            <?php
            if (USER_DEL_PRIV == 'yes') {
            ?>
            <button onclick="mswButtonOp('logclr');return false;" class="btn btn-warning" type="button"><i class="fa fa-times fa-fw" title="<?php echo mswSH($msg_levels9); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_log2; ?></span></button>
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