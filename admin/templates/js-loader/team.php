<?php

/* JS PAGE LOADER
--------------------------------*/

if (!defined('PARENT')) {
  exit;
}

if (isset($_GET['edit'], $EDIT->id)) {
?>
<script>
//<![CDATA[
jQuery(document).ready(function() {
  mswTeamPerms('<?php echo ($EDIT->id == '1' || $EDIT->admin == 'yes' ? 'hide' : 'show'); ?>');
});
//]]>
</script>
<?php
}
?>