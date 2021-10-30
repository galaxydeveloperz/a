<?php if (!defined('PARENT') || !isset($SUPTICK->id)) { exit; } ?>
  <div class="fluid-container windowpanelarea">

    <div class="panel panel-default">
      <div class="panel-heading">
        (#<?php echo mswTicketNumber($SUPTICK->id, $SETTINGS->minTickDigits, $SUPTICK->tickno); ?>) <b><?php echo mswResData(mswSH($SUPTICK->subject), TICK_SUBJECT_TXT); ?></b>
      </div>
      <div class="panel-body">
        <select name="stat_change" class="form-control">
          <option value="">- - - - - -</option>
					<?php
					if ($SUPTICK->ticketStatus == '') {
					  $SUPTICK->ticketStatus = 'open';
					}
					foreach ($ticketStatusSel AS $sk => $sv) {
            if (!in_array($sk, array('close','closed')) && $sk != $SUPTICK->ticketStatus) {
            ?>
            <option value="index.php?p=view-<?php echo ($SUPTICK->isDisputed == 'yes' ? 'dispute' : 'ticket') . '&amp;id=' . $SUPTICK->id . '&amp;act=' . ($sk != 'open' ? 'status-' : '') . $sk; ?>"><?php echo $sv[0]; ?></option>
            <?php
            }
          }
          ?>
        </select>
      </div>
      <div class="panel-footer">
        <button class="btn btn-primary" type="button" onclick="mswStatChange('<?php echo preg_replace('/[^a-zA-Z]/', '', $_GET['showStatuses']); ?>','<?php echo $SUPTICK->id; ?>')"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_levels10; ?></span></button>
	    </div>
    </div>

  </div>