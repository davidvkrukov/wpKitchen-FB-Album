var filterContentAjax=function(inst){
	<?php global $post; ?>
	<?php if(is_object($post)): ?>
	var arr=new Array();
	jQuery(tinyMCE.activeEditor.dom.getRoot()).find('img').each(function(){
		var img=jQuery(this);
		arr.push({
			src:img.attr("src"),
			caption:jQuery.trim(img.attr("alt"))
		});
	});
	var data={
		action:'content_filter_action',
		img:arr,
		content:inst.getBody().innerHTML,
		post_id:<?php global $post; echo $post->ID; ?>
	};
	jQuery.post(ajaxurl,data,function(response){
		var img='<ul>';
		jQuery.each(response,function(k,v){
			img+='<li class="wpk_fb_icon_row">';
			img+='<div class="wpk_fb_icon" style="background:url(<?php echo plugins_url('lib/timthumb.php',dirname(__FILE__)); ?>?src='+v.url+'&w=50&h=50&q=80&zc=1) no-repeat center center"></div>';
			if(v.fb_id!=''){
				img+='<span class="wpk_fb_uploaded"> Image already uploaded to album </span>';// | <a hred="#" id="delete_'+v.fb_id+'">delete from album</a>';
			}else{
				img+='<input class="wpk_fb_icon_checkbox" id="wpk_fb_image_'+k+'" name="wpk_fb_image[]" type="checkbox" value="'+v.url+'::'+v.title+'"/><label for="wpk_fb_image_'+k+'">'+v.title+'</label>';
			}
			img+='</li>';
		});
		img+='</ul>'
		jQuery('#wpk_metabox_container').html(jQuery(img));
		jQuery('#titlewrap input[type=text]').unbind('click').bind('click',function(){
			jQuery(this).blur(function(evt){
				filterContentAjax(inst);
			});
		});
	});
	<?php endif ?>
};
