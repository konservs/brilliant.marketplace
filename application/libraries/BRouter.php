<?php
/**
 * Sets of functions and classes to work with routes: Get the
 * URL and convert it to MVC, load component
 * 
 * Get the MVC and convert into URL
 * 
 * @author Andrii Biriev, <a@konservs.com>
 * 
 * @copyright © Andrii Biriev, <a@konservs.com>
 */
namespace Application;
use Brilliant\cms\BLang;
use Brilliant\cms\BRouterBase;
use Brilliant\log\BLog;
use Brilliant\users\BUsers;

class BRouter extends BRouterBase{
	use \Brilliant\BSingleton;
	protected static $starttime=0;
	protected static $instance=NULL;
	protected $components=array('content','users');
	protected $router=array();
	protected $positions=array();
	protected $rules=array();
	protected $soft_rules=array();
	protected $maincom=NULL;
	protected $langcode='';
	protected $deflang='en';
	public $templatename='default';
	public $frontendtemplate='default';
	/**
	 * Get logged user object. Helper function.
	 * 
	 * @return \BUser|NULL Logged user
	 */
	public function getLoggedUser(){
		$busers=BUsers::getInstance();
		return $busers->getLoggedUser();
		}
	/**
	 * For example, check 'mycompany-5' for 'mycompany-' and return 5.
	 *
	 * $str  - entire string
	 * $pref - preffix,
	 */
	public function checkIntSuffix($str,$pref){
		$preflen=strlen($pref);
		if(substr($str,0,$preflen)!=$pref){
			return 0;
			}
		$ints=substr($str,$preflen);
		return (int)$ints;
		}
	/**
	 * Add some fixed rules - languages switch, etc.
	 */
	public function addFixedRules(){
		$user = $this->getLoggedUser();
		if(!empty($user)){
			$this->rules[]=(object)array(
				'com' => 'users',
				'position' => 'userpanel',
				'segments' => array('view'=>'userpanel','uid'=>$user->id),
				);
			}else{
			$this->rules[]=(object)array(
				'com' => 'users',
				'position' => 'userpanel',
				'segments' => array('view'=>'userpanel'),
				);
			}
		}
	/**
	 * Generate url for Users component
	 *
	 * @param $lang
	 * @param $segments
	 * @return string
	 */
	public function generateUrlUsers($lang, $segments){
		$URL_users='users/';
		$view=isset($segments['view'])?$segments['view']:'';
		if(($view=='logout')||($view=='login')||($view=='register')||($view=='register_company')) {
			return $URL_users.$view;
			}
		//Control panel
		if($view=='dashboard'){
			return 'members/';
			}
		return '';
		}
	/**
	 *
	 */
	public function generateUrlCountry($lang, $segments){
		$view=isset($segments['view'])?$segments['view']:'';
		//Operations groups
		if($view=='country'){
			return 'xxx';
			}
		return false;
		}
	/**
	 * Generate URL by component, language and segments
	 * in case of sucessfull parse return URL, else return false;
	 *
	 * @param string $component
	 * @param string $lang
	 * @param array $segments
	 */
	public function generateURL($component,$segments,$options=array()){
		$opt_protocol=isset($options['protocol'])?$options['protocol']:'//';
		$opt_hostname=isset($options['usehostname'])?$options['usehostname']:false;
		//forming preffix
		$pref='';
		if($opt_hostname){
			$pref=$opt_protocol.BHOSTNAME;
			}
		$pref.='/';
		$lang=isset($segments['lang'])?$segments['lang']:\Brilliant\CMS\BLang::$langcode;
		if(($lang!=='en')&&(!empty($lang))){
			$pref.=$lang.'/';
			}
		switch($component){
			case 'users':
				return $pref.$this->generateUrlUsers($lang,$segments);
			case 'country':
				return $pref.$this->generateUrlCountry($lang,$segments);
			case 'mainpage':
				return $pref.'';
			}
		}
	/**
	 *
	 */
	public function generateURLmain($lang='',$useparams=true){
		if(empty($lang)){
			$lang=BLang::$langcode;
			}
		$url=$this->generateURL($this->maincom->com,$lang,$this->maincom->segments);
		if($useparams){
			bimport('http.request');
			$url.=BRequest::getGetString();
			}
		return $url;
		}
	/**
	 * Parse /users/ branch.
	 * 
	 * Language - $this->langcode
	 */
	public function parseUrlUsers($f_path){
		BLog::addtolog('[Router]: We are in users branch now!');
		//Unset the latest empty "/" in url.
		if((count($f_path))&&(empty($f_path[count($f_path)-1]))){
			BLog::addtolog('[Router]: parseUrlUsers() removing latest "/" character.');
			unset($f_path[count($f_path)-1]);
			}
		//
		if((count($f_path)==1)&&(($f_path[0]=='login')||($f_path[0]=='logout')||($f_path[0]=='register')||($f_path[0]=='register_company'))){
			$this->maincom=(object)array(
				'com'=>'users',
				'position'=>'content',
				'segments'=>array('view'=>$f_path[0]),
				);
			$this->rules[]=$this->maincom;
			$this->addFixedRules();
			return true;
			}
		BLog::addtolog('[Router]: parseUrlUsers() no rules! $f_path='.var_export($f_path,true),LL_ERROR);
		return false;
		}
	/**
	 * Parse URL and returns segments, if all is ok.
	 */
	public function parseurl($URL,$host){
		$u=parse_url($URL);
		$u_path=$u['path'];
		$u_query=$u['query'];
		$u_fragment=$u['fragment'];
		//
		parse_str($u_query,$f_query);
		$f_path=explode('/',$u_path);
		array_shift($f_path);
		//Get subdomain type
		$exploded_host=explode('.',$host);
		if($exploded_host[0]=='www'){
			$this->ctype=CTYPE_REDIRECT301;
			$this->redirectURL='//'.BHOSTNAME.$URL;
			return;
			}
		//
		if($exploded_host[0]=='admin'){
			BLang::init('ru','admin');// adminlagugages
			return $this->parse_adminurl($f_path);
			}
		//Detect language
		if(($f_path[0]==='ru')||($f_path[0]==='ua')){
			$this->langcode=$f_path[0];
			array_shift($f_path);
			}else{
			$this->langcode='en';
			}
		$lang=$this->langcode;
		BLang::init($this->langcode);
		//
		if($f_path[0]=='switchmobile'){
			$this->maincom=(object)array(
				'com'=>'switchmobileversion',
				'position'=>'content',
				'segments'=>array('view'=>'switch')
				);
			$this->rules[]=$this->maincom;
			return true;
			}
		elseif($f_path[0]=='users'){
			array_shift($f_path);
			return $this->parseUrlUsers($f_path);
			}
		if($f_path[0]=='country'){
			array_shift($f_path);
			return $this->parseUrlCountry($f_path);
			}

		elseif(count($f_path)==0||(count($f_path)==1&&$f_path[0]=='')){
			$this->maincom=(object)array(
				'com'=>'mainpage',
				'position'=>'content',
				'segments'=>array('view'=>'mainpage')
				);
			$this->addFixedRules();
			$this->rules[]=$this->maincom;
			$this->softmodulesget('mainpage:mainpage');
			return true;
			}
		return false;
		}//end of ParseURL
	}
