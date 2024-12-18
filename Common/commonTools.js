//==============================================================================
// COMMON TOOLS
//------------------------------------------------------------------------------
function getCurrentDateBatchFormatted() {
	const now = new Date();
  
	// Extract date components
	const year = now.getUTCFullYear();
	const month = String(now.getUTCMonth() + 1).padStart(2, "0"); // Months are zero-based
	const day = String(now.getUTCDate()).padStart(2, "0");
	const hours = String(now.getUTCHours()).padStart(2, "0");
	const minutes = String(now.getUTCMinutes()).padStart(2, "0");
	const seconds = String(now.getUTCSeconds()).padStart(2, "0");
  
	// Combine into the desired format
	return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}.000Z`;
  }
//-------------------------------------------------------------------------------
// This code empowers all input tags having a "placeholder" and "data-slots" attributes
function SetInputMaskMngt() {
	//document.addEventListener('DOMContentLoaded', () => {
		for (const el of document.querySelectorAll("[placeholder][data-slots]")) {
			const pattern = el.getAttribute("placeholder"),
				slots = new Set(el.dataset.slots || "_"),
				prev = (j => Array.from(pattern, (c,i) => slots.has(c)? j=i+1: j))(0),
				first = [...pattern].findIndex(c => slots.has(c)),
				accept = new RegExp(el.dataset.accept || "\\d", "g"),
				clean = input => {
					input = input.match(accept) || [];
					return Array.from(pattern, c =>
						input[0] === c || slots.has(c) ? input.shift() || c : c
					);
				},
				format = () => {
					const [i, j] = [el.selectionStart, el.selectionEnd].map(i => {
						i = clean(el.value.slice(0, i)).findIndex(c => slots.has(c));
						return i<0? prev[prev.length-1]: back? prev[i-1] || first: i;
					});
					el.value = clean(el.value).join``;
					el.setSelectionRange(i, j);
					back = false;
				};
			let back = false;
			el.addEventListener("keydown", (e) => back = e.key === "Backspace");
			el.addEventListener("input", format);
			el.addEventListener("focus", format);
			el.addEventListener("blur", () => el.value === pattern && (el.value=""));
		}
	//});
}

//-------------------------------------------------------------------------------
//Fonction permettant la lecture des fichiers .json
var getJSON = function(url, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
	xhr.responseType = "text";
	xhr.onload = function() {
	  var status = xhr.status;
	  if (status === 200) {
		callback(null, JSON.parse(xhr.response));
	  } else {
		callback(status, xhr.response);
	  }
	};
    xhr.send();
};

//-------------------------------------------------------------------------------
//Changement de la langue utilisée
function switchLang() {
	var selectedLang = parseInt(document.getElementById('langSelect').value);
	
	if(gLocalConfiguration) isSwitchingLang = true;
	var date = new Date();
	date.setTime(date.getTime()+2500000000);
	var expire = "; expire=" + date.toGMTString();
	
	document.cookie = "lang=" + selectedLang + expire + "; path=/";
	document.location.reload(true);
}

//-------------------------------------------------------------------------------
//Fonction récupérant la valeur du cookie de langue
function getLang() {	
	var langCookie = "lang=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(langCookie) == 0) {
			var langCookieValue = c.substring(langCookie.length,c.length);
			return parseInt(langCookieValue);
		}
	}
	return 0;
}

//-------------------------------------------------------------------------------
//Obtenir la variable de local setup
function getLocalConfiguration() {
	getJSON("configuration.json?v=" + (new Date()).getTime(),
		function(err, LocalConfigurations) {
			if (err !== null) {
				alert('Une erreur est survenue : ' + err);
			} else {
				gLocalConfiguration = LocalConfigurations.OutilLocalDeConfigurationOTA;
				refreshTiming = LocalConfigurations.refreshTimingOnGet;
				path = LocalConfigurations.pathPost;
			}
		}
	);
}

//================================================================================
// Some common global variables
var lang = getLang();
var gLocalConfiguration = false;
var refreshTiming = 100000;
var path = "";
var isSwitchingLang = false;
