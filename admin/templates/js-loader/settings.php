<?php

/* JS PAGE LOADER
--------------------------------*/

if (!defined('PARENT')) {
  exit;
}

?>
<script>
//<![CDATA[
function mswSetProtocol(pr, nm, cls) {
  jQuery('input[name="' + nm + '"]').val(pr);
  switch(pr) {
    case 'http':
      jQuery('.' + cls + ' li:first-child').addClass('active');
      jQuery('.' + cls + ' li:nth-child(2)').removeClass('active');
      break;
    default:
      jQuery('.' + cls + ' li:nth-child(2)').addClass('active');
      jQuery('.' + cls + ' li:first-child').removeClass('active');
      break;
  }
}
function autoPath(type,box) {
  jQuery(document).ready(function() {
    jQuery('input[name="' + box + '"]').addClass('box_updating');
		jQuery.ajax({
      url      : 'index.php',
      data     : 'ajax=autopath&type=' + type,
      dataType : 'json',
      success  : function (data) {
				jQuery('input[name="' + box + '"]').removeClass('box_updating');
				jQuery('input[name="' + box + '"]').val(data['path']);
      }
    });
  });
  return false;
}
//]]>
</script>