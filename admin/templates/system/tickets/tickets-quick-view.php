<?php if (!defined('PARENT') || !isset($SUPTICK->id)) { exit; } ?>
  <div class="fluid-container windowpanelarea">

    <div class="panel panel-default">
      <div class="panel-heading">
        (#<?php echo mswTicketNumber($SUPTICK->id, $SETTINGS->minTickDigits, $SUPTICK->tickno); ?>) <?php echo mswSH($SUPTICK->subject); ?>
      </div>
      <div class="panel-body">
        <?php
        echo $MSPARSER->mswTxtParsingEngine($SUPTICK->comments);
        $url = '?p=view-' . ($SUPTICK->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&amp;id=' . $SUPTICK->id;
        ?>
      </div>
      <div class="panel-footer text-center">
         <button class="btn btn-primary" type="button" onclick="window.location = '<?php echo $url; ?>'"><i class="fa fa-search fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo ($SUPTICK->isDisputed == 'yes' ? $msg_open24 : $msg_open7); ?></span></button>
	    </div>
    </div>

    <?php
    $q_replies = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "replies`
                 WHERE `ticketID` = '{$SUPTICK->id}'
                 ORDER BY `id`
                 ", __file__, __line__);
    while ($REPLIES = mswSQL_fetchobj($q_replies)) {
      switch ($REPLIES->replyType) {
        case 'admin':
          $USER       = mswSQL_table('users', 'id', $REPLIES->replyUser);
          $replyName  = (isset($USER->name) ? mswSH($USER->name) : $msg_script17);
          $label      = 'panel panel-warning';
          $icon       = 'users';
          break;
        case 'visitor':
          if ($REPLIES->disputeUser > 0) {
            $DU         = mswSQL_table('portal', 'id', $REPLIES->disputeUser, '', '`name`');
            $replyName  = (isset($DU->name) ? mswSH($DU->name) : $msg_script17);
          } else {
            $USER       = mswSQL_table('portal', 'id', $REPLIES->replyUser, '', '`name`');
            $replyName  = (isset($USER->name) ? mswSH($USER->name) : $msg_script17);
          }
          $label      = 'panel panel-default';
          $icon       = 'user';
          break;
      }
      ?>
      <div class="<?php echo $label; ?>">
        <div class="panel-heading colorchangeheader left-align">
          <i class="fa fa-<?php echo $icon; ?> fa-fw"></i> <?php echo $replyName; ?> <span class="mobilebreakpoint"><i class="fa fa-clock-o fa-fw"></i><?php echo $MSDT->mswDateTimeDisplay($REPLIES->ts, $SETTINGS->dateformat).' @ '.$MSDT->mswDateTimeDisplay($REPLIES->ts,$SETTINGS->timeformat); ?></span>
        </div>
        <div class="panel-body" id="rp<?php echo $REPLIES->id; ?>">
          <?php
          echo $MSPARSER->mswTxtParsingEngine($REPLIES->comments);
          ?>
        </div>
      </div>
      <?php
    }

    // Time worked on ticket..
    if ($SETTINGS->timetrack == 'yes') {
    ?>
    <div class="ticketworktime">
      <i class="fa fa-clock-o fa-fw"></i> <?php echo $msadminlang_tickets_3_7[18] . '<p>' . $MSDT->worktime(($SUPTICK->worktime ? $SUPTICK->worktime : '00:00:00'), $msadminlang_tickets_3_7[19]); ?></p>
    </div>
    <?php
    }
    ?>

  </div>