<?php

if (!isset ($is_mobile))
	$is_mobile = wp_is_mobile ();

if (!isset ($is_ajax))
	$is_ajax = isset ($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest";

if (!isset ($is_konq))
	$is_konq = isset ($_SERVER["HTTP_USER_AGENT"]) && strpos ($_SERVER["HTTP_USER_AGENT"], "Konqueror") !== FALSE;

if (!isset ($META_SLASH))
	$META_SLASH = "";

if (!isset ($LINK_SLASH))
	$LINK_SLASH = "";

if (!function_exists ("encodeURI")) :

	function encodeURI ($uri)	{

		$revert = array (

			// reserved characters
			"%3B" => ";", "%2C" => ",", "%2F" => "/", "%3F" => "?", "%3A" => ":",
			"%40" => "@", "%26" => "&", "%3D" => "=", "%2B" => "+", "%24" => "$",

			// unescaped characters
			"%2D" => "-", "%5F" => "_", "%2E" => ".", "%21" => "!", "%7E" => "~",
			"%2A" => "*", "%27" => "'", "%28" => "(", "%29" => ")",

			// number sign
			"%23" => "#"
		);

		return strtr (rawurlencode ($uri), $revert);
	}

endif;

if (!function_exists ("encodeURIComponent")) :

	function encodeURIComponent ($str)	{

		$revert = array (

			// unescaped characters
			"%2D" => "-", "%5F" => "_", "%2E" => ".", "%21" => "!", "%7E" => "~",
			"%2A" => "*", "%27" => "'", "%28" => "(", "%29" => ")"
		);

		return strtr (rawurlencode ($str), $revert);
	}

endif;

if (!function_exists ("ipros24_get_post_thumbnail_id")) :

	function ipros24_get_post_thumbnail_id ($post_id = NULL)	{

		$post_thumbnail_id = FALSE;
		if (has_post_thumbnail ($post_id))
			$post_thumbnail_id = get_post_thumbnail_id ($post_id);
		else
		if (wp_attachment_is_image ($post_id))
			$post_thumbnail_id = $post_id;

		return $post_thumbnail_id;
	}

endif;

if (!function_exists ("get_post_thumbnail")) :

	function get_post_thumbnail ($post_id = NULL, $size = "full")	{

		$post_thumbnail_id = ipros24_get_post_thumbnail_id ($post_id);
		return $post_thumbnail_id !== FALSE ? wp_get_attachment_image_url ($post_thumbnail_id, $size) : FALSE;
	}

endif;

if (!function_exists ("ipros24_get_post_video_id")) :

	function ipros24_get_post_video_id ($post_id = NULL)	{

		global $post;

		if (!$post_id && $post)
			$post_id = $post->ID;

		$attachments = array ();
		if ($post_id)

			$attachments = get_children (array (

				"post_parent"	=> $post_id,

				"post_status"	=> "inherit",
				"post_type"	=> "attachment",

				"post_mime_type"	=> "video",
				"posts_per_page"	=> 1
			));

		$attachments = array_values ($attachments);
		return $attachments ? $attachments[0]->ID : FALSE;
	}

endif;

if (!function_exists ("get_post_video")) :

	function get_post_video ($post_id = NULL)	{

		$post_video_id = ipros24_get_post_video_id ($post_id);
		return $post_video_id !== FALSE ? wp_get_attachment_url ($post_video_id) : FALSE;
	}

endif;

if (!function_exists ("is_bbpress")) :

	function is_bbpress ()	{

		return FALSE;
	}

endif;

if (!class_exists ("iPROS24_Utils")) :

	class iPROS24_Utils	{

		const	LOG_NONE = -1,
			LOG_ERROR = 0,
			LOG_DEBUG = 1,
			LOG_TRACE = 2;

		public static $log_level = self::LOG_ERROR;
		protected static $BOTS = array (

			"facebookexternalhit",

			"YaBrowser",
			"vkShare",

			"Googlebot",
			"Google Favicon",
			"Google Web Preview",
			"Google (+https://developers.google.com/+/web/snippet/)",
			"Lighthouse",

			"Twitterbot",
			"LinkedInBot",
			"Pinterestbot",

			"Wget",
			"CCBot",
			"DotBot",
			"MJ12bot",
			"MauiBot",
			"Riddler",
			"AhrefsBot",
			"SeznamBot",
			"SemrushBot",
			"LinkpadBot",
			"Baiduspider",
			"ExtLinksBot",
			"Mail.RU_Bot",
			"MegaIndex.ru",
			"statdom.ru/Bot",
			"openstat.ru/Bot",

			"bingbot",
			"BingPreview",

			"YandexBot",
			"YandexMobileBot",
			"YandexImages",
			"YandexMetrika"
		);

		public static function log ($message, $level = self::LOG_ERROR)	{

			if ($level > self::$log_level)
				return;

			$backtrace = version_compare (PHP_VERSION, "5.4.0") < 0 ? debug_backtrace (0) : debug_backtrace (0, 1);
			error_log ("[".basename ($backtrace[0]["file"]).":".$backtrace[0]["line"]."]".$message);
		}

		public static function debug_backtrace ()	{

			$backtrace = debug_backtrace ();
			$backtrace = array_map (function ($a)	{

				if (isset ($a["file"]) && isset ($a["line"]))
					$a = preg_replace ("/^".preg_quote (ABSPATH, "/")."/", "", $a["file"]).":".$a["line"];
				else
				if (isset ($a["class"]) && isset ($a["type"]) && isset ($a["function"]))
					$a = $a["class"].$a["type"].$a["function"]." ()";
				else
				if (isset ($a["function"]))
					$a = $a["function"]." ()";

				return $a;
			}, $backtrace);

			self::log (" debug_backtrace = ".print_r ($backtrace, TRUE));
		}

		public static function start_session ()	{

			if (!session_id ())
				session_start ();
		}

		public static function is_bot ($user_agent)	{

			$found = FALSE;
			foreach (self::$BOTS as $bot)
				if (strpos ($user_agent, $bot) !== FALSE)	{

					$found = TRUE;
					break;
				}

			return $found;
		}

		public static function is_yandex_bot ($user_agent)	{

			return self::is_bot ($user_agent) && strpos ($user_agent, "Yandex") !== FALSE;
		}

		public static function is_google_bot ($user_agent)	{

			return self::is_bot ($user_agent) && strpos ($user_agent, "Google") !== FALSE;
		}

		public static function get_query_arg ($key, $url)	{

			parse_str (parse_url ($url, PHP_URL_QUERY), $query);
			return isset ($query[$key]) ? $query[$key] : FALSE;
		}

		// matches javascript String.hash ()
		public static function hash ($str)	{

			$hash = 0;
			$len = mb_strlen ($str);

			for ($i = 0; $i < $len; $i++)

				// NB: ord doesn't work with UTF-8
				$hash  = (($hash << 5) - $hash + ord ($str[$i])) & 0xFFFFFFFF;

			$hash = ($hash + 0x7FFFFFFF + 1) & 0xFFFFFFFF;
			return $hash;
		}
	}

endif;

if (!isset ($is_bot))
	$is_bot = isset ($_SERVER["HTTP_USER_AGENT"]) && iPROS24_Utils::is_bot ($_SERVER["HTTP_USER_AGENT"]);

if (!isset ($is_yandex_bot))
	$is_yandex_bot = isset ($_SERVER["HTTP_USER_AGENT"]) && iPROS24_Utils::is_yandex_bot ($_SERVER["HTTP_USER_AGENT"]);

if (!isset ($is_google_bot))
	$is_google_bot = isset ($_SERVER["HTTP_USER_AGENT"]) && iPROS24_Utils::is_google_bot ($_SERVER["HTTP_USER_AGENT"]);

if (!class_exists ("iPROS24_WP_Plugin")) :

	class iPROS24_WP_Plugin	{

//		static $text_domain;

		protected $options_page;

		protected $plugin_dir_path;
		protected $plugin_dir_url;

		public static function update_post_meta ($post_id, $meta_key, $meta_value, $prev_value = "")	{

			if ($meta_value)
				update_post_meta ($post_id, $meta_key, $meta_value, $prev_value);
			else
				delete_post_meta ($post_id, $meta_key, $prev_value);
		}

		public static function update_user_meta ($post_id, $meta_key, $meta_value, $prev_value = "")	{

			if ($meta_value)
				update_user_meta ($post_id, $meta_key, $meta_value, $prev_value);
			else
				delete_user_meta ($post_id, $meta_key, $prev_value);
		}

		function __construct ()	{

			$this->options_page = static::$text_domain."-options-page";
		}

		function checkbox_field_callback ($args)	{

			$field = "";
			foreach ($args["field"] as $f)	{

				if ($field)
					$field .= "<br />";
			
				$field .=
					"<label for='".esc_attr ($f["name"])."'>".
						"<input ".
							"type='checkbox' ".
							"id='".esc_attr ($f["name"])."' ".
							"name='".esc_attr ($f["name"])."' ".
							"value='1' ".
							checked ("1", $f["value"], FALSE).
						"/>"." ".

						esc_html ($f["label"]).
					"</label>";
			}

			if (isset ($args["legend"]) && $args["legend"])
				$field = "<legend class='screen-reader-text'><span>".esc_html ($args["legend"])."</span></legend>".$field;

			if (isset ($args["description"]) && $args["description"])
				$field = $field."<br />"."<p class='description'>".esc_html ($args["description"])."</p>";

			echo	"<fieldset>".$field."</fieldset>";
		}

		function radio_field_callback ($args)	{

			$field = "";
			foreach ($args["field"] as $f)	{

				if ($field)
					$field .= "<br />";
			
				$field .=
					"<label>".
						"<input ".
							"type='radio' ".
							"name='".esc_attr ($f["name"])."' ".
							"value='".esc_attr ($f["value"])."' ".

							(isset ($f["attributes"]) ? $f["attributes"] : "").
						"/>"." ".

						esc_html ($f["label"]).
					"</label>";
			}

			if (isset ($args["legend"]) && $args["legend"])
				$field = "<legend class='screen-reader-text'><span>".esc_html ($args["legend"])."</span></legend>".$field;

			if (isset ($args["description"]) && $args["description"])
				$field = $field."<br />"."<p class='description'>".esc_html ($args["description"])."</p>";

			echo	"<fieldset>".$field."</fieldset>";
		}

		function text_field_callback ($args)	{

			$field = "";
			foreach ($args["field"] as $f)

				$field .=
					"<tr>".
						($f["label"] ? "<td><label for='".esc_attr ($f["name"])."'>".esc_html ($f["label"])."</label></td>" : "").
						"<td ".($f["label"] ? "" : "colspan='2' ").">".
							"<input ".
								"type='text' ".
								"class='regular-text' ".
								"id='".esc_attr ($f["name"])."' ".
								"name='".esc_attr ($f["name"])."' ".
								"value='".esc_attr ($f["value"])."' ".

								(isset ($f["attributes"]) ? $f["attributes"] : "").
							"/>".
						"</td>".
					"</tr>";

			echo "<table class='ipros24-wp-plugin-option'>".$field."</table>";
			if (isset ($args["description"]) && $args["description"])
				echo "<p class='description'>".esc_html ($args["description"])."</p>";
		}

		function textarea_field_callback ($args)	{

			echo	"<textarea ".
					"class='large-text code' ".
					"id='".esc_attr ($args["name"])."' ".
					"name='".esc_attr ($args["name"])."' ".

					(isset ($args["attributes"]) ? $args["attributes"] : "").
				">".
					esc_html ($args["value"]).
				"</textarea>";
		}

		function select_field_callback ($args)	{

			$opt = "";
			foreach ($args["option"] as $v => $o)

				$opt .=	"<option ".
						"value='".esc_attr ($v)."' ".
						($v == $args["value"] ? "selected='selected' " : "").
					">".esc_html ($o)."</option>";

			echo	"<select ".
					"id='".esc_attr ($args["name"])."' ".
					"name='".esc_attr ($args["name"])."' ".
				">".$opt."</select>";
		}

		function number_field_callback ($args)	{

			$field = "";
			foreach ($args["field"] as $f)	{

				if ($field)
					$field .= "<br />";

				$field .=
					"<input ".
						"type='number' ".
						"class='small-text' ".
						"id='".esc_attr ($f["name"])."' ".
						"name='".esc_attr ($f["name"])."' ".
						"value='".esc_attr ($f["value"])."' ".

						(isset ($f["min"]) && $f["min"] ? "min='".esc_attr ($f["min"])."' " : "").
						(isset ($f["max"]) && $f["max"] ? "max='".esc_attr ($f["max"])."' " : "").

						(isset ($f["step"]) && $f["step"] ? "step='".esc_attr ($f["step"])."' " : "").
						(isset ($f["attributes"]) ? $f["attributes"] : "").
					"/>".

					esc_html ($f["label"] ? " ".$f["label"] : "");
			}

			echo $field;
			if (isset ($args["description"]) && $args["description"])
				echo "<p class='description'>".esc_html ($args["description"])."</p>";
		}

		function enqueue_utils ()	{

			global $is_chrome, $is_safari, $is_iphone, $is_gecko, $is_opera, $is_konq, $is_IE, $is_mobile, $is_ajax, $is_bot;
			static $enqueue_utils = TRUE;

//			if (!file_exists ($this->plugin_dir_path."assets/js/utils.js"))
//				return;

			if ($enqueue_utils)	{

				wp_enqueue_script ("ipros24-utils-script", $this->plugin_dir_url."assets/js/utils.js", array ("jquery", "underscore", "wp-hooks"));
				wp_localize_script ("ipros24-utils-script", "ipros24_utils", array (

					"COOKIE_DOMAIN"	=> COOKIE_DOMAIN,

					"COOKIEPATH"	=> COOKIEPATH,
					"COOKIEHASH"	=> COOKIEHASH,

					"is_chrome"	=> $is_chrome,
					"is_safari"	=> $is_safari,
					"is_iphone"	=> $is_iphone,
					"is_gecko"	=> $is_gecko,
					"is_opera"	=> $is_opera,
					"is_konq"	=> $is_konq,
					"is_IE"		=> $is_IE,

					"is_mobile"	=> $is_mobile,
					"is_ajax"	=> $is_ajax,
					"is_bot"	=> $is_bot,

					"ajaxurl"	=> admin_url ("admin-ajax.php", "relative")
				));

				$enqueue_utils = FALSE;
			}
		}

		function wp_enqueue_scripts ()	{

			$theme = get_stylesheet ();

			if (file_exists ($this->plugin_dir_path."assets/js/utils.js"))	{

				self::enqueue_utils ();

				$deps = array ("ipros24-utils-script");
			}
			else
				$deps = array ("jquery", "underscore", "wp-hooks");

			if (file_exists ($this->plugin_dir_path."assets/js/my.js"))
				wp_enqueue_script (static::$text_domain."-script", $this->plugin_dir_url."assets/js/my.js", $deps);

			if (file_exists ($this->plugin_dir_path."assets/css/my.css"))
				wp_enqueue_style (static::$text_domain."-style", $this->plugin_dir_url."assets/css/my.css", array ("dashicons"));

			if (file_exists ($this->plugin_dir_path."assets/css/my-".$theme.".css"))
				wp_enqueue_style (static::$text_domain."-".$theme."-style", $this->plugin_dir_url."assets/css/my-".$theme.".css");

			if (is_rtl ())	{

				if (file_exists ($this->plugin_dir_path."assets/css/my-rtl.css"))
					wp_enqueue_style (static::$text_domain."-rtl-style", $this->plugin_dir_url."assets/css/my-rtl.css");

				if (file_exists ($this->plugin_dir_path."assets/css/my-".$theme."-rtl.css"))
					wp_enqueue_style (static::$text_domain."-".$theme."-rtl-style", $this->plugin_dir_url."assets/css/my-".$theme."-rtl.css");
			}
		}

		function plugin_action_links ($actions)	{

//			iPROS24_Utils::log (" actions = ".print_r ($actions, TRUE));

			$actions["settings"] =	"<a href='".esc_url (add_query_arg ("page", $this->options_page, admin_url ("options-general.php")))."'>".
							esc_html__ ("Settings", static::$text_domain).
						"</a>";
			return $actions;
		}
	}

endif;

