var updated=false;
var wpk_filterContentAjax=function(inst){
	<?php global $post; ?>
	<?php if(is_object($post)): ?>
	var arr=new Array();
	jQuery(inst.dom.getRoot()).find('img').each(function(){
		var img=jQuery(this);
		var _title=(jQuery(img).attr("title")&&jQuery.trim(jQuery(img).attr("title"))!='')?jQuery.trim(jQuery(img).attr("title")):jQuery.trim(jQuery(img).attr("alt"));
		arr.push({
			src:jQuery(img).attr("src"),
			caption:_title
		});
	});
	var _content=(updated==false)?inst.getBody().innerHTML:updated;
	updated=false;
	var data={
		action:'content_filter_action',
		img:arr,
		content:_content,
		post_id:<?php echo $post->ID; ?>
	};
	jQuery.post(ajaxurl,data,function(response){
		var img='<ul>';
		jQuery.each(response,function(k,v){
			img+='<li class="wpk_fb_icon_row">';
			img+='<div class="wpk_fb_icon"><img src="'+v.url+'" onload="wpk_buildIcon(event)"/></div>';
			img+='<input id="wpk_fb_image_'+k+'" name="wpk_fb_image[]" class="wpk_fb_icon_checkbox" type="checkbox" value="'+v.url+'::'+v.title+'"/><label for="wpk_fb_image_'+k+'">'+v.title;
			if(v.fb_id!=''){
				img+=' ( <a href="http://www.facebook.com/photo.php?fbid='+v.fb_id+'">Image already uploaded</a> )';
			}
			img+='</label>';
			img+='</li>';
		});
		img+='</ul>'
		jQuery('#wpk_metabox_container').html(jQuery(img));
		jQuery('#titlewrap input[type=text]').click(function(){
			jQuery(this).blur(function(evt){
				wpk_filterContentAjax(inst);
			});
		});
		jQuery('#remove-post-thumbnail').click(function(){
			inst.dom.getRoot()).find('li#wpk_featured').remove();
			wpk_filterContentAjax(inst);
		});
	});
	<?php endif ?>
};

var wpk_checkForImage=function(editor_id,node,undo_index,undo_levels,visual_aid,any_selection){
	if(node.nodeName=='IMG'){
		var editor=tinyMCE.getInstanceById(editor_id);
		updated=editor.dom.getRoot();
		wpk_filterContentAjax(editor);
	}
};

var wpk_commandObserver=function(editor_id,elm,command,user_interface,value){
	if(command=='mceInsertContent'){
		var arr=new Array();
		jQuery(value).find('img').each(function(k,item){
			var html='<li class="wpk_fb_icon_row">';
			html+='<div class="wpk_fb_icon"><img src="'+jQuery(this).attr('src')+'" onload="wpk_buildIcon(event)"/></div>';
			html+='<input class="wpk_fb_icon_checkbox" id="wpk_fb_image_'+k+'" name="wpk_fb_image[]" type="checkbox" value="'+jQuery(this).attr('src')+'::'+jQuery(this).attr('alt')+'"/><label for="wpk_fb_image_'+k+'">'+jQuery(this).attr('alt');
			html+='</label>';
			html+='</li>';
			jQuery('#wpk_metabox_container ul').append(html);
		});
	}else{
		var editor=tinyMCE.getInstanceById(editor_id);
		updated=editor.dom.getRoot();
		wpk_filterContentAjax(editor);
	}
};
