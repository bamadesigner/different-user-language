<?php
/**
 * Plugin Name:     Different User, Different Language
 * Plugin URI:      https://github.com/bamadesigner/different-user-language
 * Description:     Allows users to view a different language than designated for the site.
 * Version:         1.0.0
 * Author:          Rachel Carden
 * Author URI:      https://bamadesigner.com
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     diff-user-language
 * Domain Path:     /languages
 *
 * @package         Different_User_Language
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// We only need admin functionality in the admin.
if ( is_admin() ) {
	require_once plugin_dir_path( __FILE__ ) . 'inc/admin.php';
}

/**
 * Class that holds basic/root
 * functionality for the plugin.
 *
 * Class Different_User_Language
 */
class Different_User_Language {

	/**
	 * Holds the default locale.
	 *
	 * @var string
	 */
	private $default_locale;

	/**
	 * Holds the user locales.
	 *
	 * @var string
	 */
	private $user_locales;

	/**
	 * Holds the user display settings.
	 *
	 * @var string
	 */
	private $user_display;

	/**
	 * Holds the plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Holds the class instance.
	 *
	 * @var Different_User_Language
	 */
	private static $instance;

	/**
	 * Returns the instance of this class.
	 *
	 * @return Different_User_Language
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			$class_name = __CLASS__;
			self::$instance = new $class_name;
		}
		return self::$instance;
	}

	/**
	 * Warming things up.
	 */
	protected function __construct() {

	    // Runs on install.
	    register_activation_hook( __FILE__, array( $this, 'install' ) );

	    // Runs when the plugin is upgraded.
	    add_action( 'upgrader_process_complete', array( $this, 'upgrader_process_complete' ), 1, 2 );

	    // Load our textdomain.
	    add_action( 'plugins_loaded', array( $this, 'textdomain' ) );

	    // Set the user's locale.
	    $this->add_locale_filter();

	}

	/**
	 * Method to keep our instance from being cloned.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Method to keep our instance from being unserialized.
	 *
	 * @return void
	 */
	private function __wakeup() {}

	/**
	 * Runs when the plugin is installed.
	 */
	public function install() {}

	/**
	 * Runs when the plugin is upgraded.
	 */
	public function upgrader_process_complete() {}

	/**
	 * Get the plugin version.
	 *
	 * @return the version number
	 */
	public function get_version() {

		// If set, return the version.
		if ( isset( $this->version ) ) {
			return $this->version;
		}

		// Check version file.
		$plugin_version_path = plugin_dir_path( __FILE__ ) . '.version';
		if ( true === file_exists( $plugin_version_path ) ) {

			// Get version value from file.
			$plugin_version = file_get_contents( $plugin_version_path );
			if ( ! empty( $plugin_version ) ) {
				return $this->version = $plugin_version;
			}
		}

		/**
		 * If no version file or value, set version.
		 *
		 * The WordPress function, get_plugin_data() only works in the admin.
		 */
		return $this->version = '1.0.0';
	}

	/**
	 * Internationalization FTW.
	 * Load our textdomain.
	 */
	public function textdomain() {
		load_plugin_textdomain( 'diff-user-language', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Add the local filter.
	 */
	public function add_locale_filter() {
		add_filter( 'locale', array( $this, 'set_user_locale' ), 10000000000 );
	}

	/**
	 * Remove the locale filter.
	 */
	public function remove_locale_filter() {
		remove_filter( 'locale', array( $this, 'set_user_locale' ), 10000000000 );
	}

	/**
	 * Returns the default locale.
	 *
	 * Removes our filter first and then adds it back.
	 */
	public function get_default_locale() {

		// If already set, get out of here.
		if ( isset( $this->default_locale ) ) {
			return $this->default_locale;
		}

		// Remove our filter so it doesn't interfere.
		$this->remove_locale_filter();

		// Get the default locale.
		$default_locale = get_locale();

		// Add our filter back.
		$this->add_locale_filter();

		// Return the default locale.
		return $this->default_locale = $default_locale;
	}

	/**
	 * Get the user's locale.
	 *
	 * @param   int = $user_id - the ID for the desired user locale.
	 * @return  string|false - the user locale or false if not defined.
	 */
	public function get_user_locale( $user_id = 0 ) {
		global $wpdb;

		// If no user ID, get current user ID.
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// Make sure we have a user ID.
		if ( ! $user_id || ! is_numeric( $user_id ) ) {
			return false;
		}

		// Check to see if the value already exists.
		if ( is_array( $this->user_locales ) && array_key_exists( $user_id, $this->user_locales ) ) {
			return $this->user_locales[ $user_id ];
		}

		/**
		 * Check to see if the row exists.
		 *
		 * Will return false if no locale and should therefore use default.
		 */
		$row_exists = $wpdb->get_var( $wpdb->prepare( "SELECT EXISTS( SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = 'diff_user_lang_locale' )", $user_id ) );
		if ( ! $row_exists ) {
			return false;
		}

		// Store/return the locale.
		return $this->user_locales[ $user_id ] = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = 'diff_user_lang_locale'", $user_id ) );
	}

	/**
	 * Get the user's display settings.
	 *
	 * @param   int - $user_id - the ID for the desired user locale.
	 * @return  false|array - the user display settings.
	 */
	public function get_user_display( $user_id = 0 ) {

		// If no user ID, get current user ID.
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// Make sure we have a user ID.
		if ( ! $user_id || ! is_numeric( $user_id ) ) {
			return false;
		}

		// Check to see if the value already exists.
		if ( is_array( $this->user_display ) && array_key_exists( $user_id, $this->user_display ) ) {
			return $this->user_display[ $user_id ];
		}

		// Get the user meta.
		$display_settings = get_user_meta( $user_id, 'diff_user_lang_display', true );

		// Make sure its an array.
		if ( empty( $display_settings ) || ! is_array( $display_settings ) ) {
			$display_settings = array();
		}

		// Store/return the settings.
		return $this->user_display[ $user_id ] = $display_settings;
	}

	/**
	 * Set the user's locale.
	 *
	 * @param   string - $locale - the default locale.
	 * @return  string - the filtered locale.
	 */
	public function set_user_locale( $locale ) {

		// Get user locale.
		$user_locale = $this->get_user_locale();

		// If no user defined locale, use the default locale.
		if ( false === $user_locale ) {
			return $locale;
		}

		// Get the user's display settings.
		$display_settings = $this->get_user_display();

		// Should we only display in the admin?
		if ( isset( $display_settings['only_admin'] ) && $display_settings['only_admin'] ) {
			return ! is_admin() ? $locale : $user_locale;
		}

		// Set the custom locale.
		return $user_locale;
	}

}

/**
 * Returns the instance of our main Different_User_Language class.
 *
 * Will come in handy when we need to access the
 * class to retrieve data throughout the plugin.
 *
 * @return Different_User_Language
 */
function different_user_language() {
	return Different_User_Language::instance();
}

// Let's get this show on the road.
different_user_language();
