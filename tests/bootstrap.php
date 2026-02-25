<?php
/**
 * PHPUnit bootstrap for Default Image Expand plugin tests.
 *
 * Builds a minimal in-memory WordPress stub environment so every test
 * runs without a live WordPress installation.  All mutable state lives
 * in prefixed $GLOBALS entries; each test's setUp() resets them to a
 * clean baseline so tests are fully isolated from one another.
 *
 * @package Default_Image_Expand
 */

// ---------------------------------------------------------------------------
// Constants.
// ---------------------------------------------------------------------------

define( 'ABSPATH', __DIR__ . '/tmp/' );

// ---------------------------------------------------------------------------
// Initial global state — every entry is reset by each test's setUp().
//
// Prefix: "die_" (Default Image Expand) keeps names short and collision-free.
// ---------------------------------------------------------------------------

$GLOBALS['die_is_logged_in']         = false; // Controlled by is_user_logged_in().
$GLOBALS['die_current_user_id']      = 0;     // Controlled by get_current_user_id().
$GLOBALS['die_current_user_can']     = true;  // Controlled by current_user_can().
$GLOBALS['die_nonce_valid']          = true;  // Controlled by wp_verify_nonce().
$GLOBALS['die_user_meta']            = array(); // [ user_id ][ meta_key ] => value.
$GLOBALS['die_user_meta_updates']    = array(); // Log of update_user_meta() calls.
$GLOBALS['die_delete_metadata_log']  = array(); // Log of delete_metadata() calls.
$GLOBALS['die_textdomain_log']       = array(); // Log of load_plugin_textdomain() calls.
$GLOBALS['die_registered_hooks']     = array(); // Log of add_action() / add_filter() calls.

// ---------------------------------------------------------------------------
// WordPress function stubs.
// ---------------------------------------------------------------------------

// --- Hook registration ------------------------------------------------------

/**
 * Stub for add_action — logs the call; does not execute anything.
 */
function add_action( string $hook, mixed $callback, int $priority = 10, int $accepted_args = 1 ): bool {
	$GLOBALS['die_registered_hooks'][] = array(
		'type'     => 'action',
		'hook'     => $hook,
		'callback' => $callback,
	);
	return true;
}

/**
 * Stub for add_filter — logs the call; does not execute anything.
 */
function add_filter( string $hook, mixed $callback, int $priority = 10, int $accepted_args = 1 ): bool {
	$GLOBALS['die_registered_hooks'][] = array(
		'type'     => 'filter',
		'hook'     => $hook,
		'callback' => $callback,
	);
	return true;
}

/**
 * Stub for register_deactivation_hook — intentional no-op in unit tests.
 */
function register_deactivation_hook( string $file, mixed $callback ): void {
	// Deactivation hooks cannot fire in unit tests.
}

// --- Authentication & capabilities ------------------------------------------

/**
 * Stub for is_user_logged_in — returns the value of $GLOBALS['die_is_logged_in'].
 */
function is_user_logged_in(): bool {
	return (bool) $GLOBALS['die_is_logged_in'];
}

/**
 * Stub for get_current_user_id — returns the value of $GLOBALS['die_current_user_id'].
 */
function get_current_user_id(): int {
	return (int) $GLOBALS['die_current_user_id'];
}

/**
 * Stub for current_user_can — returns the value of $GLOBALS['die_current_user_can'].
 *
 * A single boolean is sufficient because each test controls the capability
 * result directly; the specific capability string is not inspected here.
 */
function current_user_can( string $capability, mixed ...$args ): bool {
	return (bool) $GLOBALS['die_current_user_can'];
}

// --- User meta --------------------------------------------------------------

/**
 * Stub for get_user_meta — reads from $GLOBALS['die_user_meta'].
 *
 * Returns false (matching real WordPress behaviour) when the key has
 * not been seeded for the given user.
 */
function get_user_meta( int $user_id, string $key = '', bool $single = false ): mixed {
	return $GLOBALS['die_user_meta'][ $user_id ][ $key ] ?? false;
}

/**
 * Stub for update_user_meta — writes to $GLOBALS['die_user_meta'] and
 * appends a record to $GLOBALS['die_user_meta_updates'] for assertion.
 */
function update_user_meta( int $user_id, string $meta_key, mixed $meta_value, mixed $prev_value = '' ): mixed {
	$GLOBALS['die_user_meta'][ $user_id ][ $meta_key ] = $meta_value;
	$GLOBALS['die_user_meta_updates'][]                = array(
		'user_id' => $user_id,
		'key'     => $meta_key,
		'value'   => $meta_value,
	);
	// Real update_user_meta() returns the new meta ID on insert or true on
	// update; we return 1 as a truthy stand-in.
	return 1;
}

/**
 * Stub for delete_metadata — appends a record to
 * $GLOBALS['die_delete_metadata_log'] for assertion.
 */
function delete_metadata( string $meta_type, int $object_id, string $meta_key, mixed $meta_value = '', bool $delete_all = false ): bool {
	$GLOBALS['die_delete_metadata_log'][] = array(
		'type'       => $meta_type,
		'object_id'  => $object_id,
		'key'        => $meta_key,
		'value'      => $meta_value,
		'delete_all' => $delete_all,
	);
	return true;
}

// --- Internationalisation ----------------------------------------------------

/**
 * Stub for load_plugin_textdomain — logs the call for assertion.
 */
function load_plugin_textdomain( string $domain, bool $deprecated = false, mixed $plugin_rel_path = false ): bool {
	$GLOBALS['die_textdomain_log'][] = array(
		'domain' => $domain,
		'path'   => $plugin_rel_path,
	);
	return true;
}

/**
 * Stub for plugin_basename — returns a plugin-relative path from a full path.
 */
function plugin_basename( string $file ): string {
	return basename( dirname( $file ) ) . '/' . basename( $file );
}

/**
 * Stub for __() — returns the original string (no translation in tests).
 */
function __( string $text, string $domain = 'default' ): string {
	return $text;
}

/**
 * Stub for esc_html__() — HTML-escapes and returns the string.
 */
function esc_html__( string $text, string $domain = 'default' ): string {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

/**
 * Stub for esc_html_e() — HTML-escapes and echoes the string.
 */
function esc_html_e( string $text, string $domain = 'default' ): void {
	echo htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

/**
 * Stub for esc_html() — HTML-escapes and returns the string.
 */
function esc_html( string $text ): string {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

/**
 * Stub for esc_attr() — HTML-escapes and returns the string.
 */
function esc_attr( string $text ): string {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

// --- Security / nonces -------------------------------------------------------

/**
 * Stub for wp_verify_nonce — returns 1 (valid) or false (invalid) based on
 * $GLOBALS['die_nonce_valid'].
 */
function wp_verify_nonce( string $nonce, mixed $action = -1 ): int|false {
	return $GLOBALS['die_nonce_valid'] ? 1 : false;
}

/**
 * Stub for wp_nonce_field — echoes a minimal hidden input.
 */
function wp_nonce_field( mixed $action = -1, string $name = '_wpnonce', bool $referer = true, bool $echo = true ): string {
	$field = '<input type="hidden" name="' . esc_attr( (string) $name ) . '" value="test_nonce" />';
	if ( $echo ) {
		echo $field;
	}
	return $field;
}

// --- Sanitisation ------------------------------------------------------------

/**
 * Stub for sanitize_text_field — strips tags and trims whitespace.
 */
function sanitize_text_field( string $str ): string {
	return trim( strip_tags( $str ) );
}

/**
 * Stub for wp_unslash — strips slashes from strings.
 */
function wp_unslash( mixed $value ): mixed {
	if ( is_string( $value ) ) {
		return stripslashes( $value );
	}
	return $value;
}

// --- Form helpers ------------------------------------------------------------

/**
 * Stub for checked() — echoes/returns ' checked="checked"' when the two
 * values are loosely equal, matching real WordPress behaviour.
 */
function checked( mixed $checked, mixed $current = true, bool $echo = true ): string {
	// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
	$result = ( $checked == $current ) ? ' checked="checked"' : '';
	if ( $echo ) {
		echo $result;
	}
	return $result;
}

// ---------------------------------------------------------------------------
// WordPress class stubs.
// ---------------------------------------------------------------------------

/**
 * Stub for WP_Theme_JSON_Data.
 *
 * Records whether update_with() was called and captures the data it
 * received so tests can assert on the exact theme.json payload.
 */
class WP_Theme_JSON_Data {

	/**
	 * Whether update_with() has been called on this instance.
	 */
	private bool $update_with_called = false;

	/**
	 * The data array passed to the most recent update_with() call.
	 *
	 * @var array<string, mixed>
	 */
	private array $update_with_data = array();

	/**
	 * Records the call and returns $this so the plugin's `return` chain works.
	 *
	 * @param array<string, mixed> $data Theme JSON data to merge.
	 * @return static
	 */
	public function update_with( array $data ): static {
		$this->update_with_called = true;
		$this->update_with_data   = $data;
		return $this;
	}

	/**
	 * Returns true if update_with() was called at least once.
	 */
	public function was_update_with_called(): bool {
		return $this->update_with_called;
	}

	/**
	 * Returns the data passed to the last update_with() call.
	 *
	 * @return array<string, mixed>
	 */
	public function get_update_with_data(): array {
		return $this->update_with_data;
	}
}

/**
 * Stub for WP_User.
 *
 * Only the $ID property is needed by the plugin.
 */
class WP_User {

	/**
	 * User ID.
	 */
	public int $ID;

	/**
	 * Constructor.
	 *
	 * @param int $id The user ID.
	 */
	public function __construct( int $id ) {
		$this->ID = $id;
	}
}

// ---------------------------------------------------------------------------
// Load the plugin under test.
//
// Stubs must all be defined before this require so that the namespace-level
// add_action / add_filter calls at the bottom of the plugin file resolve
// to our stubs rather than real WordPress functions.
// ---------------------------------------------------------------------------

require_once dirname( __DIR__ ) . '/default-image-expand.php';
