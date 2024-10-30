<?php

/*
Plugin Name: Monoslideshow For Wordpress
URI: http://www.monoslideshow.com/
Description: Monoslideshow For Wordpress replaces the standard photo gallery created in Wordpress with Monoslideshow.
Version: 1.0
Author: Monokai
Author URI: http://www.monokai.nl
License: GPL2
*/

/*
Copyright 2014 Monokai

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists('Monoslideshow')) {

	class Monoslideshow {

		private static $defaultOptions = array(
			'monoslideshow-preset'	=> 'default',
			'monoslideshow-width'	=> '100%',
			'monoslideshow-height'	=> '0.5625',
			'monoslideshow-resize'	=> 'true'
		);

		public function __construct() {
			require_once(sprintf("%s/monoslideshow-settings.php", dirname(__FILE__)));
			$monoslideshowSettings = new MonoslideshowSettings(__FILE__);
		}

		public static function activate() {
			foreach (self::$defaultOptions as $option => $default) {
				$value = get_option($option);
				if (empty($value)) {
					update_option($option, $default);
				}
			}
		}

		public static function deactivate() {
		}

		public static function uninstall() {
			foreach (self::$defaultOptions as $option => $default) {
				delete_option($option);
			}
		}

	}

}

if (class_exists('Monoslideshow')) {

	$imageSizes = array('thumbnail', 'medium', 'large');

	// optionally add custom sizes:
	/*
	add_image_size('monoslideshow-size1', 320, 240, false);
	add_image_size('monoslideshow-size2', 640, 480, false);
	add_image_size('monoslideshow-size3', 1280, 960, false);
	add_image_size('monoslideshow-size4', 2560, 1920, false);

	$imageSizes = ['monoslideshow-size1', 'monoslideshow-size2', 'monoslideshow-size3', 'monoslideshow-size4'];
	*/

	register_activation_hook(__FILE__, array('Monoslideshow', 'activate'));
	register_deactivation_hook(__FILE__, array('Monoslideshow', 'deactivate'));
	register_uninstall_hook(__FILE__, array('Monoslideshow', 'uninstall'));

	$monoslideshow = new Monoslideshow();

	if (isset($monoslideshow)) {

		function getSettingsLink($links) {
			$link = '<a href="options-general.php?page=monoslideshow">Settings</a>';
			array_unshift($links, $link);
			return $links;
		}

		function enqueueScripts() {
			if (!is_admin()) {
				wp_enqueue_script('monoslideshow-loader', plugins_url('/js/monoslideshow-loader.js', __FILE__));
				wp_enqueue_script('monoslideshow', plugins_url('/js/monoslideshow.js', __FILE__), array('monoslideshow-loader'));
			}
		}

		function getSlideshowConfiguration($preset) {
			if (!$preset) {
				return '';
			}

			$c = @file_get_contents(dirname(__FILE__) . '/presets/' . $preset . '.xml');
			if ($c === FALSE) {
				return '';
			}

			$xml = simplexml_load_string($c);
			$backgroundColor = $xml['backgroundColor'];

			$c = preg_replace('/\r\n|\r|\n/', '', $c);
			$c = preg_replace('/"/', '\"', $c);
			$c = preg_replace('/>\s+</', "><", $c);

			return array('configuration' => $c, 'backgroundColor' => $backgroundColor);
		}

		function getSlideshowContent($attachments) {
			global $imageSizes;

			$xml = "<contents>";

			foreach ($attachments as $attachment) {

				$title = $attachment -> post_excerpt;
				$description = $attachment -> post_content;
				$img = wp_get_attachment_image_src($attachment->ID);

				$xml .= "<image itemPath='" . dirname($img[0]) . "' title='" . $title . "' description='" . $description . "'>";
				$xml .= "<sources>";

				foreach ($imageSizes as $imageSize) {
					$src = wp_get_attachment_image_src($attachment->ID, $imageSize);
					$xml .= "<variant source='" . basename($src[0]) . "' width='" . $src[1] . "' height='" . $src[2] . "' />";
				}

				$xml .= "</sources>";
				$xml .= "</image>";
			}

			$xml .= "</contents>";

			return $xml;
		}

		function processShortcode($attr) {
			global $post;

			extract(shortcode_atts(array(
				'orderby'		=> 'menu_order ASC, ID ASC',
				'id'			=> $post->ID,
				'include'		=> '',
				'ids'			=> '',
				'width'			=> '',
				'height'		=> '',
				'resize'		=> '',
				'preset'		=> ''
			), $attr));

			$id = intval($id);

			if (!empty($ids)) {
				// [gallery ids="1, 2, 3"]
				$ids = preg_replace('/[^0-9,]+/', '', $ids);
				$attachments = get_posts(array(
					'include'			=> $ids,
					'post_type'			=> 'attachment',
					'post_mime_type'	=> 'image',
					'orderby'			=> $orderby
				));
			} else {
				// [gallery]
				$include = preg_replace('/[^0-9,]+/', '', $include);

				$attachments = get_posts( array(
					'include'			=> $include,
					'post_parent'		=> $id,
					'post_type'			=> 'attachment',
					'post_mime_type'	=> 'image',
					'orderby'			=> $orderby
				));
			}

			if (is_feed()) {
				// no gallery for feeds
				$output = "\n";
				foreach ($attachments as $attachment) {
					$output .= wp_get_attachment_link($attachment->ID, $size, true) . "\n";
				}
				return $output;
			}

			$configuration = getSlideshowConfiguration(!empty($attr['preset']) ? $attr['preset'] : get_option('monoslideshow-preset'));

			$xml = "<?xml version='1.0' encoding='utf-8'?>";
			$xml .= "<album>";
			$xml .= $configuration['configuration'];
			$xml .= getSlideshowContent($attachments);
			$xml .= "</album>";

			$w = !empty($attr['width']) ? $attr['width'] : get_option('monoslideshow-width');
			$h = !empty($attr['height']) ? $attr['height'] : get_option('monoslideshow-height');
			$resize = !empty($attr['resize']) ? $attr['resize'] : get_option('monoslideshow-resize');

			if (empty($resize)) {
				$resize = 'false';
			}

			$style = !empty($configuration['backgroundColor']) ? 'background-color: ' . $configuration['backgroundColor'] . ';' : '';
			$style .= 'min-width: 16px; min-height: 16px;';

			$output = "<div class='monoslideshowHolder'><div class='monoslideshow' id='monoslideshow" . $id . "' style='" . $style . "'></div></div>\n";
			$output .= "<script type='text/javascript'>\n";
			$output .= "window['MonoslideshowLoader'].add(" .$id . ", '" . $w . "', '" . $h . "', \"" . $xml . "\", " . $resize .");\n";
			$output .= "</script>\n";

			return $output;
		}

		$plugin = plugin_basename(__FILE__);
		add_filter("plugin_action_links_$plugin", 'getSettingsLink');
		add_action('wp_enqueue_scripts', 'enqueueScripts');
		remove_shortcode('gallery');
		add_shortcode('gallery', 'processShortcode');

	}

}

?>