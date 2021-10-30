<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container push" id="mscontainer">

    <div class="row">
      <div class="col-lg-8">

        <div class="panel panel-default">
          <div class="panel-body">
            <?php echo $this->TXT[7]; ?>
          </div>
        </div>

      </div>
      <div class="col-lg-4">

        <div class="panel panel-default accountinfo">
          <div class="panel-body">
            <i class="fa fa-user fa-fw"></i> <b><?php echo mswSH($this->USER_DATA->name); ?></b><br>
		        <i class="fa fa-envelope-o fa-fw"></i> <?php echo mswSH($this->USER_DATA->email); ?><br><br>
            <i class="fa fa-ticket fa-fw"></i> <?php echo $this->TICKETS_CNT; ?> <?php echo $this->TXT[17]; ?>
            <?php
            // Only show dispute count if dispute system is enabled..
            if ($this->SETTINGS->disputes == 'yes') {
            ?>
            <br><i class="fa fa-bullhorn fa-fw"></i> <?php echo $this->DISPUTES_CNT; ?> <?php echo $this->TXT[18]; ?>
            <?php
            }
            ?><br><br>
            <a class="btn btn-primary btn-block" href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=open"><i class="fa fa-pencil fa-fw"></i> <?php echo $this->TXT[9]; ?></a>
          </div>
        </div>

      </div>
    </div>

    <?php
    // CURRENT OPEN TICKETS
    ?>
    <h2 class="head_h2"><i class="fa fa-ticket fa-fw"></i> <?php echo $this->TXT[3]; ?></h2>
    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-body panel-no-padding">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th><?php echo TABLE_HEAD_DECORATION . $this->TXT2[3]; ?></th>
                    <th><?php echo TABLE_HEAD_DECORATION . $this->TXT2[0]; ?></th>
                    <th><?php echo TABLE_HEAD_DECORATION . $this->TXT2[1]; ?></th>
                    <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $this->TXT2[2]; ?></th>
                  </tr>
                </thead>
                <tbody>
                <?php
                // TICKETS
                // html/tickets/tickets-dashboard.htm
                // html/tickets/tickets-no-data.htm
                echo $this->TICKETS;
                ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php
    // CURRENT OPEN DISPUTES
    // Only show if dispute system is enabled..
    if ($this->SETTINGS->disputes == 'yes') {
    ?>
    <h2 class="head_h2"><i class="fa fa-bullhorn fa-fw"></i> <?php echo $this->TXT[4]; ?></h2>
    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-body panel-no-padding">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th><?php echo TABLE_HEAD_DECORATION . $this->TXT2[3]; ?></th>
                    <th><?php echo TABLE_HEAD_DECORATION . $this->TXT2[0]; ?></th>
                    <th><?php echo TABLE_HEAD_DECORATION . $this->TXT2[1]; ?></th>
                    <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $this->TXT2[2]; ?></th>
                  </tr>
                </thead>
                <tbody>
                <?php
                // DISPUTE TICKETS
                // html/tickets/tickets-dashboard.htm
                // html/tickets/tickets-no-data.htm
                echo $this->DISPUTES;
                ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
    }
    ?>

  </div>