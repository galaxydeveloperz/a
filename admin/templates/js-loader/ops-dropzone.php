<?php

/* JS PAGE LOADER
--------------------------------*/

if (!defined('PARENT')|| empty($mswUploadDropzone)) {
  exit;
}

?>
<script src="templates/js/plugins/jquery.form.js"></script>
<script src="templates/js/plugins/jquery.uploader.js"></script>
<script>
//<![CDATA[
jQuery(document).ready(function() {
  jQuery('#dropzone').uploadFile({
    url          : 'index.php?ajax=<?php echo $mswUploadDropzone['ajax']; ?>',
    maxFileCount : <?php echo $mswUploadDropzone['max-files']; ?>,
    maxFileSize  : '<?php echo $MSUPL->getMaxSize(); ?>',
    dragDrop     : <?php echo $mswUploadDropzone['drag']; ?>,
    multiple     : <?php echo $mswUploadDropzone['multiple']; ?>,
    returnType   : 'json',
    showCancel   : false,
    showAbort    : true,
    showDone     : false,
    showError    : false,
    showDelete   : false,
    showDownload : false,
    showFileSize : true,
    abortStr     : '<?php echo mswJSClean($msadminlang3_1uploads[1]); ?>',
    onSelect     : function(files) {
      <?php
      switch($mswUploadDropzone['ajax']) {
        case 'faqimport-upload':
        case 'srimport-upload':
        case 'accimp-upload':
          ?>
          jQuery('div[class="ajax-file-upload"]').slideUp();
          <?php
          break;
      }
      ?>
    },
    onSuccess : function(files, data, xhr, pd) {
      switch(data['msg']) {
        case 'ok':
          <?php
          switch($mswUploadDropzone['ajax']) {
            case 'faqimport-upload':
            case 'srimport-upload':
            case 'accimp-upload':
              ?>
              jQuery('#upbutton').prop('disabled', false);
              jQuery('#dropzonereload').show();
              if (data['importrows'] > 0) {
                jQuery('#upbutton').append(' <span id="improws">(' + data['importrows'] + ')</span> ');
              }
              var updata = jQuery('div[class="ajax-file-upload"]').html();
              jQuery('div[class="ajax-file-upload"]').html('');
              jQuery('body').append('<div id="hiddendatadiv" style="display:none">' + updata + '</div>');
              <?php
              break;
          }
          ?>
          break;
        case 'err':
          mswAlert(data['info'], data['sys'], 'err');
          setTimeout(function() {
            mswShowSpinner();
            window.location.reload();
          }, 1000);
          break;
      }
    }
  });
});
//]]>
</script>