<?php if (!defined('PARENT') || !isset($_GET['id'])) { exit; }
$_GET['id']   = (int) $_GET['id'];
$SQL          = '';

if (isset($_GET['keys']) && $_GET['keys']) {
  $_GET['keys']  = mswSQL(strtolower($_GET['keys']));
  $SQL           = 'AND (LOWER(`' . DB_PREFIX . 'replies`.`comments`) LIKE \'%' . $_GET['keys'] . '%\')';
}
if (isset($_GET['from'],$_GET['to']) && $_GET['from'] && $_GET['to']) {
  $from  = $MSDT->mswDatePickerFormat($_GET['from']);
  $to    = $MSDT->mswDatePickerFormat($_GET['to']);
  $SQL  .= " AND (DATE(FROM_UNIXTIME(`" . DB_PREFIX . "replies`.`ts`)) BETWEEN '{$from}' AND '{$to}')";
}

$q = mswSQL_query("SELECT SQL_CALC_FOUND_ROWS *,
     (SELECT `subject` FROM `" . DB_PREFIX . "tickets`
       WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
     ) AS `tickSubject`,
     (SELECT `tickno` FROM `" . DB_PREFIX . "tickets`
       WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
     ) AS `tickRandNo`
     FROM `" . DB_PREFIX . "replies`
     WHERE `replyType` = 'admin'
     AND `replyUser` = '{$_GET['id']}'
     $SQL
     GROUP BY `" . DB_PREFIX . "replies`.`id`,`" . DB_PREFIX . "replies`.`ticketID`
     ORDER BY `" . DB_PREFIX . "replies`.`id` DESC
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
      if (in_array('teamman', $userAccess) || USER_ADMINISTRATOR == 'yes') {
      ?>
      <li><a href="index.php?p=teamman"><?php echo $msg_adheader58; ?></a></li>
      <?php
      }
      ?>
      <li><a href="index.php?p=team&amp;edit=<?php echo $_GET['id']; ?>"><?php echo mswSH($U->name); ?></a></li>
      <?php
      if (isset($_GET['keys'])) {
      ?>
      <li><a href="?p=responses&amp;id=<?php echo $_GET['id']; ?>"><?php echo $msg_user87; ?></a></li>
      <li class="active"><?php echo $mspubliclang3_7[7]; ?> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      } else {
      ?>
      <li class="active"><a href="?p=responses&amp;id=<?php echo $_GET['id']; ?>"><?php echo $msg_user87; ?></a> (<?php echo mswNFM($sqlQryRows); ?>)</li>
      <?php
      }
      ?>
    </ol>

    <?php
    // Search..
    include(PATH . 'templates/system/bootstrap/search-responses.php');
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
            include(PATH . 'templates/system/bootstrap/page-filter.php');
            if (in_array(MSW_PFDTCT, array('mobile', 'tablet'))) {
            ?>
            </div>
            <?php
            }
            ?>
          </div>
          <div class="panel-body">
            <div class="table-responsive userreplyperf">
                <?php
                if ($sqlQryRows > 0) {
                ?>
                <table class="table table-striped table-hover">
                <tbody>
                <?php
                while ($REPLY = mswSQL_fetchobj($q)) {
                ?>
                <tr>
                  <td><a href="?p=view-ticket&amp;id=<?php echo $REPLY->ticketID; ?>" onclick="window.open(this);return false"><?php echo mswTicketNumber($REPLY->ticketID, $SETTINGS->minTickDigits, $REPLY->tickRandNo); ?></a><span class="date"><?php echo $MSDT->mswDateTimeDisplay($REPLY->ts, $SETTINGS->dateformat).' @ '.$MSDT->mswDateTimeDisplay($REPLY->ts,$SETTINGS->timeformat); ?></span></td>
                  <td><b><?php echo mswSH($REPLY->tickSubject); ?></b><hr><?php echo mswNL2BR((TEAM_REPLY_COMM_LIMIT > 0 ? substr(mswSH($MSBB->cleaner($REPLY->comments)),0,TEAM_REPLY_COMM_LIMIT) . '...' : mswSH($MSBB->cleaner($REPLY->comments)))); ?></td>
                  <td><a href="?p=edit-reply&amp;id=<?php echo $REPLY->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a></td>
                </tr>
                <?php
                }
                ?>
                </tbody>
                </table>
                <?php
                } else {
                echo $msg_user22;
                }
                ?>
            </div>
        </div>

        <?php
        if (in_array('teamman', $userAccess)  || USER_ADMINISTRATOR == 'yes') {
        ?>
        <div class="panel-footer">
          <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=teamman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels11; ?></span></button>
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
    </form>

  </div>