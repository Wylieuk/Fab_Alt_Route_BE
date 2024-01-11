<?php
defined("isInSideApplication")?null:die('no access');
class encryption{
	
	
	static function lightHash($str){
		$hashAlgorithm = 'md5';
		return hash($hashAlgorithm, $str);
	}
	
	static function medHash($str){
		$hashAlgorithm = 'sha256';
		return hash($hashAlgorithm, $str);
	}

	static function strongHash($str){
		$hashAlgorithm = 'sha512';
		return hash($hashAlgorithm, $str);
	}

	static function shortHash($str){
		$hashAlgorithm = 'crc32';
		return hash($hashAlgorithm, $str);
	}

	/*
	* usage: encryption::encrypt('something for the weekend?', 'your_password_string' [, cipherMethod])
	***************************************/
	static function encrypt($string, $key, $cipher = 'aes-256-cbc'){
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
		return base64_encode(bin2hex($iv).openssl_encrypt($string, $cipher, $key, $options = 0, $iv));
	}

	/*
	* usage: encryption::decrypt('<encrypted string>', 'your_password_string' [, cipherMethod]);
	***************************************/
	static function decrypt($encryptedString, $key, $cipher = 'aes-256-cbc'){

        


		$encryptedString = base64_decode(trim($encryptedString));
		$ivlen           = strlen(bin2hex(openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher))));


        if (!ctype_xdigit(substr($encryptedString, 0, $ivlen))){
            throw new Exception('Bad encryptedString');
        }


		$iv              = hex2bin(substr($encryptedString, 0, $ivlen));
		$encryptedString = substr($encryptedString, $ivlen);
		return openssl_decrypt($encryptedString, $cipher, $key, $options = 0, $iv);
    }
    
    
    /**
     * @link http://php.net/manual/en/function.openssl-get-cipher-methods.php Available methods.
     * @var string Cipher method. Recommended AES-128-CBC, AES-192-CBC, AES-256-CBC
     */
    protected $encryptMethod = 'AES-256-CBC';


    /**
     * Decrypt string.
     * 
     * @link https://stackoverflow.com/questions/41222162/encrypt-in-php-openssl-and-decrypt-in-javascript-cryptojs Reference.
     * @param string $encryptedString The encrypted string that is base64 encode.
     * @param string $key The key.
     * @return mixed Return original string value. Return null for failure get salt, iv.
     */
    public function cryptJsDecrypt($encryptedString, $key)
    {
        
        $t = explode('.', base64_decode($encryptedString, true));

        
        if (count($t) !== 4){
            return false;
        }

        $json = [
            'ciphertext' => $t[0],
            'iv'         => $t[1],
            'salt'       => $t[2],
            'iterations' => $t[3]
        ];

        if(!$json || is_null($json)){
            return false;
        }
        
        try {
            $salt = hex2bin($json["salt"]);
            $iv = hex2bin($json["iv"]);
        } catch (Exception $e) {
            return false;
        }

        $cipherText = base64_decode($json['ciphertext']);

        $iterations = intval(abs($json['iterations']));
        if ($iterations <= 0) {
            $iterations = 999;
        }
        $hashKey = hash_pbkdf2('sha512', $key, $salt, $iterations, ($this->encryptMethodLength() / 4));
        unset($iterations, $json, $salt);

        $decrypted= openssl_decrypt($cipherText , $this->encryptMethod, hex2bin($hashKey), OPENSSL_RAW_DATA, $iv);
        unset($cipherText, $hashKey, $iv);

        return $decrypted;
    }// decrypt


    /**
     * Encrypt string.
     * 
     * @link https://stackoverflow.com/questions/41222162/encrypt-in-php-openssl-and-decrypt-in-javascript-cryptojs Reference.
     * @param string $string The original string to be encrypt.
     * @param string $key The key.
     * @return string Return encrypted string.
     */
    public function cryptJsEncrypt($string, $key)
    {
        $ivLength = openssl_cipher_iv_length($this->encryptMethod);
        $iv = openssl_random_pseudo_bytes($ivLength);
 
        $salt = openssl_random_pseudo_bytes(16);
        $iterations = 999;
        $hashKey = hash_pbkdf2('sha512', $key, $salt, $iterations, ($this->encryptMethodLength() / 4));

        $encryptedString = openssl_encrypt($string, $this->encryptMethod, hex2bin($hashKey), OPENSSL_RAW_DATA, $iv);

        $encryptedString = base64_encode($encryptedString);
        unset($hashKey);

        //$output = ['ciphertext' => $encryptedString, 'iv' => bin2hex($iv), 'salt' => bin2hex($salt), 'iterations' => $iterations];

        $output = $encryptedString.'.'.bin2hex($iv).'.'.bin2hex($salt).'.'.$iterations;

        unset($encryptedString, $iterations, $iv, $ivLength, $salt);
        return base64_encode($output);
        //return base64_encode(json_encode($output));
    }// encrypt


    /**
     * Get encrypt method length number (128, 192, 256).
     * 
     * 
     */
    protected function encryptMethodLength()
    {
        $number = filter_var($this->encryptMethod, FILTER_SANITIZE_NUMBER_INT);

        return intval(abs((int)$number));
    }// encryptMethodLength


    /**
     * Set encryption method.
     * 
     * @link http://php.net/manual/en/function.openssl-get-cipher-methods.php Available methods.
     * @param string $cipherMethod
     */
    public function setCipherMethod($cipherMethod)
    {
        $this->encryptMethod = $cipherMethod;
    }// setCipherMethod

	
	
	
	
	
	
	
	
	
	
	
	
}