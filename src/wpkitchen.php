<?php 
/**
 * Main plugin class
 * 
 * @author David V. Krukov
 */

if(!class_exists('WP_Kitchen_Metabox')):

class WP_Kitchen{
	/**
	 * @var WP_Kitchen
	 */
	private static $__instance=null;
	
	/**
	 * The main loader
	 */
	protected function __construct(){
		if(is_admin()){
			load_plugin_textdomain('wpkitchen-fb-album',false,WPK_ROOT_DIR.'lang/');
			add_action('init',array(&$this,'_startSession'),0);
			add_action('wp_logout',array(&$this,'_endSession'));
			add_action('admin_init',array(&$this,'_loadLibraries'),1);
			add_action('admin_init',array(&$this,'_registerSettings'),2);
			add_action('admin_menu',array(&$this,'_registerMenu'));
			add_action('admin_print_scripts',array(&$this,'_loadScripts'));
		}
	}
	
	/**
	 * @return WP_Kitchen
	 */
	public static function init(){
		if(self::$__instance===null){
			self::$__instance=new self();
		}
		return self::$__instance;
	}
	
	/**
	 * Session start
	 */
	public function _startSession(){
		if(!headers_sent()){
			if(!session_id()){
				session_start();
			}
		}
	}

	/**
	 * Session end
	 */
	public function _endSession(){
		if(session_id()){
			session_destroy();
		}
	}
	
	/**
	 * Checking for necessity of installation/update 
	 * and loading main libraries
	 */
	public function _loadLibraries(){
		global $wpk_facebook;
		$appId=get_option('wpk_fb_app_id',null);
		$appSecret=get_option('wpk_fb_app_secret',null);
		if(!is_null($appId)&&!is_null($appSecret)){
			require WPK_ROOT_DIR.'wpkitchen-facebook.php';
			$wpk_facebook=new WP_Kitchen_Facebook(array(
				'appId'=>$appId,
				'secret'=>$appSecret,
				'cookie'=>false,
				'fileUpload'=>true,
				'scope'=>'user_photos,email,publish_stream,user_birthday,user_location,user_work_history,user_about_me,user_hometown'
			));
			if($wpk_facebook->getUser()==0){
				echo '<script type="text/javascript"> top.location.href="'.$wpk_facebook->getLoginUrl().'"; </script>';
			}else{
				$user=$wpk_facebook->api('/me');
			}
			require WPK_ROOT_DIR.'wpkitchen-metabox.php';
			$metabox=new WP_Kitchen_Metabox();
			add_action('add_meta_boxes',array(&$this,'_loadMetabox'));
			add_action('save_post',array(&$metabox,'saveMetaData'));
		}
	}
	
	/**
	 * Register plugin settings group and init callbacks for editor events
	 */
	public function _registerSettings(){
		add_action('admin_print_scripts',array(&$this,'_filterContentScript'));
		add_filter('tiny_mce_before_init',array(&$this,'_filterContent'));
		add_action('wp_ajax_content_filter_action',array(&$this,'_filterContentAction'));
		add_action('wp_ajax_delete_fb_item_action',array(&$this,'_deleteFBItemAction'));
		register_setting('wpkfb-settings-group','wpk_fb_version');
		register_setting('wpkfb-settings-group','wpk_fb_app_id');
		register_setting('wpkfb-settings-group','wpk_fb_app_secret');
		register_setting('wpkfb-settings-group','wpk_fb_post_by_default');
		register_setting('wpkfb-settings-group','wpk_fb_album_template');
		register_setting('wpkfb-settings-group','wpk_fb_use_type');
	}
	
	/**
	 * Load JS and CSS
	 */
	public function _loadScripts(){
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_style('wp-jquery-ui-dialog');
		wp_register_style('wpkitchen-stylesheet',plugins_url('css/wpkitchen-fb-album.css',dirname(__FILE__)));
		wp_enqueue_style('wpkitchen-stylesheet');
	}
	
	/**
	 * Add plugin settings page to WP Settings menu
	 */
	public function _registerMenu(){
		$settings_url=add_options_page('wpKitchen FB Album','wpKitchen FB Album','administrator',__FILE__,array(&$this,'_settingsPage'));
		add_action('load-'.$settings_url,array(&$this,'_saveSettings'));
	}
	
	public function _saveSettings(){
		// TODO
	}
	
	/**
	 * Get view for settings page
	 */
	public function _settingsPage(){
		ob_start();
		require WPK_ROOT_DIR.'../tpl/settings.php';
		$html=ob_get_contents();
		ob_clean();
		echo $html;
	}
	
	/**
	 * Loading of metaboxes
	 */
	public function _loadMetabox(){
		$appId=get_option('wpk_fb_app_id',null);
		$appSecret=get_option('wpk_fb_app_secret',null);
		if(!is_null($appId)&&!is_null($appSecret)){
			global $wpk_facebook;
			require WPK_ROOT_DIR.'../lib/facebook.php';
			$wpk_facebook=new Facebook(array(
				'appId'=>$appId,
				'secret'=>$appSecret,
				'cookie'=>false,
				'scope'=>'user_photos,email,publish_stream,user_birthday,user_location,user_work_history,user_about_me,user_hometown'
			));
			$options=get_option('wpk_fb_use_type',array());
			foreach(get_post_types(array('public'=>true,'_builtin'=>true),'objects') as $post_type){
				if(isset($options[$post_type->name])){
					add_meta_box(
						'wpkitchen-fb-album-metabox',
						__('Images to Facebook (wpKitchen Facebook Album)','wpkitchen-fb-album'),
						array(&$this,'_getMetabox'),
						$post_type->name
					);
				}
			}
		}
	}
	
	/**
	 * Get view for metabox
	 */
	public function _getMetabox(){
		global $post;
		$album_name_format=get_option('wpk_fb_album_template');
		$album_name_preformatted=str_replace('%post-title%',get_the_title($post->ID),$album_name_format);
		$album_name_preformatted=str_replace('%year%',date('Y'),$album_name_preformatted);
		$album_name_preformatted=str_replace('%month%',date('n'),$album_name_preformatted);
		$album_name_preformatted=str_replace('%month-name%',date('F'),$album_name_preformatted);
		$album_name_preformatted=str_replace('%week%',date('W'),$album_name_preformatted);
		$album_name_preformatted=str_replace('%day%',date('jS'),$album_name_preformatted);
		$album_name_preformatted=str_replace('%day-name%',date('l'),$album_name_preformatted);
		require WPK_ROOT_DIR.'../tpl/metabox.php';
		$html=ob_get_contents();
		ob_clean();
		echo $html;
	}

	/**
	 * Add script to doing AJAX calls when editor content changed
	 */
	public function _filterContentScript(){
		ob_start();
		require_once WPK_ROOT_DIR.'../js/callback.js';
		$js=ob_get_contents();
		ob_clean();
		echo '<script type="text/javascript" >'.$js.'</script>';
	}
	
	/**
	 * Parse post content for galleries, load images related to current post/page etc
	 */
	public function _filterContentAction(){
		if(isset($_POST['content'])&&isset($_POST['img'])){
			$content=stripcslashes($_POST['content']);
			$post_id=intval($_POST['post_id']);
			$tmp=array();
			if(has_post_thumbnail($post_id)){
				$image_id=get_post_thumbnail_id($post_id);
				$image_url=wp_get_attachment_image_src($image_id,'full');
				$tmp[]=array(
					'url'=>$image_url[0],
					'fb_id'=>'',
					'title'=>''
				);
			}
			preg_match('/gallery ids="([^\s]+)""/i',htmlspecialchars_decode($content),$gallery);
			if(isset($gallery[1])){
				$_tmp=explode(',',$gallery[1]);
				foreach($_tmp as $id){
					$url=wp_get_attachment_url($id);
					$tmp[]=array(
						'url'=>$url,
						'fb_id'=>'',
						'title'=>''
					);
				}
			}
			$images=array();
			foreach($_POST['img'] as $img){
				$tmp[]=array(
					'url'=>$img['src'],
					'fb_id'=>'',
					'title'=>$img['caption']
				);
			}
			$meta=get_post_meta($post_id,'wpk_fb_album_meta',array());
			foreach($tmp as $image){
				$wh=getimagesize($image['url']);
				if($wh[0]>1&&$wh[1]>1){
					$images[]=array(
						'url'=>$image['url'],
						'fb_id'=>(isset($meta[0]['images'][$image['url']])?$meta[0]['images'][$image['url']]:''),
						'title'=>$image['title']
					);
				}
			}
			header('Content-type: application/json');
			echo json_encode($images);
		}
		die();
	}

	/**
	 * Add callbacks to tinyMCE
	 * ('filterContentAjax' function loaded by self::_filterContentScript method)
	 *
	 * @param array $init
	 * @return array
	 */
	public function _filterContent($init){
		$init['init_instance_callback']='[wpk_filterContentAjax][0]';
		// $init['onchange_callback']='[wpk_filterContentAjax][0]';
		$init['handle_node_change_callback']='[wpk_checkForImage][0]'; 
		return $init;
	}
	
}

endif;
