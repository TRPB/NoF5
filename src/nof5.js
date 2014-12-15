var xmlHttp = new XMLHttpRequest();
var requests = [];

var requestId = Math.floor((Math.random() * 10000) + 1);

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

function registerFile(name) {
	ajaxRequest(window.location.href + '?nof5=registerFile&nof5arg[]=' + encodeURIComponent(name) + '&nof5id=' + requestId);
}

document.addEventListener("DOMContentLoaded", function(event) {
	var base = document.getElementsByTagName('base').length > 0 ? document.getElementsByTagName('base')[0].href : window.location.protocol + '//' + window.location.host + window.location.pathname; 

	var links = document.getElementsByTagName('link');	
	for (var i = 0; i < links.length; i++) {
		if (links[i].rel == 'stylesheet') registerFile(links[i].href.replace(base, ''));		  
	}	
	var scripts = document.getElementsByTagName('script');
	for (var i = 0; i < scripts.length; i++) {
		registerFile(scripts[i].src.replace(base, ''));		  
	}	
});


//Override setInterval and setTimeout so they can be cleared when JS is reset
var intervals = [];
var timeouts = [];

window.oldSetInterval = window.setInterval;
window.setInterval = function(func, int) {
	var id = window.oldSetInterval(func, int);
	intervals.push(id);
	return id;
}

window.oldSetTimeout = window.setTimeout;
window.setInterval = function(func, int) {
	var id = window.oldSetInterval(func, int);
	timeouts.push(id);
	return id;
}


var evtSource = new EventSource(window.location.href + '?nof5=monitor&nof5id=' + requestId);
evtSource.onmessage = function(e) {
	var files = JSON.parse(e.data);
		
	for (var file in files) {
		if (files[file] == '') continue;
		if (files[file].indexOf('.css') > -1) {
			var links = document.getElementsByTagName('link');
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
			//Clear all timeoughts
			for (var i = 0; i < timeouts.length; i++) clearTimeout(timeouts[i]);
			for (var i = 0; i < intervals.length; i++) clearInterval(intervals[i]);
			intervals = [];
			timeouts = [];
			
			//Replace the innerHTML, this is the simplest way to remove all event handlers
			//Without doing this, reloading a script adds an event listener will have the event added each time the script is changed -not what we want!
			document.body.innerHTML = document.body.innerHTML;

			//Now reload all scripts, can't only reload the one that was modified because the event listeners may not have been defined in that file			
			var scripts = document.getElementsByTagName('script');
			var oScripts = [];
			for (var i = 0; i < scripts.length; i++) {
				if (scripts[i].id != '__nof5')	{
					oScripts.push(scripts[i]);
				}
			}
			
			for (var i = 0; i < oScripts.length; i++) {
				var script = document.createElement('script');
				var symbol = oScripts[i].src.indexOf('?') > -1 ? '&' : '?'; 
				script.src = oScripts[i].src.split('nof5=')[0] + symbol + 'nof5=' + Math.random();
				document.getElementsByTagName('head')[0].appendChild(script);
				oScripts[i].parentNode.removeChild(oScripts[i]);
			}

		}
		else {
			//console.log(files);
			window.location.reload();
		}
	}
};