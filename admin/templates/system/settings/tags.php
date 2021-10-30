<?php if (!defined('PARENT')) { exit; } ?>

      <div class="fluid-container windowpanelarea">
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-tags fa-fw"></i> <?php echo strtoupper($msg_tools22); ?>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <tbody>
                <?php
                if (!empty($msg_tools23)) {
                  foreach ($msg_tools23 AS $k => $v) {
                  ?>
                  <tr>
                    <td><?php echo $k; ?></td>
                    <td class="text-right"><?php echo $v; ?></td>
                  </tr>
                  <?php
                  }
                }
                ?>
              </tbody>
              </table>
            </div>
          </div>
        </div>
		  </div>
