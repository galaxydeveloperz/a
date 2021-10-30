<?php if (!defined('PARENT')) { exit; }
include(PATH . 'control/navigation.php');
$unread = mswUnreadMailbox($MSTEAM->id);
?>
<!DOCTYPE html>
<html lang="<?php echo (isset($html_lang) ? $html_lang : 'en'); ?>" dir="<?php echo (isset($lang_dir) ? $lang_dir : 'ltr'); ?>">
	<head>
    <meta charset="<?php echo $msg_charset; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($title ? $title.': ' : '').$msg_script.' - '.$msg_adheader.(LICENCE_VER!='unlocked' ? ' (Free Version)' : '').(defined('DEV_BETA') && DEV_BETA!='no' ? ' - BETA VERSION' : ''); ?></title>
    <link href="templates/css/bootstrap.css" rel="stylesheet">
    <link href="templates/css/theme.css" rel="stylesheet">
    <link href="templates/css/font-awesome/font-awesome.css" rel="stylesheet">
    <link href="templates/css/jquery-ui.css" rel="stylesheet">
    <link href="templates/css/fam-icons.css" rel="stylesheet">
    <?php
    if (isset($loadGraph)) {
    ?>
    <link href="templates/css/chartist.css" rel="stylesheet">
    <?php
    }
    ?>
    <link href="templates/css/mobile.css" rel="stylesheet">
    <link href="templates/css/plugins.css" rel="stylesheet">
    <link rel="shortcut icon" href="favicon.ico">

    <?php
	  // For meta reloads, do NOT remove..
	  if (isset($metaReload)) {
	    echo $metaReload;
	  }
    if (defined('PRINT_MODE_ENABLED')) {
	  ?>
    <link href="templates/css/print.css" rel="stylesheet">
    <?php
    }
    ?>
	</head>

	<body>

    <?php
    // Shows only on extra small screens
    ?>
    <div class="toppagebar hidden-sm hidden-md hidden-lg push">
      <a href="index.php"><?php echo $msg_adheader . (defined('DEV_BETA') && DEV_BETA != 'no' ? ' (BETA)' : ''); ?></a>
    </div>

    <div class="navbar push">
      <div class="navbar-inner">
        <div class="container">
          <div class="table-responsive">
            <table class="table">
              <tbody>
                <tr>
                  <?php
                  if (!empty($userAccess) || USER_ADMINISTRATOR == 'yes') {
                  ?>
                  <td><i class="fa fa-bars fa-fw menu-btn"></i></td>
                  <?php
                  } else {
                  ?>
                  <td>&nbsp;</td>
                  <?php
                  }
                  ?>
                  <td class="hidden-xs"><a href="index.php"><?php echo $msg_adheader . (defined('DEV_BETA') && DEV_BETA != 'no' ? ' (BETA)' : ''); ?></a></td>
                  <td>
                  <?php
                  foreach ($msTopMenu AS $ntm) {
                    if ($ntm['url'] == 'index.php?p=mailbox') {
                    ?>
                    <a href="<?php echo $ntm['url']; ?>"<?php echo ($ntm['ext'] == 'yes' ? ' onclick="window.open(this);return false"' : ''); ?>><i class="fa <?php echo $ntm['icon']; ?> fa-fw" title="<?php echo $ntm['text']; ?>"></i><span class="<?php echo $ntm['class']; ?>"> <?php echo $ntm['text']; ?></span> (<span class="mailboxcount"><?php echo ($unread > 0 ? '<span class="unread">' . $unread . '</span>' : '0'); ?></span>)</a>
                    <?php
                    } elseif ($ntm['url'] == 'index.php') {
                    ?>
                    <span class="hidden-xs"><a href="<?php echo $ntm['url']; ?>"<?php echo ($ntm['ext'] == 'yes' ? ' onclick="window.open(this);return false"' : ''); ?>><i class="fa <?php echo $ntm['icon']; ?> fa-fw" title="<?php echo $ntm['text']; ?>"></i><span class="<?php echo $ntm['class']; ?>">  <?php echo $ntm['text']; ?></span></a></span>
                    <?php
                    } else {
                    ?>
                    <a href="<?php echo $ntm['url']; ?>"<?php echo ($ntm['ext'] == 'yes' ? ' onclick="window.open(this);return false"' : ''); ?>><i class="fa <?php echo $ntm['icon']; ?> fa-fw" title="<?php echo $ntm['text']; ?>"></i><span class="<?php echo $ntm['class']; ?>">  <?php echo $ntm['text']; ?></span></a>
                    <?php
                    }
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