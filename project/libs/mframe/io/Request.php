<?
class Request{
	private $useragent	= null;
	
	private $auth	= false;
	private $user	= '';
	private $pass	= '';
	
	
	// ## KONSTRUKTOR ##
	public function __construct(){}
	
	/** Sendet einen POST-Request 
	 * @param: $url -> Request-URL
	 * @param: $func(1) -> assArr mit zu übertragenden Daten */
	public function post($url){
		// (1) - Zusatzparameter auslesen
		// Mit dem POST-Request zu versendende Datem
		$data	= null;
		if(func_num_args()>1){
			$data 	= func_get_arg(1);
		}
		
		// cURL-Aufruf
		$req 	= curl_init();
		
		// Parameter
		curl_setopt($req, CURLOPT_VERBOSE, 1);
		curl_setopt($req, CURLOPT_URL, $url);
		curl_setopt($req, CURLOPT_POST, TRUE);
		if($data){
			curl_setopt($req, CURLOPT_POSTFIELDS, $data);
		}
		if($this->auth){
			curl_setopt($req, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($req, CURLOPT_USERPWD, $this->user.":".$this->pass);
		}
		curl_setopt($req, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
		
		// Request
		$res	= curl_exec($req);
		
		// Rückgabe Ergebnis
		return $res;
	}

	/** Setzt einen User-Agent für alle Requests */
	public function setUserAgent($ua){
		$this->useragent	= $ua;	
	}
	
	/** Setzt die Login-Daten für ein Auth-Zugriff */
	public function setAuth($user,$pass){
		$this->auth	= true;
		$this->user = $user;
		$this->pass = $pass;
	}
}
?>