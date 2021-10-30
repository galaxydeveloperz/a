<?php

/* JS PAGE LOADER
--------------------------------*/

if (!defined('PARENT')) {
  exit;
}

if (defined('MAILBOX_UNREAD_REFRESH_TIME')) {
?>
<script>
//<![CDATA[
jQuery(document).ready(function() {
  setInterval(function () {
    mswUnreadFlag();
  }, <?php echo MAILBOX_UNREAD_REFRESH_TIME; ?>);
  <?php
  if (defined('PRINT_MODE_ENABLED')) {
  ?>
  window.print();
  <?php
  }
  ?>
});
//]]>
</script>
<?php
}
?>