<?php
defined("isInSideApplication")?null:die('no access');

#[AllowDynamicProperties]
class page {
		
	function __construct($page_name){

		global $config;
		global $lang;
        global $user;

        $page = &$this;

		$this->runtimeInfo           = true;
		$this->page_name             = $page_name;
		$this->html                  = '';
		$this->css                   = '';
		$this->script                = '';
		$this->user                  = $user;
		$this->template_vars['user'] = $user;
        $this->template_vars['page'] = $this;

        if ($config['enableRootSmarty']){
            $this->smarty            = new_smarty();
        }
        
		$data 						 = data_submit($_REQUEST, $_FILES, $this);
		$this->data                  = $data['clean'];
		$_SERVER['FRONTEND_PAGE']    = base64_decode($this->data['r_page'] ?? ''); 
		$_SERVER['FRONTEND_ROOT']	 = current(explode('#', $_SERVER['FRONTEND_PAGE'] ?? []) ?? []) ?? null;
		$this->dirtyData             = $data['dirty'];
		$this->form_data             = &$this->data;
		$this->session_id            = session_id();
		$this->apiKey                = $this->getDateBasedApiKey();
		$this->siteAddress           = $config['siteaddress'];
		$this->contentSecurityPolicy = array();

		if (!headers_sent()) {
			$this->sendSecurityPolicy();
		}

        if(file_exists('pages/core_pages/page.'.$_REQUEST['page'].'.php'))
        {
            require('pages/core_pages/page.'.$_REQUEST['page'].'.php');
        } 
        else if(file_exists('pages/page.'.$_REQUEST['page'].'.php'))
        {
            require('pages/page.'.$_REQUEST['page'].'.php');
        }

        
		
	}

	/**
	 * Added by Kai 2021-07-09: Allows you to either get the input data or a default value if the input data has not been defined...
	 */
	public function getInput($key, $default = null)
	{
		return $this->data[$key] ?? $default;
	}
	
	function getDateBasedApiKey(){
		global $config;
		return hash('sha1', $config['siteaddress'] . date('Hmdy'));	
	}
	
	static function requiresAccess($pageStr){
		global $config;
		if (!isset($config['requires_login'][$pageStr])){
			return true;
		}
		else{
			return $config['requires_login'][$pageStr];
		}
	}
	
	function setTitle($title){
		$this->title = $title;
	}
	
	## usage $page->setAction('load_timetable_data', some array maybe $page->data);
	function setAction($action, $data=array()){

		$this->action = $action;	
		$action = new action($this);
        $output = $action->create($this->action, $data, $this);
		//$this->data = array_merge((array)$this->data, [$this->action => $output]);
        //debug($action);
        return $output;
	}
	
	//components added via ajax
	function  addRefreshableComponent ($component_name){
		$component = new component($component_name);
		$component->loadFRAMETEMPLATE($this);
		$this->addHtml ( $component->outputHtml() );
	}
	
	//components added as inline Html
	function addHardComponent ($component_name, array $data=[]){
		global $user;
		$component = new component($component_name, $data);
		$this->{$component_name} = $component->loadComponent($this, false);
		$this->addHtml ( $component->outputHtml() );
		//unset($component);
	}
	
	//components added with no html output
	function addSoftComponent ($component_name){
		$component = new component($component_name);
		$this->{$component_name} = $component->loadComponent($this, true);
	}
	
	function suppressRuntimeInfo(){
		$this->runtimeInfo  = false;
	}
	
	
	function addJavascript($javascript){
        global $config;
		$this->addHtml ( '<script>  nonce="'.$config['CspNonce'].'"' );
		$this->addHtml ( $javascript );
		$this->addHtml ( '</script>' );
		return '<script>'.$javascript.'</script>';
	}
	
	function loadTemplate($data){
		$this->smarty->assign('template_vars', $data);
		$this->smarty->assign('user', $this->user);
		return $this->smarty->fetch($data['file'].'.tpl');
	}
	
	function addPageHeader(){
			$this->smarty->assign('user', $this->user);
			$this->smarty->assign('template_vars', $this->template_vars);
			$this->addHtml( $this->smarty->fetch('page_header.tpl'));
	}
	
	function addHtml($html){
		$this->html .= $html.PHP_EOL;		
	}
	
	function addCssFile( $css_file, $return=false ){
        global $config;
		//used to display CSS file that are not in the header
		if ($return){return '<link  nonce="'.$config['CspNonce'].'" href="'.$css_file.'" rel="stylesheet" type="text/css">'.PHP_EOL;}
		$this->css .= '<link  nonce="'.$config['CspNonce'].'" href="'.$css_file.'" rel="stylesheet" type="text/css">'.PHP_EOL;
	}
	
	function addDynamicCssFile( $css_file, $return=false ){
		global $config;
		//used to display CSS file that are not in the header
		if ($return){return '<link href="'.$config['siteaddress'].'/templates/css/CSSloader.php?CSSfile='.$css_file.'" rel="stylesheet" type="text/css">'.PHP_EOL;}
		$this->css .= '<link href="'.$config['siteaddress'].'/templates/css/CSSloader.php?CSSfile='.$css_file.'" rel="stylesheet" type="text/css">'.PHP_EOL;	
	}
	
	function addDynamicJsFile( $js_file, $return=false ){
		//used to display CSS file that are not in the header
		if ($return){return '<script src="libs/js/JSloader.php?JSfile='. $js_file.'"></script>'.PHP_EOL;}
		$this->css .= '<link href="libs/js/JSloader.php?JSfile='. $js_file.'"></script>'.PHP_EOL;	
	}
	
	function addScriptFile( $script_file, $return=false, $order='' ){
		global $config;
		//used to display JS file that are not in the header
		if ($return){return '<script  nonce="'.$config['CspNonce'].'" '.$order.' src="'.$script_file.'"></script>'.PHP_EOL;}
		$this->script .= '<script  nonce="'.$config['CspNonce'].'" '.$order.' src="'.$script_file.'"></script>'.PHP_EOL;	
	}
	
	function addHtmlHeader(){
		global $config;
        //$this->addScriptFile('libs/js/session.js.php?session_id=' . $this->session_id . '&apiKey=' . $this->apiKey . '&siteAddress=' . urlencode($this->siteAddress));
        $this->smarty = new_smarty();
		$this->smarty->assign('template_vars', $this);
        $this->smarty->assign('config', $config);
		$this->addHtml ( $this->smarty->fetch('html_header.tpl') );
	}
	
	function addHtmlfooter(){
		$this->addHtml( $this->smarty->fetch('html_footer.tpl'));
	}
	
	function addPagefooter(){
		$this->addHtml( $this->smarty->fetch('page_footer.tpl'));
	}

	function sendSecurityPolicy(){
		$policies = implode(' ', $this->contentSecurityPolicy);
		include_once('config/config.headers.php');
	}
		
	function outputHtml(){

		if (PHP_SAPI === 'cli') {
			$this->html = strip_tags($this->html);
		}
		$this->html = preg_replace('/^\h*\v+/m', '', $this->html);
		return 	$this->html;
	}

}
	
	
		
	
?>