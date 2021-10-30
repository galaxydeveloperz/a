<?php

/* JS PAGE LOADER
--------------------------------*/

if (!defined('PARENT')) {
  exit;
}

?>
<script src="templates/js/plugins/jquery.textareafullscreen.js"></script>
<script>
//<![CDATA[
jQuery(document).ready(function() {
  jQuery('textarea').textareafullscreen({
    overlay   : true,
    maxWidth  : '80%',
    maxHeight : '80%'
  });
});
//]]>
</script>