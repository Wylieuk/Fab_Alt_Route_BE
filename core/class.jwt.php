<?php
defined("isInSideApplication")?null:die('no access');
#[AllowDynamicProperties]
class jwt{

	private $payload;
	private $header;
	private $hash_key;
	private $header_json;
	private $payload_json;
	private $passed_jwt_array;
	private $passed_hash_key;
	private $header_json_encoded;
	private $payload_json_encoded;
	private $created_jw;
	private $client_storage_type;
	private $passed_jwt;
	
	
	
	public function __construct($secret=false){
		$this->header['alg'] = 'HS256';
		$this->header['typ'] = 'JWT';
		$this->set_client_storage_type('COOKIE');//default setting.
		if ($secret){
			$this->set_password($secret);
		}
		else{
			die('<div class="error">Cannot create <b>class jwt</b> without key</div>');
		}
	}
	
	
	public function set_payload ($label, $value){
		$this->payload[$label] = $value;	
	}
	
	private function set_password($secret){
		$this->hash_key = $secret;
	}
	
	
	public function create_token(){
		$this->header_json            = json_encode($this->header, JSON_PRETTY_PRINT);
		$this->payload_json           = json_encode($this->payload, JSON_PRETTY_PRINT);
		$this->header_json_encoded    = base64_encode($this->header_json);
		$this->payload_json_encoded   = base64_encode($this->payload_json);
		$this->created_jwt            = ($this->header_json_encoded.'.'.$this->payload_json_encoded.'.'.base64_encode($this->hash($this->hash_key)));
		if ($this->client_storage_type =='COOKIE'){$this->set_cookie();}
		return $this->created_jwt;
    }
    
    public function  getToken(){
        return ($this->header_json_encoded . '.' . $this->payload_json_encoded . '.' . base64_encode($this->hash($this->hash_key)));
    }
	
	public function read_cookie_data(){
		return $this->header_json.$this->payload_json;
	}
	
	
	private function hash($hash_key){
		if ($this->hash_key == $hash_key){
			return (hash_hmac('sha256' ,$this->header_json_encoded.'.'.$this->payload_json_encoded,$hash_key, true));
		}else{
			return false;
		}
	}
	
	
	public function verify(){
		if ($this->client_storage_type == 'REQUEST' or $this->client_storage_type == 'COOKIE'){
			if (!isset($_COOKIE['JWT'])){return false;}
			$this->passed_jwt = $_COOKIE['JWT'];
		}

		$this->read($this->passed_jwt);

        //debug($this->passed_hash_key == base64_encode($this->hash($this->hash_key)));exit;

		if($this->passed_hash_key == base64_encode($this->hash($this->hash_key))){
			return $this->passed_jwt;
		}
		return false;		
	}
	
	public function read($token){
		$this->passed_jwt_array     = explode('.',$token);
		$this->header_json_encoded  = $this->passed_jwt_array[0];
		$this->payload_json_encoded = $this->passed_jwt_array[1];
		$this->passed_hash_key      = $this->passed_jwt_array[2];

		return json_decode(base64_decode($this->passed_jwt_array[1]));
	}

	static function _read($token){
		global $config;
		$_jwt = new JWT($config['JSON_WEB_TOKEN_KEY']);
		return $_jwt->read($token);
	}
	
	public function set_client_storage_type($type='COOKIE'){
		$this->client_storage_type = $type;
	}
	
	
	public function set_cookie(){
		global $config;
		if(session_id() == '') {
    		session_start();
		}
		setcookie('JWT', $this->created_jwt, [
            'expires' => 0,
            'path' => dirname($_SERVER['PHP_SELF']) . '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => $config['sameSiteCookie']
        ]);
        //, 0, dirname($_SERVER['PHP_SELF']).'/', '', false, true);
	}
	
	public function delete_token(){
		if ($this->client_storage_type=='COOKIE'){
			$this->unset_cookie();
		}
    }
	
	public function unset_cookie(){
		unset($_COOKIE['JWT']);
        //setcookie('JWT', '', 1);
        setcookie('JWT', '', time() - 3600, dirname($_SERVER['PHP_SELF']) . '/', '', false, false);
        setcookie('PHPSESSID', '', time() - 3600, '/', '', false, false);

	}
	
	public function save($jwt, $username=NULL) {
		global $config;
		$user = new user;
		$user->saveJWT($username, $jwt);
	}
	
}

?>