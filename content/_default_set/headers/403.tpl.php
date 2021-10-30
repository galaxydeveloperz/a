<?php if (!defined('PATH')) { exit; } ?>
<!DOCTYPE html>
<html lang="<?php echo $this->LANG; ?>" dir="<?php echo $this->DIR; ?>">
	<head>
    <meta charset="<?php echo $this->CHARSET; ?>">

    <title>403</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/theme.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/font-awesome/font-awesome.css" rel="stylesheet">
    <link rel="shortcut icon" href="<?php echo $this->SETTINGS->scriptpath; ?>/favicon.ico">

	</head>

	<body>

  <div class="navbar navbar-default" id="msnavheader">

    <div class="container msheader">
      <span class="pull-right"><i class="fa fa-warning fa-fw"></i> 403</span>
      <i class="fa fa-life-ring fa-fw"></i> <a href="<?php echo $this->SETTINGS->scriptpath; ?>/"><?php echo $this->SETTINGS->website; ?></a>
    </div>

  </div>

  <div class="container margin-top-container" id="mscontainer">

    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">

        <div class="panel panel-default">
          <div class="panel-body">
            <?php echo $this->TXT[1]; ?><br><br>
            <i class="fa fa-reply fa-fw"></i> <a href="<?php echo $this->SETTINGS->scriptpath; ?>/"><?php echo $this->TXT[2]; ?></a>
          </div>
        </div>

      </div>
    </div>

  </div>

  <footer>
    <?php
	  // Please don`t remove the footer unless you have purchased a licence..
    // This software is protected by UK copyright laws.
	  // https://www.maiansupport.com/purchase.html
	  if (LICENCE_VER == 'unlocked' && $this->SETTINGS->publicFooter) {
	  echo mswCD($this->SETTINGS->publicFooter);
	  } else {
	  ?>
	  Powered by: <a href="https://www.maiansupport.com" onclick="window.open(this);return false" title="Maian Support">Maian Support</a><br>
    <a href="https://www.maianscriptworld.co.uk" title="Maian Script World" onclick="window.open(this);return false">&copy; 2005 - <?php echo date('Y'); ?> Maian Script World</a>
	  <?php
	  }
	  ?>
  </footer>

  </body>
</html>