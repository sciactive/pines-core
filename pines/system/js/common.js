/* 
 * Pines Common JavaScript
 */
// Make sure arrays have an indexOf method.
if (!Array.prototype.indexOf)
{
  Array.prototype.indexOf = function(elt /*, from*/)
  {
    var len = this.length >>> 0;

    var from = Number(arguments[1]) || 0;
    from = (from < 0)
         ? Math.ceil(from)
         : Math.floor(from);
    if (from < 0)
      from += len;

    for (; from < len; from++)
    {
      if (from in this &&
          this[from] === elt)
        return from;
    }
    return -1;
  };
}

var pines = {
	full_location: "",
	rela_location: "",
	loadedjs: [],
	loadedcss: [],
	get: function(url, params){
		if (params) {
			url += (url.indexOf("?") == -1) ? "?" : "&";
			var parray = [];
			for (var i in params) {
				if (params.hasOwnProperty(i)) {
					if (encodeURIComponent)
						parray.push(encodeURIComponent(i)+"="+encodeURIComponent(params[i]));
					else
						parray.push(escape(i)+"="+escape(params[i]));
				}
			}
			url += parray.join("&");
		}
		window.location = url;
	},
	post: function(url, params){
		var form = document.createElement("form");
		form.action = url;
		form.method = "POST";
		for (var i in params) {
			if (params.hasOwnProperty(i)) {
				var input = document.createElement("input");
				input.type = "hidden";
				input.name = i;
				input.value = params[i];
				form.appendChild(input);
			}
		}
		document.body.appendChild(form);
		form.submit();
	},
	loadjs: function(filename, multiple){
		if (this.loadedjs.indexOf(filename) > -1 && !multiple) return;
		var n=document.createElement("script");
		n.setAttribute("type","text/javascript");
		n.setAttribute("src", filename);
		if (typeof n!="undefined")
			document.getElementsByTagName("head")[0].appendChild(n);
		this.loadedjs[this.loadedjs.length]=filename;
	},
	loadcss: function(filename, multiple){
		if (this.loadedcss.indexOf(filename) > -1 && !multiple) return;
		var n=document.createElement("link");
		n.setAttribute("type","text/css");
		n.setAttribute("rel", "stylesheet");
		n.setAttribute("href", filename);
		if (typeof n!="undefined")
			document.getElementsByTagName("head")[0].appendChild(n);
		this.loadedcss[this.loadedcss.length]=filename;
	},
	alert: function(message, title){
		alert((title ? title : "Alert") + "\n\n" + message);
	},
	error: function(message, title){
		alert((title ? title : "Error") + "\n\n" + message);
	}
};