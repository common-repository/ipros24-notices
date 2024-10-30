<?php

/*
	Plugin Name: iPROS24 Notices
	Plugin URI: http://ipros24.ru/wordpress/#ipros24-notices
	Description: iPROS24 advanced notices for WordPress.
	Version: 1.7.2
	Author: A. Kirillov
	Author URI: http://ipros24.ru/
	License: GPL2
	Text Domain: ipros24-notices
	Domain Path: /languages
*/

if (!defined ("ABSPATH"))
	exit;

define ("IPROS24_NOTICES_PLUGIN_DIR_PATH", plugin_dir_path (__FILE__));
define ("IPROS24_NOTICES_PLUGIN_DIR_URL", plugin_dir_url (__FILE__));

require_once "assets/php/utils.php";

class iPROS24_notices_plugin extends iPROS24_WP_Plugin	{

	const	POST_TYPE	= "ipros24-notice",
		IMPORTANT	= "_ipros24_notices_important",

		EDIT_SERVER_SIDE_SCRIPTS	= "edit_server_side_scripts",
		EDIT_CLIENT_SIDE_SCRIPTS	= "edit_client_side_scripts";

	static	$text_domain,

		$COOKIE_NAME,
		$COOKIE_TEST,

		$server_side_conditions = array (

			"User logged in",
			"Mobile device"
		),

		$client_side_conditions = array (

			"Cookies enabled",
			"JavaScript enabled",
			"Browser supported"
		),

		$map = array (

			"ipros24-cookies-enabled"			=> "ipros24-cookies",
			"ipros24-not-cookies-enabled"			=> "ipros24-no-cookies",
			"ipros24-hide-if-cookies-enabled"		=> "ipros24-hide-if-cookies",
			"ipros24-hide-if-not-cookies-enabled"		=> "ipros24-hide-if-no-cookies",

			"ipros24-javascript-enabled"			=> "js",
			"ipros24-not-javascript-enabled"		=> "no-js",
			"ipros24-hide-if-javascript-enabled"		=> "hide-if-js",
			"ipros24-hide-if-not-javascript-enabled"	=> "hide-if-no-js",

//			"ipros24-browser-supported"			=> "ipros24-browser-supported",
			"ipros24-not-browser-supported"			=> "ipros24-browser-not-supported",
//			"ipros24-hide-if-browser-supported"		=> "ipros24-hide-if-browser-supported",
			"ipros24-hide-if-not-browser-supported"		=> "ipros24-hide-if-browser-not-supported"
		);

	public static function sanitize_key ($key, $sp = "_")	{

		return str_replace (" ", $sp, strtolower ($key));
	}

	public static function sanitize_html_class ($cn)	{

		return array_key_exists ($cn, self::$map) ? self::$map[$cn] : $cn;
	}

	static function explode ($delimiter, $string)	{

		return $string ? explode ($delimiter, $string) : array ();
	}

	function __construct ()	{

//		self::$text_domain = basename (__DIR__);
		self::$text_domain = "ipros24-notices";

		self::$COOKIE_NAME = "ipros24_notices_".COOKIEHASH;
		self::$COOKIE_TEST = "ipros24_test_cookie";

		$this->plugin_dir_path = IPROS24_NOTICES_PLUGIN_DIR_PATH;
		$this->plugin_dir_url = IPROS24_NOTICES_PLUGIN_DIR_URL;

		parent::__construct ();

		register_activation_hook (__FILE__, array ($this, "register_activation_hook"));
		register_deactivation_hook (__FILE__, array ($this, "register_deactivation_hook"));

		add_action ("plugins_loaded", array ($this, "plugins_loaded"));
		add_action ("init", array ($this, "init"));

		add_action ("admin_menu", array ($this, "admin_menu"));
		add_action ("admin_init", array ($this, "admin_init"));

		add_action ("add_meta_boxes_".self::POST_TYPE, array ($this, "add_meta_boxes_ipros24_notice"));
		add_action ("save_post_".self::POST_TYPE, array ($this, "save_post_ipros24_notice"), 10, 3);

		add_action ("wp_head", array ($this, "wp_head"));

		add_action ("wp_enqueue_scripts", array ($this, "wp_enqueue_scripts"));
		add_action ("admin_enqueue_scripts", array ($this, "admin_enqueue_scripts"));
		add_action ("admin_print_scripts-settings_page_ipros24-notices-options-page", array ($this, "admin_print_scripts_settings_page_ipros24_notices_options_page"));

		add_action ("wp_footer", array ($this, "wp_footer"));

//		if (is_admin ())	{
//
//			add_action ("wp_ajax_add-ipros24-server-side-condition", array ($this, "wp_ajax_add_ipros24_server_side_condition"));
//			add_action ("wp_ajax_add-ipros24-client-side-condition", array ($this, "wp_ajax_add_ipros24_client_side_condition"));
//
//			add_action ("wp_ajax_delete-ipros24-server-side-condition", array ($this, "wp_ajax_delete_ipros24_server_side_condition"));
//			add_action ("wp_ajax_delete-ipros24-client-side-condition", array ($this, "wp_ajax_delete_ipros24_client_side_condition"));
//		}

		add_filter ("map_meta_cap", array ($this, "map_meta_cap"), 10, 4);
		add_filter ("plugin_action_links_".plugin_basename (__FILE__), array ($this, "plugin_action_links"), 10, 4);

		add_filter ("ipros24_notices_user_logged_in", "is_user_logged_in");
		add_filter ("ipros24_notices_mobile_device", "wp_is_mobile");

		add_shortcode ("ipros24_user", array ($this, "ipros24_user"));

		$opt = get_option ("ipros24-notices-options");

		if (isset ($opt["server-side-script"]) && $opt["server-side-script"])
			eval ($opt["server-side-script"]);
	}

	function register_activation_hook ()	{

		update_option ("ipros24-notices-options", get_option ("ipros24-notices-options", array (

			"position" => "bottom"
		)));

		$role = get_role ("administrator");

		$role->add_cap (self::EDIT_SERVER_SIDE_SCRIPTS);
		$role->add_cap (self::EDIT_CLIENT_SIDE_SCRIPTS);

		$this->init ();
		flush_rewrite_rules ();
	}

	function register_deactivation_hook ()	{

//		delete_option ("ipros24-notices-options");
		flush_rewrite_rules ();
	}

	function plugins_loaded ()	{

		load_plugin_textdomain (self::$text_domain, FALSE, dirname (plugin_basename (__FILE__))."/languages/");
		setcookie (self::$COOKIE_TEST, "test", 0, COOKIEPATH, COOKIE_DOMAIN);
	}

	function init ()	{

		register_post_type (self::POST_TYPE, array (

			"labels" => array (

				"name"			=> __ ("Notices",			self::$text_domain),
				"menu_name"		=> __ ("Notices",			self::$text_domain),
				"singular_name"		=> __ ("Notice",			self::$text_domain),
				"all_items"		=> __ ("All Notices",			self::$text_domain),
				"add_new"		=> __ ("Add New",			self::$text_domain),
				"add_new_item"		=> __ ("Add New Notice",		self::$text_domain),
				"edit"			=> __ ("Edit",				self::$text_domain),
				"edit_item"		=> __ ("Edit Notice",			self::$text_domain),
				"new_item"		=> __ ("New Notice",			self::$text_domain),
				"view"			=> __ ("View Notice",			self::$text_domain),
				"view_item"		=> __ ("View Notice",			self::$text_domain),
				"search_items"		=> __ ("Search Notices",		self::$text_domain),
				"not_found"		=> __ ("No notices found",		self::$text_domain),
				"not_found_in_trash"	=> __ ("No notices found in Trash",	self::$text_domain)
			),

			"description"		=> __ ("iPROS24 Notices", self::$text_domain),
			"public"		=> TRUE,
			"exclude_from_search"	=> TRUE,
			"hierarchical"		=> FALSE,

//			"rewrite"		=> FALSE,
//			"rewrite"		=> array ("slug" => self::POST_TYPE, "with_front" => FALSE)
		));
	}

	function admin_menu ()	{

		add_options_page (

			__ ("Notices", self::$text_domain),
			__ ("Notices", self::$text_domain),

			"manage_options",
			$this->options_page,
			array ($this, "options")
		);
	}

	function options ()	{

		echo	"<div class='wrap'>".
				"<h2>".esc_html__ ("Notices settings", self::$text_domain)."</h2>".
				"<form method='POST' action='options.php'>";

					settings_fields ("ipros24-notices");
					do_settings_sections ($this->options_page);
					submit_button ();

		echo		"</form>".
			"</div>";
	}

	function list_field_callback ($args)	{

		$what = $args["what"];
		$list = self::explode (",", $args["value"]);

		$head =

			"<tr>".
				"<th class='left'>".esc_html (isset ($args["head"][0]) ? $args["head"][0] : "")."</th>".
				"<th>".esc_html (isset ($args["head"][1]) ? $args["head"][1] : "")."</th>".
				"<th>".esc_html (isset ($args["head"][2]) ? $args["head"][2] : "")."</th>".
			"</tr>";

		$body = "";
		foreach ($list as $i => $v)

			$body .=

				"<tr id='${what}-${i}' class='".($i%2 ? "" : "alternate")."'>".
					"<td class='left'>".esc_html__ ($v, self::$text_domain)."</td>".
					"<td>".
						get_submit_button (__ ("Delete", self::$text_domain), "small", "", FALSE,

							array ("data-wp-lists" => "delete:${what}-list:${what}-${i}::_ajax_nonce=".wp_create_nonce ("delete-${what}_${i}"))
						).
					"</td>".
					"<td>".esc_html (isset ($args["label"]) && is_callable ($args["label"]) ? call_user_func ($args["label"], $v) : "")."</td>".
				"</tr>";

		echo	"<div class='ipros24-list'>".
			"<div id='${what}-ajax-response'></div>".
				"<table>".
					"<thead>".$head."</thead>".
					"<tbody id='${what}-list' data-wp-lists='list:${what}'>".$body."</tbody>".
				"</table>".
				"<br />".
				"<table>".
					"<tbody id='add-${what}'>".
						"<tr>".
							"<td class='left'><input type='text' id='${what}-value' name='${what}-value' value='' /></td>".
							"<td>".
								get_submit_button (__ ("Add", self::$text_domain), "small", "add-${what}-submit", FALSE,

									array ("data-wp-lists" => "add:${what}-list:add-${what}::_ajax_nonce=".wp_create_nonce ("add-${what}"))
								).
							"</td>".
						"</tr>".
					"</tbody>".
				"</table>".
				"<input type='hidden' id='${what}-input' name='".esc_attr ($args["name"])."' value='".esc_attr ($args["value"])."' />".
				(isset ($args["description"]) && $args["description"] ? "<p class='description'>".esc_html ($args["description"])."</p>" : "").
			"</div>";
	}

	function admin_init ()	{

		register_setting ("ipros24-notices", "ipros24-notices-options", array ($this, "sanitize_callback"));

		$opt = get_option ("ipros24-notices-options");

		add_settings_section (

			"ipros24-notices-options",
			"",

			function ()	{
			},

			$this->options_page
		);

		$self = $this;

		add_settings_section (

			"ipros24-notices-server-side-conditions",
			esc_html__ ("Custom server-side conditions", self::$text_domain),

			function () use ($self, $opt)	{

				$self->list_field_callback (array (

					"what"	=> "ipros24-server-side-condition",
					"name"	=> "ipros24-notices-options[server-side-conditions]",
					"head"	=> array (__ ("Condition name", $self::$text_domain), "", __ ("WordPress filter", $self::$text_domain)),
					"value"	=> isset ($opt["server-side-conditions"]) ? $opt["server-side-conditions"] : "",

					"label"	=> function ($val) use ($self)	{

						return $self::sanitize_key ($val);
					},

					"description"	=> __ ("Condition names can only contain alphanumeric characters, spaces and underscores and start with a letter or underscore. Save the changes after editing.", $self::$text_domain)
				));
			},

			$this->options_page
		);

		add_settings_section (

			"ipros24-notices-server-side-script",
			esc_html__ ("Server-side script", self::$text_domain),

			function () use ($self, $opt)	{

				$self->textarea_field_callback (array (

					"name" => "ipros24-notices-options[server-side-script]",
					"value" => isset ($opt["server-side-script"]) ? $opt["server-side-script"] : "",

					"attributes" => current_user_can ($self::EDIT_SERVER_SIDE_SCRIPTS) ? "" : "disabled"
				));
			},

			$this->options_page
		);

		add_settings_section (

			"ipros24-notices-client-side-conditions",
			esc_html__ ("Custom client-side conditions", self::$text_domain),

			function () use ($self, $opt)	{

				$self->list_field_callback (array (

					"what"	=> "ipros24-client-side-condition",
					"name"	=> "ipros24-notices-options[client-side-conditions]",
					"head"	=> array (__ ("Condition name", $self::$text_domain), "", __ ("JavaScript variable", $self::$text_domain)),
					"value"	=> isset ($opt["client-side-conditions"]) ? $opt["client-side-conditions"] : "",

					"label"	=> function ($val) use ($self)	{

						return $self::sanitize_key ($val);
					},

					"description"	=> __ ("Condition names can only contain alphanumeric characters, spaces and underscores and start with a letter or underscore. Save the changes after editing.", $self::$text_domain)
				));
			},

			$this->options_page
		);

		add_settings_section (

			"ipros24-notices-client-side-script",
			esc_html__ ("Client-side script", self::$text_domain),

			function () use ($self, $opt)	{

				$self->textarea_field_callback (array (

					"name" => "ipros24-notices-options[client-side-script]",
					"value" => isset ($opt["client-side-script"]) ? $opt["client-side-script"] : "",

					"attributes" => current_user_can ($self::EDIT_CLIENT_SIDE_SCRIPTS) ? "" : "disabled"
				));
			},

			$this->options_page
		);

		add_settings_field (

			"position",
			esc_html__ ("Position", self::$text_domain),
			array ($this, "radio_field_callback"),
			$this->options_page,
			"ipros24-notices-options",
			array (

				"field" => array (

					array (

						"name" => "ipros24-notices-options[position]",
						"value" => "top",
						"label" => __ ("Top", self::$text_domain),
						"attributes" => isset ($opt["position"]) && $opt["position"] == "top" ? "checked" : ""
					),

					array (

						"name" => "ipros24-notices-options[position]",
						"value" => "bottom",
						"label" => __ ("Bottom", self::$text_domain),
						"attributes" => isset ($opt["position"]) && $opt["position"] == "bottom" ? "checked" : ""
					)
				),

				"description" => ""
			)
		);

		add_settings_field (

			"use-database",
			esc_html__ ("Use database", self::$text_domain),
			array ($this, "checkbox_field_callback"),
			$this->options_page,
			"ipros24-notices-options",
			array (

				"legend" => __ ("Use database", self::$text_domain),
				"field" => array (

					array (

						"name" => "ipros24-notices-options[use-user-meta]",
						"value" => isset ($opt["use-user-meta"]) ? $opt["use-user-meta"] : "",
						"label" => __ ("Store the list of viewed notices in user metadata", self::$text_domain)
					)
				),

				"description" => ""
			)
		);
	}

	function sanitize_callback ($opt)	{

		$option = get_option ("ipros24-notices-options");

		if (!current_user_can (self::EDIT_SERVER_SIDE_SCRIPTS))
			$opt["server-side-script"] = isset ($option["server-side-script"]) ? $option["server-side-script"] : "";

		if (!current_user_can (self::EDIT_CLIENT_SIDE_SCRIPTS))
			$opt["client-side-script"] = isset ($option["client-side-script"]) ? $option["client-side-script"] : "";

		return $opt;
	}

	function add_meta_boxes_ipros24_notice ($post)	{

		add_meta_box (

			"ipros24-notice-attributes",
			__ ("Notice Attributes", self::$text_domain),
			array ($this, "notice_attributes"),
			self::POST_TYPE,
			"side",
			"low"
		);
	}

	function notice_attributes ($post)	{

		$opt = get_option ("ipros24-notices-options");

		$server_side_conditions = array_merge (self::$server_side_conditions, self::explode (",", isset ($opt["server-side-conditions"]) ? $opt["server-side-conditions"] : ""));
		$client_side_conditions = array_merge (self::$client_side_conditions, self::explode (",", isset ($opt["client-side-conditions"]) ? $opt["client-side-conditions"] : ""));

//		iPROS24_Utils::log (" server_side_conditions = ".print_r ($server_side_conditions, TRUE));
//		iPROS24_Utils::log (" client_side_conditions = ".print_r ($client_side_conditions, TRUE));

		echo	"<p>".
				"<label for='ipros24-notices-important' class='selectit'>".
					"<input type='checkbox' ".
						"id='ipros24-notices-important' ".
						"name='ipros24-notices-important' ".
						"value='1' ".
						checked ("1", get_post_meta ($post->ID, self::IMPORTANT, TRUE), FALSE).
					" />"." ".
					esc_html__ ("Important", self::$text_domain).
				"</label>".
			"</p>";

		echo	"<hr />";
		echo	"<p><strong>".esc_html__ ("Server-side conditions", self::$text_domain)."</strong></p>";

		foreach ($server_side_conditions as $condition)	{

			$key = "ipros24-notices-server-side-".self::sanitize_key ($condition, "-");
			$val = get_post_meta ($post->ID, "_ipros24_notices_server_side_".self::sanitize_key ($condition), TRUE);

			echo	"<p class='ipros24-attribute'>".
					"<strong class='label'>".esc_html__ ($condition, self::$text_domain)."</strong>".
					"<label class='screen-reader-text' for='".$key."'>".esc_html__ ($condition, self::$text_domain)."</label>".
					"<select id='".$key."' name='".$key."'>".
						"<option value='' ".selected ("", $val, FALSE)."></option>".
						"<option value='yes' ".selected ("yes", $val, FALSE).">".esc_html_x ("True", "condition", self::$text_domain)."</option>".
						"<option value='no' ".selected ("no", $val, FALSE).">".esc_html_x ("False", "condition", self::$text_domain)."</option>".
					"</select>".
				"</p>";
		}

		echo	"<p><strong>".esc_html__ ("Client-side conditions", self::$text_domain)."</strong></p>";

		foreach ($client_side_conditions as $condition)	{

			$key = "ipros24-notices-client-side-".self::sanitize_key ($condition, "-");
			$val = get_post_meta ($post->ID, "_ipros24_notices_client_side_".self::sanitize_key ($condition), TRUE);

			echo	"<p class='ipros24-attribute'>".
					"<strong class='label'>".esc_html__ ($condition, self::$text_domain)."</strong>".
					"<label class='screen-reader-text' for='".$key."'>".esc_html__ ($condition, self::$text_domain)."</label>".
					"<select id='".$key."' name='".$key."'>".
						"<option value='' ".selected ("", $val, FALSE)."></option>".
						"<option value='yes' ".selected ("yes", $val, FALSE).">".esc_html_x ("True", "condition", self::$text_domain)."</option>".
						"<option value='no' ".selected ("no", $val, FALSE).">".esc_html_x ("False", "condition", self::$text_domain)."</option>".
					"</select>".
				"</p>";
		}

		echo	"<p class='howto'>".esc_html__ ("Select additional conditions for displaying the notice", self::$text_domain)."</p>";
	}

	function save_post_ipros24_notice ($post_id, $post, $update)	{

//		iPROS24_Utils::log (" post_id = ".$post_id." update = ".$update);
//		iPROS24_Utils::log (" post = ".print_r ($post, TRUE));

		$opt = get_option ("ipros24-notices-options");

		$server_side_conditions = array_merge (self::$server_side_conditions, self::explode (",", isset ($opt["server-side-conditions"]) ? $opt["server-side-conditions"] : ""));
		$client_side_conditions = array_merge (self::$client_side_conditions, self::explode (",", isset ($opt["client-side-conditions"]) ? $opt["client-side-conditions"] : ""));

		self::update_post_meta ($post_id, self::IMPORTANT, isset ($_POST["ipros24-notices-important"]) && $_POST["ipros24-notices-important"] ? "1" : "");

		foreach ($server_side_conditions as $condition)	{

			$key = "ipros24-notices-server-side-".self::sanitize_key ($condition, "-");
			$val = isset ($_POST[$key]) ? $_POST[$key] : "";

			self::update_post_meta ($post_id, "_ipros24_notices_server_side_".self::sanitize_key ($condition), in_array ($val, array ("", "yes", "no")) ? $val : "");
		}

		foreach ($client_side_conditions as $condition)	{

			$key = "ipros24-notices-client-side-".self::sanitize_key ($condition, "-");
			$val = isset ($_POST[$key]) ? $_POST[$key] : "";

			self::update_post_meta ($post_id, "_ipros24_notices_client_side_".self::sanitize_key ($condition), in_array ($val, array ("", "yes", "no")) ? $val : "");
		}
	}

	function wp_head ()	{

		$opt = get_option ("ipros24-notices-options");
		$client_side_conditions = array_merge (self::$client_side_conditions, self::explode (",", isset ($opt["client-side-conditions"]) ? $opt["client-side-conditions"] : ""));

		$output = "";
		foreach ($client_side_conditions as $condition)	{

			$key = self::sanitize_key ($condition, "-");

			$output .=	".ipros24-notices > .".self::sanitize_html_class ("ipros24-hide-if-".$key).",".
					".ipros24-notices > .".self::sanitize_html_class ("ipros24-hide-if-not-".$key)." {".
						"display: none;".
					"} ".

					"html.".self::sanitize_html_class ("ipros24-not-".$key)." .ipros24-notices > .".self::sanitize_html_class ("ipros24-hide-if-".$key).",".
					"html.".self::sanitize_html_class ("ipros24-".$key)." .ipros24-notices > .".self::sanitize_html_class ("ipros24-hide-if-not-".$key)." {".
						"display: block;".
					"} ";
		}

		if ($output)
			echo "<style type='text/css'>".$output."</style>";
	}

	function wp_enqueue_scripts ()	{

		$opt = get_option ("ipros24-notices-options");
		$client_side_conditions = self::explode (",", isset ($opt["client-side-conditions"]) ? $opt["client-side-conditions"] : "");

		parent::wp_enqueue_scripts ();

		wp_localize_script ("ipros24-notices-script", "ipros24_notices", array (

			"COOKIE_NAME"	=> self::$COOKIE_NAME,
			"COOKIE_TEST"	=> self::$COOKIE_TEST,

			"conditions"	=> $client_side_conditions
		));
	}

	function admin_enqueue_scripts ()	{

		wp_enqueue_style ("ipros24-notices-admin-style", $this->plugin_dir_url."assets/css/my-admin.css");

		if (is_rtl ())
			wp_enqueue_style ("ipros24-notices-admin-rtl-style", $this->plugin_dir_url."assets/css/my-admin-rtl.css");
	}

	function admin_print_scripts_settings_page_ipros24_notices_options_page ()	{

		self::enqueue_utils ();

		wp_enqueue_script ("ipros24-notices-admin-script", $this->plugin_dir_url."assets/js/my-admin.js", array ("wp-lists", "underscore"));
		wp_localize_script ("ipros24-notices-admin-script", "ipros24_notices", array (

			"DELETE"	=> esc_js (__ ("Delete", self::$text_domain)),
			"INVALID_VALUE"	=> esc_js (__ ("Invalid value.", self::$text_domain)),

			"default_conditions"	=> array_merge (self::$server_side_conditions, self::$client_side_conditions)
		));
	}

	function wp_footer ()	{

		global $current_user, $is_bot;

		if ($is_bot)
			return;

		$opt = get_option ("ipros24-notices-options");

		if (isset ($opt["client-side-script"]) && $opt["client-side-script"])

			echo "	<script type='text/javascript'>
					//<![CDATA[
					(function (ipros24_notices, $)	{

						\"use strict\";

						{$opt['client-side-script']}

					}) (window.ipros24_notices = window.ipros24_notices || {}, jQuery);
					//]]>
				</script>";

		$notices = get_posts (array (

			"post_type"	=> self::POST_TYPE,
//			"post_status"	=> "publish",

//			"orderby"	=> "date",
//			"order"		=> "DESC",

			"posts_per_page"	=> -1
		));

		$ids = self::explode (",", isset ($_COOKIE[self::$COOKIE_NAME]) ? $_COOKIE[self::$COOKIE_NAME] : "");

		$server_side_conditions = array_merge (self::$server_side_conditions, self::explode (",", isset ($opt["server-side-conditions"]) ? $opt["server-side-conditions"] : ""));
		$client_side_conditions = array_merge (self::$client_side_conditions, self::explode (",", isset ($opt["client-side-conditions"]) ? $opt["client-side-conditions"] : ""));

		if (isset ($opt["use-user-meta"]) && $opt["use-user-meta"] && is_user_logged_in ())	{

			$user_id = $current_user->ID;

//			delete_user_meta ($user_id, self::$COOKIE_NAME);

			$val = get_user_meta ($user_id, self::$COOKIE_NAME, TRUE);
			$val = self::explode (",", $val);

			$ids = array_unique (array_merge ($ids, $val));

			self::update_user_meta ($user_id, self::$COOKIE_NAME, join (",", $ids));
		}

//		iPROS24_Utils::log (" ids = ".print_r ($ids, TRUE));

		$output = "";
		foreach ($notices as $notice)	{

			if (in_array ($notice->ID, $ids))
				continue;

			foreach ($server_side_conditions as $condition)	{

				$key = self::sanitize_key ($condition);
				$val = apply_filters ("ipros24_notices_".$key, FALSE);

				$meta = get_post_meta ($notice->ID, "_ipros24_notices_server_side_".$key, TRUE);
				if ($meta == "yes" && !$val || $meta == "no" && $val)
					continue 2;
			}

			$cn = array ();

			$meta = get_post_meta ($notice->ID, self::IMPORTANT, TRUE);
			if ($meta)
				$cn[] = "ipros24-nb";

			foreach ($client_side_conditions as $condition)	{

				$key = self::sanitize_key ($condition, "-");

				$meta = get_post_meta ($notice->ID, "_ipros24_notices_client_side_".self::sanitize_key ($condition), TRUE);
				if ($meta == "yes")
					$cn[] = self::sanitize_html_class ("ipros24-hide-if-not-".$key);
				else
				if ($meta == "no")
					$cn[] = self::sanitize_html_class ("ipros24-hide-if-".$key);
			}

			$output .=	"<div ipros24-id='".$notice->ID."' class='".join (" ", $cn)."'>".
						"<h3>".get_the_title ($notice->ID)."</h3>".
						"<div>".(post_password_required ($notice->ID) ? get_the_password_form () : apply_filters ("the_content", $notice->post_content))."</div>".
						"<div class='ipros24-button ipros24-close hide-if-no-js'></div>".
					"</div>";
		}

		if (!$output)
			return;

		$cn = array ("ipros24-notices");

		if (isset ($opt["position"]) && $opt["position"] == "bottom")
			$cn[] = "ipros24-bottom";

		if (has_filter ("the_content", "wpautop"))
			$cn[] = "ipros24-wpautop";

		echo "<div class='".join (" ", $cn)."'>".$output."</div>";
	}

//	function wp_ajax_add_ipros24_server_side_condition ()	{
//
//		check_ajax_referer ("add-ipros24-server-side-condition");
//
//		wp_die (1);
//	}
//
//	function wp_ajax_add_ipros24_client_side_condition ()	{
//
//		check_ajax_referer ("add-ipros24-client-side-condition");
//
//		wp_die (1);
//	}
//
//	function wp_ajax_delete_ipros24_server_side_condition ()	{
//
//		$id = isset ($_POST["id"]) ? (int) $_POST["id"] : 0;
//		check_ajax_referer ("delete-ipros24-server-side-condition_".$id);
//
//		wp_die (1);
//	}
//
//	function wp_ajax_delete_ipros24_client_side_condition ()	{
//
//		$id = isset ($_POST["id"]) ? (int) $_POST["id"] : 0;
//		check_ajax_referer ("delete-ipros24-client-side-condition_".$id);
//
//		wp_die (1);
//	}

	function map_meta_cap ($caps, $cap, $user_id, $args)	{

		switch ($cap)	{

			case self::EDIT_SERVER_SIDE_SCRIPTS:
//			case self::EDIT_CLIENT_SIDE_SCRIPTS:

				if (defined ("ALLOW_EDIT_SERVER_SIDE_SCRIPTS") && !ALLOW_EDIT_SERVER_SIDE_SCRIPTS || is_multisite () && !is_super_admin ($user_id))
					foreach (array_keys ($caps, $cap, TRUE) as $k)
						$caps[$k] = "do_not_allow";

//				iPROS24_Utils::log (" caps = ".print_r ($caps, TRUE));

				break;
		}

		return $caps;
	}

	function ipros24_user ($atts, $content, $tag)	{

		global $current_user;

		$atts = shortcode_atts (array ("property" => "display_name"), $atts, $tag);

		$property = $atts["property"];
		$allowed_properties = apply_filters ("ipros24_allowed_user_properties", array ("display_name"));

		return in_array ($property, $allowed_properties) && isset ($current_user->$property) ? $current_user->$property : $content;
	}
}

new iPROS24_notices_plugin;

