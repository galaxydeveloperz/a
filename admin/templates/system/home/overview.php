<?php if (!defined('PARENT')) { exit; } 
$howManyCustomStats = mswSQL_rows('statuses');
?>
        <div class="row-fluid">
          <div class="col-lg-6 windowpanelarea">
            <div class="panel panel-default">
              <div class="panel-heading left-align">
                <i class="fa fa-edit fa-fw"></i> <?php echo $msg_home3; ?>
              </div>
              <div class="panel-body text_height_25">
                <i class="fa fa-ellipsis-v fa-fw"></i> <a href="?p=open"><?php echo mswSQL_rows('tickets WHERE `ticketStatus` = \'open\' AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'no\' ' . mswSQL_deptfilter($ticketFilterAccess)); ?> <?php echo (isset($ticketStatusSel['open'][0]) ? $ticketStatusSel['open'][0] : $msg_adheader5); ?></a><br>
                <?php
                if (count($ticketStatusSel) > 3) {
                  foreach($ticketStatusSel AS $hcsk => $hcsv) {
                    if (!in_array($hcsk, array('open','close','closed'))) {
                    ?>
                    <i class="fa fa-ellipsis-v fa-fw"></i> <a href="?t_status=<?php echo $hcsk; ?>"><?php echo mswSQL_rows('tickets WHERE `ticketStatus` = \'' . mswSQL($hcsk) . '\' AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'no\' ' . mswSQL_deptfilter($ticketFilterAccess)); ?> <?php echo $hcsv[0]; ?></a><br>
                    <?php
                    }
                  }
                }
                ?>
                <hr>
                <i class="fa fa-ellipsis-v fa-fw"></i> <a href="?p=assign"><?php echo mswSQL_rows('tickets WHERE `ticketStatus` = \'open\' AND `assignedto` = \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'no\' ' . mswSQL_deptfilter($ticketFilterAccess)); ?> <?php echo $msg_home52; ?></a><br>
                <i class="fa fa-ellipsis-v fa-fw"></i> <a href="?p=close"><?php echo mswSQL_rows('tickets WHERE `ticketStatus` IN(\'close\',\'closed\') AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'no\' ' . mswSQL_deptfilter($ticketFilterAccess)); ?> <?php echo $msg_adheader6; ?></a><br>
                <i class="fa fa-ellipsis-v fa-fw"></i> <a href="?p=spam"><?php echo mswSQL_rows('tickets WHERE `spamFlag` = \'yes\' AND `isDisputed` = \'no\' ' . mswSQL_deptfilter($ticketFilterAccess)); ?> <?php echo $msg_adheader63; ?></a>
                <?php
        	      if ($SETTINGS->disputes == 'yes') {
        	      ?>
                <hr>
                <i class="fa fa-ellipsis-v fa-fw"></i> <a href="?p=disputes"><?php echo mswSQL_rows('tickets WHERE `ticketStatus` = \'open\' AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'yes\' ' . mswSQL_deptfilter($ticketFilterAccess)); ?> <?php echo $msg_adheader28; ?></a><br>
                <i class="fa fa-ellipsis-v fa-fw"></i> <a href="?p=cdisputes"><?php echo mswSQL_rows('tickets WHERE `ticketStatus` IN(\'close\',\'closed\') AND `assignedto` != \'waiting\' AND `spamFlag` = \'no\' AND `isDisputed` = \'yes\' ' . mswSQL_deptfilter($ticketFilterAccess)); ?> <?php echo $msg_adheader29; ?></a>
                <?php
                }
                ?>
              </div>
            </div>
          </div>
          <div class="col-lg-6 windowpanelarea">
            <div class="panel panel-default">
              <div class="panel-heading left-align">
                <i class="fa fa-gears fa-fw"></i> <?php echo $msg_home2; ?>
              </div>
              <div class="panel-body text_height_25">
                <?php
                $arrSysOverview = array(
                  mswSQL_rows('users'),
                  mswSQL_rows('departments'),
                  mswSQL_rows('imap'),
                  mswSQL_rows('cusfields'),
                  mswSQL_rows('responses'),
                  mswSQL_rows('faq'),
                  mswSQL_rows('categories'),
                  mswSQL_rows('faqattach'),
                  mswNFM(count($ticketLevelSel)),
                  mswNFM(count($ticketStatusSel)),
                  mswSQL_rows('portal WHERE `enabled` = \'yes\' AND `verified` = \'yes\''),
                  mswSQL_rows('pages'),
                  mswSQL_rows('admin_pages')
                );
                ?>
    		        <i class="fa fa-ellipsis-v fa-fw"></i> <?php echo str_replace(array('{visitors}'),array($arrSysOverview[10]),$msg_home50); ?><br>
                <i class="fa fa-ellipsis-v fa-fw"></i> <?php echo str_replace(array('{users}'),array($arrSysOverview[0]),$msg_home8); ?><br>
                <i class="fa fa-ellipsis-v fa-fw"></i> <?php echo str_replace(array('{levels}','{dept}'),array($arrSysOverview[8],$arrSysOverview[1]),$msg_home51); ?><br>
                <i class="fa fa-ellipsis-v fa-fw"></i> <?php echo str_replace(array('{imap}'),array($arrSysOverview[2]),$msg_home48); ?><br>
                <i class="fa fa-ellipsis-v fa-fw"></i> <?php echo str_replace(array('{fields}'),array($arrSysOverview[3]),$msg_home49); ?><br>
                <i class="fa fa-ellipsis-v fa-fw"></i> <?php echo str_replace(array('{statuses}'),array($arrSysOverview[9]),$msticketstatuses4_3[11]); ?><br>
                <i class="fa fa-ellipsis-v fa-fw"></i> <?php echo str_replace(array('{responses}'),array($arrSysOverview[4]),$msg_home9); ?><br>
                <i class="fa fa-ellipsis-v fa-fw"></i> <?php echo str_replace(array('{pages}','{apages}'),array($arrSysOverview[11],$arrSysOverview[12]),$msadminlang4_3[13]); ?><br>
                <i class="fa fa-ellipsis-v fa-fw"></i> <?php echo str_replace(array('{questions}','{cats}','{attachments}'),array($arrSysOverview[5],$arrSysOverview[6],$arrSysOverview[7]),$msg_home10); ?>
              </div>
            </div>
            
            <?php
            if (DISPLAY_SOFTWARE_VERSION_CHECK && !defined('DEV_BETA') || (defined('DEV_BETA') && DEV_BETA == 'no')) {
            ?>
            <div class="panel panel-default">
              <div class="panel-heading left-align">
                <i class="fa fa-info-circle fa-fw"></i> <?php echo SCRIPT_NAME . ' ' . $msg_adheader27; ?>
              </div>
              <div class="panel-body text_height_25">
                <i class="fa fa-ellipsis-v fa-fw"></i> <?php echo $msadminlang3_7[10]; ?>: <a href="index.php?p=vc"><b><?php echo SCRIPT_VERSION; ?></b></a> / <a href="https://www.<?php echo SCRIPT_URL; ?>/changelog.html" onclick="window.open(this);return false"><?php echo $msgloballang4_3[7]; ?></a>
              </div>
            </div>
            <?php
            }
            ?>
          </div>
        </div>