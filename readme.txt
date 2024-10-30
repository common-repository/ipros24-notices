=== iPROS24 Notices ===

Contributors: nevis2us
Donate link: http://ipros24.ru/
Tags: iPROS24, notices
Requires at least: 5.0
Tested up to: 5.4
Stable tag: 1.7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced notices.

== Description ==

* Allows to show notices on your WordPress site.
* Comes with a number of built-in conditions for displaying the notices.
* Supports extensible set of custom conditions.
* Includes a shortcode for personalising the notices.
* Supports password protected notices.
* Has basic support for RTL languages.

[Demo](http://ipros24.ru/wordpress/)

We have to display notices to our visitors.

We have to ask for their consent to the use of cookies. We have to warn them when their browsers are out of date or they have cookies or JavaScript turned off and some features of the site may not work as intended.

This plugin allows you to do all of the above and more. It comes with a number of built-in conditions which make such a chore as trivial as pie. It also supports an extensible set of custom conditions and takes care of generating the necessary code and CSS rules.

Notices are implemented as a custom post type. They will be displayed site-wide at the top or bottom of your pages until closed. Optionally the list of viewed notices can be permanently stored in user metadata.

Built-in conditions:

* User logged in/logged out.
* Cookies enabled/disabled.
* JavaScript enabled/disabled.
* Browser supported/not supported.

There's a reasonable default for *Browser supported* but you may fine-tune it in a client-side filter.

You may select any combination of built-in and custom conditions for displaying a notice.

Plugin and theme developers can hook to custom filters and events to set up custom or fine-tune built-in conditions.

[Advanced usage and examples](http://ipros24.ru/ipros24-notices-advanced-usage-and-examples/)

**Other iPROS24 plugins**

[iPROS24 Google Translate](https://wordpress.org/plugins/ipros24-google-translate-widget/)

== Installation ==

1. Download iPROS24-notices-1.7.2.zip.
1. Unpack the archive into the plugins directory of your WordPress installation (wp-content/plugins).
1. Activate the plugin through the 'Plugins' screen in WordPress administration panel.
1. Use the Settings -> Notices screen to configure the plugin and add custom conditions if needed.
1. Add some notices and specify the conditions for displaying them.

**Notes**

Use server-side conditions to filter sensitive notices. If a notice makes it to the client it can be viewed in the HTML source of a page.

Use the shortcode to personalise your notices.

[Support](http://ipros24.ru/forums/forum/wordpress/)

== Screenshots ==

1. Sample notice.
2. Password protected and personalised notices.
3. Plugin settings.
4. Editing a notice.

== Changelog ==

= 1.7.2 =
* Confirmed WordPress 5.4 compatible.
* Updated libraries.

= 1.7.1 =
* Confirmed WordPress 5 compatible.

= 1.7 =
* Fixed cookie path.
* Fixed notice styles with javascript disabled.
* Added to readme.txt.
* Updated libraries.

= 1.6.1 =
* Fixed readme.txt.

= 1.6 =
* Fixed the styles with the wpautop filter enabled.

= 1.5 =
* Fixed a dependency error.

= 1.4 =
* Released on January 9, 2018.

