<?php

if (!isset($_GET['ajax-ops']) || !defined('PARENT')) {
  exit;
}

$arr = array('status' => 'err', 'txt' => array('System Error', 'An error has occurred during install, please check the error log.<br><br><b>logs/install-error-report.txt</b>'));
$engine = (isset($_POST['engine']) && in_array($_POST['engine'], array('MyISAM','InnoDB')) ? $_POST['engine'] : 'MyISAM');
$c      = (isset($_POST['charset']) ? $_POST['charset'] : $defChar);
$tableD = array();

if ($sqlVer < 5) {
  if ($c) {
    $split      = explode('_', $c);
    $tableType  = 'DEFAULT CHARACTER SET ' . $split[0] . mswNL();
    $tableType .= 'COLLATE ' . $c . mswNL();
    define('DB_COLLATION', $c);
  }
  $tableType .= 'TYPE = ' . $engine;
} else {
  if ($c) {
    $split      = explode('_', $c);
    $tableType  = 'CHARSET = ' . $split[0] . mswNL();
    $tableType .= 'COLLATE ' . $c . mswNL();
    define('DB_COLLATION', $c);
  }
  $tableType .= 'ENGINE = ' . $engine;
}

switch($_GET['ajax-ops']) {
  case 'install':
    // Set timezone for installer..
    @date_default_timezone_set($_POST['timezone']);
    define('INSTALL_RUN', 1);
    $iner = array();
    if (!isset($_POST['em']) || !mswIsValidEmail($_POST['em'])) {
      $iner[] = 'Please specify global administrator email address';
    }
    if (!isset($_POST['pw']) || $_POST['pw'] == '') {
      $iner[] = 'Please specify global administrator password';
    }
    if (!empty($iner)) {
      $arr['txt'][1] = '<b>Please fix the following errors</b>:<hr>' . implode('<br>', $iner);
    } else {
      include(PATH . 'control/tables.php');
      if ($count > 0) {
        $arr['txt'][1] = 'One or more database tables could not be installed. Check the error log for more information.<br><br><b>logs/install-error-report.txt</b><br><br>If this log doesn`t exist, try running the installer again with the install log enabled in the "install/control/config.php" file.';
      } else {
        include(PATH . 'control/data.php');
        if ($dtcount > 0) {
          $arr['txt'][1] = 'The database errored when installing the default system data. Check the error log for more information.<br><br><b>logs/install-error-report.txt</b><br><br>If this log doesn`t exist, try running the installer again with the install log enabled in the "install/control/config.php" file.';
        } else {
          if ($count == 0) {
            $arr['status'] = 'ok';
            $arr['txt'] = array(
              'Installation Successful',
              '<b>' . SCRIPT_NAME . '</b> installed with no errors and the software is ready to use.<br><br>Your global administrator login details are:<br><br><b>Email</b>: ' . mswSH($_POST['em']) . '<br><b>Pass</b>: ' . mswSH($_POST['pw']) . '<br><br>Please refer to the installation instructions for further setup options.<br><br>I hope you enjoy ' . SCRIPT_NAME . '<hr><a href="../admin/index.php">&raquo; <b>Go to Administration</b></a><br><a href="../index.php">&raquo; <b>View Helpdesk</b></a>'
            );
          }
        }
      }
    }
    break;
  case 'upgrade':
    // Set timezone for upgrade..
    @date_default_timezone_set($SETTINGS->timezone);
    define('UPGRADE_RUN', 1);
    $skipVersions = array();
    if (in_array(SCRIPT_VERSION, $skipVersions)) {
      include(PATH . 'control/upgrade/version.php');
      include(PATH . 'control/upgrade/fixes.php');
      $arr['next'] = 'done';
    } else {
      if (isset($_GET['ustage'])) {
        switch ($_GET['ustage']) {
          case 'start':
            include(PATH . 'control/upgrade/tables.php');
            $arr['next'] = (count($ops) == 1 ? 'done' : '1');
            $arr['prev'] = '0';
            break;
          case $_GET['ustage']:
            sleep(3);
            switch ($_GET['ustage']) {
              case '1':
                include(PATH . 'control/upgrade/imap.php');
                break;
              case '2':
                include(PATH . 'control/upgrade/settings.php');
                break;
              case '3':
                include(PATH . 'control/upgrade/tickets.php');
                break;
              case '4':
                include(PATH . 'control/upgrade/accounts.php');
                break;
              case '5':
                include(PATH . 'control/upgrade/staff.php');
                break;
              case '6':
                include(PATH . 'control/upgrade/faq.php');
                break;
              case '7':
                include(PATH . 'control/upgrade/depts-cats-responses.php');
                break;
              case '8':
                include(PATH . 'control/upgrade/indexes.php');
                break;
              case '9':
                include(PATH . 'control/upgrade/other.php');
                break;
            }
            if ($_GET['ustage'] == count($ops) - 1) {
              include(PATH . 'control/upgrade/version.php');
              $arr['next'] = 'done';
            } else {
              $arr['next'] = ($_GET['ustage'] + 1);
              $arr['prev'] = $_GET['ustage'];
            }
            break;
        }
      }
    }
    break;
}

echo $JSON->encode($arr);
exit;

?>