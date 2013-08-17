<?
/** Zentrale Routing-Klasse */
class Core{
	
	// SINGLETON
    public static $SINGLETON = null;

	public static $DEBUG	= false;

    public static $BASE;
    public static $APP;
    public static $LIB;
    public static $TPL;
    public static $TMP;
	
	// Zusatzinformation für Fehlermeldungen (z.B. welche Zeile einer Datei)
	public static $ERROR_POINTER	= '';

	// Allg. Meldungen
    private static $MSG	= array();
	
	// Fehlermeldungen
	private static $MSG_ERROR	= array();

	private $view;

	private static $DB;
	
	public static $TOKEN	= false;
	
	
	// ## KONSTRUKTOR ## -----------------------
	private function __construct(){

		date_default_timezone_set('Europe/Berlin');
		Core::$BASE	= $_SERVER['DOCUMENT_ROOT'].'api_v2/app/';

		Core::$LIB	= Core::$BASE.'lib/tlib/';
		Core::$APP	= Core::$BASE.'class/';
		Core::$TPL	= Core::$BASE.'templates/';
		Core::$TMP	= Core::$BASE.'tmp/';
		
		// Import (conf)
        require_once 'View.php';

        require_once Core::$LIB.'core/Item.php';
        require_once Core::$LIB.'core/Time.php';
        require_once Core::$LIB.'core/Tracer.php';
        require_once Core::$LIB.'core/Validator.php';

        require_once Core::$LIB.'io/User.php';
        require_once Core::$LIB.'io/File.php';
        require_once Core::$LIB.'io/DBset.php';
        require_once Core::$LIB.'io/DBget.php';
		
        require_once Core::$APP.'log/Log.php';
		
		
		// Header auf UTF-8 setzen
		View::SET_HEADER();

		if(User::INPUT('debug','num')){
			Core::$DEBUG	= true;
		}
		
		
	}

    public static function INIT(){
        if(Core::$SINGLETON==null){
            Core::$SINGLETON = new Core();
        }
        return Core::$SINGLETON;
    } // -----------------------------------------




    /** --- # AKTION # ---- */
    public function route($route){
		
		// Check: Globaler SicherheitsToken (global)
		try{
			if(User::INPUT('token_global','text')){
				$token	= User::INPUT('token_global','text');			
				$salt	= 'hBnd75sjndkdmU';
				$ts		= User::INPUT('ts','num');
				$uid	= User::INPUT('uid','text');
				$modul	= User::INPUT('m','text');
				$task	= User::INPUT('task','text');
				 
				$valid	= md5($salt.$ts.$uid.$modul.$task);
				if($token==$valid){
					Core::$TOKEN = true;
				}else{	// Token ungültig
					if($route['intern']){
						Core::$TOKEN = true;
					}
				}
			}else{	// Kein Token übermittelt
				if($route['intern']){
					Core::$TOKEN = true;
				}
			}
		}catch(Exception $e){
			
		}
		
		// Konfig einlesen
		$config		= File::READ(Core::$APP.".conf","json");
		Core::$DB	= $config['db'];

		$structure	= File::READ(Core::$APP.".structure","json");

		View::SET_TEMPLATES($structure['tpl']);
		
		
		
		// # (1) - Start: Routing # ------------  
        // - Gewähltes Modul erkennen
		if(User::INPUT('m','text')){
			$route['mod']   = User::INPUT('m','text');
		}
		
		// # (2) - Modul-Aufruf # ------------  
		// - Check: Modul existiert? (Konfiguration aus Datei ".structure")
		if(isset($structure['moduls'][$route['mod']]['modul'])){
			Core::LOG('Info: Modul "'.$modul.'" gewählt.');	// <= LOG
			
			// .structure-Daten verkürzen
			$modul	= $structure['moduls'][$route['mod']]['modul'];
			
			// Zugriffskontrolle
			$access	= true;	// default
			if(isset($structure['moduls'][$route['mod']]['token'])){
				if($structure['moduls'][$route['mod']]['token']!=$route['token']){
					$access	= false;
					Core::LOG('Zugriffsbeschränkung: Sie haben keine Berechtigung für dieses Modul!');
				}
			}

			// -- Dynamischer Controller-Aufruf --
			if($access){
	            $class   = ucfirst($modul).'Controller';
	            require_once Core::$APP.$modul.'/'.$class.'.php';
				
				// Struktur-Daten des Moduls laden (noch inaktiv)
				$modStructure = File::READ(Core::$APP.$modul."/.structure","json");
				
	            $con    = new $class($modStructure);
			}
		}else{
			Core::LOG('Fehler: Modul '.$route['mod'].' ist nicht bekannt!');
		}
		
		
		
		
		
		// # (3) - Seite ausgeben # ------------ 
		if($route['intern']){
			return View::$OUTPUT;
		} 
		View::SHOW();

		
		// # (4) - Logfile ausgeben # ------------  
		if(User::INPUT('trace','text')){
			View::OUTC(Core::$MSG,'arr','list');
			print_r(Core::$MSG_ERROR);
		}
		
    } // ENDE: route()
	

	/** Verwaltung der Datenbank-Zugriffe
	 * @param: args[0]: Nutzertyp (read, add, update, master) */
	public static function GET_DB_ACCESS(){
		$user	= 'add';
		if(func_num_args()>0){
			$args	= func_get_args();
			$user	= $args[0];
		}else{
			Core::LOG('DB-User: add');
		}
		
		$access	= array();
		
		$access['host']		= Core::$DB['host'];
		$access['dbname']	= Core::$DB['db_name'];
		$access['user']		= Core::$DB['user'][$user]['name'];
		$access['pass']		= Core::$DB['user'][$user]['pass'];
		
		return $access;
	}
	
	
	// LOGGING (Nachträglich Zzusammenfassend)
	public static function LOG($msg){
		array_push(Core::$MSG,'# '.$msg);
	}
	
	
	/** Logging von Fehlern
	 * @param: $msg - Fehlermeldung
	 * @param: $arg[0] - Fehlerquelle (optional) */
	public static function LOG_ERROR($msg){
		$scope = "global";
		if(func_num_args()>1){			
			$scope = func_get_arg(1);
		}
		
		if(Core::$ERROR_POINTER!=''){
			$scope = Core::$ERROR_POINTER."_".$scope;
		}
		
		array_push(Core::$MSG_ERROR,array("scope"=>$scope,"msg"=>$msg));
	}
	
	/** Fehler-Status ermitteln */
	public static function GET_STATUS(){
		$status	= array();
		$status['status']	= "ok";
		
		if(sizeof(Core::$MSG_ERROR)>0){
			$status['status']	= "error";			
			$status['data']		= Core::$MSG_ERROR;
		}else{			
			if(func_num_args()>0){
				$status['data']		= func_get_arg(0);
			}
		}
		
		return $status;
	}
	
	public static function HAS_ERROR(){
		if(sizeof(Core::$MSG_ERROR)>0){
			true;
		}else{
			false;
		}
	}
	
	/** Prüft, ob eine Fehlermeldung vorliegt */
	public static function SEND_STATUS(){		
		View::OUTC(Core::GET_STATUS(),"arr","json");
	}
	
	// Ping (Sofort reagierend)
	public static function PING($msg){
		echo $msg."<br />";
	}
}
?>