<?php if (!defined('RESET_LOADER')) { exit; } ?>
<!DOCTYPE html>
<html lang="<?php echo (isset($html_lang) ? $html_lang : 'en'); ?>" dir="<?php echo $lang_dir; ?>">
	<head>
    <meta charset="<?php echo $msg_charset; ?>">

    <title><?php echo $title; ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <link href="templates/css/bootstrap.css" rel="stylesheet">
    <link href="templates/css/theme.css" rel="stylesheet">
    <link href="templates/css/font-awesome/font-awesome.css" rel="stylesheet">
    <link rel="shortcut icon" href="favicon.ico">

  </head>

	<body style="padding-bottom:50px">

  <div id="mscontainer">

    <form method="post" action="#">
    <div class="container margin-top-container-nonefixed">

      <div class="panel panel-default">
        <div class="panel-heading text-uppercase">
          <i class="fa fa-lock fa-fw"></i> <?php echo $title; ?>
        </div>
        <div class="panel-body">
          <?php echo $msg_passreset; ?>
        </div>
      </div>

    </div>

    <div class="container">

     <div class="panel panel-default">
       <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th><?php echo TABLE_HEAD_DECORATION . $msg_passreset7; ?></th>
              <th><?php echo TABLE_HEAD_DECORATION . $msg_passreset2; ?></th>
              <th><?php echo TABLE_HEAD_DECORATION . $msg_passreset3; ?></th>
            </tr>
          </thead>
          <tbody>
            <?php
            $q = mswSQL_query("SELECT `id`,`email`,`accpass`,`name` FROM `" . DB_PREFIX . "users` ORDER BY `name`", __file__, __line__);
            while ($U = mswSQL_fetchobj($q)) {
            ?>
            <tr>
              <td<?php echo ($U->id == '1' ? ' style="color:red;font-weight:bold"' : ''); ?>><i class="fa fa-user fa-fw"></i> <b><?php echo mswSH($U->name) . '</b>' . ($U->id == '1' ? ' (ADMIN)' : ''); ?><input type="hidden" name="name[]" value="<?php echo mswSH($U->name); ?>"></td>
              <td><input type="hidden" name="id[]" value="<?php echo $U->id; ?>"><input type="text" name="mail[]" value="<?php echo mswSH($U->email); ?>" class="form-control"></td>
              <td><input type="hidden" name="password2[]" value="<?php echo $U->accpass; ?>"><input type="password" id="<?php echo $U->id; ?>" name="password[]" value="" class="form-control"></td>
            </tr>
            <?php
            }
            ?>
          </tbody>
          </table>
        </div>
      </div>
     </div>

      <hr style="border:0;border-bottom:1px solid #c0c0c0">

      <div class="text-center">
        <div class="form-group">
          <div class="checkbox">
            <label><input type="checkbox" name="autoall" value="1"> <?php echo $msadminlang3_1[24]; ?></label>
          </div>
          <div class="checkbox">
            <label><input type="checkbox" name="sendem" value="1" checked="checked"> <?php echo $msadminlang3_7[0]; ?></label>
          </div>
        </div>
        <hr style="border:0;border-bottom:1px solid #c0c0c0">
        <button class="btn btn-primary" type="button" onclick="mswButtonOp('pass-reset')"><i class="fa fa-check fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_passreset4; ?></span></button>
      </div>

    </div>
    </form>

  </div>

  <script src="templates/js/jquery.js"></script>
  <script>
  //<![CDATA[
  var mswlang = {
    aus         : '<?php echo mswJSClean($msgloballang4_3[2]); ?>',
    confirm_yes : '<?php echo mswJSClean($msgloballang4_3[0]); ?>',
    confirm_no  : '<?php echo mswJSClean($msgloballang4_3[1]); ?>'
  }
  //]]>
  </script>
  <script src="templates/js/msops.js"></script>
  <script src="templates/js/msp.js"></script>
  <script src="templates/js/bootstrap.js"></script>
  <script src="templates/js/plugins/jquery.bootbox.js"></script>

  <?php
  // Action spinner, DO NOT REMOVE
  ?>
  <div class="overlaySpinner" style="display:none"></div>

  </body>
</html>