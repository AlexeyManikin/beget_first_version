var req;
var requ = Array();
var callbacks = Array()

function loadXML(func, url)
{
  if (window.XMLHttpRequest)  requ[func] = new XMLHttpRequest();
  else if (window.ActiveXObject)  requ[func] = new ActiveXObject("Microsoft.XMLHTTP");
  else
    if(document.all)  alert(warn_reqi)
    else  alert(warn_reqm)

  requ[func].open('post',  url);
  requ[func].setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  return requ[func];
}; 

function do_call(method, args, callback)
{
  req_url = '?ajax&method='+method;
  response = '';
  requ[method] = loadXML(method, req_url);
  callbacks[method] = callback
  requ[method].onreadystatechange = function(){if (requ[method].readyState == 4
                                && requ[method].status == 200){ callbacks[method](requ[method]);}};
  requ[method].send(args.join("&")); 
};
