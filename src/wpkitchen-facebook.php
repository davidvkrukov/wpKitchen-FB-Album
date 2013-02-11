<?php 
/**
 * Main plugin FB API class
 * 
 * @author David V. Krukov
 */
require_once WPK_ROOT_DIR.'../lib/base_facebook.php';

/**
 * Renamed copy of standard 'Facebook' class from FB PHP SDK
 * Changed only cookie and session keys 
 */
class WP_Kitchen_Facebook extends BaseFacebook{
	const FBSS_COOKIE_NAME='wpk_fbss';
	const FBSS_COOKIE_EXPIRE=31556926;
	protected $sharedSessionID;
	protected static $kSupportedKeys=array('state','code','access_token','user_id');
	
	public function __construct($config){
		if(!session_id()){
			session_start();
		}
		parent::__construct($config);
		if(!empty($config['sharedSession'])){
			$this->initSharedSession();
		}
	}
	
	protected function initSharedSession(){
		$cookie_name=$this->getSharedSessionCookieName();
		if(isset($_COOKIE[$cookie_name])){
			$data=$this->parseSignedRequest($_COOKIE[$cookie_name]);
			if($data&&!empty($data['domain'])&&self::isAllowedDomain($this->getHttpHost(),$data['domain'])){
				$this->sharedSessionID=$data['id'];
				return;
			}
		}
		$base_domain=$this->getBaseDomain();
		$this->sharedSessionID=md5(uniqid(mt_rand(),true));
		$cookie_value=$this->makeSignedRequest(array(
			'domain'=>$base_domain,
			'id'=>$this->sharedSessionID,
		));
		$_COOKIE[$cookie_name]=$cookie_value;
		if(!headers_sent()){
			$expire=time()+self::FBSS_COOKIE_EXPIRE;
			setcookie($cookie_name,$cookie_value,$expire,'/','.'.$base_domain);
		}else{
			self::errorLog(
				'Shared session ID cookie could not be set! You must ensure you '.
				'create the Facebook instance before headers have been sent. This '.
				'will cause authentication issues after the first request.'
			);
		}
	}
	
	protected function getSignedRequestCookieName() {
		return 'wpk_fbsr_'.$this->getAppId();
	}
	
	protected function getMetadataCookieName() {
		return 'wpk_fbm_'.$this->getAppId();
	}
	
	protected function setPersistentData($key,$value){
		if(!in_array($key,self::$kSupportedKeys)){
			self::errorLog('Unsupported key passed to setPersistentData.');
			return;
		}
		$session_var_name=$this->constructSessionVariableName($key);
		$_SESSION[$session_var_name]=$value;
	}
	
	protected function getPersistentData($key,$default=false){
		if(!in_array($key,self::$kSupportedKeys)){
			self::errorLog('Unsupported key passed to getPersistentData.');
			return $default;
		}
		$session_var_name=$this->constructSessionVariableName($key);
		return isset($_SESSION[$session_var_name])?$_SESSION[$session_var_name]:$default;
	}
	
	protected function clearPersistentData($key){
		if(!in_array($key,self::$kSupportedKeys)){
			self::errorLog('Unsupported key passed to clearPersistentData.');
			return;
		}
		$session_var_name=$this->constructSessionVariableName($key);
		unset($_SESSION[$session_var_name]);
	}
	
	protected function clearAllPersistentData(){
		foreach(self::$kSupportedKeys as $key){
			$this->clearPersistentData($key);
		}
		if($this->sharedSessionID){
			$this->deleteSharedSessionCookie();
		}
	}
	
	protected function getCode(){
		if(isset($_REQUEST['code'])){
			if($this->state!==null&&isset($_REQUEST['wpk_state'])&&$this->state===$_REQUEST['wpk_state']) {
				$this->state=null;
				$this->clearPersistentData('state');
				return $_REQUEST['code'];
			}else{
				self::errorLog('CSRF state token does not match one provided.');
				return false;
			}
		}
		return false;
	}
	
	public function getLoginUrl($params=array()){
		$this->establishCSRFTokenState();
		$currentUrl=$this->getCurrentUrl();
		$scopeParams=isset($params['scope'])?$params['scope']:null;
		if($scopeParams&&is_array($scopeParams)){
			$params['scope']=implode(',',$scopeParams);
		}
		return $this->getUrl(
			'www','dialog/oauth',
			array_merge(array(
				'client_id'=>$this->getAppId(),
				'redirect_uri'=>$currentUrl,
				'wpk_state'=>$this->state
			),$params));
	}

	protected function deleteSharedSessionCookie(){
		$cookie_name=$this->getSharedSessionCookieName();
		unset($_COOKIE[$cookie_name]);
		$base_domain=$this->getBaseDomain();
		setcookie($cookie_name,'',1,'/','.'.$base_domain);
	}
	
	protected function getSharedSessionCookieName(){
		return self::FBSS_COOKIE_NAME.'_'.$this->getAppId();
	}
	
	protected function constructSessionVariableName($key){
		$parts=array('wpk_fb',$this->getAppId(),$key);
		if($this->sharedSessionID){
			array_unshift($parts,$this->sharedSessionID);
		}
		return implode('_',$parts);
	}
}
