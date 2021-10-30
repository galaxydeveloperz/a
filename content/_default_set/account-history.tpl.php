<?php if (!defined('PATH')) { exit; }
// Pre-populate search box if query exists.
$searchTxt = '';
// Ticket search..
if (isset($_GET['qt']) && $_GET['qt']) {
  $searchTxt = $_GET['qt'];
}
if (isset($_GET['qd']) && $_GET['qd']) {
  $searchTxt = $_GET['qd'];
}
$pageParam = ($this->IS_DISPUTED == 'yes' ? 'disputes' : 'history');
?>
  <div class="container margin-top-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="<?php echo $this->SETTINGS->scriptpath; ?>/?p=dashboard"><?php echo $this->TXT[1]; ?></a></li>
      <li class="active"><?php echo ($this->IS_DISPUTED == 'yes' ? $this->TXT[9] : $this->TXT[0]); ?></li>
    </ol>

    <form method="get" action="<?php echo $this->SETTINGS->scriptpath; ?>/">

    <div class="row searchboxarea" style="display:none">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-body" style="padding-bottom:0">
            <div class="form-group">
              <input class="form-control" type="text" name="<?php echo ($this->IS_DISPUTED=='yes' ? 'qd' : 'qt'); ?>" value="<?php echo $searchTxt; ?>">
              <div class="text-center" style="margin-top:10px">
                <button class="btn btn-primary" type="submit"><i class="fa fa-search fa-fw"></i><span class="hidden-xs"> <?php echo $this->TXT[3]; ?></span></button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10 text-right">
        <?php
        // For small screens, we hide the filters unless button is clicked..
        if (in_array(MSW_PFDTCT, array('mobile', 'tablet'))) {
        ?>
        <button class="btn btn-primary btn-sm" type="button" onclick="mswToggleButton('filters')"><i class="fa fa-sort-amount-asc fa-fw"></i></button>
        <button class="btn btn-info btn-sm" type="button" onclick="mswToggleSearch()"><i class="fa fa-search fa-fw"></i></button>
        <div class="hidetkfltrs" style="display:none">
        <hr>
        <?php
        include(dirname(__file__) . '/ticket-filters-mobile.php');
        } else {
        include(dirname(__file__) . '/ticket-filters-standard.php');
        }
        if (in_array(MSW_PFDTCT, array('mobile', 'tablet'))) {
        ?>
        </div>
        <?php
        } else {
        ?>
        <button class="btn btn-info btn-sm" type="button" onclick="mswToggleSearch()"><i class="fa fa-search fa-fw"></i></button>
        <?php
        }
        ?>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body panel-no-padding">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th><?php echo TABLE_HEAD_DECORATION . $this->TXT[7]; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $this->TXT[8]; ?></th>
                  <th><?php echo TABLE_HEAD_DECORATION . $this->TXT[5]; ?></th>
                  <th class="text-right"><?php echo TABLE_HEAD_DECORATION . $this->TXT[6]; ?></th>
                </tr>
              </thead>
              <tbody>
              <?php
                // TICKETS
                // html/tickets/ticket-list-entry.htm
                // html/tickets/tickets-no-data.htm
                // html/tickets/tickets-last-reply-date.htm
                echo $this->TICKETS;
              ?>
              </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php
        // PAGE NUMBERS
        // html/pagination/*
        if ($this->PAGES) {
          echo $this->PAGES;
        }
        ?>

      </div>
    </div>

    </form>

  </div>