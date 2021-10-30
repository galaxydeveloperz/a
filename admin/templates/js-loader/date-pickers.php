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
  jQuery('#from').datepicker({
    changeMonth     : true,
    changeYear      : true,
    monthNamesShort : <?php echo trim($msg_cal); ?>,
    dayNamesMin     : <?php echo trim($msg_cal2); ?>,
    firstDay        : <?php echo ($SETTINGS->weekStart=='sun' ? '0' : '1'); ?>,
    dateFormat      : '<?php echo $MSDT->mswDatePickerFormat(); ?>',
    isRTL           : <?php echo $msg_cal3; ?>
  });
  jQuery('#to').datepicker({
    changeMonth     : true,
    changeYear      : true,
    monthNamesShort : <?php echo trim($msg_cal); ?>,
    dayNamesMin     : <?php echo trim($msg_cal2); ?>,
    firstDay        : <?php echo ($SETTINGS->weekStart=='sun' ? '0' : '1'); ?>,
    dateFormat      : '<?php echo $MSDT->mswDatePickerFormat(); ?>',
    isRTL           : <?php echo $msg_cal3; ?>
  });
});
function mswFldDatePicker(obj) {
  jQuery(obj).datepicker({
    changeMonth     : true,
    changeYear      : true,
    monthNamesShort : <?php echo trim($msg_cal); ?>,
    dayNamesMin     : <?php echo trim($msg_cal2); ?>,
    firstDay        : <?php echo ($SETTINGS->weekStart=='sun' ? '0' : '1'); ?>,
    dateFormat      : '<?php echo $MSDT->mswDatePickerFormat(); ?>',
    isRTL           : <?php echo $msg_cal3; ?>
  });
}
<?php
if (defined('LOAD_CAL_INPUT_FUNCTION')) {
?>
// Attach event handlers for calendar field boxes..
jQuery('#<?php echo LOAD_CAL_INPUT_FUNCTION; ?> input[type="text"]').each(function(){
  if (jQuery(this).hasClass('jsdatepicker')) {
    mswFldDatePicker(this); 
  }
});
<?php
}
?>
//]]>
</script>