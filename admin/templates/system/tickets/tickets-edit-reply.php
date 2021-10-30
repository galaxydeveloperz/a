<?php if (!defined('PARENT')) { exit; }
$countOfEnFlds  = mswSQL_rows('cusfields WHERE `enField` = \'yes\'');
$repType        = ($REPLY->replyType=='admin' ? 'admin' : 'reply');
$qF = mswSQL_query("SELECT * FROM `" . DB_PREFIX . "cusfields`
      WHERE FIND_IN_SET('{$repType}',`fieldLoc`)              > 0
      AND `enField`                                           = 'yes'
			AND FIND_IN_SET('{$SUPTICK->department}',`departments`) > 0
      ORDER BY `orderBy`
      ", __file__, __line__);
$countOfCusFields = mswSQL_numrows($qF);
if ($countOfCusFields > 0) {
  define('LOAD_DATE_PICKERS', 1);
  define('LOAD_CAL_INPUT_FUNCTION', 'two');
}
?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      // Status of ticket for link
      $link = getTicketLink(array(
        't' => $SUPTICK,
        'l' => array($msg_adheader5,$msg_adheader6,$msg_adheader28,$msg_adheader29,$msg_adheader63,$msg_adheader32),
        's' => $ticketStatusSel
      ));
      if ($link[0]) {
      ?>
      <li><a href="<?php echo $link[0]; ?>"><?php echo $link[1]; ?></a></li>
      <?php
      }
      ?>
      <li><a href="?p=view-<?php echo ($SUPTICK->isDisputed == 'yes' ? 'dispute' : 'ticket'); ?>&amp;id=<?php echo $REPLY->ticketID; ?>"><?php echo ($SUPTICK->isDisputed == 'yes' ? $msg_portal35 : $msg_portal8); ?></a></li>
      <li class="active"><?php echo $msg_viewticket37; ?></li>
    </ol>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-reply fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_edit; ?></span></a></li>
          <?php
	        if ($countOfEnFlds > 0 && $countOfCusFields > 0) {
	        ?>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_adheader26; ?></span></a></li>
          <?php
	        }
	        ?>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div class="form-group">
                  <?php
                  // BBCode..
                  include(PATH . 'templates/system/bbcode-buttons.php');
                  ?>
                  <textarea name="comments" rows="15" cols="40" id="comments" tabindex="<?php echo (++$tabIndex); ?>" class="form-control"><?php echo mswSH($REPLY->comments); ?></textarea>
                </div>

              </div>
              <?php
              if ($countOfEnFlds > 0) {
              ?>
              <div class="tab-pane fade" id="two">

                <?php
                if ($countOfCusFields > 0) {
                  while ($FIELDS = mswSQL_fetchobj($qF)) {
                    $TF = mswSQL_table('ticketfields','ticketID',$REPLY->ticketID,' AND `replyID` = \'' . $REPLY->id . '\' AND `fieldID` = \'' . $FIELDS->id . '\'');
                    switch ($FIELDS->fieldType) {
                      case 'textarea':
                        echo $MSFM->buildTextArea(mswCD($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex),(isset($TF->fieldData) ? $TF->fieldData : ''));
                        break;
                      case 'input':
                        echo $MSFM->buildInputBox(mswCD($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex),(isset($TF->fieldData) ? $TF->fieldData : ''));
                        break;
                      case 'calendar':
                        echo $MSFM->buildCalBox(mswCD($FIELDS->fieldInstructions),$FIELDS->id,(++$tabIndex),(isset($TF->fieldData) ? $TF->fieldData : ''));
                        break;
                      case 'select':
                        echo $MSFM->buildSelect(mswCD($FIELDS->fieldInstructions),$FIELDS->id,$FIELDS->fieldOptions,(++$tabIndex),(isset($TF->fieldData) ? $TF->fieldData : ''));
                        break;
                      case 'checkbox':
                        echo $MSFM->buildCheckBox(mswCD($FIELDS->fieldInstructions),$FIELDS->id,$FIELDS->fieldOptions,(isset($TF->fieldData) ? $TF->fieldData : ''));
                        break;
                    }
                  }
                } else {
                  echo '<i class="fa fa-warning fa-fw ms_red"></i> ' . $msadminlang3_1adminticketedit[0];
                }
                ?>

              </div>
              <?php
              }
              ?>
            </div>
          </div>
          <div class="panel-footer">
           <input type="hidden" name="ticketID" value="<?php echo $SUPTICK->id; ?>">
           <input type="hidden" name="replyID" value="<?php echo $REPLY->id; ?>">
           <button class="btn btn-primary" type="button" onclick="mswProcess('tickrepedit')"><i class="fa fa-check fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_viewticket37; ?></span></button>
           <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=view-ticket&amp;id=<?php echo $REPLY->ticketID; ?>')"><i class="fa fa-times fa-fw"></i> <?php echo $msg_levels11; ?></button>
          </div>
        </div>

      </div>
    </div>
    </form>

  </div>