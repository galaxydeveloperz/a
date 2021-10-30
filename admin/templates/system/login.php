<?php if (!defined('PARENT')) { exit; } ?>
<!DOCTYPE html>
<html lang="<?php echo (isset($html_lang) ? $html_lang : 'en'); ?>" dir="<?php echo $lang_dir; ?>">
	<head>
    <meta charset="<?php echo $msg_charset; ?>">

    <title><?php echo ($title ? $title . ': ' : '') . $msg_login; ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <link href="templates/css/bootstrap.css" rel="stylesheet">
    <link href="templates/css/theme.css" rel="stylesheet">
    <link href="templates/css/font-awesome/font-awesome.css" rel="stylesheet">
    <link href="templates/css/fam-icons.css" rel="stylesheet">
    <link rel="shortcut icon" href="favicon.ico">

  </head>

	<body>

  <div class="container margin-top-login-container" id="mscontainer">

    <form method="post" action="#">
    <div class="row">
      <div class="col-md-4 col-md-offset-4 col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
            <span style="float:right"><i class="fa fa-lock fa-fw"></i></span>
            <h3 class="panel-title">- <?php echo ($title ? $title . ': ' : '') . $msg_login; ?> -</h3>
          </div>
          <div class="panel-body">
            <fieldset>
              <div class="form-group">
                <div class="form-group input-group">
                  <span class="input-group-addon"><i class="fa fa-envelope fa-fw"></i></span>
                  <input class="form-control" placeholder="<?php echo mswSH($msg_login8); ?>" onkeyup="mswLoginClearErr()" onkeypress="if(mswKeyCode(event)==13){mswLogin()}" type="text" name="user" value="" autofocus>
                </div>
              </div>
              <div class="form-group">
                <div class="form-group input-group">
                  <span class="input-group-addon"><i class="fa fa-lock fa-fw"></i></span>
                  <input class="form-control" placeholder="<?php echo mswSH($msg_login2); ?>" onkeyup="mswLoginClearErr()" onkeypress="if(mswKeyCode(event)==13){mswLogin()}" type="password" name="pass" value="" autocomplete="off">
                </div>
              </div>
              <?php
              // Is cookie set?
              if (COOKIE_NAME) {
              ?>
              <div class="form-group">
                <label><input type="checkbox" name="cookie" value="1"> <?php echo $msg_login3; ?></label>
              </div>
              <?php
              }
              ?>
              <div class="alert alert-warning" style="display:none">
                <span></span>
              </div>
              <button class="btn btn-lg btn-success btn-block" type="button" onclick="mswLogin()"><i class="fa fa-sign-in fa-fw"></i> <?php echo $msg_login5; ?></button>
            </fieldset>
          </div>
        </div>
      </div>
    </div>
    </form>

  </div>

  <script src="templates/js/jquery.js"></script>
  <script src="templates/js/jquery-ui.js"></script>
  <script src="templates/js/bootstrap.js"></script>
  <script src="templates/js/msops.js"></script>
  <script src="templates/js/msp.js"></script>
  
  <?php
  // CSRF tokens
  include(PATH . 'templates/js-loader/security.php');
  ?>
  
  </body>

</html>
