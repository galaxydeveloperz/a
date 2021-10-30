<?php if (!defined('PARENT')) { exit; } ?>
    <footer class="push">
      <?php
      // SOFTWARE PROTECTED BY UK COPYRIGHT LAWS
      //===================================================================================
      // PLEASE DO NOT REMOVE THE FOOTER UNLESS YOU HAVE PURCHASED A LICENCE, THANK YOU!
      // https://www.maiansupport.com/purchase.html
      //===================================================================================
      if (LICENCE_VER == 'unlocked' && $SETTINGS->adminFooter) {
        echo mswCD($SETTINGS->adminFooter);
      } else {
      ?>
      <?php echo $msgloballang4_3[11]; ?>: <a href="https://www.<?php echo SCRIPT_URL; ?>" onclick="window.open(this);return false" title="<?php echo SCRIPT_NAME; ?>"><?php echo SCRIPT_NAME; ?></a><br>
      <a href="https://www.maianscriptworld.co.uk" title="Maian Script World" onclick="window.open(this);return false">&copy; <?php echo SCRIPT_RELEASE_YR . ' - ' . date('Y'); ?> Maian Script World</a>
      <?php
      }
      ?>
		</footer>

    <nav class="pushy pushy-left">
      <div class="pushy-content">
        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
        <?php
        // Left slider menu..
        if (!empty($slidePanelLeftMenu)) {
          $defMenuPanel = DEF_OPEN_MENU_PANEL;
          if ($SSN->active('adm_menu_panel') == 'yes') {
            $defMenuPanel = $SSN->get('adm_menu_panel');
          }
          foreach (array_keys($slidePanelLeftMenu) AS $smk) {
            if (!empty($slidePanelLeftMenu[$smk]['links'])) {
              ?>
              <div class="panel panel-default linkbodypanel">
                <div class="panel-heading" role="tab" id="heading<?php echo $smk; ?>">
                  <h4 class="panel-title">
                    <a<?php echo ($smk != $defMenuPanel ? ' class="collapsed" ' : ' '); ?>role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $smk; ?>" aria-expanded="<?php echo ($smk == $defMenuPanel ? 'true' : 'false'); ?>" onclick="mswPanel('<?php echo $smk; ?>')" aria-controls="collapse<?php echo $smk; ?>" title="<?php echo mswSH($slidePanelLeftMenu[$smk][0]); ?>">
                      <i class="fa fa-<?php echo $slidePanelLeftMenu[$smk][1]; ?> fa-fw"></i> <?php echo $slidePanelLeftMenu[$smk][0]; ?>
                    </a>
                  </h4>
                </div>
                <div id="collapse<?php echo $smk; ?>" class="panel-collapse collapse<?php echo ($smk == $defMenuPanel ? ' in' : ''); ?>" role="tabpanel" aria-labelledby="heading<?php echo $smk; ?>">
                  <div class="panel-body linkbodyarea">
                  <?php
                  if (!empty($slidePanelLeftMenu[$smk]['links'])) {
                    for ($i=0; $i<count($slidePanelLeftMenu[$smk]['links']); $i++) {
                    ?>
                    <div><a href="<?php echo $slidePanelLeftMenu[$smk]['links'][$i]['url']; ?>" title="<?php echo mswSH($slidePanelLeftMenu[$smk]['links'][$i]['name']); ?>"><?php echo $slidePanelLeftMenu[$smk]['links'][$i]['name']; ?></a></div>
                    <?php
                    }
                  }
                  ?>
                  </div>
                </div>
              </div>
              <?php
            }
          }
        }
        ?>
        </div>
        <?php
        if (DISPLAY_SOFTWARE_VERSION_CHECK && USER_ADMINISTRATOR == 'yes' && !defined('DEV_BETA') || (defined('DEV_BETA') && DEV_BETA == 'no')) {
        ?>
        <div class="panel panel-default">
          <div class="panel-body">
            <?php echo $msadminlang3_7[10]; ?>: <a href="index.php?p=vc"><b><?php echo SCRIPT_VERSION; ?></b></a>
          </div>
        </div>
        <?php
        }
        ?>
      </div>
		</nav>
    <div class="site-overlay"></div>

    <script src="templates/js/jquery.js"></script>
    <script src="templates/js/jquery-ui.js"></script>
    <script src="templates/js/bootstrap.js"></script>
    <script src="templates/js/plugins/jquery.bootbox.js"></script>
    <script>
    //<![CDATA[
    var mswlang = {
      aus         : '<?php echo mswJSClean($msgloballang4_3[2]); ?>',
      confirm_yes : '<?php echo mswJSClean($msgloballang4_3[0]); ?>',
      confirm_no  : '<?php echo mswJSClean($msgloballang4_3[1]); ?>'
    }
    //]]>
    </script>
    <?php
    if (isset($loadiBox)) {
    ?>
    <script src="templates/js/plugins/jquery.ibox.js"></script>
    <?php
    }
    ?>
    <script src="templates/js/plugins/jquery.pushy.js"></script>
    <script src="templates/js/msops.js"></script>
    <script src="templates/js/msp.js"></script>

    <?php
    // Dependencies
    if (defined('LOAD_DATE_PICKERS')) {
      include(PATH . 'templates/js-loader/date-pickers.php');
    }
    if (isset($loadGraph)) {
      include(PATH . 'templates/js-loader/ops-chartist.php');
    }
    if (defined('JS_LOADER') && file_exists(PATH . 'templates/js-loader/' . JS_LOADER)) {
      include(PATH . 'templates/js-loader/' . JS_LOADER);
    }
    if (isset($mswUploadDropzone2['ajax'])) {
      include(PATH . 'templates/js-loader/ops-dropzone2.php');
    }
    if (isset($mswUploadDropzone['ajax'])) {
      include(PATH . 'templates/js-loader/ops-dropzone.php');
    }
    if (isset($textareaFullScr)) {
      include(PATH . 'templates/js-loader/ops-textarea.php');
    }
    if ($MSTEAM->mailbox == 'yes' && defined('MAILBOX_UNREAD_REFRESH_TIME') && MAILBOX_UNREAD_REFRESH_TIME > 0) {
      include(PATH . 'templates/js-loader/ops-mailbox.php');
    }
    // CSRF tokens
    include(PATH . 'templates/js-loader/security.php');
    // Action spinner, DO NOT REMOVE
    ?>
    <div class="overlaySpinner" style="display:none"></div>

  </body>
</html>