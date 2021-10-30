<?php if (!defined('PATH')) { exit; } ?>
  <div class="container margin-top-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=dashboard"><?php echo $this->TXT[0]; ?></a></li>
      <li class="active"><?php echo $this->TXT[1]; ?></li>
    </ol>

    <form method="get" action="<?php echo $this->SETTINGS->scriptpath; ?>/">

      <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
          <div class="panel panel-default">
            <div class="panel-body">
              <?php echo $this->TXT[2][2]; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="row text-center confarea" style="display:none">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
          <div class="panel panel-default delconfirmbar">
            <div class="panel-body">
              <input type="checkbox" name="delyes" value="yes"> <?php echo $this->TXT[2][3]; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="row text-center">
        <button class="btn btn-danger" type="button" onclick="closeAcc()"><i class="fa fa-times-circle fa-fw"></i> <?php echo $this->TXT[2][1]; ?></button>
      </div>

    </form>

  </div>