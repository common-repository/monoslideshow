=== Monoslideshow ===
Contributors: monokai_nl
Tags: monoslideshow, slideshow, slider, touch, iOS, iPad, iPhone, customize, WebGL
Donate link: http://www.monoslideshow.com
Requires at least: 3.8
Tested up to: 3.8
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Monoslideshow For Wordpress replaces the standard photo gallery created in Wordpress with Monoslideshow.

== Description ==
Monoslideshow For Wordpress replaces the standard photo gallery created in Wordpress with Monoslideshow. Note: you have to purchase [Monoslideshow 3](http://www.monoslideshow.com) in order for this plugin to work.

== Installation ==
1. Upload the plugin folder `monoslideshow` to `/wp-content/plugins/`.
2. Upload the slideshow file `monoslideshow.js` from your Monoslideshow purchase to `/wp-content/plugins/monoslideshow/js`.
3. Optionally put your custom configuration files in the `monoslideshow/presets` folder.

== Frequently Asked Questions ==
= How do I use a custom theme? =
Upload an XML file to the `presets` folder. Make sure that the .XML only contains the `<configuration>` node.

= How do I use different configuration per slideshow? =
You can select a default configuration via the Monoslideshow plugin settings. You can override this by manually changing the shortcode in a blog post: `[gallery ids="1,2,3", preset="yourPreset", width="640px", height="480px"]`.

= Which Monoslideshow versions are supported? =
Only Monoslideshow 3 and up are supported by this Wordpress plugin.

== Screenshots ==
1. Example of Monoslideshow 3
2. Simple configuration of slideshows in Wordpress
3. This Wordpress plugin requires that you already have Monoslideshow 3

== Changelog ==
= 1.0 =
* Initial release.