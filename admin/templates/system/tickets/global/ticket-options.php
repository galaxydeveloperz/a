    <?php if (!defined('PARENT') || !isset($TICKETS->ticketID)) { exit; }
               if ($tLock == 'yes') {
                 echo '<i class="fa fa-lock fa-fw"></i> ' . str_replace('{staff}', (isset($TICKETS->lockTeamName) ? mswSH($TICKETS->lockTeamName) : $msg_script17), $msadminlang_tickets_3_7[5]);
               } else {
                 ?>
                 <span class="pull-left">
                 <?php
                 if (USER_EDIT_T_PRIV == 'yes') {
                 ?>
                 <button class="btn btn-success btn-xs" type="button" onclick="window.location='?p=edit-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>'" title="<?php echo mswSH($msg_viewticket120); ?>"><i class="fa fa-pencil fa-fw"></i><span class="hidden-xs">  <?php echo $msadminlang_tickets_3_7[6]; ?></span></button>
                 <?php
                 }
                 if ($MSTEAM->notePadEnable == 'yes' || USER_ADMINISTRATOR == 'yes') {
                 /*
                 ?>
                 <button class="btn btn-primary btn-xs" type="button" onclick="iBox.showURL('?p=view-<?php echo ($TICKETS->isDisputed == 'yes' ? 'dispute' : 'ticket'); ?>&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;editNotes=yes','',{width:<?php echo IBOX_NOTES_WIDTH; ?>,height:<?php echo IBOX_NOTES_HEIGHT; ?>});return false" title="<?php echo mswSH($msg_viewticket72); ?>"><i class="fa fa-file-text fa-fw"></i><span class="hidden-xs">  <?php echo $msadminlang_tickets_3_7[7]; ?></span></button>
                 <?php
                 */
                 }
                 if (!in_array($TICKETS->assignedto, array('', 'waiting'))) {
                 ?>
                 <button class="btn btn-primary btn-xs" type="button" onclick="iBox.showURL('?p=view-ticket&amp;id=<?php echo $TICKETS->ticketID; ?>&amp;as_staff=yes','',{width:<?php echo IBOX_ASTFF_WIDTH; ?>,height:<?php echo IBOX_ASTFF_HEIGHT; ?>});return false" title="<?php echo mswSH($msadminlang3_1adminviewticket[22]); ?>"><i class="fa fa-users fa-fw"></i><span class="hidden-xs">  <?php echo $msg_user3; ?></span></button>
                 <?php
                 }
                 ?>
                 </span>
                 <?php
                 if (!defined('EXTRAS_LOAD_NO')) {
                   // Extras..
                   include(PATH . 'templates/system/tickets/global/drop-extra.php');
                 }
               }
               ?>