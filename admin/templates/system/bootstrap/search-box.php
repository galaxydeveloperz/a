<?php if (!defined('PARENT')) { exit; } 
$_GET['p'] = (isset($_GET['p']) ? $_GET['p'] : 'x');
?>
      <form method="get" action="index.php">
      <div class="row searchboxarea" style="display:none">
        <?php
        if (isset($_GET['t_status'])) {
        ?>
        <input type="hidden" name="t_status" value="<?php echo (int) $_GET['t_status']; ?>">
        <?php
        } else {
        ?>
        <input type="hidden" name="p" value="<?php echo $_GET['p']; ?>">
        <?php
        }
        ?>
        <div class="col-lg-12">
          <div class="panel panel-default">
            <div class="panel-body" style="padding-bottom:0">
              <div class="form-group searchbox">
                <input class="form-control" type="text" placeholder="<?php echo mswSH($msg_pkbase2); ?>" name="keys" value="<?php echo (isset($_GET['keys']) ? urlencode($_GET['keys']) : ''); ?>">
                <div class="text-center" style="margin-top:5px">
                  <button class="btn btn-primary" type="submit"><i class="fa fa-search fa-fw"></i> <?php echo $msadminlang3_7[12]; ?></button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      </form>