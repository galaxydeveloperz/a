<?php if (!defined('PARENT')) { exit; } ?>
  <div class="container margin-top-container min-height-container push" id="mscontainer">

    <ol class="breadcrumb">
      <li><a href="index.php"><?php echo $msg_adheader11; ?></a></li>
      <?php
      if (in_array('faqman', $userAccess) || USER_ADMINISTRATOR == 'yes') {
      ?>
      <li><a href="index.php?p=faqman"><?php echo $msg_adheader47; ?></a></li>
      <?php
      }
      ?>
      <li class="active"><?php echo $msg_adheader55; ?></li>
    </ol>

    <?php
    if (isset($_GET['cnt'])) {
    ?>
    <div class="alert alert-info alert-dismissable border_2x">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="fa fa-times fa-fw"></i></button>
      <b><?php echo (int) $_GET['cnt']; ?></b> <?php echo $msadminlang_faq_3_7[5]; ?>
    </div>
    <?php
    }
    ?>

    <form method="post" action="#">
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mstabmenuarea">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-file-text-o fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_response22; ?></span></a></li>
          <li><a href="#two" data-toggle="tab"><i class="fa fa-reorder fa-fw"></i> <span class="hidden-sm hidden-xs"><?php echo $msg_import10; ?></span></a></li>
        </ul>
      </div>
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin_top_10">
        <div class="panel panel-default">
          <div class="panel-body">
            <div class="tab-content">
              <div class="tab-pane active in" id="one">

                <div id="dropzone" class="dropzone">
                  <div class="droparea">
                    <?php echo str_replace('{max}', mswFSC($MSUPL->getMaxSize()), $msadminlang3_1uploads[0]); ?>
                  </div>
                </div>

             </div>
              <div class="tab-pane fade" id="two">

                <div class="form-group">
                  <div class="checkbox">
                    <label><input type="checkbox" name="clear" value="yes"> <?php echo $msg_import4; ?></label>
                  </div>
                </div>

                <hr>

                <div id="cb">
                <?php
                $fqcc = 0;
                $q_cat = mswSQL_query("SELECT `id`, `name`, `private`, `accounts` FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '0' ORDER BY `name`", __file__, __line__);
                while ($CAT = mswSQL_fetchobj($q_cat)) {
                ?>
		            <div class="form-group">
                  <div class="radio">
                    <label><input type="radio" name="cat" value="<?php echo $CAT->id; ?>"<?php echo (++$fqcc == '1' ? ' checked="checked"' : ''); ?>><?php echo ($CAT->private == 'yes' ? '<i class="fa fa-lock fa-fw" title="' . mswSH($msadminlang3_1faq[3]) . '"></i> ' . (!in_array($CAT->accounts, array('','all',null)) ? '<a href="#" onclick="iBox.showURL(\'?p=faq-catman&amp;pr_acc=' . $CAT->id . '\',\'\',{width:' . IBOX_FQACC_WIDTH . ',height:' . IBOX_FQACC_HEIGHT . '});return false"><i class="fa fa-user fa-fw" title="' . mswSH($msadminlang_faq_3_7[3]) . '"></i></a> ' : '')  : '') . mswSH($CAT->name); ?></label>
                  </div>
		            </div>
                <?php
                $q_cat2 = mswSQL_query("SELECT `id`, `name`, `private` FROM `" . DB_PREFIX . "categories` WHERE `subcat` = '{$CAT->id}' ORDER BY `name`", __file__, __line__);
                while ($SUB = mswSQL_fetchobj($q_cat2)) {
                ?>
                <div class="form-group">
                  <div class="radio indent_10">
                    <label><input type="radio" name="cat" value="<?php echo $SUB->id; ?>"><?php echo ($CAT->private == 'yes' ? '<i class="fa fa-lock fa-fw" title="' . mswSH($msadminlang3_1faq[3]) . '"></i> ' . (!in_array($CAT->accounts, array('','all',null)) ? '<a href="#" onclick="iBox.showURL(\'?p=faq-catman&amp;pr_acc=' . $CAT->id . '\',\'\',{width:' . IBOX_FQACC_WIDTH . ',height:' . IBOX_FQACC_HEIGHT . '});return false"><i class="fa fa-user fa-fw" title="' . mswSH($msadminlang_faq_3_7[3]) . '"></i></a> ' : '')  : '') . mswCD($SUB->name); ?></label>
                  </div>
                </div>
                <?php
                }
                }
                ?>
                </div>

              </div>
            </div>
          </div>
          <div class="panel-footer">
            <button class="btn btn-primary" type="button" disabled="disabled" onclick="mswProcess('faqimport')" id="upbutton"><i class="fa fa-upload fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_adheader55; ?></span></button>
            <button class="btn btn-link" type="button" onclick="mswDropZoneReload('after')" id="dropzonereload" style="display:none"><i class="fa fa-refresh fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msadminlang3_1uploads[2]; ?></span></button>
            <?php
            if (in_array('faqman', $userAccess)  || USER_ADMINISTRATOR == 'yes') {
            ?>
            <button class="btn btn-link" type="button" onclick="mswWindowLoc('index.php?p=faqman')"><i class="fa fa-times fa-fw"></i> <span class="hidden-xs hidden-sm"><?php echo $msg_levels11; ?></span></button>
            <?php
            }
            ?>
          </div>
        </div>

        <div class="text-right">
          &#8226; <a href="templates/examples/faqs.csv"><?php echo $msg_import15; ?></a> &#8226;
        </div>

      </div>
    </div>
    </form>

  </div>