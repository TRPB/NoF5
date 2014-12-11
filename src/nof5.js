var xmlHttp = new XMLHttpRequest();
var requests = [];


function nextRequest() {
	if (xmlHttp.readyState == 0 || xmlHttp.readyState == 4) {
		var url = requests.shift();
		if (url != undefined) {
			xmlHttp.open('GET', url, true);
			xmlHttp.send();	
			if (requests.length > 0) nextRequest();
		}
	}
}

xmlHttp.onreadystatechange = nextRequest;

function ajaxRequest(url) {
	requests.push(url);	
	nextRequest();
}

ajaxRequest(window.location.href + '?nof5=registerFiles');

function registerFile(name) {
	ajaxRequest(window.location.href + '?nof5=registerFile&nof5arg[]=' + encodeURIComponent(name));
}

document.addEventListener("DOMContentLoaded", function(event) { 
	  var links = document.getElementsByTagName('link');
	  for (var i = 0; i < links.length; i++) {
		  if (links[i].rel == 'stylesheet') registerFile(links[i].href.replace(window.location.href, ''));		  
	  }	
	  var scripts = document.getElementsByTagName('script');
	  for (var i = 0; i < scripts.length; i++) {
		 registerFile(scripts[i].src.replace(window.location.href, ''));		  
	  }	
});

var evtSource = new EventSource(window.location.href + '?nof5=monitor');
evtSource.onmessage = function(e) {
	var files = JSON.parse(e.data);
	console.log('tick');
	var links = document.getElementsByTagName('link');
	var scripts = document.getElementsByTagName('script');
	for (var file in files) {
		console.log(files[file]);
		if (files[file].indexOf('.css') > -1) {		
			for (var i = 0; i < links.length; i++) {		
				
				if (links[i].href.indexOf(files[file]) != -1) {
					var next = links[i].nextSibling;
					var parent = links[i].parentNode;
					
					
					var link = document.createElement('link');
					link.href = files[file] + '?' + Math.random();
					link.rel = 'stylesheet';
					
					if (next) parent.insertBefore(link, next);
					else parent.appendChild(link);
					parent.removeChild(links[i]);
					//and refresh the DOM
					
					link.onload = function() {
					document.body.focus();
					}
				}
			}
		}
		else if (files[file].indexOf('.js') > -1) {
			for (var i = 0; i < scripts.length; i++) {
				
				if (scripts[i].src.indexOf(files[file]) != -1) {
					var next = scripts[i].nextSibling;
					var parent = scripts[i].parentNode;					
					
					var script = document.createElement('script');
					script.src = files[file] + '?' + Math.random();
					
					if (next) parent.insertBefore(script, next);
					else parent.appendChild(script);
					parent.removeChild(scripts[i]);
					//and refresh the DOM					
					script.onload = function() {
						document.body.focus();
					}
				}
			}
		}
		else {
			window.location.reload();
		}		
	}	
};


