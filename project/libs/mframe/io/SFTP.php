<?
class SFTP{
	private static $LIB	= array("SFTP"=>false);
	
	private $con	= null;
	
    private $host	= "";
	private $port	= null;
	
    private	$user	= "";
    private $pass	= "";
	
	// ## KONSTRUKTOR ##
	public function __construct(){
    	if(func_num_args()>=3){
			$this->user	= func_get_arg(0);
			$this->pass	= func_get_arg(1);
			$this->host	= func_get_arg(2);
    	}
    	if(func_num_args()>=4){
			$this->port	= func_get_arg(3);
    	}	
		
		
		if(!SFTP::$LIB['SFTP']){
			include('Net/SFTP.php');
			SFTP::$LIB['SFTP']	= true;
		}
		print_r($this);
		
		if($this->port){			
			$this->con	= new Net_SFTP($this->host,$this->port);
		}else{
			$this->con	= new Net_SFTP($this->host);
		}
		
		$this->con->login($this->user,$this->pass);
	}

	/** Datei von Server laden */
	public function downloadFile(){
		
	}
	
	/** String-Daten in Datei auf Server speichern */
	public function uploadData($filename,$data){
		$this->con->put($filename,$data);
	}
	
	/** Verzeichnisauflistung */
	public function getToc(){
		
	}
}
?>
