<?php 
/**
 * Main plugin admin class
 *
 * @author David V. Krukov
 */
if(!class_exists('WP_Kitchen_Admin')):

class WP_Kitchen_Admin{
	/**
	 * @var WP_Kitchen_Admin
	 */
	private static $__instance=null;
	
	/**
	 * Constructor
	 * Register callbacks for main admin WP actions
	 */
	protected function __construct(){
		add_action('admin_menu',array(&$this,'_registerMenu'));
		add_action('admin_init',array(&$this,'_registerSettings'));
	}
	
	/**
	 * @return WP_Kitchen_Admin
	 */
	public static function init(){
		if(self::$__instance===null){
			self::$__instance=new self();
		}
		return self::$__instance;
	}
	
	/**
	 * Add plugin settings page to WP Settings menu
	 */
	public function _registerMenu(){
		$settings_url=add_options_page('wpKitchen FB Album','wpKitchen FB Album','administrator',__FILE__,array(&$this,'_settingsPage'));
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
	 * Add script to doing AJAX calls when editor content changed
	 */
	public function _filterContentScript(){
		if(!isset($_GET['page'])||trim($_GET['page'])!='wpkitchen-fb-album/src/wpkitchen-admin.php'){
			ob_start();
			require_once WPK_ROOT_DIR.'../js/callback.js';
			$js=ob_get_contents();
			ob_clean();
			echo '<script type="text/javascript" >'.$js.'</script>';
		}
	}
	
	/**
	 * Parse post content for galleries, load images related to current post/page etc
	 */
	public function _filterContentAction(){
		if(isset($_POST['content'])&&isset($_POST['img'])){
			$content=stripcslashes($_POST['content']);
			$post_id=intval($_POST['post_id']);
			$tmp=array();
			// Get featured image
			if(has_post_thumbnail($post_id)){
				$image_id=get_post_thumbnail_id($post_id);
				$image_url=wp_get_attachment_image_src($image_id,'full');
				$tmp[]=array(
					'url'=>$image_url[0],
					'fb_id'=>'',
					'title'=>''
				);
			}
			// Get gallerie images
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
			// Get all other images presented in post content
			foreach($_POST['img'] as $img){
				$tmp[]=array(
					'url'=>$img['src'],
					'fb_id'=>'',
					'title'=>$img['caption']
				);
			}
			// Merge all images and check for previously published
			$meta=get_post_meta($post_id,'wpk_fb_album_meta',array());
			$images=array();
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
		$init['init_instance_callback']='[filterContentAjax][0]';
		$init['onchange_callback']='[filterContentAjax][0]';
		return $init;
	}
	
}

endif;