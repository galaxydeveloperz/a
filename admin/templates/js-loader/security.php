<?php

if (!defined('PARENT')) { exit; }

/* SECURITY
   CSRF attack prevention
------------------------------------------*/

?>
<script>
//<![CDATA[
jQuery(document).ready(function() {
  jQuery('form').each(function(){
    jQuery(this).append('<input type="hidden" name="cs_rf" value="<?php echo $SSN->get('csrf_token'); ?>">');
  });
});
//]]>
</script>