<?php if (!defined('PARENT')) { exit; }
$_GET['p'] = (isset($_GET['p']) ? $_GET['p'] : 'x');
?>
      <form method="get" action="index.php">
      <div class="row searchboxarea" style="display:none">
        <input type="hidden" name="p" value="<?php echo $_GET['p']; ?>">
        <div class="col-lg-12">
          <div class="panel panel-default">
            <div class="panel-body">
              <div class="form-group searchbox">
               <div class="form-group">
                 <input class="form-control" type="text" placeholder="<?php echo mswSH($msg_log10); ?>" name="keys" value="<?php echo (isset($_GET['keys']) ? urlencode($_GET['keys']) : ''); ?>">
               </div>
               <div class="form-group">
                 <input type="text" placeholder="<?php echo mswSH($msg_reports2); ?>" class="form-control" id="from" name="from" value="<?php echo (isset($_GET['from']) ? $_GET['from'] : ''); ?>">
               </div>
               <div class="form-group">
                 <input placeholder="<?php echo mswSH($msg_reports3); ?>" type="text" class="form-control" id="to" name="to" value="<?php echo (isset($_GET['to']) ? $_GET['to'] : ''); ?>">
               </div>
               <div class="text-center" style="margin-top:5px">
                 <button class="btn btn-primary" type="submit"><i class="fa fa-search fa-fw"></i> <?php echo $msadminlang3_7[12]; ?></button>
               </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      </form>