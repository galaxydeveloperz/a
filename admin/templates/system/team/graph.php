<?php if (!defined('PARENT')) { exit; }
$_GET['id'] = (int) $_GET['id'];
$USER       = mswSQL_table('users', 'id', $_GET['id']);
mswVLQY($USER);
include(PATH . 'control/classes/class.graphs.php');
$lastYear         = date('Y', strtotime('last year'));
$graph            = new graphs();
$graph->settings  = $SETTINGS;
$graph->datetime  = $MSDT;
$graph->years     = array($lastYear, date('Y', $MSDT->mswTimeStamp()));
$gdata            = $graph->responses($ticketFilterAccess, $USER->id);
define('JS_LOADER', 'home-graph.php');
$lastTS           = $MSUSERS->last($USER->id);
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
      <li><a href="index.php?p=team&amp;edit=<?php echo $_GET['id']; ?>"><?php echo mswSH($USER->name); ?></a></li>
      <li class="active"><?php echo $msg_user86; ?></li>
    </ol>

    <div class="row">
      <div class="col-lg-8">
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-bar-chart fa-fw"></i> <?php echo str_replace(array('{lastyear}','{year}'), array($lastYear, date('Y', $MSDT->mswTimeStamp())), $msadminlang3_7[6]); ?>
          </div>
          <div class="panel-body" style="padding-right:0;padding-left:0">
            <?php
            if ($gdata[0] || $gdata[1]) {
            define('GRAPH_LOADER', 1);
            ?>
            <div class="graphLoader"></div>
            <div class="ct-chart"></div>
            <?php
            } else {
            ?>
            <div class="no_graph_to_see"><?php echo $msg_home58; ?></div>
            <?php
            }
            ?>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="panel panel-default">
          <div class="panel-body">
            <?php
            $total  = $MSUSERS->stats($_GET['id'], 'total');
            $sfreps = mswSQL_rows('replies WHERE `replyType` = \'admin\'', false);
            $allrp  = (($total / $sfreps) * 100);
            $perc   = mswNFM($allrp, 2);
            echo $msadminlang_user_3_7[5]; ?>: <span class="highlightPass"><?php echo $MSDT->mswDateTimeDisplay($USER->ts, $SETTINGS->dateformat); ?></span><br><br>
            <?php echo $msadminlang_user_3_7[0]; ?>: <b><?php echo $total; ?></b><br>
            <?php echo $msadminlang_user_3_7[1]; ?>: <b><?php echo $MSUSERS->stats($_GET['id'], 'year'); ?></b><br>
            <?php echo $msadminlang_user_3_7[2]; ?>: <b><?php echo $MSUSERS->stats($_GET['id'], 'month'); ?></b><br>
            <?php echo $msadminlang_user_3_7[3]; ?>: <b><?php echo $MSUSERS->stats($_GET['id'], '3month'); ?></b><br>
            <?php echo $msadminlang_user_3_7[4]; ?>: <b><?php echo $MSUSERS->stats($_GET['id'], '6month'); ?></b><br>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-body">
            <?php
            $yearspassed = date('Y', $MSDT->mswTimeStamp()) - date('Y', $USER->ts);
            $monthspassed = abs((date('Y', $MSDT->mswTimeStamp()) - date('Y', $USER->ts))*12 + (date('m', $MSDT->mswTimeStamp()) - date('m', $USER->ts)));
            $mp = ($total > 0 && $monthspassed > 0 ? mswNFM($total / $monthspassed, 2) : $msg_script17);
            $yp = ($total > 0 && $yearspassed > 0 ? mswNFM($total / $yearspassed, 2) : $msg_script17);
            echo $msadminlang_user_3_7[6]; ?>: <span class="highlightPass"><?php echo ($lastTS > 0 ? $MSDT->mswDateTimeDisplay($lastTS, $SETTINGS->dateformat) : $msg_script17); ?></span><br><br>
            <?php echo $msadminlang_user_3_7[7]; ?>: <b><?php echo (substr($mp, -2) == '00' ? substr($mp, 0, -3) : $mp); ?></b><br>
            <?php echo $msadminlang_user_3_7[8]; ?>: <b><?php echo (substr($yp, -2) == '00' ? substr($yp, 0, -3) : $yp); ?></b><br><br>
            <?php echo $msadminlang_user_3_7[9]; ?>: <b><?php echo (substr($perc, -2) == '00' ? substr($perc, 0, -3) : $perc); ?>%</b><br><br>
            <a href="#" onclick="mswProcess('tmrep', '<?php echo $_GET['id']; ?>');return false;"><i class="fa fa-save fa-fw"></i> <?php echo $msadminlang_user_3_7[21]; ?></a>
          </div>
        </div>
      </div>
    </div>

    <?php
    if (TEAM_USER_REPLIES > 0) {
    ?>
    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-commenting fa-fw"></i> <?php echo str_replace('{count}', TEAM_USER_REPLIES, $msadminlang_user_3_7[10]); ?>
          </div>
          <div class="panel-body">
            <div class="table-responsive userreplyperf">
              <?php
              $q = mswSQL_query("SELECT *,
                   (SELECT `subject` FROM `" . DB_PREFIX . "tickets`
                    WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
                   ) AS `tickSubject`,
                   (SELECT `tickno` FROM `" . DB_PREFIX . "tickets`
                    WHERE `" . DB_PREFIX . "replies`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
                   ) AS `tickRandNo`
                   FROM `" . DB_PREFIX . "replies`
                   WHERE `replyType` = 'admin'
                   AND `replyUser` = '{$USER->id}'
                   ORDER BY `id` DESC LIMIT
                   " . TEAM_USER_REPLIES, __file__, __line__);
              if (mswSQL_numrows($q) > 0) {
              ?>
              <table class="table table-striped table-hover">
              <tbody>
              <?php
              while ($REP = mswSQL_fetchobj($q)) {
              ?>
                <tr>
                  <td><a href="?p=view-ticket&amp;id=<?php echo $REP->ticketID; ?>" onclick="window.open(this);return false"><?php echo mswTicketNumber($REP->ticketID, $SETTINGS->minTickDigits, $REP->tickRandNo); ?></a><span class="date"><?php echo $MSDT->mswDateTimeDisplay($REP->ts, $SETTINGS->dateformat).' @ '.$MSDT->mswDateTimeDisplay($REP->ts,$SETTINGS->timeformat); ?></span></td>
                  <td><b><?php echo mswSH($REP->tickSubject); ?></b><hr><?php echo mswNL2BR((TEAM_REPLY_COMM_LIMIT > 0 ? substr(mswSH($MSBB->cleaner($REP->comments)),0,TEAM_REPLY_COMM_LIMIT) . '...' : mswSH($MSBB->cleaner($REP->comments)))); ?></td>
                  <td><a href="?p=edit-reply&amp;id=<?php echo $REP->id; ?>" title="<?php echo mswSH($msg_script9); ?>"><i class="fa fa-pencil fa-fw"></i></a></td>
                </tr>
              <?php
              }
              ?>
              </tbody>
              </table>
              <?php
              } else {
              echo $msg_open10;
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
    }
    ?>
  </div>