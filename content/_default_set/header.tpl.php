<?php if (!defined('PATH')) { exit; } ?>
<!DOCTYPE html>
<html lang="<?php echo $this->LANG; ?>" dir="<?php echo $this->DIR; ?>">
	<head>
    <meta charset="<?php echo $this->CHARSET; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?php echo $this->TITLE; ?></title>

    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/theme.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/font-awesome/font-awesome.css" rel="stylesheet">

    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/jquery-ui.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/fam-icons.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/plugins.css" rel="stylesheet">
    <link href="<?php echo $this->SYS_BASE_HREF; ?>css/mobile.css" rel="stylesheet">

    <link rel="shortcut icon" href="<?php echo $this->SETTINGS->scriptpath; ?>/favicon.ico">

	</head>

	<body>

  <?php
  // Shows only on extra small screens
  ?>
  <div class="toppagebar hidden-sm hidden-md hidden-lg push">
    <a href="<?php echo $this->SETTINGS->scriptpath; ?>/<?php echo ($this->LOGGED_IN == 'yes' ? '?p=dashboard' : ''); ?>"><?php echo $this->TOP_BAR_TITLE; ?></a>
  </div>

  <div class="navbar push" id="msnavheader">
    <div class="navbar-inner">
      <div class="container">
        <div class="table-responsive">
          <table class="table">
            <tbody>
              <tr>
                <td><i class="fa fa-<?php echo ($this->LOAD_OFF_CANVAS_MENU == 'yes' ? 'bars fa-fw menu-btn' : ($this->LOGGED_IN == 'yes' ? 'lock fa-fw nocanvas' : 'life-ring fa-fw nocanvas')); ?>"></i></td>
                <td class="hidden-xs"><a href="<?php echo $this->SETTINGS->scriptpath; ?>/<?php echo ($this->LOGGED_IN == 'yes' ? '?p=dashboard' : ''); ?>"><?php echo $this->TOP_BAR_TITLE; ?></a></td>
                <td>
                <?php
                // Is user logged in?
                if ($this->LOGGED_IN == 'yes') {
                ?>
                <a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=dashboard"><i class="fa fa-dashboard fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[2]; ?></span></a>
                <?php
                // Show if FAQ is enabled...
                if ($this->SETTINGS->kbase == 'yes') {
                ?>
                <a href="<?php echo $this->SETTINGS->scriptpath; ?>/"><i class="fa fa-question-circle fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[0]; ?></span></a>
                <?php
                }
                } else {
                ?>
                <a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=open" rel="nofollow"><i class="fa fa-pencil fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[1]; ?></span></a>
                <?php
                // Is account creation enabled?
                if ($this->SETTINGS->createAcc == 'yes') {
                ?>
                <a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=create" rel="nofollow"><i class="fa fa-user fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[8]; ?></span></a>
                <?php
                }
                ?>
                <a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=login" rel="nofollow"><i class="fa fa-sign-in fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $this->TXT[4]; ?></span></a>
                <?php
                }
                ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
