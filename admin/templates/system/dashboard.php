<?php if (!defined('PARENT')) { exit; }
if (SHOW_ADMIN_DASHBOARD_GRAPH) {
  $lastYear = date('Y', strtotime('last year'));
  include(PATH . 'control/classes/class.graphs.php');
  $tz               = ($MSTEAM->timezone ? $MSTEAM->timezone : $SETTINGS->timezone);
  $graph            = new graphs();
  $graph->settings  = $SETTINGS;
  $graph->datetime  = $MSDT;
  $graph->team      = $MSTEAM;
  $graph->years     = array($lastYear, date('Y', $MSDT->mswTimeStamp()));
  $gdata            = $graph->home($ticketFilterAccess);
  define('JS_LOADER', 'home-graph.php');
}
define('LOADED_HOME', 1);
define('LOAD_DATE_PICKERS', 1);
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">
    <div class="row">
      <?php
      if (SHOW_ADMIN_DASHBOARD_GRAPH) {
        include(PATH . 'templates/system/home/graph.php');
      }
      ?>
      <div class="col-lg-<?php echo (SHOW_ADMIN_DASHBOARD_GRAPH ? '4' : '12'); ?>">
        <?php
        include(PATH . 'templates/system/home/panel.php');
        ?>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <?php
        include(PATH . 'templates/system/home/tickets.php');
        ?>
      </div>
    </div>
  </div>