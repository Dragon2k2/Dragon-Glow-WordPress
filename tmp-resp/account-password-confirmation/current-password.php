<?php
/** Local checks for field-level current-password feedback. */
define( 'ABSPATH', __DIR__ );
function add_action( ...$args ) {}
function is_user_logged_in() { return $GLOBALS['logged_in']; }
function wp_unslash( $value ) { return stripslashes( $value ); }
function sanitize_text_field( $value ) { return $value; }
function wp_verify_nonce( $nonce, $action ) { return 'valid-nonce' === $nonce && 'save_account_details' === $action; }
function wp_get_current_user() { return (object) array( 'ID' => 7, 'user_pass' => $GLOBALS['hash'] ); }
function wp_check_password( $password, $hash, $id ) { return 7 === $id && password_verify( $password, $hash ); }
require __DIR__ . '/../../wp-content/themes/dragon-glow/inc/woocommerce/account/edit-account.php';
$GLOBALS['logged_in'] = true;
$GLOBALS['hash'] = password_hash( ' Correct-password! ', PASSWORD_DEFAULT );
$base = array( 'action' => 'save_account_details', 'save-account-details-nonce' => 'valid-nonce', 'password_current' => 'wrong' );
function check_case( $label, $post, $expected ) {
    $_POST = $post;
    if ( dg_account_current_password_invalid() !== $expected ) { throw new RuntimeException( $label ); }
    echo "PASS: $label\n";
}
check_case( 'Wrong password receives field error', $base, true );
check_case( 'Correct password including whitespace is accepted', array_replace( $base, array( 'password_current' => ' Correct-password! ' ) ), false );
check_case( 'Empty optional password stays neutral', array_replace( $base, array( 'password_current' => '' ) ), false );
check_case( 'Initial GET stays neutral', array(), false );
check_case( 'Invalid nonce is not checked', array_replace( $base, array( 'save-account-details-nonce' => 'invalid' ) ), false );
check_case( 'Array input is rejected without error', array_replace( $base, array( 'password_current' => array( 'wrong' ) ) ), false );
check_case( 'Wrong action stays neutral', array_replace( $base, array( 'action' => 'other' ) ), false );
$GLOBALS['logged_in'] = false;
check_case( 'Logged-out request is not checked', $base, false );

// Exercise the real AJAX handler with isolated WordPress API substitutes.
define( 'MINUTE_IN_SECONDS', 60 );
function check_ajax_referer( $action, $field ) {
    if ( 'dg_check_current_password' !== $action || 'ajax-nonce' !== ( $_POST[$field] ?? '' ) ) { wp_send_json_error( null, 403 ); }
}
function get_transient( $key ) { return $GLOBALS['limits'][$key] ?? false; }
function set_transient( $key, $value, $ttl ) { $GLOBALS['limits'][$key] = $value; }
function wp_send_json_error( $data = null, $status = 400 ) { throw new RuntimeException( json_encode( array( 'status' => $status, 'valid' => null ) ) ); }
function wp_send_json_success( $data ) { throw new RuntimeException( json_encode( array( 'status' => 200, 'valid' => $data['valid'] ) ) ); }
require __DIR__ . '/../../wp-content/themes/dragon-glow/inc/ajax/account.php';
function ajax_case( $label, $post, $status, $valid = null ) {
    $_POST = $post;
    try { dg_ajax_check_current_password(); } catch ( RuntimeException $e ) {
        $result = json_decode( $e->getMessage(), true );
        if ( $result !== array( 'status' => $status, 'valid' => $valid ) ) { throw new RuntimeException( $label . ': ' . $e->getMessage() ); }
        echo "PASS: $label\n";
        return;
    }
    throw new RuntimeException( 'Missing AJAX response' );
}
$ajax = array( 'nonce' => 'ajax-nonce', 'password_current' => 'wrong' );
ajax_case( 'AJAX rejects logged-out users', $ajax, 401 );
$GLOBALS['logged_in'] = true;
ajax_case( 'AJAX rejects invalid nonce', array_replace( $ajax, array( 'nonce' => 'bad' ) ), 403 );
ajax_case( 'AJAX rejects array password', array_replace( $ajax, array( 'password_current' => array() ) ), 400 );
ajax_case( 'AJAX detects wrong password', $ajax, 200, false );
ajax_case( 'AJAX accepts exact password', array_replace( $ajax, array( 'password_current' => ' Correct-password! ' ) ), 200, true );
class WP_User { public $ID = 7; }
class WP_Error {
    public $codes = array();
    public function add( $code, $message ) { $this->codes[] = $code; }
}
function get_current_user_id() { return 7; }
function __( $text, $domain ) { return $text; }
foreach ( array( ' Correct-password! ' => true, 'New-password!' => false, '' => false ) as $password => $expected ) {
    $_POST = array( 'password_1' => $password );
    $errors = new WP_Error();
    dg_account_validate_new_password( $errors, new WP_User() );
    if ( in_array( 'dg_password_reused', $errors->codes, true ) !== $expected ) { throw new RuntimeException( 'Password reuse validation failed' ); }
}
echo "PASS: server rejects reused password and accepts different or empty password\n";
$GLOBALS['limits']['dg_password_checks_7']['count'] = 10;
ajax_case( 'AJAX rate limit enforced', $ajax, 429 );
