warn_reqi = "Необходима поддержка ActiveX";
warn_reqm = "Необходимо обновить браузер";
var requ;
var req;

function _getXMLHttp(){
	var XMLHttp = null;
	if (window.XMLHttpRequest){
		try{
			XMLHttp = new XMLHttpRequest();
		} catch (e) {
			alert(warn_reqm);
		}
	} else if (window.ActiveXObject) {
		try {
			XMLHttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				XMLHttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {
				alert(warn_reqi);
			}
		}
	} else alert(warn_reqm);
	return XMLHttp;
};

function _loadXML(url){
  requ = _getXMLHttp();
  requ.open("POST",  url);
  requ.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  return requ;
};

function ajax_get_date(onData,par){
 	req_url = "mysql.php";
 	requ 	= _loadXML(req_url);
   	requ.onreadystatechange = function(){
     if (requ.readyState == 4 && requ.status == 200){
     onData(requ);}};
   	requ.send(par);
};

function onReq(req){
	var div_date              = document.getElementById('showdate');
	div_date.innerHTML        = req.responseText;
};

function show_access(dbname){
  var div_show_form           = document.getElementById('div_show_form');
  div_show_form.style.left    = (parseInt(document.body.clientWidth) - parseInt(div_show_form.style.width)) / 2;
  var string = "actions=getAssecc&basename="+dbname;
//  ajax_get_date(onReq,encodeURI(string));
  div_show_form.style.display = 'block';
};

function hide_access(){
  var div_show_form           = document.getElementById('div_show_form');
  div_show_form.style.display = 'none';
  
};
