/** Class: URL (20131122)
 * Zerlegt eine URL und bietet einen 
 * einfachen Zugriff auf die einzelnen Elemente */
function mUrl(url){
	url	= url+"";
	
	// URL-Fragmente
	this.protocol	= '';
	this.domain		= new Array();
	this.path		= new Array();
	this.file		= '';
	this.params		= {};
	this.hash		= '';
	
	
	// ## Logik ##
	var tmp;
	// Analyse
	// - Params
	tmp	= url.split("?");
	
	// - Params & Hash extrahieren
	if(tmp.length>1){
		// Hash vorhanden
		var x	= tmp[1].split("#");
		if(x.length>1){
			this.hash	= x[1];
		}
		
		var params	= x[0].split("&");
		var l	= params.length;
		for(var i=0;i<l;i++){
			 var param	= params[i].split("=");
			 this.params[param[0]]	= param[0];
		}
	}
	
	// - Protokoll extrahieren
	var x	= tmp[0].split(":");
	this.protocol	= x[0];
		
	// - Domain & Datei extrahieren
	var y	= x[1].split("/");
	
	this.domain	= y[2];
	this.file	= y[y.length-1];
	
	// Pfad extrahieren
	for(var i=0;i<y.length-4;i++){
		this.path[i]	= y[i+3];
	}
}