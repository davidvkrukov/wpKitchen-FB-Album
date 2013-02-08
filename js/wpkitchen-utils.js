jQuery(document).ready(function($){
	$(document).data('current_post_thumbnail',null);
	$('#postimagediv .inside').bind("DOMSubtreeModified",function(){
		var featured=$(this).find('img.attachment-post-thumbnail');
		if($(document).data('current_post_thumbnail')!=$(featured).attr('src')){
			if(featured.length>0){
				$(document).data('current_post_thumbnail',$(featured).attr('src'));
				var k=$('#wpk_metabox_container ul li.wpk_fb_icon_row').size();
				var img='<li class="wpk_fb_icon_row" id="wpk_featured">';
				var html='<div class="wpk_fb_icon" style="background:url('+timthumbPath+'?src='+$(featured).attr('src')+'&w=50&h=50&q=80&zc=1) no-repeat center center"></div>';
				html+='<input class="wpk_fb_icon_checkbox" id="wpk_fb_image_'+k+'" name="wpk_fb_image[]" type="checkbox" value="'+$(featured).attr('src')+'::'+$(featured).attr('alt')+'"/><label for="wpk_fb_image_'+k+'">'+$(featured).attr('alt');
				html+='</label>';
				img+=html+'</li>';
				if($('#wpk_metabox_container ul li#wpk_featured').length>0){
					$('#wpk_metabox_container ul li#wpk_featured').html(html);
				}else{
					$('#wpk_metabox_container ul').append(img);
				}
			}
		}
	});
});