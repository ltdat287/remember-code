/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.0.2
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2010 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
vBulletin.events.systemInit.subscribe(function(){if(AJAX_Compatible){vB_QuickEditor_Factory=new vB_QuickEditor_Factory()}});function vB_QuickEditor_Factory(){this.editorcounter=0;this.controls=new Array();this.open_objectid=null;this.init()}vB_QuickEditor_Factory.prototype.init=function(){vBulletin.attachinfo={};this.target="blog_post.php";this.postaction="postcomment";this.objecttype="bt";this.getaction="editcomment";this.ajaxtarget="blog_ajax.php";this.ajaxaction="quickeditcomment";this.deleteaction="deleteblog";this.messagetype="comment_text_";this.containertype="comment";this.responsecontainer="commentbits";if(vBulletin.elements.vB_QuickEdit){for(var A=0;A<vBulletin.elements.vB_QuickEdit.length;A++){var B=vBulletin.elements.vB_QuickEdit[A];var C=YAHOO.util.Dom.get(this.containertype+"_edit_"+B);if(C){this.controls[B]=new vB_QuickEditor(B,this)}}vBulletin.elements.vB_QuickEdit=null}};vB_QuickEditor_Factory.prototype.obj_init=function(A){Comment_Init(YAHOO.util.Dom.get(this.containertype+"_"+A))};vB_QuickEditor_Factory.prototype.close_all=function(){if(this.open_objectid){this.controls[this.open_objectid].abort()}};function vB_QuickEditor(B,A){this.init(B,A)}vB_QuickEditor.prototype.init=function(B,A){this.originalhtml=null;this.ajax_req=null;this.show_advanced=true;if(B){this.objectid=B}if(A){this.factory=A}this.messageobj=YAHOO.util.Dom.get(this.factory.messagetype+this.objectid);this.node=YAHOO.util.Dom.get(this.factory.containertype+"_"+this.objectid);this.progress_indicator=YAHOO.util.Dom.get(this.factory.containertype+"_progress_"+this.objectid);var C=YAHOO.util.Dom.get(this.factory.containertype+"_edit_"+this.objectid);YAHOO.util.Event.on(C,"click",this.edit,this,true)};vB_QuickEditor.prototype.ready=function(){if(this.factory.open_objectid!=null||YAHOO.util.Connect.isCallInProgress(this.ajax_req)){return false}else{return true}};vB_QuickEditor.prototype.edit=function(A){if(A){YAHOO.util.Event.stopEvent(A)}if(typeof vb_disable_ajax!="undefined"&&vb_disable_ajax>0){return true}if(YAHOO.util.Connect.isCallInProgress(this.ajax_req)){return false}else{if(!this.ready()){if(this.objectid==this.factory.open_objectid){this.full_edit();return false}this.factory.close_all()}}this.factory.open_objectid=this.objectid;this.factory.editorcounter++;this.editorid="vB_Editor_QE_"+this.factory.containertype+this.factory.editorcounter;this.originalhtml=this.messageobj.innerHTML;this.unchanged=null;this.unchanged_reason=null;this.fetch_editor();return false};vB_QuickEditor.prototype.fetch_editor=function(){if(this.progress_indicator){this.progress_indicator.style.display=""}document.body.style.cursor="wait";YAHOO.util.Connect.asyncRequest("POST",this.factory.ajaxtarget+"?do="+this.factory.ajaxaction+"&"+this.factory.objecttype+"="+this.objectid,{success:this.display_editor,failure:this.error_opening_editor,timeout:vB_Default_Timeout,scope:this},SESSIONURL+"securitytoken="+SECURITYTOKEN+"&do="+this.factory.ajaxaction+"&"+this.factory.objecttype+"="+this.objectid+"&editorid="+PHP.urlencode(this.editorid))};vB_QuickEditor.prototype.error_opening_editor=function(A){vBulletin_AJAX_Error_Handler(A);window.location=this.target+"?"+SESSIONURL+"do="+this.getaction+"&"+this.objecttype+"="+this.objectid};vB_QuickEditor.prototype.handle_save_error=function(A){vBulletin_AJAX_Error_Handler(A);this.show_advanced=false;this.full_edit()};vB_QuickEditor.prototype.display_editor=function(G){if(G.responseXML){if(this.progress_indicator){this.progress_indicator.style.display="none"}document.body.style.cursor="auto";if(fetch_tag_count(G.responseXML,"disabled")){window.location=this.failed_location+this.objectid}else{if(fetch_tag_count(G.responseXML,"error")){}else{if(G.responseXML.getElementsByTagName("contenttypeid").length>0){vBulletin.attachinfo={contenttypeid:G.responseXML.getElementsByTagName("contenttypeid")[0].firstChild.nodeValue,userid:G.responseXML.getElementsByTagName("userid")[0].firstChild.nodeValue,attachlimit:G.responseXML.getElementsByTagName("attachlimit")[0].firstChild.nodeValue,max_file_size:G.responseXML.getElementsByTagName("max_file_size")[0].firstChild.nodeValue,auth_type:G.responseXML.getElementsByTagName("auth_type")[0].firstChild.nodeValue,posthash:G.responseXML.getElementsByTagName("posthash")[0].firstChild.nodeValue,poststarttime:G.responseXML.getElementsByTagName("poststarttime")[0].firstChild.nodeValue};var B=G.responseXML.getElementsByTagName("values");if(B.length>0&&B[0].childNodes.length){vBulletin.attachinfo.values={};for(var E=0;E<B[0].childNodes.length;E++){if(B[0].childNodes[E].nodeName!="#text"&&typeof (B[0].childNodes[E].childNodes[0])!="undefined"){vBulletin.attachinfo.values[B[0].childNodes[E].nodeName]=B[0].childNodes[E].childNodes[0].nodeValue}}}var A=G.responseXML.getElementsByTagName("phrases");if(A.length>0&&A[0].childNodes.length){for(var E=0;E<A[0].childNodes.length;E++){if(A[0].childNodes[E].nodeName!="#text"&&typeof (A[0].childNodes[E].childNodes[0])!="undefined"){vbphrase[A[0].childNodes[E].nodeName]=A[0].childNodes[E].childNodes[0].nodeValue}}}}var F=fetch_tags(G.responseXML,"editor")[0];var H=F.getAttribute("reason");this.messageobj.innerHTML=F.firstChild.nodeValue;var C=YAHOO.util.Dom.get(this.editorid+"_edit_reason");if(C){this.unchanged_reason=PHP.unhtmlspecialchars(H);C.value=this.unchanged_reason;C.onkeypress=vB_QuickEditor_Delete_Events.prototype.reason_key_trap}vB_Editor[this.editorid]=new vB_Text_Editor(this.editorid,F.getAttribute("mode"),F.getAttribute("parsetype"),F.getAttribute("parsesmilies"));if(YAHOO.util.Dom.get(this.editorid+"_editor")&&YAHOO.util.Dom.get(this.editorid+"_editor").scrollIntoView){YAHOO.util.Dom.get(this.editorid+"_editor").scrollIntoView(true)}vB_Editor[this.editorid].set_editor_width("100%",true);vB_Editor[this.editorid].check_focus();this.unchanged=vB_Editor[this.editorid].get_editor_contents();YAHOO.util.Event.on(YAHOO.util.Dom.get(this.editorid+"_save"),"click",this.save,this,true);YAHOO.util.Event.on(YAHOO.util.Dom.get(this.editorid+"_abort"),"click",this.abort,this,true);YAHOO.util.Event.on(YAHOO.util.Dom.get(this.editorid+"_adv"),"click",this.full_edit,this,true);YAHOO.util.Event.on("quick_edit_errors_hide","click",this.hide_errors,this,true);YAHOO.util.Event.on("quick_edit_errors_cancel","click",this.abort,this,true);var D=YAHOO.util.Dom.get(this.editorid+"_delete");if(D){YAHOO.util.Event.on(this.editorid+"_delete","click",this.show_delete,this,true)}}}}};vB_QuickEditor.prototype.restore=function(B,A){this.hide_errors(true);if(this.editorid&&vB_Editor[this.editorid]&&vB_Editor[this.editorid].initialized){vB_Editor[this.editorid].destroy()}if(A=="node"){var C=string_to_node(B);this.node.parentNode.replaceChild(C,this.node)}else{this.messageobj.innerHTML=B}this.factory.open_objectid=null};vB_QuickEditor.prototype.abort=function(A){if(A){YAHOO.util.Event.stopEvent(A)}if(this.progress_indicator){this.progress_indicator.style.display="none"}document.body.style.cursor="auto";this.restore(this.originalhtml,"messageobj")};vB_QuickEditor.prototype.full_edit=function(B){if(B){YAHOO.util.Event.stopEvent(B)}var A=new vB_Hidden_Form(this.factory.target+"?do="+this.factory.postaction+"&"+this.factory.objecttype+"="+this.objectid);A.add_variable("do",this.factory.postaction);A.add_variable("s",fetch_sessionhash());A.add_variable("securitytoken",SECURITYTOKEN);if(this.show_advanced){A.add_variable("advanced",1)}A.add_variable(this.factory.objecttype,this.objectid);A.add_variable("wysiwyg",vB_Editor[this.editorid].wysiwyg_mode);A.add_variable("message",vB_Editor[this.editorid].get_editor_contents());A.add_variable("reason",YAHOO.util.Dom.get(this.editorid+"_edit_reason").value);A.submit_form()};vB_QuickEditor.prototype.save=function(C){YAHOO.util.Event.stopEvent(C);var E=vB_Editor[this.editorid].get_editor_contents();var B=YAHOO.util.Dom.get(this.editorid+"_edit_reason");if(E==this.unchanged&&B&&B.value==this.unchanged_reason){this.abort(C)}else{YAHOO.util.Dom.get(this.editorid+"_posting_msg").style.display="";document.body.style.cursor="wait";var D=YAHOO.util.Dom.get("postcount"+this.objectid);var A=YAHOO.util.Dom.get("blog_entry_list");this.ajax_req=YAHOO.util.Connect.asyncRequest("POST",this.factory.target+"?do="+this.factory.postaction+"&"+this.factory.objecttype+"="+this.objectid,{success:this.update,failure:this.handle_save_error,timeout:vB_Default_Timeout,scope:this},SESSIONURL+"securitytoken="+SECURITYTOKEN+"&do="+this.factory.postaction+"&ajax=1&"+this.factory.objecttype+"="+this.objectid+"&posthash="+vBulletin.attachinfo.posthash+"&wysiwyg="+vB_Editor[this.editorid].wysiwyg_mode+"&message="+PHP.urlencode(E)+"&reason="+PHP.urlencode(YAHOO.util.Dom.get(this.editorid+"_edit_reason").value)+(D!=null?"&postcount="+PHP.urlencode(D.name):"")+(A!=null?"&linkblog=1":""));this.pending=true}};vB_QuickEditor.prototype.show_delete=function(){this.deletedialog=YAHOO.util.Dom.get("quickedit_delete");if(this.deletedialog&&this.deletedialog.style.display!=""){this.deletedialog.style.display="";this.deletebutton=YAHOO.util.Dom.get("quickedit_dodelete");YAHOO.util.Event.on(this.deletebutton,"click",this.delete_post,this,true);var B=YAHOO.util.Dom.get("del_reason");if(B){B.onkeypress=vB_QuickEditor_Delete_Events.prototype.delete_items_key_trap}if(!is_opera&&!is_saf){this.deletebutton.disabled=true;this.deleteoptions=new Array();this.deleteoptions.leave=YAHOO.util.Dom.get("rb_del_leave");this.deleteoptions.soft=YAHOO.util.Dom.get("deltype_soft");this.deleteoptions.hard=YAHOO.util.Dom.get("deltype_hard");for(var A in this.deleteoptions){if(YAHOO.lang.hasOwnProperty(this.deleteoptions,A)&&this.deleteoptions[A]){this.deleteoptions[A].onclick=this.deleteoptions[A].onchange=vB_QuickEditor_Delete_Events.prototype.delete_button_handler;this.deleteoptions[A].onkeypress=vB_QuickEditor_Delete_Events.prototype.delete_items_key_trap}}}}};vB_QuickEditor.prototype.delete_post=function(){var A=YAHOO.util.Dom.get("rb_del_leave");if(A&&A.checked){this.abort();return }var B=new vB_Hidden_Form(this.factory.target);B.add_variable("do",this.factory.deleteaction);B.add_variable("s",fetch_sessionhash());B.add_variable("securitytoken",SECURITYTOKEN);B.add_variable(this.factory.objecttype,this.objectid);B.add_variables_from_object(this.deletedialog);B.submit_form()};vB_QuickEditor.prototype.update=function(D){if(D.responseXML){this.pending=false;document.body.style.cursor="auto";YAHOO.util.Dom.get(this.editorid+"_posting_msg").style.display="none";if(fetch_tag_count(D.responseXML,"error")){var E=fetch_tags(D.responseXML,"error");var A="<ol>";for(var B=0;B<E.length;B++){A+="<li>"+E[B].firstChild.nodeValue+"</li>"}A+="</ol>";this.show_errors(A)}else{var C=D.responseXML.getElementsByTagName("message");this.restore(C[0].firstChild.nodeValue,"node");this.factory.obj_init(this.objectid)}}return false};vB_QuickEditor.prototype.show_errors=function(A){set_unselectable("quick_edit_errors_hide");YAHOO.util.Dom.get("ajax_post_errors_message").innerHTML=A;var B=YAHOO.util.Dom.get("ajax_post_errors");var C=(is_saf?"body":"documentElement");YAHOO.util.Dom.setStyle(B,"left",(is_ie?document.documentElement.clientWidth:self.innerWidth)/2-200+document[C].scrollLeft+"px");YAHOO.util.Dom.setStyle(B,"top",(is_ie?document.documentElement.clientHeight:self.innerHeight)/2-150+document[C].scrollTop+"px");YAHOO.util.Dom.removeClass(B,"hidden")};vB_QuickEditor.prototype.hide_errors=function(A){this.errors=false;YAHOO.util.Dom.addClass("ajax_post_errors","hidden");if(A!=true){vB_Editor[this.editorid].check_focus()}};function vB_QuickEditor_Delete_Events(){}vB_QuickEditor_Delete_Events.prototype.delete_button_handler=function(C){var B=vB_QuickEditor_Factory.open_objectid;var A=vB_QuickEditor_Factory.controls[B];if(this.id=="rb_del_leave"&&this.checked){A.deletebutton.disabled=true}else{A.deletebutton.disabled=false}};vB_QuickEditor_Delete_Events.prototype.reason_key_trap=function(C){var B=vB_QuickEditor_Factory.open_objectid;var A=vB_QuickEditor_Factory.controls[B];C=C?C:window.event;switch(C.keyCode){case 9:YAHOO.util.Dom.get(A.editorid+"_save").focus();return false;break;case 13:A.save();return false;break;default:return true}};vB_QuickEditor_Delete_Events.prototype.delete_items_key_trap=function(C){var B=vB_QuickEditor_Factory.open_objectid;var A=vB_QuickEditor_Factory.controls[B];C=C?C:window.event;if(C.keyCode==13){if(open_obj.deletebutton.disabled==false){open_obj.delete_post()}return false}return true};