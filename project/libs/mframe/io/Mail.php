<?
/** Versand von E-Mails */
class Mail {
	// Mail-Attribute
	private $mail		= array();

	// ## KONSTRUKTOR ##
    public function __construct(){
    	// Default-Werte
    	$this->mail['charset']		= 'iso-8859-1';
		$this->mail['mime']			= 'text/html';
		$this->mail['enc']			= '8bit';
		$this->mail['addressees']	= array();
		
		// Versenderadresse (Domain sollte auf dem Server liegen)
		$this->mail['send_name']	= 'GrünHausEnergie';
		$this->mail['send_name']	= 'confirm@gruenhausenergie.de';
    } //--------------------------------------------------------
	
	/** Setzen von E-Mail-Attributen */
	public function set($field,$value){
				
		// Fieldcheck
		switch($field){
			case "subject":
			case "msg":
			case "send_name":
			case "send_mail":
				$this->mail[$field]	= $value;
				break;
			
			// Adressat ggfs. mit zusätzlichem Namen.
			// Mehrere Adressaten möglich
			case "recipient":
				$recipient	= $value; // ggfs. mit Namen angehängt
				array_push($this->mail['recipients'],$recipient);
				break;
			default:
				Core::LOG_ERROR('Interner Fehler: E-Mail-Feld "'.$field.'" ist nicht bekannt!','email');
		}
	}
	
	
	/** Mail zusammenbauen und verschicken */
	public function send(){		
		// Check, ob alle Felder vorhanden sind;
		if(true){
			// Mail-Header
			$header	= '';
			$header	= "From: ".$this->mail['send_name']." <".$this->mail['send_mail'].">\r\n";
			$header	.= "Content-Type: ".$this->mail['mime']."; charset=".$this->mail['charset']."\r\n";
			$header	.= "Content-Transfer-Encoding: ".$this->mail['enc']."\r\n";
	
			
			// Liste der Adressaten
			$recipients	= implode(';', $this->mail['recipients']);
			
			mail($recipients,$this->mail['subject'],$this->mail['msg'],$header);
		}else{
			Core::LOG_ERROR('Interner Fehler: Es sind nicht alle Bedingungen für den Versand der E-Mail erfüllt!','email');
		}
	}
	
}
?>