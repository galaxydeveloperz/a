<?php if (!defined('PARENT') || !isset($SUPTICK->id)) { exit; } ?>

      <div class="fluid-container windowpanelarea">
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-users fa-fw"></i> <?php echo strtoupper($msg_disputes5); ?> (#<?php echo mswTicketNumber($SUPTICK->id, $SETTINGS->minTickDigits, $SUPTICK->tickno); ?>)
          </div>
          <div class="panel-body">
            <?php
            $VIS = mswSQL_table('portal', 'id', $SUPTICK->visitorID);
            echo '<b>' . (isset($VIS->name) ? mswSH($VIS->name) : $msg_script17) . '</b><hr>';
            $q = mswSQL_query("SELECT `name` FROM `" . DB_PREFIX . "disputes`
                 LEFT JOIN `" . DB_PREFIX . "portal`
                 ON `" . DB_PREFIX . "disputes`.`visitorID` = `" . DB_PREFIX . "portal`.`id`
                 WHERE `" . DB_PREFIX . "disputes`.`ticketID` = '{$SUPTICK->id}'
                 ORDER BY `" . DB_PREFIX . "portal`.`name`", __file__, __line__);
            while ($ACC = mswSQL_fetchobj($q)) {
              if (isset($ACC->name)) {
                echo mswSH($ACC->name) . '<br>';
              } else {
                echo $msg_script17 . '<br>';
              }
            }
            ?>
          </div>
        </div>

        <div class="text-center">
          <button class="btn btn-primary" onclick="window.location = '?p=view-dispute&amp;disputeUsers=<?php echo $SUPTICK->id; ?>'"><i class="fa fa-edit fa-fw"></i> <?php echo $msg_disputes8; ?></button>
        </div>
		  </div>
