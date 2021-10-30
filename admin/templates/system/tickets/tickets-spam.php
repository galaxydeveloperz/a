<?php if (!defined('PARENT')) { exit; }
define('HIDE_ASSIGN_FILTERS', 1);
define('EXTRAS_LOAD_NO', 1);
// Order and filter by files..
$sql = '';
include(PATH . 'templates/system/tickets/global/order-by.php');
include(PATH . 'templates/system/tickets/global/filter-by.php');
if (isset($_GET['keys']) && $_GET['keys']) {
  $sKeys =  mswSQL($_GET['keys']);
  $sql   = "AND (LOWER(`" . DB_PREFIX . "portal`.`name`) LIKE '%" . $sKeys . "%' OR LOWER(`" . DB_PREFIX . "tickets`.`subject`) LIKE '%" . $sKeys . "%' OR LOWER(`" . DB_PREFIX . "tickets`.`id`) = '" . (int) ltrim($sKeys, '0') . "' OR LOWER(`email`) LIKE '%" . $sKeys . "%' OR LOWER(`comments`) LIKE '%" . $sKeys . "%')";
}
$q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS *,
     `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
     `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
     `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
     `" . DB_PREFIX . "departments`.`name` AS `deptName`,
     `" . DB_PREFIX . "levels`.`name` AS `levelName`,
     IF(`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'),
       `" . DB_PREFIX . "levels`.`id`,
       `" . DB_PREFIX . "levels`.`marker`
     ) AS `levelMarker`,
     (SELECT `name` FROM `" . DB_PREFIX . "users`
      WHERE `" . DB_PREFIX . "users`.`id` = `" . DB_PREFIX . "tickets`.`lockteam`
     ) AS `lockTeamName`,
     (SELECT count(*) FROM `" . DB_PREFIX . "replies`
      WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
     ) AS `replyCount`
     FROM `" . DB_PREFIX . "tickets`
     LEFT JOIN `" . DB_PREFIX . "departments`
     ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
     LEFT JOIN `" . DB_PREFIX . "portal`
     ON `" . DB_PREFIX . "tickets`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
     LEFT JOIN `" . DB_PREFIX . "levels`
     ON (`" . DB_PREFIX . "tickets`.`priority` = 
       IF (`" . DB_PREFIX . "levels`.`marker` NOT IN('low','medium','high'), 
         `" . DB_PREFIX . "levels`.`id`,
         `" . DB_PREFIX . "levels`.`marker`
       )
     )
     WHERE `spamFlag`  = 'yes'
	   $sql
     " . $filterBy . ' ' . mswSQL_deptfilter($ticketFilterAccess) . "
     " . $orderBy."
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
      <li><a href="?p=spam"><?php echo $msg_adheader63; ?></a></li>
      <li class="active"><?php echo $mspubliclang3_7[7]; ?> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      } else {
      ?>
      <li class="active"><a href="?p=spam"><?php echo $msg_adheader63; ?></a> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      }
      ?>
    </ol>

    <?php
    // Were any spam messages cleared?
    if ($SETTINGS->autospam > 0 && isset($spamCleared) && $spamCleared > 0) {
    ?>
    <div class="alert alert-warning alert-dismissable border_2x">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="fa fa-times fa-fw"></i></button>
      <b><i class="fa fa-check fa-fw"></i></b> <?php echo str_replace('{count}', mswNFM($spamCleared), $msadminlang_tickets_3_7[12]); ?>
    </div>
    <?php
    }

    // Search..
    include(PATH . 'templates/system/bootstrap/search-box.php');
    ?>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-heading text-right topfilterarea">
            <?php
            if (USER_ADMINISTRATOR == 'yes' || in_array('add', $userAccess)) {
            ?>
            <button class="btn btn-success btn-sm" type="button" onclick="mswWindowLoc('index.php?p=add')"><i class="fa fa-plus fa-fw"></i></button>
            <?php
            }
            // For small screen devices, we hide the filters to give us more screen space..
            if (in_array(MSW_PFDTCT, array('mobile', 'tablet'))) {
            ?>
            <button class="btn btn-primary btn-sm" type="button" onclick="mswToggleButton('filters')"><i class="fa fa-sort-amount-asc fa-fw"></i></button>
            <button class="btn btn-info btn-sm" type="button" onclick="mswToggleButton('search')"><i class="fa fa-search fa-fw"></i></button>
            <div class="hidetkfltrs" style="display:none">
            <hr>
            <?php
            }
            include(PATH . 'templates/system/tickets/global/order-filter.php');
            include(PATH . 'templates/system/tickets/global/status-filter.php');
            include(PATH . 'templates/system/tickets/global/dept-filter.php');
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
                      <input type="checkbox" onclick="mswCheckBoxes(this.checked,'.panel-body');mswCheckCount('panel-body','delButton','mswCVal');mswCheckCount('panel-body','delButton2','mswCVal2')">
                    </th>
                    <?php
                    } else {
                    ?>
                    <th style="width:5%">
                      <input type="checkbox" onclick="mswCheckBoxes(this.checked,'.panel-body');mswCheckCount('panel-body','delButton2','mswCVal2')">
                    </th>
                    <?php
                    }
                    ?>
                    <th><?php echo TABLE_HEAD_DECORATION . $msadminlang3_7prlevels[3]; ?>, <?php echo $msg_showticket16; ?></th>
                    <th><?php echo TABLE_HEAD_DECORATION . $msg_viewticket25; ?></th>
                    <th><?php echo TABLE_HEAD_DECORATION . $msg_open36; ?></th>
                    <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $msg_script43; ?></th>
                  </tr>
                </thead>
              <tbody>
               <?php
               if ($sqlQryRows > 0) {
               while ($TICKETS = mswSQL_fetchobj($q)) {
               $tLock = 'no';
               ?>
               <tr id="datatr_<?php echo $TICKETS->ticketID; ?>">
               <?php
               if (USER_DEL_PRIV == 'yes') {
               ?>
               <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton','mswCVal');mswCheckCount('panel-body','delButton2','mswCVal2')" name="del[]" value="<?php echo $TICKETS->ticketID; ?>" id="tickets_<?php echo $TICKETS->ticketID; ?>"></td>
               <?php
               } else {
               ?>
               <td><input type="checkbox" onclick="mswCheckCount('panel-body','delButton2','mswCVal2')" name="del[]" value="<?php echo $TICKETS->ticketID; ?>" id="tickets_<?php echo $TICKETS->ticketID; ?>"></td>
               <?php
               }
               ?>
               <td class="tdticketno"><a href="?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>" title="<?php echo mswSH($msg_open7); ?>"><?php echo mswTicketNumber($TICKETS->ticketID, $SETTINGS->minTickDigits, $TICKETS->tickno); ?></a>
               <span class="ticketPriority"><?php echo mswPRMarker($TICKETS->levelMarker, $TICKETS->colors, $TICKETS->levelName); ?></span>
               </td>
               <td><b><?php echo mswResData(mswSH($TICKETS->subject), TICK_SUBJECT_TXT); ?></b>
               <span class="tdCellInfo"><?php echo $MSYS->department($TICKETS->department,$msg_script30); ?></span>
               </td>
               <td><?php echo mswSH($TICKETS->ticketName); ?>
               <span class="ticketDate"><?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->dateformat); ?> @ <?php echo $MSDT->mswDateTimeDisplay($TICKETS->ticketStamp,$SETTINGS->timeformat); ?></span>
               </td>
               <td class="text-right"><button id="tkactbtn_<?php echo $TICKETS->ticketID; ?>" class="btn btn-info btn-xs" type="button" onclick="mswTogTKActions('<?php echo $TICKETS->ticketID; ?>')"><i class="fa fa-chevron-down fa-fw"></i></button> <button class="btn btn-success btn-xs" type="button" onclick="iBox.showURL('?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;quickView=yes','',{width:<?php echo IBOX_QVIEW_WIDTH; ?>,height:<?php echo IBOX_QVIEW_HEIGHT; ?>});return false"><i class="fa fa-binoculars fa-fw"></i></button><span class="treplies"><?php echo str_replace('{count}', mswNFM($TICKETS->replyCount), $msadminlang_tickets_3_7[13]); ?></span></td>
              </tr>
              <tr>
               <td colspan="6" class="ticketactionbuttons" id="tickactions_<?php echo $TICKETS->ticketID; ?>">
               <?php
               // Ticket Options..
               include(PATH . 'templates/system/tickets/global/ticket-options.php');
               ?>
               <button class="btn btn-info btn-xs search_btn_open" type="button" onclick="mswTickAct('open', '<?php echo $TICKETS->ticketID; ?>', 'spam');return false;" title="<?php echo mswSH($msadminlang_tickets_3_7[4]); ?>"><i class="fa fa-chevron-right fa-fw"></i><span class="hidden-xs">  <?php echo $msadminlang_tickets_3_7[4]; ?></span></button>
               <a class="btn btn-default btn-xs" href="?p=view-ticket&amp;print=<?php echo $TICKETS->ticketID; ?>" onclick="window.open(this);return false" style="padding:2px 5px 2px 5px" title="<?php echo mswSH($msadminlang3_7[1]); ?>"><i class="fa fa-print fa-fw"></i><span class="hidden-xs"> <?php echo $msadminlang_tickets_3_7[11]; ?></span></a></td>
              </tr>
              <?php
              }
              } else {
              ?>
              <tr class="warning nothing_to_see">
                <td colspan="5"><?php echo $msg_open10; ?></td>
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
          <button onclick="mswButtonOp('tickdel');return false;" class="btn btn-danger button_margin_right20" disabled="disabled" type="button" id="delButton"><i class="fa fa-trash fa-fw" title="<?php echo mswSH($msg_open15); ?>"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_open15; ?></span> <span id="mswCVal">(0)</span></button>
	        <?php
	        }
	        ?>
          <div class="btn-group">
            <button type="button" class="btn btn-success dropdown-toggle" id="delButton2" disabled="disabled" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <?php echo $msadminlang_tickets_3_7[2]; ?> <i class="fa fa-chevron-down fa-fw"></i> <span id="mswCVal2">(0)</span>
            </button>
            <ul class="dropdown-menu">
              <li><a href="#" onclick="mswButtonOp('tickaccept');return false;"><?php echo $msadminlang_tickets_3_7[1]; ?></a></li>
              <li><a href="#" onclick="mswButtonOp('tickaccept2');return false;"><?php echo $msadminlang_tickets_3_7[0]; ?></a></li>
            </ul>
          </div>
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