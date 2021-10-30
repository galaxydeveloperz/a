<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container push" id="mscontainer">

    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="panel panel-default">
          <div class="panel-body">
            <?php echo $this->TXT[1]; ?>
          </div>
        </div>
      </div>
    </div>

    <?php
	  // Show if FAQ is enabled...
	  if ($this->SETTINGS->kbase == 'yes') {
	  ?>
    <form method="get" action="<?php echo $this->SETTINGS->scriptpath; ?>/" id="sform">
    <div class="panel panel-default">
      <div class="panel-body" style="padding-bottom:0">
        <div class="form-group">
          <div class="form-group input-group">
            <input type="hidden" name="p" value="faq-search">
            <input type="text" placeholder="<?php echo $this->TXT[7]; ?>" name="q" value="" class="form-control">
            <span class="input-group-addon"><i class="fa fa-search fa-fw cursor_pointer" onclick="mswSearchAction()"></i></span>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-paperclip fa-fw"></i> <?php echo $this->TXT[4]; ?>
          </div>
          <div class="panel-body faqquestionwrapper">
            <?php
            // FEATURED QUESTIONS
            // html/faq-question-link.htm
            // html/nothing-found.htm
            echo $this->FEATURED;
            ?>
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-heart-o fa-fw"></i> <?php echo $this->TXT[2]; ?>
          </div>
          <div class="panel-body faqquestionwrapper">
            <?php
            // POPULAR QUESTIONS
            // html/faq-question-link.htm
            // html/nothing-found.htm
            echo $this->POPULAR;
            ?>
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading">
            <i class="fa fa-calendar fa-fw"></i> <?php echo $this->TXT[3]; ?>
          </div>
          <div class="panel-body faqquestionwrapper">
            <?php
            // LATEST QUESTIONS
            // html/faq-question-link.htm
            // html/nothing-found.htm
            echo $this->LATEST;
            ?>
          </div>
        </div>

      </div>
    </div>
    </form>
    <?php
    }
    ?>
  </div>