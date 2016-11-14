<?php
/**
 * File that holds admin
 * functionality for the plugin.
 *
 * @package         Different_User_Language
 */

/**
 * Class that holds admin
 * functionality for the plugin.
 *
 * Class Different_User_Language_Admin
 */
class Different_User_Language_Admin {

	/**
	 * Holds the class instance.
	 *
	 * @var Different_User_Language_Admin
	 */
	private static $instance;

	/**
	 * Returns the instance of this class.
	 *
	 * @return Different_User_Language_Admin
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

		// Add our language field to the edit user form.
		add_action( 'personal_options', array( $this, 'add_edit_user_fields' ) );

		// Save user profile settings.
		add_action( 'personal_options_update', array( $this, 'save_user_fields' ), 0 );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_fields' ), 0 );

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
	 * Add our fields to the edit user form.
	 *
	 * @param WP_User $profile_user The current WP_User object.
	 */
	public function add_edit_user_fields( $profile_user ) {

		// We need the WordPress Translation Install API.
		require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );

		// Get the default site locale.
		$default_locale = different_user_language()->get_default_locale();

		// Get the user defined locale.
		$diff_user_lang_locale = different_user_language()->get_user_locale( $profile_user->ID );

		// If no user defined locale, use the default locale.
		if ( false === $diff_user_lang_locale ) {
			$diff_user_lang_locale = $default_locale;
		}

		// Get available languages.
		$languages = get_available_languages();

		// Get available translations.
		$translations = wp_get_available_translations();

		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="diff_user_lang_locale"><?php _e( 'Select your language', 'diff-user-language' ); ?></label></th>
					<td>
						<fieldset style="width:50%;">
							<?php

							// Get the display settings.
							$diff_user_lang_display = different_user_language()->get_user_display( $profile_user->ID );

							// Print the languages dropdown.
							wp_dropdown_languages( array(
								'name'         => 'diff_user_lang_locale',
								'id'           => 'diff_user_lang_locale',
								'selected'     => $diff_user_lang_locale,
								'languages'    => $languages,
								'translations' => $translations,
							));

							?>
							<p style="line-height: 1.5em;"><strong><?php printf( __( 'The default language: %s', 'diff-user-language' ), $default_locale ); ?></strong></p>
							<p class="description" style="line-height: 1.5em;"><?php _e( 'This setting allows you to define the language that you see when you are logged in to the website.', 'diff-user-language' ); ?><br /><?php _e( 'This comes in handy if you need to support a website in a different language.', 'diff-user-language' ); ?></p>
							<div style="margin:15px 0 0 0;">
								<legend class="screen-reader-text"><span><?php _e( 'Where to display custom language', 'diff-user-language' ); ?></span></legend>
								<label for="diff_user_lang_display_admin"> <input name="diff_user_lang_display[only_admin]" type="checkbox" id="diff_user_lang_display_admin" value="1"<?php checked( isset( $diff_user_lang_display['only_admin'] ) && $diff_user_lang_display['only_admin'] ); ?> /> <?php _e( 'Only show this language in the admin', 'diff-user-language' ); ?></label>
								<p class="description" style="line-height: 1.5em;"><?php _e( 'If unchecked, this will show your language site-wide. Otherwise, this will allow you to see what the user sees on the front-end while using your native language in the admin.', 'diff-user-language' ); ?></p>
							</div>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
		<?php

	}

	/**
	 * Saves custom user fields.
	 *
	 * The check_admin_referer() function is run
	 * before this action so we're good to go.
	 *
	 * @param int - $user_id - the user ID.
	 */
	public function save_user_fields( $user_id ) {

		// Make sure the 'diff_user_lang_locale' information is set.
		if ( isset( $_POST['diff_user_lang_locale'] ) ) {

			// Update the user meta.
			update_user_meta( $user_id, 'diff_user_lang_locale', $_POST['diff_user_lang_locale'] );

		}

		/**
		 * Get the display information.
		 *
		 * Since checkboxes, could be "not set"
		 * if no checkboxes are selected so
		 * always need to test as blank
		 * checkboxes is a setting.
		 */
		$diff_user_lang_display = isset( $_POST['diff_user_lang_display'] ) ? $_POST['diff_user_lang_display'] : '';

		// Update the user meta.
		update_user_meta( $user_id, 'diff_user_lang_display', $diff_user_lang_display );

	}

}

/**
 * Returns the instance of our Different_User_Language_Admin class.
 *
 * Will come in handy when we need to access the
 * class to retrieve data throughout the plugin.
 *
 * @return Different_User_Language_Admin
 */
function different_user_language_admin() {
	return Different_User_Language_Admin::instance();
}

// Get our instance.
different_user_language_admin();
