<?php

/* JS PAGE LOADER
--------------------------------*/

if (!defined('PARENT')) {
  exit;
}

if (defined('GRAPH_LOADER')) {
?>
<script>
//<![CDATA[
jQuery(document).ready(function(){
  setTimeout(function() {
    jQuery('div[class="graphLoader"]').remove();
    new Chartist.Line('.ct-chart', {
      labels : <?php echo (in_array(MSW_PFDTCT, array('tablet','mobile')) ? $msg_cal_mobile : $msg_cal); ?>,
      series : [
        [<?php echo $gdata[0]; ?>],
        [<?php echo $gdata[1]; ?>]
      ]
    }, {
    fullWidth    : true,
    chartPadding : {
      right : 40
    },
    height       : '280px',
    axisY        : {
      onlyInteger : true
    }
    });
  }, 2000);
});
//]]>
</script>
<?php
}
?>