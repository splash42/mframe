<?
/** View-Klasse zum Aufbereiten der Ausgabe inkl. Templating */
class View{
	// Template-Puffer
	private static $PUFFER	= array();
	// Ausgabe-String
	public static $OUTPUT	= '';
	
	private static $MIME		= 'text/html';
	private static $ENC			= 'utf-8';
	private static $BASE_ENC	= 'utf-8';
	private static $FILENAME	= 'export.csv';
	
	/** HTTP-Header setzen */
	public static function SET_HEADER(){
		
		switch(func_num_args()){
			case 4:
				View::$BASE_ENC	= func_get_arg(3);
			case 3:
				View::$FILENAME	= func_get_arg(2);
				View::FORMAT_FILENAME();
				header('Content-Disposition: attachment; filename="'.View::$FILENAME.'"');
			case 2:
				View::$ENC	= func_get_arg(1);
			case 1:
				View::$MIME	= func_get_arg(0);
				break;				
		}
		
		
		header("Content-Type: ".View::$MIME."; charset=".View::$ENC);
	}
	
	/** Gibt den View-Speicher $OUTPUT aus */
	public static function GET_OUT(){
		return View::$OUTPUT();
	}
	
	/** Print-Ausgabe von Werte 
	 * @param $msg: Ausgabewert */
	public static function OUT($msg){
		
		if(View::$ENC!=View::$BASE_ENC){
			$msg	= iconv(View::$BASE_ENC,View::$ENC."//TRANSLIT",$msg);			
		}
		echo $msg;			
	}
	
	
	/** Ausgabefunktion für die komplette Website */
	public static function SHOW(){
		echo View::$OUTPUT;
	}
	
	/** Print-Ausgabe von Werte mit Format-Konvertierung
	 * @param $msg: Ausgabewert
	 * @param $in: Format der Eingabe
	 * @param $out: Format der Ausgabe */
	public static function OUTC($msg,$in,$out){
		$txt;
		
		if(View::$ENC!=View::$BASE_ENC){
			$msg	= iconv(View::$BASE_ENC,View::$ENC."//TRANSLIT",$msg);			
		}
		
		switch($in){
			case "json":
				switch($out){
					case "str":		// JSON-Arr -> String (horizontal)
						$txt	= $msg;
						break;
					case "arr":		// JSON-Arr -> String (vertikal)
						$txt	= $msg;
						break;
					default:
						$txt	= $msg;
				}
				break;
				
			case "arr":
				switch($out){
					case "str":		// ARR -> STRING
						foreach($msg AS $v){
							$txt	.= $v."\n";
						}
						break;
						
					case "html":		// ARR -> STRING
						foreach($msg AS $v){
							$txt	.= $v."<br />";
						}
						break;
						
					case "list":		// ARR -> LIST (HTML-Liste)
						$txt	= '<ul>';
						foreach($msg AS $v){
							$txt	.= "\n<li>".$v."</li>";
						}
						$txt	.= "\n</ul>";
						break;
						
					case "json":	// ARR -> JSON
						$txt	= json_encode($msg);
						break;
						
					default:
						$txt	= $msg;
				}
				break;
				
			default: // Keine Formatierung
				$txt	= $msg;
		}
		echo $txt;
	}
	
	/** Ausgabe des View-Speichers als print_r */
	public static function OUTP($msg){
		print_r($msg);
	}
	
	/** Setzt ein Basis-Template
	 * @param: $url: Url inkl. mit Pfad ab TPL-Root */
	public static function LOAD_TEMPLATE($url){
		View::$OUTPUT	= File::READ(Core::$TPL.$url,'str');
	}
	
	
	/** Array mit Template-Informationen verarbeiten */
	public static function SET_TEMPLATES($tplList){

		foreach ($tplList as $tpl) {
			if($tpl['parent']=='root'){
				View::$OUTPUT	= File::READ(Core::$TPL.$tpl['url'],'str');
			}else{
				$size	= sizeof(View::$PUFFER[$tpl['parent']]);
				if($size>0){
					if($tpl['type']=="add"){
						View::$PUFFER[$tpl['parent']][$tpl['tag']][$size+1]['id']	= $tpl['id'];
						View::$PUFFER[$tpl['parent']][$tpl['tag']][$size+1]['code']	= File::READ(Core::$TPL.$tpl['url'],'str');
					}else{
						View::$PUFFER[$tpl['parent']][$tpl['tag']]					= null;
						View::$PUFFER[$tpl['parent']][$tpl['tag']][0]['id']			= $tpl['id'];
						View::$PUFFER[$tpl['parent']][$tpl['tag']][0]['code']		= File::READ(Core::$TPL.$tpl['url'],'str');
					}					
				}else{
					View::$PUFFER[$tpl['parent']][$tpl['tag']][0]['id']		= $tpl['id'];
					View::$PUFFER[$tpl['parent']][$tpl['tag']][0]['code']	= File::READ(Core::$TPL.$tpl['url'],'str');
				}				
			}
		}
		
		
		View::PROCESS();
	}
	
	/** Verarbeitet die im Puffer befindlichen statischen Templates */
	private static function PROCESS(){
		// Kombinieren
		
		// Zusammensetzen
	}
	
	/** Ersetzt einen TAG durch einen Wert */
	public static function SET_TAG($tag,$value){
		$s	= '/##'.$tag.'##/i';
		$r	= $value;
		
		View::$OUTPUT	= preg_replace($s, $r, View::$OUTPUT);
	}
	
	
	/** Ersetzt evt. TAGs in den Dateinamen  */
	private static function FORMAT_FILENAME(){
		$time	= Time::INIT();
		
		// Zeitstempel setzen
		$s	= '##DATETIME##';
		$r	= $time->getDatum('JJJJMMTT_hh-mm-ss');		
		View::$FILENAME = str_replace($s, $r, View::$FILENAME);
		
		// Datum setzen
		$s	= '##DATE##';
		$r	= $time->getDatum('JJJJMMTT');	
		View::$FILENAME = str_replace($s, $r, View::$FILENAME);
	}
	
}
?>