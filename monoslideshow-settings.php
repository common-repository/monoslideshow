<?php

if (!class_exists('MonoslideshowSettings')) {

	class MonoslideshowSettings {

		public $mainPluginFile;

		public function __construct($mainPluginFile) {
			$this->mainPluginFile = $mainPluginFile;

			add_action('admin_init', array(&$this, 'adminInit'));
			add_action('admin_menu', array(&$this, 'addMenu'));
		}

		public function checkVersion() {
			global $wp_version;
			$mainPluginFile = $this->mainPluginFile;

			$plugin = plugin_basename($mainPluginFile);
			$plugin_data = get_plugin_data($mainPluginFile, false);

			if (version_compare($wp_version, "3.8", "<" )) {
				if (is_plugin_active($plugin)) {
					deactivate_plugins($plugin);
					wp_die("\"" . $plugin_data['Name'] . "\" requires WordPress 3.8 or higher, and has been deactivated. Please upgrade WordPress and try again.<br /><br />Back to <a href='" . admin_url() . "'>WordPress admin</a>.");
				}
			}
		}

		public function checkMonoslideshow() {
			$mainPluginFile = $this->mainPluginFile;

			$plugin = plugin_basename($mainPluginFile);
			$plugin_data = get_plugin_data($mainPluginFile, false);

			if (!file_exists(dirname($mainPluginFile) . '/js/monoslideshow.js')) {
				if (is_plugin_active($plugin)) {
					deactivate_plugins($plugin);
					wp_die("\"" . $plugin_data['Name'] . "\" requires the official Monoslideshow to work. Please <a href='http://www.monoslideshow.com'>get Monoslideshow here</a> and put <strong>\"monoslideshow.js\"</strong> in the folder <strong>\"" . plugins_url() . "/monoslideshow/js\"</strong>. This plugin has now been deactivated.<br /><br />Back to <a href='" . admin_url() . "'>WordPress admin</a>.");
				}
			}
		}

		public function adminInit() {
			$this->checkVersion();
			$this->checkMonoslideshow();

			add_settings_section('monoslideshow-settings', 'Notes', array(&$this, 'getSettingsDescription'), 'monoslideshow');
			add_settings_field('monoslideshow-preset', 'Preset', array(&$this, 'getPresetDropdown'), 'monoslideshow', 'monoslideshow-settings', array('label_for' => 'monoslideshow-preset'));
			add_settings_field('monoslideshow-width', 'Width', array(&$this, 'getDimension'), 'monoslideshow', 'monoslideshow-settings', array('label_for' => 'monoslideshow-width'));
			add_settings_field('monoslideshow-height', 'Height', array(&$this, 'getDimension'), 'monoslideshow', 'monoslideshow-settings', array('label_for' => 'monoslideshow-height'));
			add_settings_field('monoslideshow-resize', 'Auto-resize', array(&$this, 'getBoolean'), 'monoslideshow', 'monoslideshow-settings', array('label_for' => 'monoslideshow-resize'));

			register_setting('monoslideshow-settings', 'monoslideshow-preset');
			register_setting('monoslideshow-settings', 'monoslideshow-width');
			register_setting('monoslideshow-settings', 'monoslideshow-height');
			register_setting('monoslideshow-settings', 'monoslideshow-resize');
		}

		public function getSettingsDescription() {
			echo "<ol>";
			echo "<li>You can create your own preset by putting a new .XML file in the folder \"" . plugins_url() . "/monoslideshow/presets\".</li>";
			echo "<li>Dimension values are entered in percentages (e.g. \"100%\"), pixels (e.g. \"640px\") or fractions of the other dimension (e.g. a height value of \"0.5625\" for a 16/9 ratio widescreen slideshow.)</li>";
			echo "<li>Auto-resize controls automatic resizing (for responsive layouts for example). You can optionally turn this off.</li>";
			echo "<li>The settings below affect all slideshows in all posts. You can override them by manually changing the shortcode: <pre>[gallery ids=\"1,2,3\", preset=\"yourPreset\", width=\"640px\", height=\"480px\", resize=\"true\"]</pre></li>";
			echo "</ol>";
		}

		public function getPresetDropdown($args) {
			$field = $args['label_for'];
			$value = get_option($field);

			echo sprintf("<select name='%s' id='%s'>", $field, $field);
			foreach (glob(dirname(__FILE__) . '/presets/*.xml') as $filename) {
				$name = basename($filename, '.xml');
				$selected = $name == $value ? "selected='selected'" : "";
				echo sprintf("<option value='%s' %s>%s</option>", $name, $selected, $name);
			}
			echo "</select>";
		}

		public function getDimension($args) {
			$field = $args['label_for'];
			$value = get_option($field);

			echo sprintf("<input type='text' name='%s' id='%s' value='%s'>", $field, $field, $value);
		}

		public function getBoolean($args) {
			$field = $args['label_for'];
			$value = get_option($field);
			$checked = checked('true', $value, false);

			echo sprintf("<input type='checkbox' name='%s' id='%s' value='true' %s>", $field, $field, $checked);
		}

		public function addMenu() {
			add_options_page('Monoslideshow Settings', 'Monoslideshow', 'manage_options', 'monoslideshow', array(&$this, 'settingsPage'));
		}

		public function settingsPage() {
			if(!current_user_can('manage_options')) {
				wp_die(__("You do not have sufficient permissions to access this page."));
			}
			echo "<div class='wrap'>";
			echo screen_icon();
			echo "<h2>Monoslideshow settings</h2>";
			echo "<form method='post' action='options.php'>";
			settings_fields('monoslideshow-settings');
			do_settings_sections('monoslideshow');
			submit_button();
			echo "</form>";
			echo "</div>";
		}

	}

}

?>