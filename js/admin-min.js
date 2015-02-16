jQuery(document).ready(function($,undefined){function makeSortable(){$(".launchpad-flexible-container, .launchpad-repeater-container").sortable({handle:"h3",opacity:.5,placeholder:"launchpad-flexible-container-placeholder",forcePlaceholderSize:!0,revert:!0,containment:"parent",axis:"y",items:"> div"}),$(".launchpad-relationship-items").sortable({handle:"a",opacity:.5,placeholder:"launchpad-flexible-container-placeholder",forcePlaceholderSize:!0,revert:!0,containment:"parent",axis:"y",items:"> li"})}function handleUpdatingFlexibleModules(){var tinymceconfig=$.extend(!0,{},tinyMCEPreInit.mceInit.content),qtconfig=$.extend(!0,{},tinyMCEPreInit.qtInit.content),edId=this.id;tinymceconfig.selector="#"+edId,qtconfig.id=edId,tinyMCEPreInit.mceInit[edId]=tinymceconfig,tinyMCEPreInit.qtInit[edId]=qtconfig,tinyMCE.init(tinymceconfig);try{QTags(qtconfig),QTags._buttonsInit()}catch(e){}switchEditors.switchto($(tinymceconfig.selector).closest(".wp-editor-wrap").find(".wp-switch-editor.switch-"+("html"===getUserSetting("editor")?"html":"tmce")).get(0)),window.wpActiveEditor||(window.wpActiveEditor=edId)}function handleToggleStates(){$("[data-toggle]").each(function(){var me=$(this),elval=me.val(),cont=me.closest(".launchpad-flexible-metabox-container, .postbox");toggle=me.data("toggle"),null===elval&&(elval=""),$.isArray(elval)||(elval=[elval]),$.each(elval,function(index){elval[index]=(elval[index]+"").toString().toLowerCase()}),$.each(toggle,function(index){var val=this,ishidden=!0,tmp;if("none"!==me.parent().parent().css("display"))if(this.hide_when!==undefined)ishidden=!1,val=val.hide_when,$.isArray(val)||(val=[val]),$.each(val,function(index){val[index]=(val[index]+"").toString().toLowerCase()}),me.is("[type=radio]")||me.is("[type=checkbox]")?0!=val&&me.prop("checked")?ishidden=!0:0!=val||me.prop("checked")||(ishidden=!0):$.each(elval,function(){$.inArray(this+"",val)>-1&&(ishidden=!0)});else{if(val.show_when===undefined)return;ishidden=!0,val=val.show_when,$.isArray(val)||(val=[val]),$.each(val,function(index){val[index]=(val[index]+"").toString().toLowerCase()}),me.is("[type=radio]")||me.is("[type=checkbox]")?0!=val&&me.prop("checked")?ishidden=!1:0!=val||me.prop("checked")||(ishidden=!1):$.each(elval,function(){$.inArray(this+"",val)>-1&&(ishidden=!1)})}cont.find('[name*="['+index+']"]').each(function(){ishidden?$(this).parent().parent().parent().is(".launchpad-repeater-metabox-container")?$(this).parent().parent().parent().parent().parent().addClass("launchpad-toggle-hidden"):$(this).closest(".launchpad-metabox-field").addClass("launchpad-toggle-hidden"):$(this).parent().parent().parent().is(".launchpad-repeater-metabox-container")?$(this).parent().parent().parent().parent().parent().removeClass("launchpad-toggle-hidden"):$(this).closest(".launchpad-metabox-field").removeClass("launchpad-toggle-hidden")})})})}function handleWatchStates(){$("[data-watch]").each(function(){var me=$(this),watch=me.data("watch"),show_me=!0;$.each(watch,function(index){val=$(index).val(),this.hide_when!==undefined?show_me=val==this.hide_when?!1:!0:this.show_when!==undefined&&(show_me=val==this.show_when?!0:!1)}),me.parent().parent().css("display",show_me?"block":"none")})}$(document).on("change keyup","input, select, textarea",function(e){handleWatchStates(),handleToggleStates()});var regenerate_thumbnail_ids=[];makeSortable(),handleWatchStates(),handleToggleStates(),$("input.launchpad-date-picker").length&&$("input.launchpad-date-picker").datepicker().unbind("keydown").unbind("keyup").unbind("keypress"),$(document.body).on("click","#start-regen",function(){var status_area=$("#launchpad-regen-thumbnail-status"),button=$(this),percent_area=$("#launchpad-processing-percent");button.data("processing")!==!0?(status_area.html(""),button.data("processing",!0).attr("value","Stop Processing"),percent_area.html("0% Complete"),$.get("/wp-admin/admin-ajax.php?action=get_attachment_list&nonce="+launchpad_nonce,function(data){function process(){var cur;regenerate_thumbnail_ids.length?(cur=regenerate_thumbnail_ids.shift(),$("#launchpad-regen-"+cur).attr("class","launchpad-regen-processing"),$("#launchpad-regen-"+cur).find(".status").html("Processing..."),$.get("/wp-admin/admin-ajax.php?action=do_regenerate_image&attachment_id="+cur+"&nonce="+launchpad_nonce,function(data){var message;$("#launchpad-regen-"+data.attachment_id).attr("class",""),1===data.status?(message="Complete.",$("#launchpad-regen-"+data.attachment_id).addClass("launchpad-regen-complete")):(message="Failed.",$("#launchpad-regen-"+data.attachment_id).addClass("launchpad-regen-failed")),$("#launchpad-regen-"+data.attachment_id).find(".status").html(message),button.attr("value","Stop Processing"),percent_area.html(Math.round(status_area.find("div.launchpad-regen-complete, div.launchpad-regen-fail").length/status_area.find("div").length*100)+"% Complete"),process()})):button.data("processing",!1).attr("value","Start Regenerating Thumbnails")}regenerate_thumbnail_ids=data,$.each(regenerate_thumbnail_ids,function(){status_area.append('<div id="launchpad-regen-'+this+'" class="launchpad-regen-waiting">Image № '+this+': <span class="status">Waiting...</span></div>')}),process()})):(button.data("processing",!1).attr("value","Restart Regenerating Thumbnails"),$.each(regenerate_thumbnail_ids,function(){$("#launchpad-regen-"+this).attr("class",""),$("#launchpad-regen-"+this).addClass("launchpad-regen-canceled"),$("#launchpad-regen-"+this).find(".status").html("Canceled.")}),regenerate_thumbnail_ids=[])}),$(document.body).on("click",".launchpad-file-button",function(e){var me=$(this),config={title:"Upload File",button:{text:"Add File"},multiple:!1},custom_uploader;me.data("limit")&&(config.library={type:me.data("limit")}),custom_uploader=wp.media(config).on("select",function(){var attachment=custom_uploader.state().get("selection").first().toJSON(),update=$("#"+me.data("for")),delete_link=update.parent().find(".launchpad-delete-file"),remove_link;update.length?(update.attr("value",attachment.id),remove_link=$('<a href="#" class="launchpad-delete-file" data-for="'+me.data("for")+"\" onclick=\"document.getElementById(this.getAttribute('data-for')).value=''; this.parentNode.removeChild(this); return false;\"><img src=\""+(attachment.sizes&&attachment.sizes.thumbnail?attachment.sizes.thumbnail.url:attachment.icon)+'"></a>'),delete_link.length?delete_link.replaceWith(remove_link):update.parent().append(remove_link)):alert("There was a problem attaching the media.  Please contact your developer.")}).open(),e.stopImmediatePropagation(),e.preventDefault()}),$(document.body).on("click","button.launchpad-repeater-add",function(){var me=$(this),container_id=me.data("for"),container=$("#"+container_id),master=container.children().first().clone(),master_replace_with="launchpad-"+(new Date).getTime()+"-repeater",visualeditors;console.log(master),master.find("[name], [data-field-name], button[data-for]").each(function(){var me=$(this);me.is("[name]")&&(me.attr("name",me.attr("name").replace(/launchpad\-.*?\-repeater/g,master_replace_with)),me.attr("id")&&me.attr("id",me.attr("id").replace(/launchpad\-.*?\-repeater/g,master_replace_with))),me.is("button[data-for]")&&(me.attr("data-for",me.attr("data-for").replace(/launchpad\-.*?\-repeater/g,master_replace_with)),me.parent().find("a.launchpad-delete-file").remove(),me.parent().find("input[type=hidden]").get(0).value=""),me.is("[data-field-name]")&&me.attr("data-field-name",me.attr("data-field-name").replace(/launchpad\-.*?\-repeater/g,master_replace_with)),me.is("input:not(checkbox)")&&me.val(""),me.is("select")&&me.val("")}),master.find(".launchpad-relationship-items").html(""),console.log(master),container.append(master),visualeditors=master.find("textarea.wp-editor-area"),visualeditors.length&&visualeditors.each(function(){for(var me=$(this),editor=me.closest(".wp-editor-wrap"),editor_current_id=editor.attr("id").slice(3,-5),cnt=1;$("#wp-"+editor_current_id+cnt+"-wrap").length;)cnt++;editor_current_id+=cnt,editor.addClass("launchpad-editor-loading"),$.get("/wp-admin/admin-ajax.php?action=get_editor&id="+editor_current_id+"&name="+me.attr("name")+"&nonce="+launchpad_nonce,function(data){data=$(data),editor.replaceWith(data),data.find("textarea.wp-editor-area").each(handleUpdatingFlexibleModules),$(".wp-editor-wrap").off("click.wp-editor").on("click.wp-editor",function(){this.id&&(window.wpActiveEditor=this.id.slice(3,-5))})})}),makeSortable(),handleToggleStates()}),$(document.body).on("click","a.launchpad-flexible-link",function(e){var me=$(this);e.preventDefault(),$.get("/wp-admin/admin-ajax.php?action=get_flexible_field&type="+me.data("launchpad-flexible-type")+"&name="+me.data("launchpad-flexible-name")+"&id="+me.data("launchpad-flexible-post-id")+"&nonce="+launchpad_nonce,function(data){var visualeditors;data=$(data),visualeditors=data.find("textarea.wp-editor-area"),$("#launchpad-flexible-container-"+me.data("launchpad-flexible-type")).append(data),visualeditors.length&&(visualeditors.each(handleUpdatingFlexibleModules),$(".wp-editor-wrap").off("click.wp-editor").on("click.wp-editor",function(){this.id&&(window.wpActiveEditor=this.id.slice(3,-5))})),makeSortable(),handleToggleStates()})}),$(document.body).on("keyup input",".launchpad-relationship-search-field",function(e){var me=$(this),container=me.closest(".launchpad-relationship-container"),listing=container.find(".launchpad-relationship-list");("input"!==e.type||""===this.value.replace(/\s/,""))&&$.get("/wp-admin/admin-ajax.php?action=search_posts&post_type="+container.data("post-type")+"&query="+(container.data("query")?encodeURIComponent($.param(container.data("query"))):"")+"&terms="+me.val()+"&nonce="+launchpad_nonce,function(data){listing.html(""),$.each(data,function(){listing.append($('<li><a href="#" data-id="'+this.ID+'">'+this.post_title+" <small>"+this.ancestor_chain+"</small></a></li>"))})})}).on("click",".launchpad-relationship-list a",function(e){var me=$(this),cp=me.clone(),container=me.closest(".launchpad-relationship-container"),addto=container.find(".launchpad-relationship-items"),fname=container.data("field-name"),limit=container.data("limit");e.preventDefault(),cp.append($('<input type="hidden" name="'+fname+'" value="'+me.data("id")+'">')),!$('[value="'+me.data("id")+'"]',addto).length&&(0>=+limit||$("[value]",addto).length<+limit)&&(me=$("<li>"),me.css("height",0),me.append(cp),addto.append(me),me.animate({height:cp.outerHeight()}))}).on("click",".launchpad-relationship-items a",function(e){var me=$(this);e.preventDefault(),me.parent().animate({height:0},function(){me.parent().remove()})}).on("change","fieldset.launchpad-address input",function(){var me=$(this),fs=me.closest(".launchpad-address"),addr="";$("input:not([type=hidden])",fs).each(function(){var me=$(this);-1===me.attr("name").indexOf("[number]")&&(addr+=me.val(),(-1!==me.attr("name").indexOf("[street]")||-1!==me.attr("name").indexOf("[city]")&&addr)&&(addr+=","),addr+=" ")}),addr=addr.replace(/\s\s+/g," ").replace(/ , /," ").replace(/, ?$/,""),$(".launchpad-google-map-embed",fs).html("<div>Searching for: "+addr+"</div>"),addr?$.post("/wp-admin/admin-ajax.php",{action:"geocode",address:addr,nonce:launchpad_nonce},function(data){$("input",fs).each(function(){var me=$(this);-1!==me.attr("name").indexOf("[latitude]")&&me.val(data.lat),-1!==me.attr("name").indexOf("[longitude]")&&me.val(data.lng)}),$(".launchpad-google-map-embed",fs).html(0==data.lat&&0==data.lng?"<div>No Matches Found.</div>":'<iframe width="100%" height="200" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="//maps.google.com/maps?q='+data.lat+","+data.lng+'+(Your Location)&amp;output=embed"></iframe>')}):$(".launchpad-google-map-embed",fs).html("<div>No Address Provided.</div>")}),$("textarea[maxlength]").keyup(function(e){var me=$(this),range;e.ctrlKey||e.altKey||e.metaKey||91===e.which||me.val().length>me.attr("maxlength")&&(me.val(me.val().substr(0,me.attr("maxlength"))),"number"==typeof this.selectionStart?this.selectionStart=this.selectionEnd=this.value.length:"undefined"!=typeof this.createTextRange&&(this.focus(),range=this.createTextRange(),range.collapse(!1),range.select()))}).add("input[maxlength]").on("keyup",function(){var me=$(this);me.parent().find(".launchpad-char-count").html(+me.attr("maxlength")-me.val().length)}).each(function(){var me=$(this),ml=me.attr("maxlength");me.parent().append('<small>Characters Left: <span class="launchpad-char-count">'+(+ml-me.val().length)+"</span> of "+ml+"</small>")}),$('[name="launchpad_meta[SEO][title]"], [name="launchpad_meta[SEO][keyword]"]').keyup(function(){var serp_head=$("#serp-heading"),parsed_val=$('[name="launchpad_meta[SEO][title]"]').val();parsed_val=parsed_val.replace(/^\s+/,"").replace(/\s+$/,""),""===parsed_val&&(parsed_val=serp_head.data("post-title")),parsed_val&&serp_head.html(parsed_val.substr(0,70).replace(new RegExp("("+$('[name="launchpad_meta[SEO][keyword]"]').val().replace(/\s+/g,"|")+")","ig"),"<strong>$1</strong>")+(parsed_val.length>70?" ...":""))}),$('[name="launchpad_meta[SEO][meta_description]"], [name="launchpad_meta[SEO][keyword]"]').keyup(function(){var serp_head=$("#serp-meta"),parsed_val=$('[name="launchpad_meta[SEO][meta_description]"]').val();parsed_val=parsed_val.replace(/^\s+/,"").replace(/\s+$/,""),""===parsed_val&&(parsed_val=serp_head.data("post-excerpt")),parsed_val&&serp_head.html(parsed_val.substr(0,160).replace(new RegExp("("+$('[name="launchpad_meta[SEO][keyword]"]').val().replace(/\s+/g,"|")+")","ig"),"<strong>$1</strong>")+(parsed_val.length>160?" ...":""))}),$(".launchpad-checkbox-toggle").each(function(){var me=$(this),toggle=$('<div class="launchpad-metabox-field launchpad-metabox-toggle-all"><label><input type="checkbox"> Toggle All</label></div>');me.find("[type=checkbox]").length==me.find("[type=checkbox]:checked").length&&toggle.find("[type=checkbox]").attr("checked","checked"),$("legend",me).after(toggle),toggle.find("[type=checkbox]").on("change",function(){me=$(this),me.is(":checked")?me.closest(".launchpad-checkbox-toggle").find("[type=checkbox]").attr("checked","checked"):me.closest(".launchpad-checkbox-toggle").find("[type=checkbox]").removeAttr("checked")})}),$("#migrate-table-checkbox, [name=migrate_attached_files]").on("change",function(){setTimeout(function(){var total_rows=0,total_files=0,count_rows=$("[name=migrate_attached_files]").is(":checked");$("#migrate-table-checkbox :checked").each(function(){var me=$(this);me.data("rows")&&(total_rows+=+me.data("rows")),count_rows&&me.data("files")&&(total_files+=+me.data("files"))}),$("#migrate-rows-total").html(total_rows),$("#migrate-files-total").html(total_files)},250)})});