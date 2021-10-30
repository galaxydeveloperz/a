<?php if (!defined('PARENT') || !isset($_GET['view'])) { exit; }
$_GET['view'] = (int) $_GET['view'];
$KB           = mswSQL_table('faq', 'id' , $_GET['view']);
if (!isset($KB->id)) {
  die('Invalid ID');
}
if ($SETTINGS->enableVotes == 'yes') {
  $yes  = ($KB->kviews > 0 ? mswNFM(($KB->kuseful / $KB->kviews) * 100, 2) : '0.00');
  $no   = ($KB->kviews > 0 ? mswNFM(($KB->knotuseful / $KB->kviews) * 100, 2) : '0.00');
  if (substr($yes, -3) == '.00') {
    $yes = substr($yes, 0, -3);
  }
  if (substr($no, -3) == '.00') {
    $no = substr($no, 0, -3);
  }
}
?>
  <div class="fluid-container windowpanelarea">

    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-question fa-fw"></i> <?php echo mswSH($KB->question); ?>
      </div>
      <div class="panel-body">
        <?php
	      echo $MSPARSER->mswTxtParsingEngine($KB->answer);
        ?>
      </div>
      <?php
      if ($SETTINGS->enableVotes == 'yes') {
      ?>
      <div class="panel-footer">
        <?php echo str_replace(array('{count}','{helpful}','{nothelpful}'), array($KB->kviews,$yes,$no), $msg_kbase18); ?>
      </div>
      <?php
      }
      ?>
    </div>

    <div class="text-center mswitalics">
      <i class="fa fa-clock-o fa-fw"></i> <?php echo $msg_response18 . ': ' . $MSDT->mswDateTimeDisplay($KB->ts, $SETTINGS->dateformat) . ' / ' . $MSDT->mswDateTimeDisplay($KB->ts, $SETTINGS->timeformat); ?>
    </div>

  </div>