<?php if (!defined('PARENT')) { exit; }
        if (defined('LOADED_HOME')) {
        ?>
        <div class="form-group">
          <form method="get" action="index.php" id="sform">
          <input type="hidden" name="p" value="search">
          <div class="form-group">
            <div class="form-group input-group">
              <input type="text" class="form-control" name="keys" placeholder="<?php echo mswSH($msg_header4); ?>">
              <span class="input-group-addon"><i class="fa fa-search fa-fw cursorp" onclick="mswSubmit('sform', 'keys')"></i></span>
            </div>
          </div>
          </form>
        </div>
        <?php
        // Show for beta ONLY..
        if (defined('DEV_BETA') && DEV_BETA != 'no') {
        ?>
        <div class="alert alert-warning" style="border-width:2px">
          <span class="pull-right"><i class="fa fa-flask fa-fw"></i></span>
          <b>BETA VERSION</b>
          <hr>
          <i class="fa fa-hourglass fa-fw"></i> Currently at beta: <?php echo DEV_BETA; ?><br>
          <i class="fa fa-calendar fa-fw"></i> Beta Expiry: <?php echo date('j M Y', strtotime(DEV_BETA_EXP)); ?>
          <hr>
          <i class="fa fa-arrow-right fa-fw"></i> <a href="https://www.maianbeta.com/support/" onclick="window.open(this);return false">View Beta Forum</a>
        </div>
        <?php
        }
        ?>
        <div class="panel panel-default">
          <div class="panel-body">
            <?php
            if (USER_ADMINISTRATOR == 'yes') {
            ?>
            <i class="fa fa-user fa-fw"></i> <a href="index.php?p=team&amp;edit=<?php echo $MSTEAM->id; ?>"><?php echo mswSH($MSTEAM->name); ?></a><br>
            <?php
            } else {
              if ($MSTEAM->profile == 'yes') {
              $url = (in_array('team', $userAccess) ? 'index.php?p=team&amp;edit=' . $MSTEAM->id : 'index.php?p=cp');
              ?>
              <i class="fa fa-user fa-fw"></i> <a href="<?php echo $url; ?>"><?php echo mswSH($MSTEAM->name); ?></a><br>
              <?php
              } else {
              ?>
              <i class="fa fa-user fa-fw"></i> <?php echo mswSH($MSTEAM->name); ?><br>
              <?php
              }
            }
            ?>
            <i class="fa fa-envelope-o fa-fw"></i> <?php echo mswSH($MSTEAM->email); ?>
            <?php
              if (USER_ADMINISTRATOR == 'yes') {
              ?>
              <hr>
              <a href="#" onclick="iBox.showURL('?sys_overview=yes','',{width:<?php echo IBOX_SYSOVV_WIDTH; ?>,height:<?php echo IBOX_SYSOVV_HEIGHT; ?>});return false"><i class="fa fa-caret-right fa-fw"></i> <?php echo $msadminlang_dashboard_3_7[6]; ?></a>
              <?php
              if ($SETTINGS->adminlock == 'yes' && USER_ADMINISTRATOR == 'yes') {
              ?>
              <br><a href="#" onclick="iBox.showURL('?ticket_locks=yes','',{width:<?php echo IBOX_SYSLOCKS_WIDTH; ?>,height:<?php echo IBOX_SYSLOCKS_HEIGHT; ?>});return false"><i class="fa fa-caret-right fa-fw"></i> <?php echo $msadminlang_dashboard_3_7[9]; ?></a>
              <?php
              }
            }
            ?>
          </div>
        </div>
        <div class="panel panel-default homepanelov">
          <div class="panel-body<?php echo (MSW_PFDTCT == 'pc' ? ' homepanellov_scroll' : ''); ?>">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <tbody>
                <tr>
                  <td><a href="?p=open"><?php echo (isset($ticketStatusSel['open'][0]) ? $ticketStatusSel['open'][0] : $msg_adheader5); ?></a></td>
                  <td class="text-right"><a href="?p=open"><span class="label label-success"><?php echo mswSQL_rows('tickets WHERE `ticketStatus` = \'open\' AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'no\' ' . mswSQL_deptfilter($ticketFilterAccess)); ?></span></a></td>
                </tr>
                <?php
                if (count($ticketStatusSel) > 3) {
                  foreach($ticketStatusSel AS $hcsk => $hcsv) {
                    if (!in_array($hcsk, array('open','close','closed'))) {
                    ?>
                    <tr>
                      <td><a href="?t_status=<?php echo $hcsk; ?>"><?php echo $hcsv[0]; ?></a></td>
                      <td class="text-right"><a href="?t_status=<?php echo $hcsk; ?>"><span class="label label-success"><?php echo mswSQL_rows('tickets WHERE `ticketStatus` = \'' . mswSQL($hcsk) . '\' AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'no\' ' . mswSQL_deptfilter($ticketFilterAccess)); ?></span></a></td>
                    </tr>
                    <?php
                    }
                  }
                }
                if ($SETTINGS->disputes == 'yes') {
                ?>
                <tr>
                  <td><a href="?p=disputes"><?php echo $msg_adheader28; ?></a></td>
                  <td class="text-right"><a href="?p=disputes"><span class="label label-info"><?php echo mswSQL_rows('tickets WHERE `ticketStatus` = \'open\' AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'yes\' ' . mswSQL_deptfilter($ticketFilterAccess)); ?></span></a></td>
                </tr>
                <?php
                }
                ?>
                <tr>
                  <td><a href="?p=assign"><?php echo $msg_home52; ?></a></td>
                  <td class="text-right"><a href="?p=assign"><span class="label label-default"><?php echo mswSQL_rows('tickets WHERE `ticketStatus` = \'open\' AND `assignedto` = \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'no\' ' . mswSQL_deptfilter($ticketFilterAccess)); ?></span></a></td>
                </tr>
              </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php
        if (USER_ADMINISTRATOR == 'no') {
          if (in_array('add', $userAccess) || in_array('team', $userAccess)) {
          ?>
          <div class="row dashbuttons">
            <?php
            $rws = 6;
            if (in_array('add', $userAccess)) {
              if (!in_array('team', $userAccess)) {
                $rws = 12;
              }
              ?>
              <div class="col-lg-<?php echo $rws; ?> col-md-<?php echo $rws; ?> addbutton">
                <button class="btn btn-primary btn-lg btn-block" type="button" onclick="window.location = '?p=add'"><i class="fa fa-plus-circle fa-fw"></i> <?php echo $msadminlang_dashboard_3_7[7]; ?></button>
              </div>
              <?php
            }
            if (in_array('team', $userAccess)) {
              if (!in_array('add', $userAccess)) {
                $rws = 12;
              }
              ?>
              <div class="col-lg-<?php echo $rws; ?> col-md-<?php echo $rws; ?> staffbutton">
                <button class="btn btn-info btn-lg btn-block" type="button" onclick="window.location = '?p=team'"><i class="fa fa-plus-circle fa-fw"></i> <?php echo $msg_adheader57; ?></button>
              </div>
              <?php
              }
              ?>
            </div>
            <?php
            }
          }
        }
        ?>