=== Simple GA 4 Ranking  ===
Contributors: digitalcube,amimotoami,mt8biz
Tags:  form, ranking, popular, google analytics
Requires at least: 5.9
Tested up to: 6.4
Stable tag: 0.0.9

Ranking plugin using data from google analytics (GA4).

== Description ==

Ranking plugin using data from google analytics.
The feature to work very lightweight, because it is not save ranking data in WordPress DB.

= How to use =

== Installation ==

1. Upload `simple-ga-4-ranking` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Screenshots ==

== Changelog ==
= 0.0.1 =
* BETA release. 

= 0.0.2 =
* Some fix

= 0.0.3 =
* Added fall back to composer autoloading

= 0.0.4 =
* fix Google Authentication is deactivated. https://github.com/megumiteam/simple-ga4-ranking/issues/14 thanks @shinghiro

= 0.0.5 =
* Changed to log detailed API errors only if the constant SGA4R_DETAIL_LOG is defined

= 0.0.6 =
* Added Auto Update from GitHub

= 0.0.7 =
* fix Auto Update from GitHub

= 0.0.8 =
* fix {tax}__not_in

= 0.0.9 =
* Added 'transient_key_suffix' parameter

= 0.0.10 =
* Tested: WordPress 6.6
* Merged: If the date cannot be obtained with wp_date, start_date and end_date must be specified by @shiro96
* Merged: Allow specifying post type when in debug mode by @Shizumi
