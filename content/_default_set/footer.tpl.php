<?php if (!defined('PATH')) { exit; } ?>
    <footer class="push">
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

    <?php
    // Nav Menu - Off Canvas
    if ($this->LOAD_OFF_CANVAS_MENU == 'yes') {
      include(dirname(__file__) . '/nav-menu.tpl.php');
    }
    ?>

    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/jquery.js"></script>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/jquery-ui.js"></script>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/bootstrap.js"></script>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/plugins/jquery.bootbox.js"></script>
    <?php
    if ($this->LOAD_OFF_CANVAS_MENU == 'yes') {
    ?>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/plugins/jquery.pushy.js"></script>
    <?php
    }
    ?>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/plugins/jquery.form.js"></script>

    <?php
	  // Load page specific js files..
	  echo $this->FILES;

    // Load file upload dropzone..
    if (in_array('uploader', array_keys($this->FILE_LOADER))) {
    ?>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/plugins/jquery.uploader.js"></script>
    <script>
    //<![CDATA[
    jQuery(document).ready(function() {
      jQuery('#dropzone').uploadFile({
        url : '<?php echo $this->SETTINGS->scriptpath; ?>/?ajax=<?php echo $this->DROPZONE['ajax']; ?>',
        maxFileCount : <?php echo $this->DROPZONE['max-files']; ?>,
        maxFileSize: '<?php echo $this->DROPZONE['max-size']; ?>',
        dragDrop: <?php echo $this->DROPZONE['drag']; ?>,
        multiple : <?php echo $this->DROPZONE['multiple']; ?>,
        allowedTypes : '<?php echo $this->DROPZONE['allowed']; ?>',
        returnType : 'json',
        showCancel : false,
        autoSubmit : false,
        showDone : false,
        showError : false,
        showFileSize : true,
        dragDropStr: '<?php echo $this->DROPZONE['txt']; ?>',
        dropzoneDiv: '<?php echo $this->DROPZONE['div']; ?>'
      });
    });
    //]]>
    </script>
    <?php
    }

    // Required files..
    ?>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/msops.js"></script>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/msp.js"></script>
    <?php

    // Load textarea plugin..
    if (in_array('textarea', array_keys($this->FILE_LOADER))) {
    ?>
    <script src="<?php echo $this->SYS_BASE_HREF; ?>js/plugins/jquery.textareafullscreen.js"></script>
    <script>
    //<![CDATA[
    jQuery(document).ready(function() {
      jQuery('textarea').textareafullscreen({
        overlay: true,
        maxWidth: '80%',
        maxHeight: '80%'
      });
    });
    //]]>
    </script>
    <?php
    }

    // Load page specific js code..
    echo $this->JS_HTML;

    // Tawk.to live support
    echo $this->TAWK_TO;

    // Action spinner, DO NOT REMOVE
    ?>
    <div class="overlaySpinner" style="display:none"></div>

</body>
</html>