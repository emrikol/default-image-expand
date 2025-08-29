<?php
/**
 * Plugin Name: Default Image "Enlarge on click"
 * Description: Sets the Image block's default Link setting to "Enlarge on click" (core lightbox) in the Block Editor.
 * Version: 1.0.1
 * Requires at least: 6.8
 * Requires PHP: 8.0
 * Author: Your Name
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: default-image-expand
 *
 * @package Default_Image_Expand
 */

declare(strict_types=1);

namespace Default_Image_Expand;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load plugin textdomain for translations.
 *
 * @return void
 */
function load_textdomain(): void {
	load_plugin_textdomain( 'default-image-expand', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * Clean up plugin data on deactivation.
 *
 * @return void
 */
function deactivation_cleanup(): void {
	// Remove user meta for all users (optional cleanup).
	delete_metadata( 'user', 0, 'disable_image_expand_on_click', '', true );
}


/**
 * Enable Image Enlarge on click via theme.json default layer only.
 *
 * @param WP_Theme_JSON_Data|WP_Theme_JSON_Data_Gutenberg $theme_json Theme JSON data object.
 * @return WP_Theme_JSON_Data|WP_Theme_JSON_Data_Gutenberg Modified Theme JSON data object.
 */
function enable_image_expand_defaults_only( $theme_json ) {
	// Check if current user has disabled the feature.
	if ( is_user_logged_in() ) {
		$user_disabled = get_user_meta( get_current_user_id(), 'disable_image_expand_on_click', true );
		if ( '1' === $user_disabled ) {
			return $theme_json;
		}
	}

	$data = array(
		'version'  => 2,
		'settings' => array(
			'blocks' => array(
				'core/image' => array(
					'lightbox' => array(
						'enabled' => true,
					),
				),
			),
		),
	);

	return $theme_json->update_with( $data );
}


/**
 * Add checkbox field to user profile for disabling image expand feature.
 *
 * @param WP_User $user The user object being edited.
 * @return void
 */
function add_user_profile_field( $user ): void {
	$disabled = get_user_meta( $user->ID, 'disable_image_expand_on_click', true );
	?>
	<h3><?php esc_html_e( 'Image Settings', 'default-image-expand' ); ?></h3>
	<table class="form-table">
		<tr>
			<th><label for="disable_image_expand_on_click"><?php esc_html_e( 'Image Lightbox', 'default-image-expand' ); ?></label></th>
			<td>
				<input type="checkbox" name="disable_image_expand_on_click" id="disable_image_expand_on_click" value="1" <?php checked( $disabled, '1' ); ?> />
				<label for="disable_image_expand_on_click"><?php esc_html_e( 'Disable "Enlarge on click" default for Image blocks', 'default-image-expand' ); ?></label>
				<p class="description"><?php esc_html_e( 'When checked, Image blocks will not default to "Enlarge on click" lightbox behavior.', 'default-image-expand' ); ?></p>
				<?php wp_nonce_field( 'disable_image_expand_on_click_nonce', 'disable_image_expand_on_click_nonce' ); ?>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Save user profile field for disabling image expand feature.
 *
 * @param int $user_id The user ID being saved.
 * @return void
 */
function save_user_profile_field( int $user_id ): void {
	// Verify user can edit this user profile.
	if ( ! current_user_can( 'edit_user', $user_id ) && get_current_user_id() !== $user_id ) {
		return;
	}

	// Verify nonce for security.
	if ( ! isset( $_POST['disable_image_expand_on_click_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['disable_image_expand_on_click_nonce'] ) ), 'disable_image_expand_on_click_nonce' ) ) {
		return;
	}

	$disabled = isset( $_POST['disable_image_expand_on_click'] ) ? '1' : '';
	update_user_meta( $user_id, 'disable_image_expand_on_click', $disabled, false );
}


// Load textdomain for translations.
add_action( 'init', __NAMESPACE__ . '\\load_textdomain' );

// Hook into theme.json default layer only (minimal impact).
add_filter( 'wp_theme_json_data_default', __NAMESPACE__ . '\\enable_image_expand_defaults_only' );

// Add user profile fields.
add_action( 'show_user_profile', __NAMESPACE__ . '\\add_user_profile_field' );
add_action( 'edit_user_profile', __NAMESPACE__ . '\\add_user_profile_field' );

// Save user profile fields.
add_action( 'personal_options_update', __NAMESPACE__ . '\\save_user_profile_field' );
add_action( 'edit_user_profile_update', __NAMESPACE__ . '\\save_user_profile_field' );

// Clean up on deactivation (optional).
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\deactivation_cleanup' );
