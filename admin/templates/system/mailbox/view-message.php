<?php if (!defined('PARENT') || !isset($MID)) { exit; }
// Who started this message?
if ($MMSG->staffID == $MSTEAM->id) {
  $msgPoster = mswCD($MSTEAM->name);
} else {
  $PST       = mswSQL_table('users','id',$MMSG->staffID);
  $msgPoster = (isset($PST->name) ? mswCD($PST->name) : $msg_script17);
}
define('JS_LOADER', 'print-friendly.php');
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <li><a href="index.php?p=mailbox"><?php echo $msg_mailbox; ?></a></li>
      <li class="active"><?php echo $msg_adheader61; ?> (<?php echo $msg_mailbox7; ?>)</li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <?php
      include(PATH . 'templates/system/mailbox/mailbox-nav.php');
	    ?>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="text-right">
          <?php
          $links = array();
          $links[] = array(
            'link' => '#',
            'name' => $msg_viewticket75,
            'extra' => 'onclick="mswScrollToArea(\'replyArea\', \'0\', \'0\');return false"',
            'icon' => '<i class="fa fa-plus fa-fw highlight_icon"></i> '
          );
          $links[] = array(
            'link' => '#',
            'name' => $msg_script13,
            'icon' => '<i class="fa fa-print fa-fw"></i> ',
            'extra' => 'onclick="window.print();return false"'
          );
          echo $MSBOOTSTRAP->button(array(
            'text' => $msg_script43,
            'links' => $links,
            'orientation' => ' dropdown-menu-right',
            'centered' => 'no',
            'area' => 'admin',
            'icon' => 'cog'
          ));
          ?>
        </div>  
        <div class="panel panel-default margin_top_10">
          <div class="panel-heading">
            <i class="fa fa-envelope-o fa-fw"></i> <?php echo mswSH($MMSG->subject); ?>
          </div>
          <div class="panel-body">
            <?php
            echo $MSPARSER->mswTxtParsingEngine($MMSG->message);
            ?>
          </div>
          <div class="panel-footer">
            <i class="fa fa-user fa-fw"></i><?php echo mswSH($msgPoster); ?> <i class="fa fa-clock-o fa-fw"></i> <?php echo $MSDT->mswDateTimeDisplay($MMSG->ts, $SETTINGS->dateformat) . ' @ ' . $MSDT->mswDateTimeDisplay($MMSG->ts, $SETTINGS->timeformat); ?>
		      </div>
        </div>

        <?php
        $qPMR = mswSQL_query("SELECT *,`" . DB_PREFIX . "mailreplies`.`ts` AS `repStamp` FROM `" . DB_PREFIX . "mailreplies`
		            LEFT JOIN `" . DB_PREFIX . "users`
				        ON `" . DB_PREFIX . "mailreplies`.`staffID` = `" . DB_PREFIX . "users`.`id`
                WHERE `mailID` = '{$MMSG->id}'
                ORDER BY `" . DB_PREFIX . "mailreplies`.`id`
				        ", __file__, __line__);
        if (mswSQL_numrows($qPMR)>0) {
        while ($REPLIES = mswSQL_fetchobj($qPMR)) {
        ?>
        <div class="panel panel-default">
          <div class="panel-body">
            <?php
            echo $MSPARSER->mswTxtParsingEngine($REPLIES->message);
            ?>
          </div>
          <div class="panel-footer">
            <i class="fa fa-user fa-fw"></i><?php echo mswSH($REPLIES->name); ?> <i class="fa fa-clock-o fa-fw"></i> <?php echo $MSDT->mswDateTimeDisplay($REPLIES->repStamp, $SETTINGS->dateformat) . ' @ ' . $MSDT->mswDateTimeDisplay($REPLIES->repStamp, $SETTINGS->timeformat); ?>
		      </div>
        </div>
        <?php
        }
        }
        ?>
        <div class="text-center" id="replyArea">
          <input type="hidden" name="msgStaff" value="<?php echo $MMSG->staffID; ?>">
		      <input type="hidden" name="msgID" value="<?php echo $MID; ?>">
          <input type="hidden" name="subject" value="<?php echo mswSH($MMSG->subject); ?>">
          <textarea name="message" rows="5" cols="20" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"></textarea><br><br>
          <button class="btn btn-primary" type="button" onclick="mswProcess('mbreply')"><i class="fa fa-check fa-fw"></i> <?php echo $msg_mailbox30; ?></button>
          <?php
          $BF = mswSQL_table('mailassoc','mailID',(int) $_GET['msg'],'AND `staffID` = \'' . $MSTEAM->id . '\'');
          if (isset($BF->folder)) {
          $mailBoxUrl = ($BF->folder == 'inbox' ? 'index.php?p=mailbox' : 'index.php?p=mailbox&amp;f=' . $BF->folder);
          ?>
          <button class="btn btn-link" type="button" onclick="mswWindowLoc('<?php echo $mailBoxUrl; ?>')"><i class="fa fa-times fa-fw"></i> <?php echo $msg_levels11; ?></button>
          <?php
          }
          ?>
        </div>
        <hr>
      </div>
    </div>
    </form>

  </div>