<?php if (!defined('PARENT') || !isset($SUPTICK->id)) { exit; } ?>

      <div class="fluid-container windowpanelarea">
        <div class="panel panel-default">
          <div class="panel-heading">
            <?php echo strtoupper($msadminlang3_1adminviewticket[22]); ?> (#<?php echo mswTicketNumber($SUPTICK->id, $SETTINGS->minTickDigits, $SUPTICK->tickno); ?>)
          </div>
          <div class="panel-body">
            <?php
            $q = mswSQL_query("SELECT `name` FROM `" . DB_PREFIX . "users` WHERE `id` IN(" . mswSQL($SUPTICK->assignedto) . ") ORDER BY `name`", __file__, __line__);
            while ($USR = mswSQL_fetchobj($q)) {
              echo '<i class="fa fa-check fa-fw"></i> ' . mswSH($USR->name) . '<br>';
            }
            ?>
          </div>
        </div>
		  </div>
