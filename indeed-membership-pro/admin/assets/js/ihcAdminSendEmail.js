/*
* Ultimate Membership Pro - Sends Direct Emails
*/
"use strict";
var ihcAdminSendEmail = {
  popupAjax		       : '',
  sendEmailAjax	     : '',
  ajaxPath           : '',
  openPopupSelector  : '',
  sendEmailSelector  : '',
  fromSelector       : '',
  toSelector         : '',
  subjectSelector    : '',
  messageSelector    : '',
  isEmailListenerBound: false,

  init: function(args){
    var obj = this;
    obj.setAttributes(obj, args);

        jQuery(obj.openPopupSelector).on('click', function(evt){
            obj.handleOpenPopup(obj, evt);
        });
        jQuery(document).on("click", obj.sendEmailSelector,function(evt){
          if (!obj.isEmailListenerBound) {
              obj.isEmailListenerBound = true;
              obj.handleSendEmail(obj, evt);
          }
        });
        jQuery(document).on("click", obj.closePopupBttn,function(){
           obj.handleClosePopup(obj);
        });
  },

	setAttributes: function(obj, args){
		for (var key in args) {
			obj[key] = args[key];
		}
	},

  handleOpenPopup: function(obj, evt){
    //console.log(obj.ajaxPath)
    jQuery.ajax({
        type    : "post",
        url     : decodeURI(obj.ajaxPath) + '/wp-admin/admin-ajax.php',
        data    : {
                   action    : obj.popupAjax,
                   uid       : jQuery(evt.target).attr('data-uid'),
        },
        success : function (response) {
            jQuery('body').append(response);
        }
    });
  },

  handleSendEmail: function(obj, evt){
    jQuery.ajax({
        type    : "post",
        url     : decodeURI(obj.ajaxPath) + '/wp-admin/admin-ajax.php',
        data    : {
                   action    : obj.sendEmailAjax,
                   to        : jQuery(obj.toSelector).val(),
                   from      : jQuery(obj.fromSelector).val(),
                   subject   : jQuery(obj.subjectSelector).val(),
                   message   : jQuery(obj.messageSelector).val(),
        },
        success : function (response) {
            if (response){
                obj.handleClosePopup(obj);
                obj.isEmailListenerBound = false;
            }
        }
    });
  },

  handleClosePopup: function(obj){
      jQuery(obj.popupWrapp).remove();
  },

}

jQuery(window).on('load', function(){
  ihcAdminSendEmail.init({
      popupAjax		       : 'ihc_admin_send_email_popup',
    	sendEmailAjax	     : 'ihc_admin_do_send_email',
    	ajaxPath           : decodeURI(window.ihc_site_url),
      openPopupSelector  : '.ihc-admin-do-send-email-via-ump',
      sendEmailSelector  : '#indeed_admin_send_mail_submit_bttn',
      fromSelector       : '#indeed_admin_send_mail_from',
      toSelector         : '#indeed_admin_send_mail_to',
      subjectSelector    : '#indeed_admin_send_mail_subject',
      messageSelector    : '#indeed_admin_send_mail_content',
      closePopupBttn     : '#ihc_send_email_via_admin_close_popup_bttn',
      popupWrapp         : '#ihc_admin_popup_box',
  });
});
