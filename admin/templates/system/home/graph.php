<?php if (!defined('PARENT')) { exit; } ?>
        <div class="col-lg-8">
          <div class="panel panel-default">
            <div class="panel-heading left-align">
              <i class="fa fa-bar-chart fa-fw"></i> <?php echo str_replace(array('{lastyear}','{year}'), array($lastYear, date('Y', $MSDT->mswTimeStamp())), $msadminlang3_7[5]); ?>
            </div>
            <div class="panel-body grapharea msw-graph">
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
          <?php
          if (USER_ADMINISTRATOR == 'yes') {
          ?>
          <div class="row dashbuttons">
            <div class="col-lg-6 col-md-6 addbutton">
              <button class="btn btn-primary btn-lg btn-block" type="button" onclick="window.location = '?p=add'"><i class="fa fa-plus-circle fa-fw"></i> <?php echo $msadminlang_dashboard_3_7[7]; ?></button>
            </div>
            <div class="col-lg-6 col-md-6 staffbutton">
              <button class="btn btn-info btn-lg btn-block" type="button" onclick="window.location = '?p=team'"><i class="fa fa-user-plus fa-fw"></i> <?php echo $msg_adheader57; ?></button>
            </div>
          </div>
          <?php
          }
          ?>
        </div>