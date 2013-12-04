<?
class Ftp{

    static $SINGLETON   = null;

    var $server	= "";
    var	$user	= "";
    var $pass	= "";





    // ## KONSTRUKTOR ##
    public function __construct(){
    	echo "ping";
    	if(func_num_args()==3){
    		$this->server	= func_get_arg(0);
    		$this->user		= func_get_arg(1);
    		$this->pass		= func_get_arg(2);
    	}
    }

    public function INIT(){
	// echo "init<br>";
        if(Ftp::$SINGLETON==null){
            Ftp::$SINGLETON = new Ftp();
        }
        return Ftp::$SINGLETON;
    }



    // ## WORKER ##
    /* Setzt Login-Daten */
    public function setLogin($host,$user,$pass){
		$this->server	= $host;
		$this->user		= $user;
		$this->pass		= $pass;
    }
	
	public function connect(){
		echo "ping";
		$con	= ftp_connect($this->server);
	}


    /* Läd Datei $datei in das FTP-Verzeichnis $fz */
    public function uploadFile($quelle,$ziel){
		Core::LOG("Verbinde mit ".$ziel);
		$con	= ftp_connect($this->server);
		$login	= ftp_login($con,$this->user,$this->pass);
		if(ftp_put($con,$ziel,$quelle,FTP_ASCII)){
			Core::LOG("Upload von ".$ziel." war erfolgreich");
		}else{
		    Core::LOG("Upload von ".$ziel." fehlgeschlagen");
		}
		ftp_quit($con);
    }

    /* Läd Datei $fn aus dem FTP-Verzeichnis $vz */
    public function download($fn,$vz){
	
    }
	
	/** Ermittelt die Dateien und Unterverzeichnisse eines FTP-Verzeichnisses */
	public function getToc($url){
		// ...
	}
}
?>