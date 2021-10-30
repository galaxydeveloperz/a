<?php

/* JS PAGE LOADER
--------------------------------*/

if (!defined('PARENT')) {
  exit;
}

?>
<script src="templates/js/plugins/jqcolorpicker.js"></script>
<script>
//<![CDATA[
jQuery(document).ready(function() {
  jQuery('.colorpicker').colorPicker({
    renderCallback : function($elm, toggled) {
      var colors = this.color.colors;
      $elm.val(colors.HEX);
    }
  });
});
//]]>
</script>