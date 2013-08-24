<?
/** DB-Zugriff mit PDO */
class DBpdo{
    private static $SINGLETON;

	// Zugangsdaten
    private $user;
    private $pass;
    private $host;
    private $dbname;
	
	// Connection
	private $connection;
	
	// Prepared Statement Puffer
	private $psBuffer		= array();
	
	// ID Puffer (Schon in Tabelle vorhandene Items)
	private $keyBuffer	= array();

    // ## KONSTRUKTOR ##
    public function __construct($user){
    	$db = Core::GET_DB_ACCESS($user);
		
		$this->host	    = $db['host'];
		$this->dbname 	= $db['dbname'];
		$this->user	    = $db['user'];
		$this->pass	    = $db['pass'];
    }

    public static function INIT(){
        if(DBpdo::$SINGLETON==null){
        	
			if(func_num_args()>0){				
            	DBpdo::$SINGLETON = new DBpdo(func_get_arg(0));	// Reduzierter Nutzer
			}else{
            	DBpdo::$SINGLETON = new DBpdo('master');		// Hauptnutzer
			}
        }
        return DBpdo::$SINGLETON;
    }
	
	
	
	
	// ## --- Verbindungen --- ##	
	/** Erstellt eine Verbindung */
	private function open(){
		$this->connection		= new PDO('mysql:host='.$this->host.';dbname='.$this->dbname.';',$this->user,$this->pass);
	}
	
	/** Führt eine Abfrage auf die DB durch */
	public function query($sql){
		$erg	= array();
		try{
			foreach ($this->connection->query($sql) as $row) {
				array_push($erg,$row);
			}
		// DB-Fehler
		}catch (PDOException $e) {	
			Core::LOG("Error!: " . $e->getMessage());
			echo "PDO-Fehler";
		}
	}
	
	/** Schließt eine bestehende Verbindung */
	public function close(){
		$this->connection		= null;
	}  // ENDE: Verbindungsaufbau
	
	//----------------------------------------------------------------------------------
	
	
	// ## --- INSERT-STEUERUNG --- ##
	/** Prüft, ob Daten eingefügt oder schon vorhanden und nur aktualisiert werden müssen */
	public function set($tab,$data){
		
		// (1) - Tabellen-Key setzen
		// Default-Key
		$key	= 'id';
		// Gesetzter Key (optional)
		if(func_num_args()==3){ $key	= func_get_arg(2); }
		// Falls kein Key für TAB vorhanden -> Key setzen
		if(!$this->keyBuffer[$tab]){ $this->keyBuffer[$tab]	= $key;	}
		
		
		// (2) - INSERT oder UPDATE abh. ob ITEM bereits in DB vorhanden		
		if($this->checkItem($tab,$data)){	// Item vorhanden (update)
			$this->update($tab,$data);
		}else{								// Item noch nicht vorhanden (Insert)
			$this->add($tab,$data);
		}
	}
	
	
	// ## --- PREPARED STATEMENTS--- ##
	
	/** SELECT
	 * @param: $tab - Tabelle
	 * @param: arg[0] - (String) Felder, die selektiert werden sollen
	 * @param: arg[1] : (AssArray) WHERE-Bedingungen für das Update (feld,operator,wert) */
	public function select($tab){
		
		$fields		= '*';
		$conditions	= '';
		
		switch(func_num_args()){
			case 3:
				$conditions	= $this->formatConditions(func_get_arg(2));
			case 2:
				$fields	= func_get_arg(1);
		}
		
		// Statement
		$this->open();	
		$statement	= $this->connection->prepare('SELECT :fields FROM :tab WHERE :conditions');
		
		// param
		$statement->bindParam(':fields',$fields);
		$statement->bindParam(':tab',$tab);
		$statement->bindParam(':conditions',$conditions);
		
		// Request
		$statement->execute();
	}
	
	
	/** INSERT
	 * @param: $tab - Tabelle
	 * @param: $vArr - (ValueArray) Ass. Array mit Feldern und Werten  */
	public function add($tab,$data){
		
		// Statement aus $vArr zusammenbauen
		$fields	= '';
		$values	= '';

		$first	= true;
		foreach ($data as $key => $value) {
			if($first){				
				$first	= false;
			}else{
				$fields .= ",";
				$values .= ",";
			}
			
			$fields .= $key;
			$values .= "'".$value."'";
		}		
		
		// Statement	
		$this->open();
		$sql	= "INSERT INTO ".$tab." (".$fields.") VALUES (".$values.")";

		$statement	= $this->connection->prepare($sql);
		
		// param
		$statement->bindParam(':values',$values);
		
		// Request
		$statement->execute();
	}
	
	/** UPDATE
	 * @param: $tab - Tabelle
	 * @param: $vArr - (ValueArray) Ass. Array mit Feldern und Werten
	 * @param: $conditions: WHERE-Bedingungen für das Update  */
	public function update($tab,$data){
		
		// Statement aus $vArr zusammenbauen
		$fields	= '';
		$values	= '';

		$first	= true;
		foreach ($data as $key => $value) {
			if($first){				
				$first	= false;
			}else{
				$fields .= ",";
				$values .= ",";
			}
			
			$fields .= $key;
			$values .= "'".$value."'";
		}	
		
		
		// SQL		
		$sql	= "UPDATE ".$tab." SET ".$fields." WHERE :conditions";
		
		// Statement erstellen
		$this->open();	
		$statement	= $this->connection->prepare($sql);
		
		// Statement Parameter setzen
		$statement->bindParam(':tab',$tab);
		$statement->bindParam(':fields',$fields);
		$statement->bindParam(':conditions',$conditions);
		
		// Request
		$statement->execute();
	}
	
	
	/** Ermöglicht ein INSERT mit vor-erstellten Statement-Teilen */
	public function addRaw(){
		
	}
	
	/** Ermöglicht ein UPDATE mit vor-erstellten Statement-Teilen */
	public function updateRaw(){
		
	}
	
	
	// ## --- Helper --- ##
	/** Prüft, ob ein bestimmtes ITEM existiert */
	private function checkItem(){
		
		return false;
	}	
	
	/** Erzeugt aus dem Conditions-Array einen SQL-String */
	private function formatConditions($arr){
		$out	= " WHERE ";
		
		return $out;
	}
	
	/** Formatiert ein SQL-Result in ein Ass. Array */
	public function formatResult($result){
		$out	= array();
		
		return $out;
	}
	
}
?>