
(function (ipros24_notices, $)	{

	"use strict";

	ipros24_notices.browser_supported = ipros24_utils.is_svg_supported ();
	ipros24_notices = _.defaults (ipros24_notices, {EXPIRES: 30*24*60*60});

	$ (document).ready (function ()	{

		var html, i, condition;

		ipros24_utils.trigger ("ipros24-notices-filter");

		html = $ ("html");

		html.addClass (ipros24_utils.get_cookie (ipros24_notices.COOKIE_TEST) ? "ipros24-cookies" : "ipros24-no-cookies");
		html.addClass (ipros24_notices.browser_supported ? "ipros24-browser-supported" : "ipros24-browser-not-supported");

		for (i in ipros24_notices.conditions)	{

			condition = String (ipros24_notices.conditions[i]);
			html.addClass ((ipros24_notices[condition.sanitize_key ()] ? "ipros24-" : "ipros24-not-") + condition.sanitize_key ("-"));
		}

		wp.hooks.addAction ("ipros24-load", "ipros24-notices", function ()	{

			$ (".ipros24-notices .ipros24-close, .mobile .ipros24-notices > div > div").on ("click.ipros24-notices", function (event)	{

				var notice, id, ids;

				notice = $ (this).parents ("div").eq (0);
				id = notice.attr ("ipros24-id");

				ids = ipros24_utils.get_cookie (ipros24_notices.COOKIE_NAME);
				ids = ids ? ids.split (",") : [];

				if (ids.indexOf (id) == -1)
					ids[ids.length] = id;

				ids = ids.join (",");
				ipros24_utils.set_cookie (ipros24_notices.COOKIE_NAME, ids, ipros24_notices.EXPIRES, ipros24_utils.COOKIEPATH, ipros24_utils.COOKIE_DOMAIN);

//				ipros24_utils.log ("onclick: document.cookie = " + document.cookie);

				notice.css ("height", notice.css ("height"));
				notice.slideUp ();
			});
		});
	});

}) (window.ipros24_notices = window.ipros24_notices || {}, jQuery);

