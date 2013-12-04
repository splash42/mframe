/** 20131126 
 * Hilfsklasse für den Zugriff und das Setzen von Cookies */
function mCookie(){	
	this.data	= document.cookie;	
	this.values	= {};
	
	tmp	= this.data.split(";");
	for(i=0;i<tmp.length;i++){
		elem	= tmp[i].split("=");
		
		this.values[elem[0]]	= elem[1];
	}

    /* Beliebigen Cookie setzen */
    this.set	= function(name,wert,min){
    	var url		= new mUrl(document.URL);

        var basedomain	= url.domain.main+"."+url.domain.tld;

		// Gültigkeit
        var date	= new Date();
        var valid	= date.getTime()+1000*60*min;
        date.setTime(valid);
        
        // Cookie setzen
        if(min==0){
            document.cookie	= name+"="+wert+";path=/;domain=."+basedomain;
        }else{
            document.cookie	= name+"="+wert+";path=/;domain=."+basedomain+";expires="+date.toGMTString();
        }
    };
} // ENDE: Cookie