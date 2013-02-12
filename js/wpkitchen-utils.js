var wpk_buildIcon=function(evt){
	var img=evt.currentTarget;
	var w=jQuery(img).width();
	var h=jQuery(img).height();
	var tw=jQuery(img).parent().width()+2;
	var th=jQuery(img).parent().height()+2;
	var result={width:0,height:0};
	var result=wpk_scale(w,h,tw,th,false);
	img.width=result.width+2;
    img.height=result.height+2;
    jQuery(img).css("left",result.targetleft);
    jQuery(img).css("top",result.targettop);
};

var wpk_scale=function(srcwidth,srcheight,targetwidth,targetheight,fLetterBox){
	var result={width:0,height:0,fScaleToTargetWidth:true};
	if((srcwidth<=0)||(srcheight<=0)||(targetwidth<=0)||(targetheight<=0)){
		return result;
	}
	var scaleX1=targetwidth;
	var scaleY1=(srcheight*targetwidth)/srcwidth;
	var scaleX2=(srcwidth*targetheight)/srcheight;
	var scaleY2=targetheight;
	var fScaleOnWidth=(scaleX2>targetwidth);
	if(fScaleOnWidth){
		fScaleOnWidth=fLetterBox;
	}else{
		fScaleOnWidth=!fLetterBox;
	}
	if(fScaleOnWidth){
		result.width=Math.floor(scaleX1);
		result.height=Math.floor(scaleY1);
		result.fScaleToTargetWidth=true;
	}else{
		result.width=Math.floor(scaleX2);
		result.height=Math.floor(scaleY2);
		result.fScaleToTargetWidth=false;
	}
	result.targetleft=Math.floor((targetwidth-result.width)/2);
	result.targettop=Math.floor((targetheight-result.height)/2);
	return result;
};

jQuery(document).ready(function($){
	$(document).data('current_post_thumbnail',null);
	$('#postimagediv .inside').bind('DOMNodeInserted',function(evt){
		//evt.stopPropagation();
		try{
			var featured=$(this).find('img.attachment-post-thumbnail');
			if($(document).data('current_post_thumbnail')!=$(featured).attr('src')){
				if(featured.length>0){
					$(document).data('current_post_thumbnail',$(featured).attr('src'));
					var k=$('#wpk_metabox_container ul li.wpk_fb_icon_row').size();
					var html='<div class="wpk_fb_icon"><img src="'+$(featured).attr('src')+'" onload="wpk_buildIcon(event)"/></div>';
					html+='<input class="wpk_fb_icon_checkbox" id="wpk_fb_image_'+k+'" name="wpk_fb_image[]" type="checkbox" value="'+$(featured).attr('src')+'::'+$(featured).attr('alt')+'"/><label for="wpk_fb_image_'+k+'">'+$(featured).attr('alt');
					html+='</label>';
					if($('#wpk_metabox_container ul li#wpk_featured').length>0){
						
						$('#wpk_metabox_container ul li#wpk_featured').html(html);
					}else{
						html='<li class="wpk_fb_icon_row" id="wpk_featured">'+html+'</li>';
						$('#wpk_metabox_container ul').append(html);
					}
				}
			}
		}catch(e){
			return;
		}
	});
});
