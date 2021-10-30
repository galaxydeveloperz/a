<?php if (!defined('PARENT') || !isset($SUPTICK->id)) { exit; } ?>
  <div class="fluid-container windowpanelarea">

    <div class="panel panel-default">
      <div class="panel-heading">
        <?php echo strtoupper($msadminlang3_7[3]); ?> (#<?php echo mswTicketNumber($SUPTICK->id, $SETTINGS->minTickDigits, $SUPTICK->tickno); ?>)
      </div>
      <div class="panel-body">
        <textarea name="notes" class="form-control" rows="8" cols="40"></textarea>
      </div>
      <div class="panel-footer">
         <button class="btn btn-primary" type="button" onclick="mswHistory('<?php echo $SUPTICK->id; ?>')"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msadminlang3_7[2]; ?></span></button>
	    </div>
    </div>

  </div>