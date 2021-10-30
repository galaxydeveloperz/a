<?php if (!defined('PARENT') || !isset($SUPTICK->id)) { exit; } ?>
  <div class="fluid-container windowpanelarea">

    <div class="panel panel-default">
      <div class="panel-heading">
        <?php echo strtoupper($msg_viewticket99); ?> (#<?php echo mswTicketNumber($SUPTICK->id, $SETTINGS->minTickDigits, $SUPTICK->tickno); ?>)
      </div>
      <div class="panel-body">
        <textarea name="notes" class="form-control" rows="8" cols="40"><?php echo mswSH($SUPTICK->ticketNotes); ?></textarea>
      </div>
      <div class="panel-footer">
         <button class="btn btn-primary" type="button" onclick="mswNotes('<?php echo $SUPTICK->id; ?>')"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_viewticket99; ?></span></button>
	    </div>
    </div>

  </div>