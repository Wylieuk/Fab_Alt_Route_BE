<?php
defined("isInSideApplication")?null:die('no access');
#[AllowDynamicProperties]
class component {
	
	function __construct($component, $data=[]){
		global $page;
		global $lang;
		global $config;
        $this->data = $data;
		$this->cacheTtl = false;
		$this->terminatedEarly = false;
		$this->result = true;
		$this->html = '';
		$this->name 					= 		$component;
		$this->results 				= 		true;
		$this->type					=		'';
		if (isset($_REQUEST['action'])){
			$this->action 	= $_REQUEST['action'];
		}
		
		//$this ->title_image 			=   	$config['image_folder'].$this->name.'.png';
		$this->smarty 					= new_smarty();
		
	}
	
	function loadComponent(&$page, $softComponent = false){
		global $config;
		//global $page;
		global $user;
		
		$smarty = $this->smarty;
		unset($this->smarty);
		
		//require('config/config.components.php');
		//$this->pagination_controls	= (isset($config['components'][$this->name]['pagination_controls']) 		? 	$config['components'][$this->name]['pagination_controls']	: false );
		$this ->component_name = 'com.'.$this->name;
		
		if ($softComponent){
			$this->terminatedEarly = include('components/'.$this ->component_name.'.php');	
			return $this;
		}
		
		
		$cache = new cache($this ->component_name);	
		$this->html = $cache->fetch();
		if (!$this->html)
			{//start non-cache content
				if (!isset($page->submitted_data->results)){$this->results = false;}
				//load componentfile if exists
				if (file_exists('components/'.$this ->component_name.'.php')){
					$this->terminatedEarly = include('components/'.$this ->component_name.'.php');
				}		
				//load js if exists
				if (file_exists('libs/js/'.$this ->component_name.'.js')){
					$this->html .= $page->addScriptFile( 'libs/js/'.$this ->component_name.'.js?cachebuster='.time(), true );
				}
				//load css if exists
				if (file_exists('templates/css/'.$this ->component_name.'.css')){
					$this->html .= $page->addCssFile( 'templates/css/'.$this ->component_name.'.css?cachebuster='.time(), true );
				}
				$smarty->assign('user',$user);	
				$smarty->assign('template_vars', cast_object($this));
				if(!isset($_REQUEST['ajax'])){
					$this->html .= '<div class="component '.$this->name.' '.$this->type.'">';
				}
				//load template if exists
				if (file_exists('templates/'.$this->component_name.'.tpl')){	
					$this->html .= $smarty->fetch($this->component_name.'.tpl');
				}
				//load default.tpl if exists
				else if (isset($this->type) and file_exists('templates/'.$this->type.'_default.tpl')){
					$this->html .= $smarty->fetch($this->type.'_default.tpl');
				}else{
					//complain cant find template
					$this->html .= 'no component template found [templates/'.$this->type.'_default.tpl]';
					trigger_error('no component template found [templates/'.$this->type.'_default.tpl]');
				}
				if(!isset($_REQUEST['ajax'])){	
					$this->html .= '</div><!--end component '.$this->name.'-->';
				}
	
				$cache->save($this->html, $this->cacheTtl);
		}//end non-cached content
	
		//debug ($this);
		return $this->result;
	}
	
	function loadFrameTemplate($page){
		global $user;
		require('config/config.components.php');
		//$this->edit_controls 	= (isset($config['components'][$this->name]['edit_controls']) ? 	$config['components'][$this->name]['edit_controls'] : false );

		$template_vars['component'] =	$this;
		$this->smarty->assign('user',$user);	
		$this->smarty->assign('template_vars',$template_vars);
		$this->html = $this->smarty->fetch('com.frame.tpl');
	}
	
	function loadTemplate($page){
		if (file_exists('templates/com.'.$this->page_name.'.'.$this->name.'.tpl')){
			$this ->template_name = 'templates/com.'.$this->page_name.'.'.$this->name.'.tpl';
		}
		elseif(file_exists('templates/com.'.$this->name.'.tpl')){
			$this ->template_name = 'templates/com.'.$this->name.'.tpl';	
		}
		else{
			$this ->template_name = 'templates/com.generic_table.tpl';
		}
		//$smarty = new_smarty();
		$template_vars['component'] 		= $this;
		//debug($template_vars);
		$this->smarty->assign('template_vars',$template_vars);		
		$this->html = $this->smarty->fetch($this ->template_name);
	}
	/*
	function castObject($arr) {
    	if(is_array($arr)) $arr = (object) $arr;
    	if(is_object($arr)) {
        	$new = new stdClass();
        	foreach($arr as $key => $val) {
            	$new->$key = $cast_object($val);
        	}
    	}
    	else $new = $arr;
    return $new;       
	}
	*/
	function outputHtml(){
		return $this->html;
	}
	
	
}

?>