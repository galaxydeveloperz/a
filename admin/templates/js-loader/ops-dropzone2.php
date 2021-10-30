<?php

/* JS PAGE LOADER
--------------------------------*/

if (!defined('PARENT')|| empty($mswUploadDropzone2)) {
  exit;
}

?>
<script src="templates/js/plugins/jquery.form.js"></script>
<script src="templates/js/plugins/jquery.uploader2.js"></script>
<script>
//<![CDATA[
jQuery(document).ready(function() {
  jQuery('#dropzone').uploadFile({
    url          : 'index.php?ajax=<?php echo $mswUploadDropzone2['ajax']; ?>',
    maxFileCount : <?php echo $mswUploadDropzone2['max-files']; ?>,
    maxFileSize  : '<?php echo $mswUploadDropzone2['max-size']; ?>',
    dragDrop     : <?php echo $mswUploadDropzone2['drag']; ?>,
    multiple     : <?php echo $mswUploadDropzone2['multiple']; ?>,
    allowedTypes : '*',
    returnType   : 'json',
    showCancel   : false,
    autoSubmit   : false,
    showDone     : false,
    showError    : false,
    showFileSize : true,
    dragDropStr  : '<?php echo mswJSClean($msadminlang3_1uploads[5]); ?>',
    dropzoneDiv  : '<?php echo $mswUploadDropzone2['div']; ?>'
  });
});
//]]>
</script>