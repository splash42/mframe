<?
class Time {
    static $SINGLETON = NULL;

    public $now;
    public $data;

    // ## KONSTRUKTOR ##
    private function __construct(){
    	date_default_timezone_set('Europe/Berlin');
		$this->now  = time();
		$this->data = array();
    }
    
    public function INIT(){
	// echo "init<br>";
        if(Time::$SINGLETON==null){
            Time::$SINGLETON = new Time();
        }
        return Time::$SINGLETON;
    }

    // ## WORKER ##
    public function getDatum($format){
		// Zeit abnehmen
		return $this->getDate($format,$this->now);
    }

    /* Datum, dass $tage vor oder hinter dem aktuellen Datum liegt */
    public function getRelDatum($format,$tage){
		return $this->getDate($format,$this->now+86400*$tage);
    }

    public function getTimestamp(){
		return $this->now;
    }
    
    public function refresh(){
        $this->now  = time();
    }

	/** Prüft ein unbekanntes Datumsformat auf Gültigkeit 
	 * und gibt es im Format YYYY-MM-DD zurück */
	public function valiDate($value){
		$out	= false;
		
		// Mindestlänge
		$len	= strlen($value);
		if($len>=8&&$len<=10){
			$value	= str_replace('.','-',$value);	// Einheitlicher Trenner
			$tmp	= explode('-',$value);			// Datum in Elemente zerlegen			
			
			// Korrekte Anzahl Datums-Elemente
			if(sizeof($tmp)==3){
				
				// Datumselemente auf 2-stellig formatieren
				for($i=0;$i<3;$i++){
					if(strlen($tmp[$i])==1){
						$tmp[$i]	= "0".$tmp[$i];
					}
				}
				
				
				// Rückgabe
				if(strlen($tmp[0])==4){ 		// Jahr vorne
					if(checkdate($tmp[1], $tmp[2], $tmp[0])){
						$out =	$tmp[0]."-".$tmp[1]."-".$tmp[2];
					}
				}else{							
					if(strlen($tmp[2])==4){ 	// Jahr hinten
						if(checkdate($tmp[1], $tmp[0], $tmp[2])){
							$out =	$tmp[2]."-".$tmp[1]."-".$tmp[0];
						}
					}
				}
			}
		}
		
		return $out;
	}

    // ## HELFER ##    
    public function getDate($format,$timestamp){
		if($timestamp=="now"){
			$timestamp	= $this->now;
		}
	
		// Datumswerte ermitteln
		// Tag
		$tag	= date("d",$timestamp);
		// Monat
		$monat	= date("m",$timestamp);
		// Jahr
		$jahr 	= date("Y",$timestamp);
		// Stunde
		$stunde	= date("H",$timestamp);
		// Minute
		$minute	= date("i",$timestamp);
		// Sekunde
		$sekunde= date("s",$timestamp);
	
		// Ausgabeformat
		if($format==""){
		    return $jahr.$monat.$tag;
		}else{
		    $format = $vari = str_replace ("JJJJ",$jahr,$format);
		    $format = $vari = str_replace ("YYYY",$jahr,$format); // Alternativ
		    $format = $vari = str_replace ("MM",$monat,$format);
		    $format = $vari = str_replace ("TT",$tag,$format);
		    $format = $vari = str_replace ("DD",$tag,$format); // Alternativ
		    $format = $vari = str_replace ("hh",$stunde,$format);
		    $format = $vari = str_replace ("mm",$minute,$format);
		    $format = $vari = str_replace ("ss",$sekunde,$format);
	
		    return $format;
		}
    }
    
    /** Formatiert eine Zeitdifferenz in ms in andere Zeitformate */
    public function getDifference($time,$format){
        switch($format){
			case "JJJJ-MM-TT":
				$tmp	= explode("-", $time);
				$ms 	= mktime($tmp[3],$tmp[4],$tmp[5],$tmp[1],$tmp[2],$tmp[0]);
				break;
			default:
				$ms		= $time;
        }
		
		return $this->now - $ms;
		
    }
}
?>
