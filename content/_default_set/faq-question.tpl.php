<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/"><?php echo $this->TXT[10]; ?></a></li>
      <?php
      // Is this a sub category?
      if (isset($this->PARENT['name'])) {
      if (isset($this->SUB['id'])) {
      ?>
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?c=<?php echo $this->SUB['id']; ?>"><?php echo mswCD($this->SUB['name']); ?></a></li>
      <li class="active"><?php echo mswCD($this->PARENT['name']); ?></li>
      <?php
      } else {
      ?>
      <li class="active"><?php echo mswCD($this->PARENT['name']); ?></li>
      <?php
      }
      } else {
      ?>
      <li class="active"><?php echo $this->TXT[12]; ?></li>
      <?php
      }
      ?>
    </ol>

    <form method="get" action="<?php echo $this->SETTINGS->scriptpath; ?>/" id="sform">
    <div class="panel panel-default">
      <div class="panel-body" style="padding-bottom:0">
        <div class="form-group">
          <div class="form-group input-group">
            <input type="hidden" name="p" value="faq-search">
            <input type="text" placeholder="<?php echo $this->TXT[4]; ?>" name="q" value="" class="form-control">
            <span class="input-group-addon"><i class="fa fa-search fa-fw cursor_pointer" onclick="mswSearchAction()"></i></span>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-<?php echo ($this->SETTINGS->enableVotes == 'yes' ? '8' : '12'); ?>">
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-question-circle fa-fw"></i> <b><?php echo mswSH($this->ANSWER['question']); ?></b>
          </div>
          <div class="panel-body">
            <?php
            // Answer
            echo $this->ANSWER_TXT;
            ?>
            <hr>
            <div class="row">
              <div class="col-lg-6">
                <i class="fa fa-clock-o fa-fw"></i> <?php echo $this->TXT[13]; ?>
              </div>
              <div class="col-lg-6 text-right printarticle">
                <a href="#" onclick="window.print();return false"><i class="fa fa-print fa-fw"></i> <?php echo $this->TXT[2]; ?></a>
              </div>
            </div>
          </div>
        </div>

        <?php
        // Only show if there are attachments
        if ($this->ATTACHMENTS) {
        ?>
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-paperclip fa-fw"></i> <?php echo $this->TXT[9]; ?>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <tbody>
                <?php
                // ATTACHMENTS
                // html/faq-attachment-link.htm
                echo $this->ATTACHMENTS;
                ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php
        }
        ?>
      </div>
      <?php

      // Is the voting system enabled?
      if ($this->SETTINGS->enableVotes == 'yes') {
      ?>
      <div class="col-lg-4">
        <div class="panel panel-default votingarea">
          <div class="panel-heading mswforceleftalign">
            <i class="fa fa-question-circle fa-fw"></i> <?php echo $this->TXT[1]; ?>
          </div>
          <div class="panel-body text_height_25">
            <div class="row votefont">
              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center">
                <i class="fa fa-thumbs-up fa-fw cursor_pointer" onclick="mswVote(this, '<?php echo $this->ANSWER['id']; ?>')"></i> <span><?php echo $this->STATS[0]; ?></span>
              </div>
              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center">
                <i class="fa fa-thumbs-down fa-fw cursor_pointer" onclick="mswVote(this, '<?php echo $this->ANSWER['id']; ?>')"></i> <span><?php echo $this->STATS[1]; ?></span>
              </div>
            </div>
            <div class="totalvotes">
              <?php echo $this->TXT[14] . '<span class="votetotalarea">' . $this->STATS[2]; ?></span>
            </div>
          </div>
        </div>
      </div>
      <?php
      }
      ?>
    </div>
    </form>
  </div>