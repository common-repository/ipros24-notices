
(function (ipros24_notices, $)	{

	"use strict";

	$.jQuery_ajax = $.ajax;
	$.ajax = function (settings)	{

		if ("fake_response" in settings)	{

			if (typeof settings.success == "function")
				settings.success (settings.fake_response);

			if (typeof settings.complete == "function")
				settings.complete ();

			return this.Deferred ().resolve (settings.fake_response).promise ();
		}
		else
			return this.jQuery_ajax (settings);
	}

	$ (document).ready (function ()	{

		$ (".ipros24-list tbody[data-wp-lists]").each (function ()	{

			var self;

			$ (this).wpList ({

				get: function (settings)	{

					return $ ("tbody#" + settings.what + "-list td.left:visible").map (function ()	{
						return $ (this).text ();
					}).get ();
				},

				addBefore: function (settings)	{

					var val, tmp;

					val = ipros24_utils.get_arg (settings.data.replace (/\+/g, "%20"), settings.what + "-value");
					tmp = ipros24_notices.default_conditions.concat (this.get (settings)).map (function (v) { return v.sanitize_key (); });

					if (val.match (settings.VALID) && tmp.indexOf (val.sanitize_key ()) == -1)	{

						settings.fake_response = $.parseXML (

							"<wp_ajax>" +
								"<response>" +
									"<" + settings.what + ">" +
										"<response_data>" +
										"<![CDATA[" +
											"<tr id='" + settings.what + "-" + self.cnt + "'>" +
												"<td class='left'>" + val + "</td>" +
												"<td><input type='submit' class='button button-small' value='" + ipros24_notices.DELETE + "' data-wp-lists='delete:" + settings.what + "-list:" + settings.what + "-" + self.cnt + "::_ajax_nonce=XXX' /></td>" +
												"<td>" + val.sanitize_key () + "</td>" +
											"</tr>" +
										"]]>" +
										"</response_data>" +
									"</" + settings.what + ">" +
								"</response>" +
							"</wp_ajax>"
						);

						self.cnt++;
					}
					else

						settings.fake_response = $.parseXML (

							"<wp_ajax>" +
								"<response>" +
									"<" + settings.what + ">" +
										"<wp_error code='0'><![CDATA[" + ipros24_notices.INVALID_VALUE + "]]></wp_error>" +
									"</" + settings.what + ">" +
								"</response>" +
							"</wp_ajax>"
						);

					return settings;
				},

				addAfter: function (response, settings)	{

					if (!settings.parsed || settings.parsed.errors)
						return;

					$ ("#" + settings.what + "-input").val (this.get (settings).join ());
				},

				delBefore: function (settings)	{

					settings.fake_response = "1";
					return settings;
				},

				delAfter: function (response, settings)	{

					if (!settings.parsed || settings.parsed.errors)
						return;

					$ ("#" + settings.what + "-input").val (_.without (this.get (settings), $ ("#" + settings.element + " td.left").text ()).join ());
				}
			});

			self = this.wpList.settings;

			self.response = self.what + "-ajax-response";
			self.VALID = /^[a-z_][a-z0-9_ ]*$/i;
			self.cnt = self.get (self).length;
		});

		$ (".ipros24-list tbody[data-wp-lists] tr td:nth-child(1)").addClass ("notranslate ipros24-ltr");
		$ (".ipros24-list tbody[data-wp-lists] tr td:nth-child(3)").addClass ("notranslate ipros24-ltr");
	});

}) (window.ipros24_notices = window.ipros24_notices || {}, jQuery);

