<?
/** Versand von E-Mails (2013-08-18)*/
class Mail {
	// Mail-Attribute
	private $mail	= array();
	
	// Header Zeilen-Ende
	private $eol	= "\r\n";





	// ## KONSTRUKTOR ##
    public function __construct(){
    	// Default-Werte
    	$this->mail['charset']		= 'iso-8859-1';
		$this->mail['mime']			= 'text/html';
		$this->mail['enc']			= '8bit';
		
		$this->mail['addressees']	= array();
		$this->mail['header']		= array("MIME-Version: 1.0","X-Mailer: TraDeers E-Commerce Mailer");
		
		// Versenderadresse (Domain sollte auf dem Server liegen)
		$this->mail['send_name']	= 'GrünHausEnergie';
		$this->mail['send_name']	= 'confirm@gruenhausenergie.de';
    } //--------------------------------------------------------






	/** Setzen von E-Mail-Attributen */
	public function set($field,$value){
				
		// Fieldcheck
		switch($field){
			case "subject":
			case "msg_plain":	// Text-Mail
			case "msg_html":	// HTML-Mail
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
				
			case "reply":
				$name	= '';	// Klarname
				if(func_num_args()==3){
					$name	= func_get_arg(2);
				}
				array_push($this->mail['header'],'Reply-To: '.$name.' <'.$value.'>');
				break;

			case "returnpath":
				$name	= '';	// Klarname
				if(func_num_args()==3){
					$name	= func_get_arg(2);
				}
				array_push($this->mail['header'],'Return-Path: '.$name.' <'.$value.'>');
				break;
				
			default:
				Core::LOG_ERROR('Interner Fehler: E-Mail-Feld "'.$field.'" ist nicht bekannt!','email');
		}
	}
	
	
	
	
	
	
	/** Weiteres Header-Element hinzufügen */
	public function addHeader($line){
		array_push($this->mail['header'],$line);
	}
	
	
	
	
	
	
	/** Mail zusammenbauen und verschicken */
	public function send(){		
		// Check, ob alle Felder vorhanden sind;
		if(true){
			// Mail-Header
			$header	= "From: ".$this->mail['send_name']." <".$this->mail['send_mail'].">".$this->eol;
			$header	.= "Content-Type: ".$this->mail['mime']."; charset=".$this->mail['charset'].$this->eol;
			$header	.= "Content-Transfer-Encoding: ".$this->mail['enc'].$this->eol;
			
			// Zusätzliche Header-Elemente anhängen
			if(sizeof($this->mail['header'])>0){
				foreach($this->mail['header'] AS $line){
					$header .= $line.$this->eol;
				}
			}
	
			
			// Liste der Adressaten
			$recipients	= implode(',', $this->mail['recipients']);
			
			
			// Mail senden
			mail($recipients,$this->mail['subject'],$this->mail['msg'],$header);
		}else{
			Core::LOG_ERROR('Interner Fehler: Es sind nicht alle Bedingungen für den Versand der E-Mail erfüllt!','email');
		}
	}
	
}
?>