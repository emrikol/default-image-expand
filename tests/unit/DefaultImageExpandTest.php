<?php
/**
 * Unit tests for Default Image Expand plugin.
 *
 * These tests verify the behaviour of every function defined in
 * default-image-expand.php.  WordPress is replaced by an in-memory stub
 * environment (see tests/bootstrap.php), so no database or web server is
 * required.
 *
 * Test naming convention: test_<function>_<scenario>_<expected_result>
 *
 * @package Default_Image_Expand
 */

declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

/**
 * Full behavioural coverage for the Default Image Expand plugin functions.
 */
class DefaultImageExpandTest extends TestCase {

	// =========================================================================
	// Fixtures
	// =========================================================================

	/**
	 * Resets all global state to a clean baseline before every test so that
	 * no test can bleed side-effects into a later one.
	 */
	protected function setUp(): void {
		$GLOBALS['die_is_logged_in']        = false;
		$GLOBALS['die_current_user_id']     = 0;
		$GLOBALS['die_current_user_can']    = true;
		$GLOBALS['die_nonce_valid']         = true;
		$GLOBALS['die_user_meta']           = array();
		$GLOBALS['die_user_meta_updates']   = array();
		$GLOBALS['die_delete_metadata_log'] = array();
		$GLOBALS['die_textdomain_log']      = array();
		// die_registered_hooks is intentionally NOT reset here.  Hook
		// registration runs once when the plugin file is loaded at bootstrap
		// time — those records are what the hook-registration tests inspect.
		$_POST = array();
	}

	// =========================================================================
	// enable_image_enlarge_defaults_only()
	//
	// Core contract: merge lightbox-on defaults into theme.json — unless the
	// current logged-in user has explicitly opted out.
	// =========================================================================

	/**
	 * Lightbox defaults must be applied when no user is logged in.
	 *
	 * Guest visitors have no preference to honour, so the filter always
	 * applies the lightbox setting.
	 */
	public function test_enable_applies_lightbox_when_no_user_is_logged_in(): void {
		$GLOBALS['die_is_logged_in'] = false;

		$theme_json = new WP_Theme_JSON_Data();
		Default_Image_Expand\enable_image_enlarge_defaults_only( $theme_json );

		$this->assertTrue( $theme_json->was_update_with_called() );
	}

	/**
	 * Lightbox defaults must be applied when a logged-in user has no stored
	 * preference (get_user_meta returns false — the real WP default).
	 */
	public function test_enable_applies_lightbox_when_logged_in_user_has_no_stored_preference(): void {
		$GLOBALS['die_is_logged_in']    = true;
		$GLOBALS['die_current_user_id'] = 1;
		// No entry in die_user_meta — stub returns false.

		$theme_json = new WP_Theme_JSON_Data();
		Default_Image_Expand\enable_image_enlarge_defaults_only( $theme_json );

		$this->assertTrue( $theme_json->was_update_with_called() );
	}

	/**
	 * Lightbox defaults must be applied when a logged-in user's preference is
	 * an empty string (the value stored when the checkbox is unticked).
	 */
	public function test_enable_applies_lightbox_when_logged_in_user_preference_is_empty_string(): void {
		$GLOBALS['die_is_logged_in']                                            = true;
		$GLOBALS['die_current_user_id']                                         = 2;
		$GLOBALS['die_user_meta'][2]['disable_image_enlarge_on_click'] = '';

		$theme_json = new WP_Theme_JSON_Data();
		Default_Image_Expand\enable_image_enlarge_defaults_only( $theme_json );

		$this->assertTrue( $theme_json->was_update_with_called() );
	}

	/**
	 * Lightbox defaults must NOT be applied when a logged-in user has
	 * explicitly opted out (meta value '1').
	 *
	 * The opt-out is the plugin's core user-respect mechanism — getting this
	 * wrong would force lightbox on users who disabled it.
	 */
	public function test_enable_does_not_apply_lightbox_when_user_has_opted_out(): void {
		$GLOBALS['die_is_logged_in']                                            = true;
		$GLOBALS['die_current_user_id']                                         = 1;
		$GLOBALS['die_user_meta'][1]['disable_image_enlarge_on_click'] = '1';

		$theme_json = new WP_Theme_JSON_Data();
		Default_Image_Expand\enable_image_enlarge_defaults_only( $theme_json );

		$this->assertFalse( $theme_json->was_update_with_called() );
	}

	/**
	 * When the user has opted out, the original $theme_json object must be
	 * returned unmodified — not a new or different object.
	 */
	public function test_enable_returns_original_object_when_user_has_opted_out(): void {
		$GLOBALS['die_is_logged_in']                                            = true;
		$GLOBALS['die_current_user_id']                                         = 1;
		$GLOBALS['die_user_meta'][1]['disable_image_enlarge_on_click'] = '1';

		$theme_json = new WP_Theme_JSON_Data();
		$result     = Default_Image_Expand\enable_image_enlarge_defaults_only( $theme_json );

		$this->assertSame( $theme_json, $result );
	}

	/**
	 * The data payload must declare version 2, which is required for the
	 * theme.json blocks settings structure used here.
	 */
	public function test_enable_passes_version_2_to_update_with(): void {
		$theme_json = new WP_Theme_JSON_Data();
		Default_Image_Expand\enable_image_enlarge_defaults_only( $theme_json );

		$this->assertSame( 2, $theme_json->get_update_with_data()['version'] );
	}

	/**
	 * The data payload must enable the lightbox for the core/image block.
	 *
	 * This is the singular purpose of the plugin — if this key is wrong the
	 * feature simply does not work.
	 */
	public function test_enable_sets_lightbox_enabled_true_for_core_image_block(): void {
		$theme_json = new WP_Theme_JSON_Data();
		Default_Image_Expand\enable_image_enlarge_defaults_only( $theme_json );

		$data    = $theme_json->get_update_with_data();
		$enabled = $data['settings']['blocks']['core/image']['lightbox']['enabled'];

		$this->assertTrue( $enabled );
	}

	/**
	 * The function must always return a WP_Theme_JSON_Data instance — the
	 * WordPress filter system discards non-object returns.
	 */
	public function test_enable_always_returns_a_theme_json_data_instance(): void {
		$theme_json = new WP_Theme_JSON_Data();
		$result     = Default_Image_Expand\enable_image_enlarge_defaults_only( $theme_json );

		$this->assertInstanceOf( WP_Theme_JSON_Data::class, $result );
	}

	// =========================================================================
	// save_user_profile_field()
	//
	// Two security gates before any write:
	//   1. Capability check — must be the same user OR have edit_user cap.
	//   2. Nonce check — must be present and pass verification.
	// =========================================================================

	/**
	 * No meta update must occur when the current user has neither the
	 * edit_user capability nor is the user being edited.
	 */
	public function test_save_skips_when_user_lacks_capability_and_is_not_current_user(): void {
		$GLOBALS['die_current_user_can'] = false;
		$GLOBALS['die_current_user_id']  = 99;

		Default_Image_Expand\save_user_profile_field( 1 );

		$this->assertEmpty( $GLOBALS['die_user_meta_updates'] );
	}

	/**
	 * A user must be allowed to save their own profile even without the
	 * edit_user capability (i.e. they are not an admin).
	 */
	public function test_save_allows_user_to_save_their_own_profile(): void {
		$GLOBALS['die_current_user_can'] = false; // No admin cap.
		$GLOBALS['die_current_user_id']  = 1;     // Editing own profile.
		$GLOBALS['die_nonce_valid']      = true;

		$_POST['disable_image_enlarge_on_click_nonce'] = 'valid';
		$_POST['disable_image_enlarge_on_click']       = '1';

		Default_Image_Expand\save_user_profile_field( 1 );

		$this->assertNotEmpty( $GLOBALS['die_user_meta_updates'] );
	}

	/**
	 * An admin (with edit_user capability) must be allowed to save any user's
	 * profile, not just their own.
	 */
	public function test_save_allows_admin_to_save_another_users_profile(): void {
		$GLOBALS['die_current_user_can'] = true; // Admin cap.
		$GLOBALS['die_current_user_id']  = 99;   // Different from target user.
		$GLOBALS['die_nonce_valid']      = true;

		$_POST['disable_image_enlarge_on_click_nonce'] = 'valid';

		Default_Image_Expand\save_user_profile_field( 1 );

		$this->assertNotEmpty( $GLOBALS['die_user_meta_updates'] );
	}

	/**
	 * No meta update must occur when the nonce field is absent from $_POST.
	 *
	 * Missing nonce = request did not originate from the profile form.
	 */
	public function test_save_skips_when_nonce_field_is_absent(): void {
		$GLOBALS['die_current_user_can'] = true;
		$_POST                           = array(); // No nonce.

		Default_Image_Expand\save_user_profile_field( 1 );

		$this->assertEmpty( $GLOBALS['die_user_meta_updates'] );
	}

	/**
	 * No meta update must occur when nonce verification fails.
	 *
	 * Failed verification = possible CSRF or replay attack.
	 */
	public function test_save_skips_when_nonce_verification_fails(): void {
		$GLOBALS['die_current_user_can'] = true;
		$GLOBALS['die_nonce_valid']      = false;

		$_POST['disable_image_enlarge_on_click_nonce'] = 'tampered_value';

		Default_Image_Expand\save_user_profile_field( 1 );

		$this->assertEmpty( $GLOBALS['die_user_meta_updates'] );
	}

	/**
	 * When the checkbox is ticked, the value '1' must be stored against the
	 * correct user ID and meta key.
	 */
	public function test_save_stores_disabled_flag_when_checkbox_is_checked(): void {
		$GLOBALS['die_current_user_can'] = true;
		$GLOBALS['die_nonce_valid']      = true;

		$_POST['disable_image_enlarge_on_click_nonce'] = 'valid';
		$_POST['disable_image_enlarge_on_click']       = '1';

		Default_Image_Expand\save_user_profile_field( 1 );

		$update = end( $GLOBALS['die_user_meta_updates'] );
		$this->assertSame( 1, $update['user_id'] );
		$this->assertSame( 'disable_image_enlarge_on_click', $update['key'] );
		$this->assertSame( '1', $update['value'] );
	}

	/**
	 * When the checkbox is unticked (absent from $_POST), an empty string
	 * must be stored so the preference is explicitly cleared.
	 */
	public function test_save_clears_disabled_flag_when_checkbox_is_unchecked(): void {
		$GLOBALS['die_current_user_can'] = true;
		$GLOBALS['die_nonce_valid']      = true;

		$_POST['disable_image_enlarge_on_click_nonce'] = 'valid';
		// Checkbox absent — browser does not submit unchecked checkboxes.

		Default_Image_Expand\save_user_profile_field( 1 );

		$update = end( $GLOBALS['die_user_meta_updates'] );
		$this->assertSame( 1, $update['user_id'] );
		$this->assertSame( 'disable_image_enlarge_on_click', $update['key'] );
		$this->assertSame( '', $update['value'] );
	}

	// =========================================================================
	// deactivation_cleanup()
	//
	// On deactivation every user's opt-out preference must be wiped so the
	// plugin leaves no orphaned data in the database.
	// =========================================================================

	/**
	 * Deactivation must issue a delete_metadata call that targets the
	 * correct meta key across ALL users (delete_all = true).
	 */
	public function test_deactivation_removes_opt_out_preference_from_all_users(): void {
		Default_Image_Expand\deactivation_cleanup();

		$this->assertCount( 1, $GLOBALS['die_delete_metadata_log'] );

		$call = $GLOBALS['die_delete_metadata_log'][0];
		$this->assertSame( 'user', $call['type'] );
		$this->assertSame( 'disable_image_enlarge_on_click', $call['key'] );
		$this->assertTrue( $call['delete_all'] );
	}

	// =========================================================================
	// load_textdomain()
	//
	// The plugin must register its own text domain for translation support.
	// =========================================================================

	/**
	 * load_textdomain() must call load_plugin_textdomain() with the plugin's
	 * slug as the domain argument.
	 */
	public function test_load_textdomain_registers_the_plugin_text_domain(): void {
		Default_Image_Expand\load_textdomain();

		$this->assertCount( 1, $GLOBALS['die_textdomain_log'] );
		$this->assertSame( 'default-image-expand', $GLOBALS['die_textdomain_log'][0]['domain'] );
	}

	// =========================================================================
	// add_user_profile_field()
	//
	// The profile field is the only UI surface of this plugin's opt-out
	// mechanism — its markup must be correct so users can control the feature.
	// =========================================================================

	/**
	 * The rendered output must contain a checkbox input with the correct name
	 * attribute so the browser includes it in the form submission.
	 */
	public function test_add_user_profile_field_renders_checkbox_with_correct_name(): void {
		$user = new WP_User( 1 );

		ob_start();
		Default_Image_Expand\add_user_profile_field( $user );
		$output = ob_get_clean();

		$this->assertStringContainsString( '<input type="checkbox"', $output );
		$this->assertStringContainsString( 'name="disable_image_enlarge_on_click"', $output );
	}

	/**
	 * The checkbox must be pre-checked when the user has opted out, so they
	 * can see their current preference at a glance.
	 */
	public function test_add_user_profile_field_renders_checkbox_checked_when_user_has_opted_out(): void {
		$GLOBALS['die_user_meta'][1]['disable_image_enlarge_on_click'] = '1';

		$user = new WP_User( 1 );

		ob_start();
		Default_Image_Expand\add_user_profile_field( $user );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'checked="checked"', $output );
	}

	/**
	 * The checkbox must NOT be checked when the user has not opted out, so
	 * they are not incorrectly shown as having disabled the feature.
	 */
	public function test_add_user_profile_field_renders_checkbox_unchecked_when_user_has_not_opted_out(): void {
		$GLOBALS['die_user_meta'][1]['disable_image_enlarge_on_click'] = '';

		$user = new WP_User( 1 );

		ob_start();
		Default_Image_Expand\add_user_profile_field( $user );
		$output = ob_get_clean();

		$this->assertStringNotContainsString( 'checked="checked"', $output );
	}

	// =========================================================================
	// Hook registration
	//
	// Verify the plugin wires itself into the expected WordPress hooks so
	// that renaming or removing a hook does not silently break the feature.
	// =========================================================================

	/**
	 * The plugin must hook enable_image_enlarge_defaults_only into
	 * wp_theme_json_data_default — that is the layer at which defaults are
	 * applied without overriding user or theme settings.
	 */
	public function test_plugin_registers_filter_on_wp_theme_json_data_default(): void {
		$hooks    = $GLOBALS['die_registered_hooks'];
		$filters  = array_filter(
			$hooks,
			static fn( array $h ) => 'filter' === $h['type'] && 'wp_theme_json_data_default' === $h['hook']
		);

		$this->assertNotEmpty( $filters, 'Expected a filter on wp_theme_json_data_default.' );
	}

	/**
	 * The plugin must register save_user_profile_field on both
	 * personal_options_update (own-profile saves) and edit_user_profile_update
	 * (admin saves of other profiles) so that the opt-out is persisted in
	 * both contexts.
	 */
	public function test_plugin_registers_save_handler_on_both_profile_update_actions(): void {
		$hooks = $GLOBALS['die_registered_hooks'];

		$personal = array_filter(
			$hooks,
			static fn( array $h ) => 'action' === $h['type'] && 'personal_options_update' === $h['hook']
		);
		$edit = array_filter(
			$hooks,
			static fn( array $h ) => 'action' === $h['type'] && 'edit_user_profile_update' === $h['hook']
		);

		$this->assertNotEmpty( $personal, 'Expected an action on personal_options_update.' );
		$this->assertNotEmpty( $edit, 'Expected an action on edit_user_profile_update.' );
	}
}
