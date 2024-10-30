
(function (ipros24_utils, $)	{

	"use strict";

	ipros24_utils = _.defaults (ipros24_utils, {

		REPLACE: false,

		popup_NX: 640,
		popup_NY: 320,

		DX: 10,
		DY: 10,

		RESIZE_WAIT: 200,
		SCROLL_WAIT: 100
	});

	ipros24_utils.is_IE7 = ipros24_utils.is_IE && /MSIE 7/.test (navigator.userAgent);
	ipros24_utils.is_IE8 = ipros24_utils.is_IE && /MSIE 8/.test (navigator.userAgent);
	ipros24_utils.is_IE9 = ipros24_utils.is_IE && /MSIE 9/.test (navigator.userAgent);

	ipros24_utils.log = typeof console != "undefined" ? console.log : function () {};

	window.my_responsive = window.my_responsive || {keep_visible: false};

	var	popup_NAME = "ipros24_utils_popup",
		popup_FEATURES = "toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no",

		PARSEURL = "^([^\\?#]*)(\\?[^#]*)?(#.*)?";

	String.prototype.sanitize_key = function (sp)	{

		return this.toLowerCase ().replace (/ /g, sp || "_");
	}

	String.prototype.hash = function ()	{

		var hash, i, len;

		hash = 0;
		len = this.length;

		for (i = 0; i < len; i++)
			hash  = ((hash << 5) - hash + this.charCodeAt (i)) & 0xffffffff;

		hash = (hash + 0x7fffffff + 1);
		return hash;
	}

	RegExp.escape = function (str)	{

		return String (str).replace (/([\\.+*?\[^\]$(){}=!<>|:\/])/g, "\\$1");
	}

	ipros24_utils.typeof = function (obj)	{

		var i;

		for (i = 1; i < arguments.length && typeof obj != "undefined"; i++)
			obj = obj[arguments[i]];

		return typeof obj;
	}

	ipros24_utils.parse_url = function (url)	{

		var a;

		a = document.createElement ("a");
		a.href = url;

		// IE11
		if (a.pathname[0] != "/")
			a.pathname = "/" + a.pathname;

		return a;
	}

	ipros24_utils.is_portrait = function ()	{

		return window.innerHeight > window.innerWidth;
	}

	ipros24_utils.basename = function (str, ext)	{

		if (ext)
			str = str.replace (new RegExp (RegExp.escape (ext) + "$"), "");

		return str.replace (/^.*\//, "");
	}

	ipros24_utils.quote = function (str, ch)	{

		return str.replace (new RegExp ("\\" + ch, "g"), "\\" + ch);
	}

	ipros24_utils.set_cookie = function (name, value, expires, path, domain, secure)	{

		expires = parseInt (expires);
		expires = isNaN (expires) ? "" : new Date (new Date ().getTime () + expires * 1000).toUTCString ();

//		this.log ("set_cookie: expires = " + expires);

		document.cookie = name + "=" + escape (value) +
			(expires ? "; expires=" + expires : "") +
			(path ? "; path=" + path : "") +
			(domain ? "; domain=" + domain : "") +
			(secure ? "; secure" : "");
	}

	ipros24_utils.del_cookie = function (name, path, domain, secure)	{

		this.set_cookie (name, "", -3600, path, domain, secure);
	}

	ipros24_utils.get_cookie = function (name)	{

		var val;

		val = document.cookie.match (new RegExp ("(?:^|;) *" + name + "=([^;]*)"));
		return val ? unescape (val[1]) : "";
	}

	ipros24_utils.get_arg = function (url, name)	{

		var tmp;

		tmp = url.match (new RegExp ("(?:\\?|&)" + name + "=([^&#]*)"));
		return tmp ? unescape (tmp[1]) : "";
	}

	ipros24_utils.del_arg = function (url, name)	{

		var tmp;

		tmp = url.match (new RegExp (PARSEURL));

		tmp[2] = tmp[2] ? tmp[2].replace (new RegExp ("(\\?|&)" + name + "(=[^&]*|(?=&)|$)", "g"), "").replace (new RegExp ("^&"), "?") : "";
		tmp[3] = tmp[3] ? tmp[3] : "";

		return tmp[1] + tmp[2] + tmp[3];
	}

	ipros24_utils.add_arg = function (url, name, value)	{

		var tmp;

		url = this.del_arg (url, name);
		tmp = url.match (new RegExp (PARSEURL));

		tmp[2] = (tmp[2] ? tmp[2] + "&" : "?") + name + (value ? "=" + encodeURIComponent (value) : "");
		tmp[3] = tmp[3] ? tmp[3] : "";

		return tmp[1] + tmp[2] + tmp[3];
	}

	ipros24_utils.is_svg_supported = function ()	{

		return typeof SVGRect != "undefined";
	}

	ipros24_utils.is_image = function (url)	{

		return /\.(bmp|gif|ico|jpeg|jpg|png|tif|tiff)$/i.test (url.match (new RegExp (PARSEURL))[1]);
	}

	ipros24_utils.is_audio = function (url)	{

		return /\.(m4a|mp3|ogg)$/i.test (url.match (new RegExp (PARSEURL))[1]);
	}

	ipros24_utils.is_video = function (url)	{

		return /\.(3gp|3gpp|asf|asx|avi|flv|mov|mp4|mpeg|mpg|wmv)$/i.test (url.match (new RegExp (PARSEURL))[1]);
	}

	ipros24_utils.prop = function (obj, name)	{

		var val;

		try	{

			val = obj[name];
		}
		catch (e)	{

			val = "";
		}

//		this.log ("prop: val = " + val);

		return val;
	}

	ipros24_utils.load_safe = function (url, replace)	{

		if (typeof replace == "undefined")
			replace = this.REPLACE;

		window.location[replace ? "replace" : "assign"] (url);
	}

	ipros24_utils.load = function (url, replace)	{

		if (typeof replace == "undefined")
			replace = this.REPLACE;

		if (this.typeof (window.ipros24_smooth_loading, "load") != "undefined")
			ipros24_smooth_loading.load (url, replace);
		else
			this.load_safe (url, replace);
	}

	ipros24_utils.to_history = function (url, replace)	{

		var ret;

		if (typeof replace == "undefined")
			replace = this.REPLACE;

		// IE8
		try	{

			window.history[replace ? "replaceState" : "pushState"] ({}, "", url);
			ret = true;
		}
		catch (e)	{

			ret = false;
		}

		return ret;
	}

	ipros24_utils.popup = function (url, name)	{

		var x, y, nx, ny;

		name = name || popup_NAME;

		nx = Math.min (this.popup_NX, screen.width - this.DX);
		ny = Math.min (this.popup_NY, screen.height - this.DY);

		x = (screen.width - nx) / 2;
		y = (screen.height - ny) / 2;

		window.open (url, name, popup_FEATURES + ", left=" + x + ", top=" + y + ", width=" + nx + ", height=" + ny);
	}

	// polyfills

	if (!Array.prototype.indexOf)	{

		Array.prototype.indexOf = function (searchElement, fromIndex)	{

			var i, n, len;

			if (this == null)
				throw new TypeError ("this is null or not defined");

			len = this.length;
			if (len == 0)
				return -1;

			n = fromIndex || 0;
			if (n >= len)
				return -1;
		
			for (i = Math.max (n >= 0 ? n : len + n, 0); i < len; i++)

				if (i in this && this[i] === searchElement)
					return i;

			return -1;
		};
	}

	if (!Array.prototype.map)	{

		Array.prototype.map = function (callback)	{

			var T, A, i, len;

			if (this == null)
				throw new TypeError ("this is null or not defined");

			if (typeof callback != "function")
				throw new TypeError (callback + " is not a function");

			if (arguments.length > 1)

				// thisArg
				T = arguments[1];

			len = this.length;
			A = new Array (len);

			for (i = 0; i < len; i++)

				if (i in this)
					A[i] = callback.call (T, this[i], i, this);

			return A;
		};
	}

	if (typeof $ == "undefined")
		return;

	// using jQuery

	ipros24_utils.trigger = function (event, parameters)	{

		$ (window).trigger (event, parameters);
	}

	ipros24_utils.trigger_resize = function (parameters)	{

		var keep;

		keep = my_responsive.keep_visible;

		my_responsive.keep_visible = true;
		this.trigger ("resize", parameters);
		my_responsive.keep_visible = keep;
	}

	ipros24_utils.resize_to_content = function ()	{

		var y0, y, ny, win;

		win = window.top;

//		this.log ("resize_to_content: win = " + $ (win).width () + "x" + $ (win).height ());

		if ($ (document).height () > $ (win).height ())	{

			y0 = win.screenTop || win.screenY;

			ny = Math.min ($ (document).height (), screen.height - this.DY);
			y = (screen.height - ny) / 2;

			win.moveBy (0, y - y0);
			win.resizeBy (0, ny - $ (win).height ());
		}
	}

	ipros24_utils.load_script = function (src, callback, target)	{

		var self, $el;

		self = this;

		$el = $ ("<script></script>");
		$el.on ("load", function ()	{

//			self.log ("load_script: " + src + " loaded");

			if (typeof callback == "function")
				callback ();
		});

		$el.appendTo (target || "body");
		$el.attr ("src", src);
	}

	ipros24_utils.add_style = function (style, id)	{

		style = (id ? "<style id='" + id + "'>" : "<style>") + style + "</style>";

		if (id && $ ("#" + id).length)
			$ ("#" + id).replaceWith (style);
		else
			$ (style).appendTo ("head");
	}

	$ (document).ready (function ()	{

		ipros24_utils.is_rtl	= $ ("html").attr ("dir") == "rtl";
		ipros24_utils.is_home	= $ ("body").hasClass ("home");

		$ (window).on ("load.ipros24-utils", function ()	{

			if (!wp.hooks.didAction ("ipros24-load"))
				wp.hooks.doAction ("ipros24-load");
		});

		$ (window).on ("unload.ipros24-utils", function ()	{

			if (!wp.hooks.didAction ("ipros24-unload"))
				wp.hooks.doAction ("ipros24-unload");
		});

		$ (window).on ("resize.ipros24-utils", _.debounce (function ()	{

			wp.hooks.doAction ("ipros24-resize");

		}, ipros24_utils.RESIZE_WAIT));

		$ (window).on ("scroll.ipros24-utils", _.throttle (function ()	{

			wp.hooks.doAction ("ipros24-scroll");

		}, ipros24_utils.SCROLL_WAIT, { leading: false }));

		$ (window).on ("hashchange.ipros24-utils", function ()	{

			wp.hooks.doAction ("ipros24-hashchange");
		});
	});

}) (window.ipros24_utils = window.ipros24_utils || {}, typeof jQuery != "undefined" ? jQuery : undefined);

