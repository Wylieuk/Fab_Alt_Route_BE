<?php
defined("isInSideApplication")?null:die('no access');
class form{
	
	function __construct(){
		global $smarty;
		$this->smarty = $smarty;
		//$this->smarty->assign('name',$formName);
	}
	
	function defaultParams(){
		$this->params 						= new stdClass;
		$this->params->type				=	$this->type;
		$this->params->id 					=	'';
		//$this->params->class				=	'';
		//$this->params->label				= 	'';

	}
	
	static function get_fields($table, $fields_to_skip=false){
		$db=new db;
		$query = 'SHOW FULL COLUMNS FROM `' . $db->clean($table) . '`';
		$db->query($query);
		$form_fields_array = $db->fetch_array();
		
		foreach ($form_fields_array as $field){
			$field_extras = json_decode($field['Comment']);
			if($fields_to_skip){
				if (in_array($field['Field'], $fields_to_skip)){continue;}
			}
			$fields [$field['Field']]['value'] = '';
			if (is_object($field_extras)){
				foreach ($field_extras as $key => $field_extra){
					$fields [$field['Field']][$key] = $field_extra;
				}
			}else{
				//default type
				$fields [$field['Field']]['type'] = 'input';	
			}
		}
//debug($fields);
		return $fields;
	}
	
	function addField($type, $params){
		if (!isset($params['parent_class'])){$params['parent_class'] = '';}
		$this->type	=	$type;
		$functionName = 'add'.ucfirst($type);
		if (!method_exists($this, $functionName)){
			trigger_error('Error adding field of type "'.$type.'" Function form::'.$functionName.' does not exist' );
			return;
		}
		
		$html = $this->$functionName($params);
		return $html;		
	}
	
	function setParams($params){
		$this->defaultParams();
		foreach ($params as $param => $value){
			$this->params->$param = $value;
		}
		
		if (isset($this->params->class)){
			if(strpos($this->params->class , 'mandatory') !== false and strpos($this->params->parent_class, 'mandatory') === false){
					$this->params->parent_class = $this->params->parent_class.' mandatory';
			}
		}
		//debug($this->params);
	}
	
	function addTextfield($params){
		$this->setParams($params);
		$this->smarty->assign('template_vars',$this->params);
		$this->html = $this->smarty->fetch('form.field.tpl');
		return $this->html;
	}
	
	function addPassword($params){
		$this->setParams($params);
		$this->smarty->assign('template_vars',$this->params);
		$this->html = $this->smarty->fetch('form.field.tpl');
		return $this->html;
	}
	
	function addTextarea($params){
		$this->setParams($params);
		$this->smarty->assign('template_vars',$this->params);
		$this->html = $this->smarty->fetch('form.field.tpl');
		return $this->html;
	}
	
	
	function addButton($params){
		$this->setParams($params);
		$this->smarty->assign('template_vars',$this->params);
		$this->html = $this->smarty->fetch('form.field.tpl');
		return $this->html;
	}

	function addDropdown($params){
		$this->setParams($params);
		
		$this->smarty->assign('dropdown_vars',$this->params->values);
		unset($this->params->values);
		if(isset($this->params->value)){
			$this->smarty->assign('preset_value',$this->params->value);
			unset($this->params->value);
		}
		
		$this->smarty->assign('template_vars',$this->params);
		$this->html = $this->smarty->fetch('form.field.tpl');
		
		return $this->html;
	}
	
	function addDropdownMultiple($params){
		$this->setParams($params);
		$this->params->name	=	$this->params->name.'[]';
		
		if (!is_array($this->params->value)){
			$this->params->value = array($this->params->value);
		}
		
		$this->smarty->assign('dropdown_preloaded_vars',$this->params->value);
		unset($this->params->value);

		$this->smarty->assign('dropdown_vars',$this->params->values);
		unset($this->params->values);
		
		$this->smarty->assign('template_vars',$this->params);
		$this->html = $this->smarty->fetch('form.field.tpl');
		
		
		return $this->html;
	}
	
	function addRadio($params){
		$this->setParams($params);
		$this->smarty->assign('radio_vars',$this->params->values);
		unset($this->params->values);
		$this->smarty->assign('template_vars',$this->params);
		$this->html = $this->smarty->fetch('form.field.tpl');
		return $this->html;
	}
	
	function addCheckbox($params){
		$this->setParams($params);
		$this->smarty->assign('template_vars',$this->params);
		$this->html = $this->smarty->fetch('form.field.tpl');
		return $this->html;
	}
	
	function addFile($params){
		$this->setParams($params);
		$this->smarty->assign('template_vars',$this->params);
		$this->html = $this->smarty->fetch('form.field.tpl');
		return $this->html;
	}
	
		function addImage($params){
		$this->setParams($params);
		$this->smarty->assign('template_vars',$this->params);
		$this->html = $this->smarty->fetch('form.field.tpl');
		return $this->html;
	}
	
	function addSection($params){
		$this->setParams($params);
		$this->smarty->assign('template_vars',$this->params);
		$this->html = $this->smarty->fetch('form.field.tpl');
		return $this->html;
	}
	
	function addHidden($params){
		$this->setParams($params);
		$this->smarty->assign('template_vars',$this->params);
		$this->html = $this->smarty->fetch('form.field.tpl');
		return $this->html;
	}
	
	function setDropDownOptions($array){
	/*	 [0] => Array
    (
        [value] => select text to display
    )
	or
		[0] => Array
    (
        [key] => 1
		[value] => select text to display
    )*/
		foreach ($array as $row){
			if (isset($row['key'])){
				$temp['text'] 		= $row['value'];
				$temp['value'] 	= $row['key'];
				$dropdownOptions[] = $temp;
			}else{
				$temp['text'] 		= $row['value'];
				$temp['value'] 	= $row['value'];
				$dropdownOptions[] = $temp;
			}
			unset($temp);
		}
		return $dropdownOptions;
	}
	
	function getSubmittedValue($field_name){
		if (isset($_GET[$field_name])){return filter_input(INPUT_GET, $field_name, FILTER_SANITIZE_STRING);}
		if (isset($_POST[$field_name])){return filter_input(INPUT_POST, $field_name, FILTER_SANITIZE_STRING);}
		return '';
	}
	
	function getSubmittedText($submittedValue,  $AllDropDownValues){
		if (!is_array($AllDropDownValues)){return $submittedValue;}
		foreach ($AllDropDownValues as $dropdownItem){
			if ($submittedValue == $dropdownItem['value']){return $dropdownItem['text'];}
		}
		return $submittedValue;
	}
	
}

?>