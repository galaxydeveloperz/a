<?php

/* JS PAGE LOADER
--------------------------------*/

if (!defined('PARENT')) {
  exit;
}

?>
<script>
//<![CDATA[
jQuery(document).ready(function() {
  var options = {
    dataType : 'json',
    success  : mswPR_Callback
  };
  jQuery('#mswform').on('submit', function(e) {
    mswShowSpinner();
    e.preventDefault();
    jQuery(this).ajaxSubmit(options);
    return false;
  });
});
//]]>
</script>