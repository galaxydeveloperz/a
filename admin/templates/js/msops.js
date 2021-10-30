function mswStatChange(op, id) {
  var stat = jQuery('select[name="stat_change"]').val();
  if (stat == '') {
    jQuery('select[name="stat_change"]').focus();
    return false;
  }
  switch(op) {
    case 'search':
    case 'home':
      mswTickAct(stat, id, op);
      break;
    default:
      mswLinkOp(stat);
      break;
  }
  iBox.hide();
}

function mswRelStaffLock(id) {
  jQuery('#tlk_' + id + ' td:nth-child(3)').html('<i class="fa fa-spinner fa-spin fa-fw"></i>');
  jQuery.ajax({
    url      : 'index.php',
    data     : 'ajax=release-lock&id=' + id,
    dataType : 'json',
    cache    : false,
    success  : function (data) {
      jQuery('#tlk_' + id).remove();
      if (jQuery('.ticketlockarea tbody tr').length == 0) {
        jQuery('.ticketlockarea tbody').html('<tr><td class="text-center nothing_to_see">' + data['txt'] + '</td></tr>');
      }
    }
  });
  return false;
}

function mswDL(id, parm) {
  mswShowSpinner();
  jQuery.ajax({
    url      : 'index.php',
    data     : 'ajax=' + parm + '&id=' + id,
    dataType : 'json',
    cache    : false,
    success  : function (data) {
      mswCloseSpinner();
      switch(data['status']) {
        case 'token':
          window.location = 'index.php?ajax=' + (parm == 'dl' ? 'token' : 'tokena') + '&cde=' + data['token'];
          break;
        case 'remote':
          window.location = data['remote'];
          break;
        case 'err':
          mswAlert(data['msg'], data['sys'], 'err');
          break;
      }
    }
  });
  return false;
}

function mswTickAct(act, id, ex) {
  mswAlert(mswlang['aus'], act, 'confirm', 'tickact', id, ex);
}

function mswTickAct_ajax(act, id, ex) {
  mswShowSpinner();
  jQuery(document).ready(function() {
    jQuery.ajax({
      url      : 'index.php',
      data     : 'ajax=ticket-action&id=' + id + '&act=' + act,
      dataType : 'json',
      cache    : false,
      success  : function (data) {
        mswCloseSpinner();
        switch(ex) {
          case 'search':
          case 'home':
            if (data['msg'] == 'ok') {
              jQuery('#tickactions_' + id).hide();
              switch(act) {
                case 'open':
                  jQuery('#tickactions_' + id + ' .search_btn_open').remove();
                  break;
                case 'close':
                case 'lock':
                  jQuery('#tickactions_' + id + ' .search_btn_close').remove();
                  break;
              }
              if (data['html'] != 'no-build') {
                if (jQuery('#tickactions_' + id + ' .spambutton').html()) {
                  jQuery('#tickactions_' + id + ' .spambutton').before(data['html'] + ' &nbsp;');
                } else {
                  jQuery('#tickactions_' + id + ' .printbutton').before(data['html'] + ' &nbsp;');
                }
              }
              jQuery('#spanstatus_' + id).html(data['status']);
              jQuery('#tkactbtn_' + id + ' i').attr('class', 'fa fa-chevron-down fa-fw').removeClass('rotate-start');
              mswAlert(data['info'], data['sys'], 'ok');
            }
            break;
          default:
            if (data['msg'] == 'ok') {
              if (jQuery('#tickactions_' + id).html()) {
                jQuery('#tickactions_' + id).remove();
                jQuery('#datatr_' + id).remove();
              }
            }
            break;
        }
        if (data['msg'] == 'err') {
          mswAlert(data['info'], data['sys'], 'err');
        }
      }
    });
  });
}

function mswShowImapFolders(fld) {
  var inputbox  = jQuery('#' + fld +' input[type="text"]').attr('name');
  jQuery('input[name="' + inputbox + '"]').addClass('box_updating');
  var ssl = (jQuery('input[name="im_ssl"]:checked').val() ? 'yes' : 'no');
  jQuery(document).ready(function() {
    jQuery.post('index.php?ajax=imfolders', {
        host  : jQuery('input[name="im_host"]').val(),
        user  : jQuery('input[name="im_user"]').val(),
        pass  : jQuery('input[name="im_pass"]').val(),
        ssl   : ssl,
        port  : jQuery('input[name="im_port"]').val(),
        flags : jQuery('input[name="im_flags"]').val()
      },
      function(data) {
        jQuery('input[name="' + inputbox + '"]').removeClass('box_updating');
        switch (data['msg']) {
          case 'ok':
            jQuery('#' + fld +' select').html(data['html']);
            jQuery('input[name="' + inputbox + '"]').hide();
            jQuery('#' + fld +' span').hide();
            jQuery('#' + fld +' select').show();
            break;
          default:
            mswAlert(data['info'], data['sys'], 'err');
            break;
        }
      }, 'json');
  });
  return false
}

function mswPanel(panl) {
  jQuery(document).ready(function() {
    jQuery.ajax({
      url      : 'index.php',
      data     : 'ajax=menu-panel&pnl=' + panl,
      dataType : 'json',
      cache    : false,
      success  : function (data) {}
    });
  });
  return false;
}

function mswSelectAccount(value, field) {
  var selname  = (field == 'name' ? 'accntn' : 'accnte');
  var chopdata = value.split('###');
  jQuery('.' + selname).hide();
  switch(field) {
    case 'dest_email':
      jQuery('input[name="' + field + '"]').val(chopdata[1]);
      break;
    default:
      jQuery('input[name="name"]').val(chopdata[0]);
      jQuery('input[name="email"]').val(chopdata[1]);
      break;
  }
}

function mswSearchAccounts(field, id) {
  if (jQuery('input[name="' + field + '"]').val() == '') {
    jQuery('input[name="' + field + '"]').focus();
    return false;
  }
  jQuery('input[name="' + field + '"]').addClass('box_updating');
  jQuery(document).ready(function() {
    jQuery.post('index.php?ajax=search-accounts', {
      ffld : field,
      fval : jQuery('input[name="' + field + '"]').val(),
      emal : (jQuery('input[name="email"]').val() ? jQuery('input[name="email"]').val() : '')
    },
    function(data) {
      jQuery('input[name="' + field + '"]').removeClass('box_updating');
      switch(data['msg']) {
        case 'ok':
          switch(data['accounts'].length) {
            case 1:
              mswSelectAccount(data['accounts'][0]['name'] + '###' + data['accounts'][0]['email'], field);
              break;
            default:
              var html = '';
              for (var i = 0; i<data['accounts'].length; i++) {
                html += '<option value="' + data['accounts'][i]['name'] + '###' + data['accounts'][i]['email'] + '">' + data['accounts'][i]['name'] + ' (' + data['accounts'][i]['email'] + ')</option>';
              }
              var selname = (field == 'name' ? 'accntn' : 'accnte');
              jQuery('select[name="' + selname + '"]').html('<option value="0">- - - - - -</option>' + html);
              jQuery('.' + selname).show();
              break;
          }
          break;
        default:
          mswAlert(data['info'], data['sys'], 'err');
          break;
      }
    },'json');
  });
  return false;
}

function mswHistory(ticket) {
  if (jQuery('textarea[name="notes"]').val() == '') {
    jQuery('textarea[name="notes"]').focus();
    return false;
  }
  jQuery('textarea[name="notes"]').removeClass('updated').addClass('updating');
  jQuery(document).ready(function() {
    jQuery.post('index.php?ajax=history-entry&id=' + ticket, {
      his : jQuery('textarea[name="notes"]').val()
    },
    function(data) {
      iBox.hide();
      mswShowSpinner();
      if (data['msg'] == 'ok') {
        var n = jQuery('.historyarea .table tbody tr').length;
        if (n > 0) {
          jQuery('.historyarea .table tbody tr:first').before(data['html']).fadeIn(500);
        } else {
          jQuery('.historybody').html('<div class="table-responsive historyarea"><table class="table table-striped table-hover"><tbody>' + data['html'] + '</tbody></table></div>').fadeIn(500);
        }
        if (data['del['] == 'no') {
          jQuery('.historyarea table tbody tr:first-child td:nth-child(3)').remove();
        }
      }
      mswCloseSpinner();
    }, 'json');
  });
  return false;
}

function mswNotes(ticket) {
  jQuery('textarea[name="notes"]').removeClass('updated').addClass('updating');
  jQuery(document).ready(function() {
    jQuery.post('index.php?ajax=ticknotes&id=' + ticket, {
      notes : jQuery('textarea[name="notes"]').val()
    },
    function(data) {
      jQuery('textarea[name="notes"]').removeClass('updating').addClass('updated');
      if (jQuery('textarea[name="notes"]').val()) {
        jQuery('#datatr_' + ticket + ' .noteindicator i').attr('class', 'fa fa-file-text fa-fw');
      } else {
        jQuery('#datatr_' + ticket + ' .noteindicator i').attr('class', 'fa fa-file-text-o fa-fw');
      }
    }, 'json');
  });
  return false;
}

function mswRemoveFAQHistory(id, faq) {
  mswAlert(mswlang['aus'], id, 'confirm', 'remfaqhis', faq);
}

function mswRemoveFAQHistory_ajax(id, faq) {
  mswShowSpinner();
  jQuery(document).ready(function() {
    jQuery.ajax({
      url      : 'index.php',
      data     : 'ajax=faqdelhis&id=' + id + '&f=' + faq,
      dataType : 'json',
      success  : function(data) {
        switch(data['msg']) {
          case 'ok':
            switch(id) {
              case 'all':
                mswCloseSpinner();
                jQuery('.historyarea').html('');
                break;
              default:
                mswCloseSpinner();
                jQuery('#hdata_' + id).remove();
                break;
            }
            if (jQuery('.historyarea tr').length == 0) {
              jQuery('.historyarea').html('<div class="nothing_to_see">' + data['html'] + '</div>');
            }
            break;
          default:
            mswCloseSpinner();
            mswAlert(data['info'], data['sys'], 'err');
            break;
        }
      }
    });
  });
}

function mswRemoveHistory(id, ticket) {
  mswAlert(mswlang['aus'], id, 'confirm', 'remhis', ticket);
}

function mswRemoveHistory_ajax(id, ticket) {
  mswShowSpinner();
  jQuery(document).ready(function() {
    jQuery.ajax({
      url      : 'index.php',
      data     : 'ajax=tickdelhis&id=' + id + '&t=' + ticket,
      dataType : 'json',
      success  : function(data) {
        switch(data['msg']) {
          case 'ok':
            switch(id) {
              case 'all':
                mswCloseSpinner();
                jQuery('.historyarea').html('');
                break;
              default:
                mswCloseSpinner();
                jQuery('#hdata_' + id).remove();
                break;
            }
            if (jQuery('.historyarea tr').length == 0) {
              jQuery('.historyarea').html('<div class="nothing_to_see">' + data['html'] + '</div>');
            }
            break;
          default:
            mswCloseSpinner();
            mswAlert(data['info'], data['sys'], 'err');
            break;
        }
      }
    });
  });
}

function mswDeptLoader(tab, page, replyid, area) {
  if (jQuery('select[name="dept"]').val() == '0') {
    return false;
  }
  var tickID = '0';
  if (page == 'ticket') {
    var tickID = jQuery('input[name="id"]').val();
  }
  jQuery(document).ready(function() {
    mswShowSpinner();
    jQuery.ajax({
      url      : 'index.php',
      data     : 'ajax=tickdept&dp=' + jQuery('select[name="dept"]').val() + '&id=' + tickID + '&ar=' + area,
      dataType : 'json',
      success  : function(data) {
        mswCloseSpinner();
        if (data['fields']) {
          jQuery('#' + tab).html(data['fields']);
          if (jQuery('#licus').html()) {
            jQuery('#licus').show();
          }
        } else {
          jQuery('#' + tab).html('');
          if (jQuery('#licus').html()) {
            jQuery('#licus').hide();
          }
        }
        switch(data['assign']) {
          case 'yes':
            if (jQuery('#liusr').html()) {
              jQuery('#liusr').show();
            }
            break;
          case 'no':
            if (jQuery('#liusr').html()) {
              jQuery('#liusr').hide();
            }
            break;
        }
        if (area == 'ticket') {
          if (data['subject'] != undefined) {
            jQuery('input[name="subject"]').val(data['subject']);
          }
          if (data['comments'] != undefined) {
            jQuery('textarea[name="comments"]').val(data['comments']);
          }
          if (data['priority'] != undefined) {
            jQuery('select[name="priority"]').val(data['priority']);
          }
        }
        // Attach event handlers for calendar field boxes..
        if (data['fields']) {
          jQuery('#' + tab + ' input[type="text"]').each(function(){
            if (jQuery(this).hasClass('jsdatepicker')) {
              mswFldDatePicker(this); 
            }
          });
        }
      }
    });
  });
  return false;
}

// post-submit callback
function mswPR_Callback(responseText, statusText, xhr, $form)  {
  switch (responseText['msg']) {
    case 'ok':
      switch(responseText['field']) {
        case 'redirect':
          window.location = responseText['redirect'];
          break;
        default:
          mswCloseSpinner();
          mswAlert(responseText['info'], responseText['sys'], 'ok');
          break;
      }
      break;
    case 'reload':
      mswCloseSpinner();
      mswAlert(responseText['info'], responseText['sys'], 'ok');
      break;
    case 'err':
      mswCloseSpinner();
      mswAlert(responseText['info'], responseText['sys'], 'err');
      break;
    default:
      mswCloseSpinner();
      break;
  }
}

function mswProcess(page, par) {
  jQuery(document).ready(function() {
    mswShowSpinner();
    setTimeout(function() {
      jQuery.ajax({
        type     : 'POST',
        url      : 'index.php?ajax=' + page + (par != undefined ? '&param=' + par : ''),
        data     : jQuery('#mscontainer > form').serialize(),
        cache    : false,
        dataType : 'json',
        success  : function(data) {
          switch (data['msg']) {
            case 'ok':
              switch(page) {
                case 'faqimport':
                  setTimeout(function() {
                    window.location = '?p=faq-import&cnt=' + data['faq'];
                  }, 500);
                  break;
                case 'logclr':
                case 'mbread':
                case 'mbunread':
                case 'mbclear':
                case 'mbmove':
                case 'mbfolders':
                case 'mbreply':
                case 'tickdispusers':
                  mswCloseSpinner();
                  mswAlert(data['info'], data['sys'], 'ok');
                  break;
                case 'tickrepdel':
                  jQuery('#datarp_' + par).slideUp();
                  mswCloseSpinner();
                  break;
                case 'tickcsdel':
                  var sublink = jQuery('input[name="cs_subl_' + par + '"]').val();
                  jQuery('#cs_wrap_' + par).remove();
                  var cscnt = jQuery('#cs_sublcs_' + sublink + ' div[data-cs="true"]').length;
                  if (cscnt > 0) {
                    jQuery('#sublinks_' + sublink + ' span[class="cscount"]').html(cscnt);
                  } else {
                    jQuery('#sublinks_' + sublink + ' span[class="cscount"]').html('0');
                    jQuery('#sublinks_' + sublink + ' div[class="cslnk"]').remove();
                    jQuery('#sublinks_' + sublink + ' .cs_but').remove();
                    jQuery('#cs_sublcs_' + sublink).remove();
                  }
                  
                  mswCloseSpinner();
                  break;
                case 'tickattdel':
                  jQuery('#datatrat_' + par).slideUp();
                  mswCloseSpinner();
                  if (data['cnt'] == '0') {
                    jQuery('.attachlink').remove();
                    jQuery('.mswatt').remove();
                    jQuery('#sublinks_' + data['rep'] + ' .at_but').remove();
                  } else {
                    jQuery('#sublinks_' + data['rep'] + ' span[class="atcount"]').html(data['cnt']);
                  }
                  break;
                case 'tickassign':
                  for (var i=0; i<data['accepted'].length; i++) {
                    jQuery('#datatr_' + data['accepted'][i]).remove();
                  }
                  if (jQuery('tbody input[name="del[]"]').length == 0) {
                    window.location = 'index.php?p=assign';
                  } else {
                    mswCloseSpinner();
                    mswAlert(data['info'], data['sys'], 'ok');
                    if (jQuery('#delButton').html()) {
                      jQuery('#mswCVal').html('(0)');
                      jQuery('#delButton').addClass('disabled');
                    }
                    jQuery('#mswCVal2').html('(0)');
                    jQuery('#assignButton').addClass('disabled');
                  }
                  break;
                case 'pass-reset':
                  mswCloseSpinner();
                  mswAlert(data['info'], data['sys'], 'ok');
                  break;
                default:
                  if (data['delconfirm'] > 0) {
                    jQuery('tbody input[name="del[]"]:checked').each(function() {
                      jQuery('#datatr_' + jQuery(this).attr('value')).remove();
                      if (jQuery('#tickactions_' + jQuery(this).attr('value'))) {
                        jQuery('#tickactions_' + jQuery(this).attr('value')).remove();
                      }
                    });
                    if (jQuery('tbody input[name="del[]"]').length == 0) {
                      switch(page) {
                        case 'mbdel':
                          window.location = 'index.php?p=mailbox&f=bin';
                          break;
                        default:
                          window.location.reload();
                          break;
                      }
                    } else {
                      // Reset buttons / counters..
                      if (jQuery('#delButton')) {
                        jQuery('#delButton').prop('disabled', true);
                      }
                      if (jQuery('#delButton2')) {
                        jQuery('#delButton2').prop('disabled', true);
                      }
                      if (jQuery('#delButton3')) {
                        jQuery('#delButton3').prop('disabled', true);
                      }
                      if (jQuery('#mswCVal')) {
                        jQuery('#mswCVal').html('(0)');
                      }
                      if (jQuery('#mswCVal2')) {
                        jQuery('#mswCVal2').html('(0)');
                      }
                      if (jQuery('#mswCVal3')) {
                        jQuery('#mswCVal3').html('(0)');
                      }
                      mswCloseSpinner();
                    }
                  } else {
                    switch(page) {
                      case 'mbcompose':
                        jQuery('input[name="subject"]').val('');
                        jQuery('textarea[name="message"]').val('');
                        jQuery('div[class="mailStaff"] input[type="checkbox"]').prop('checked', false);
                        mswCheckCount('mailbox','sendbutton','mswCVal');
                        mswCloseSpinner();
                        mswAlert(data['info'], data['sys'], 'ok');
                        break;
                      default:
                        mswCloseSpinner();
                        if (data['info'] != undefined) {
                          mswAlert(data['info'], data['sys'], 'ok');
                        }
                        break;
                    }
                  }
                  break;
              }
              break;
            case 'ok-tools':
              mswCloseSpinner();
              mswAlert(data['report'], data['sys'], 'ok');
              break;
            case 'ok-dl':
              window.location = 'index.php?ajax=fdl&infp=' + data['file'] + '&infpt=' + data['type'];
              mswCloseSpinner();
              break;
            case 'err':
              mswCloseSpinner();
              mswAlert(data['info'], data['sys'], 'err');
              break;
            default:
              mswCloseSpinner();
              break;
          }
        }
      });
    }, 1500);
  });
  return false;
}

function mswLogin() {
  if (jQuery('input[name="user"]').val() == '' ||
      jQuery('input[name="pass"]').val() == '') {
    if (jQuery('input[name="user"]').val() == '') {
      jQuery('input[name="user"]').focus();
    } else {
      jQuery('input[name="pass"]').focus();
    }
  } else {
    jQuery('input[name="user"]').addClass('box_updating');
    jQuery(document).ready(function() {
      jQuery.ajax({
        type     : 'POST',
        url      : 'index.php?ajax=login',
        data     : jQuery('#mscontainer > form').serialize(),
        cache    : false,
        dataType : 'json',
        success  : function (data) {
          jQuery('input[name="user"]').removeClass('box_updating');
          switch(data['msg']) {
            case 'ok':
              window.location = data['redirect'];
              break;
            default:
              jQuery('div[class="alert alert-warning"] span').html('<i class="fa fa-warning fa-fw"></i> ' + data['info']);
              jQuery('div[class="alert alert-warning"]').slideDown();
              break;
          }
        }
      });
    });
    return false;
  }
}

function mswAlert(msg, txt, mtype, op, op2, op3) {
  if (jQuery('.bootbox')) {
    jQuery('.bootbox').remove();
  }
  if (jQuery('.modal-backdrop')) {
    jQuery('.modal-backdrop').remove();
  }
  switch(mtype) {
    case 'err':
      bootbox.dialog({
        message   : msg,
        title     : '<i class="fa fa-warning fa-fw"></i> ' + txt,
        className : 'msw-box-error',
        onEscape  : true,
        backdrop  : true
      });
      break;
    case 'alert':
      bootbox.alert(msg);
      break;
    case 'confirm':
      bootbox.confirm({
        message : msg,
        buttons : {
          confirm : {
            label     : '<i class="fa fa-check-circle fa-fw"></i> ' + mswlang['confirm_yes'],
            className : 'btn-success'
          },
          cancel : {
            label     : '<i class="fa fa-times-circle fa-fw"></i> ' + mswlang['confirm_no'],
            className : 'btn-danger'
          }
        },
        callback : function (result) {
          switch(result) {
            case true:
              switch(op) {
                case 'link':
                  window.location = txt;
                  break;
                case 'ajax':
                  mswProcess(txt, op2);
                  break;
                case 'tickact':
                  mswTickAct_ajax(txt, op2, op3);
                  break;
                case 'remhis':
                  mswRemoveHistory_ajax(txt, op2)
                  break;
                case 'remfaqhis':
                  mswRemoveFAQHistory_ajax(txt, op2)
                  break;
                case 'imapcheck':
                  iBox.showURL(txt, '', op2);
                  break;
                case 'link':
                  window.location = txt;
                  break;
              }
              break;
            default:
              break;
          }
        }
      });
      break;
    default:
      bootbox.dialog({
        message   : msg,
        title     : '<i class="fa fa-check-circle fa-fw"></i> ' + txt,
        className : 'msw-box-ok',
        onEscape  : true,
        backdrop  : true
      });
      break;
  }
}

function mswShowSpinner() {
  jQuery('body').css({'opacity' : '0.7'});
  jQuery('.overlaySpinner').css({
    'left' : '50%',
    'top' : '50%',
    'position' : 'fixed',
    'margin-left' : -jQuery('.overlaySpinner').outerWidth()/2,
    'margin-top' : -jQuery('.overlaySpinner').outerHeight()/2
  });
  jQuery('div[class="overlaySpinner"]').show();
}

function mswCloseSpinner() {
  jQuery('body').css({
    'opacity': '1.0'
  });
  jQuery('div[class="overlaySpinner"]').hide();
}

function mswUnreadFlag() {
  jQuery('span[class="mailboxcount"]').html(jQuery('span[class="mailboxcount"]').html());
  jQuery.ajax({
    url      : 'index.php',
    data     : 'ajax=unread-mailbox',
    dataType : 'json',
    success  : function (data) {
      if (data['cnt'] > 0) {
        jQuery('span[class="mailboxcount"]').html('<span class="unread">' + data['cnt'] + '</span>');
      } else {
        jQuery('span[class="mailboxcount"]').html('<span class="read">0</span>');
      }
    }
  });
  return false;
}