<?
/* Abstrakte Klasse, die Content-Klassen Standard-Funktionen zur Verfügung stellt */
abstract class Item {		    
    public $werte  = array();
	
    protected $tab;
	
	protected $mode		= "complete";
	
	protected $valid	= true;
    
	/** Setzt den Active-Wert für alle Einträge einer Tabelle zurück */
    static public function RESET($tab,$id){
        $dbset  = DBset::INIT('');
        $sql    = 'UPDATE '.$tab.' SET active=0';
		if($id){
			$sql .= " WHERE id='".$id."'";
		}
        $dbset->raw($sql);
    }
    
    /** Speichert ein Objekt in der DB */
    public function save(){
        $dbset  = DBset::INIT();
		
		if($this->valid==true){
			// print_r($this->werte);
			$dbset->set($this->tab,$this->werte);
		}else{			
			Core::$VIEW->ping("#i ".$this->werte['id']);
		}
    }
	
	/** Füllt das Werte-Array */
	protected function setWerte($arr){
		$this->werte	= $arr;
	}
	
	
	/** Mapped Inputdaten z.B. aus einem Formular in 
	 * das $werte-Array für die DB-Speicherung 
     * @param: $mapping(json): Mapping-Daten im Json-Format (input,db,type,)
     * @param: $data(arr): Komplett Eingabedaten aus dem Formular
	 * @param: arg[2]: Ggfs. Daten zum befüllen von dynamic-Feldern */
	public function map($mapping,$data){

        // --- Durchlaufen des Mapping-Arrays (=> Objekt) ---
        // Mapping-Array Feld-weise durchgehen
		foreach($mapping as $map) {
			
			$proceed	= true;
			
			// -- BEDINGUNGEN PRÜFEN: Leer das Feld wieder, falls die Bedingungen (condition) nicht erfüllt ist     
            if(isset($map['condition'])){
            	
            	$valid	= 0;
	            if($map['condition']){
					
					// Bedingungen prüfen
					foreach($map['condition'] AS $condition){
						switch($condition[1]){
							case '==':						
								if($data[$condition[0]] == $condition[2]){
									$valid++;
								}
								break;
						}
					}
					
					if($valid<sizeof($map['condition'])){        				
						$proceed = false;
					}
	            } 
			} // ENDE: BEDINGUNGEN
			
			
			
            // -- INPUT: Durchlaufen der Eingabedaten
            // Attribut "input" (Nutzereingabe) in Feld ist gefüllt
            if(isset($map['input'])&&isset($data[$map['input']])&&$proceed){
				
			    if($data[$map['input']]!=''){
	                // Dyn. Werte: Werte (Nutzereingabe) validieren
	                // Rückgabe: 
	                
	                if($map['input']&&$proceed){
	                    $this->werte[$map['db']]    =  Validator::VALIDATE(trim($data[$map['input']]),$map['type'],$map['input']);
	                }                
			    }
            } // ENDE -- INPUT-Werte
            
            
        
            // -- FIX: Fixe Werte für eine Feld
            if(isset($map['fix'])&&$proceed){ // Variable vorhanden
	            if($map['fix']&&$proceed){	// Variable true
	                $this->werte[$map['db']]    = $map['fix'];
	            }
			}
            
        
            // Prefixe
            if(isset($map['prefix'])&&$proceed){
	            if($map['prefix']&&$proceed){
	                $this->werte[$map['db']]    = $map['prefix'].$this->werte[$map['db']];
	            }  
			}
        
            // Confirm-Email (Prüft, ob der Wert mit dem Confirm-Feld übereinstimmt)
            if(isset($map['confirm'])&&$proceed){				
            	if($data[$map['input']]!=$data[$map['confirm']]){
            		Core::LOG_ERROR('Die E-Mail-Adresse stimmt nicht mit der Wiederholung überein.',$map['input']);
            	}
			}
            
            // Feldauflistung
            if(Core::$DEBUG){
                echo "<br/>".$map['db']." - ".$this->werte[$map['db']];
            }
			
			
            // Prüft auf vollständige Pflichtfelder
            if(isset($map['req'])&&$proceed){
            	if(isset($this->werte[$map['db']])){
            		if($this->werte[$map['db']]==""){            			
						// Fehlermeldung an Core
						Core::LOG_ERROR('Wert ist leer!',$map['input']);
            		}
            	}else{
					// Fehlermeldung an Core
					Core::LOG_ERROR('Wert nicht gefunden!',$map['input']);
            		
            	}
            }
		}
	}
}
?>