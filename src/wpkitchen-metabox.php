<?php 
/**
 * Main plugin metabox class
 *
 * @author David V. Krukov
 */

if(!class_exists('WP_Kitchen_Metabox')):

class WP_Kitchen_Metabox{
	
	/**
	 * Save post metadata callback
	 * 
	 * @param integer $postId
	 */
	public function saveMetaData($postId,$post){
		if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE) return;
		if(!wp_is_post_revision($postId)){
			if(!isset($_POST['wpk_fb_album_add'])||intval($_POST['wpk_fb_album_add'])!=1) return;
			if(!isset($_POST['wpk_fb_album'])||trim($_POST['wpk_fb_album'])=='') return;
			$album=trim($_POST['wpk_fb_album']);
			$urls=isset($_POST['wpk_fb_image'])?$_POST['wpk_fb_image']:array();
			$images=array();
			foreach($urls as $image){
				$image=explode('::',$image);
				$caption=isset($image[1])&&trim($image[1])!==''?trim($image[1]):'(Untitled)';
				$images[]=array(
					'file'=>realpath(str_replace(site_url(),$_SERVER['DOCUMENT_ROOT'],$image[0])),
					'caption'=>$caption
				);
			}
			if(sizeof($images>0)){
				$albumId=$this->getAlbumId($album);
				$response=$this->addPhotos($albumId,$images);
				$ids=array();
				foreach($response as $key=>$data){
					$image=explode('::',$urls[$key]);
					$ids[$image[0]]=json_decode($data['body'])->id;
				}
				if(sizeof($ids)>0){
					$old=get_post_meta($postId,'wpk_fb_album_meta',array());
					if(isset($old[0]['images'])&&is_array($old[0]['images'])&&sizeof($old[0]['images'])>0){
						$ids=array_merge($ids,$old[0]['images']);
					}
					$meta=array('album'=>$albumId,'images'=>$ids);
					update_post_meta($postId,'wpk_fb_album_meta',$meta);
				}
			}
		}
	}
	
	/**
	 * Create new or get existing album on FB and return album id
	 * 
	 * @param string $name
	 * @return integer
	 */
	protected function getAlbumId($name){
		global $wpk_facebook;
		if(get_option('wpk_fb_app_page',false)!==false){
			$data=explode('::',get_option('wpk_fb_app_page'));
			$user_id=$data[0];
			$access_token=isset($data[1])?$data[1]:$wpk_facebook->getAccessToken();
		}else{
			$user_id=$wpk_facebook->getUser();
			$access_token=$wpk_facebook->getAccessToken();
		}
		$response=$wpk_facebook->api('/'.$user_id.'/albums','GET',array('access_token'=>$access_token));
		if(sizeof($response['data'])>0){
			foreach($response['data'] as $album){
				if($album['name']==$name){
					return $album['id'];
				}
			}
		}
		$response=$wpk_facebook->api('/'.$user_id.'/albums','POST',array('access_token'=>$access_token,'name'=>$name));
		return $response['id'];
	}
	
	/**
	 * Build batch request for FB Graph API call and return response
	 * 
	 * @param integer $albumId
	 * @param array $images
	 * @return array
	 */
	protected function addPhotos($albumId,$images){
		global $wpk_facebook;
		$wpk_facebook->setFileUploadSupport(true);
		$batch=array();
		$params=array();
		$count=1;
		foreach($images as $key=>$photo){
			$req=array(
				'method'=>'POST',
				'relative_url'=>'/'.$albumId.'/photos',
				'attached_files'=>'file'.$count,
				'body'=>'message='.$photo['caption']
			);
			$batch[]=json_encode($req);
			$params['file'.$count]='@'.$photo['file'];
			$count++;
		}
		$params['batch']='['.implode(',',$batch).']';
		if(get_option('wpk_fb_app_page',false)!==false){
			$data=explode('::',get_option('wpk_fb_app_page'));
			$user_id=$data[0];
			$access_token=isset($data[1])?$data[1]:$wpk_facebook->getAccessToken();
		}else{
			$user_id=$wpk_facebook->getUser();
			$access_token=$wpk_facebook->getAccessToken();
		}
		$params['access_token']=$access_token;
		$response=$wpk_facebook->api('/'.$user_id,'post',$params);
		return $response;
	}
}

endif;