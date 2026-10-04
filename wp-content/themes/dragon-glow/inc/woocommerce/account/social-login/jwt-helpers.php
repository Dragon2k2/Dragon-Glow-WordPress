<?php
/**
 * Dragon Glow — Social Login: Minimal JWT/JWK Primitives
 *
 * Small, dependency-free building blocks for signing (Apple client secret)
 * and verifying (Apple ID token) JWTs using only PHP's bundled `openssl`
 * extension — no Composer `firebase/php-jwt` or similar, consistent with
 * the theme's no-build-step / no-npm-runtime-dependency constraint.
 *
 * Scope: these are intentionally narrow (ES256 sign, RS256 verify, RSA JWK
 * → PEM) — exactly what Sign in with Apple requires. Not a general JWT
 * library.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base64url-encode a string (JWT uses this instead of standard base64).
 *
 * @param string $data Raw bytes to encode (JSON string or raw signature bytes).
 * @return string
 */
function dg_jwt_base64url_encode( string $data ): string {
	return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
}

/**
 * Base64url-decode a string back to raw bytes.
 *
 * @param string $data Base64url-encoded string.
 * @return string
 */
function dg_jwt_base64url_decode( string $data ): string {
	$padded = str_pad( $data, strlen( $data ) % 4 === 0 ? strlen( $data ) : strlen( $data ) + ( 4 - strlen( $data ) % 4 ), '=' );
	return (string) base64_decode( strtr( $padded, '-_', '+/' ), true );
}

/**
 * Convert a raw ECDSA (r,s) JWS signature (what Apple/JWT expects for
 * ES256) into the ASN.1 DER format `openssl_verify()` expects, or back.
 *
 * `openssl_sign()` with an EC key outputs DER; JWS/ES256 requires the raw
 * fixed-width r||s concatenation (64 bytes for P-256). These two functions
 * convert between the two representations.
 */

/**
 * DER-encoded ECDSA signature → raw 64-byte r||s (JWS ES256 format).
 *
 * @param string $der DER-encoded signature from openssl_sign().
 * @return string Raw 64-byte signature, or '' on parse failure.
 */
function dg_jwt_der_to_jws_signature( string $der ): string {
	// DER SEQUENCE: 30 len [02 len_r r] [02 len_s s]
	$offset = 0;
	if ( ! isset( $der[0] ) || "\x30" !== $der[0] ) {
		return '';
	}
	$offset = 2; // Skip SEQUENCE tag + length byte (assumes short-form length, true for P-256 sigs).
	if ( ord( $der[1] ) & 0x80 ) {
		$offset += ord( $der[1] ) & 0x7f; // Long-form length — skip extra length bytes.
	}

	if ( ! isset( $der[ $offset ] ) || "\x02" !== $der[ $offset ] ) {
		return '';
	}
	++$offset;
	$r_len = ord( $der[ $offset ] );
	++$offset;
	$r = substr( $der, $offset, $r_len );
	$offset += $r_len;

	if ( ! isset( $der[ $offset ] ) || "\x02" !== $der[ $offset ] ) {
		return '';
	}
	++$offset;
	$s_len = ord( $der[ $offset ] );
	++$offset;
	$s = substr( $der, $offset, $s_len );

	// Strip DER's leading zero-padding (added when the high bit is set),
	// then left-pad to 32 bytes each so r||s is a fixed 64 bytes.
	$r = str_pad( ltrim( $r, "\x00" ), 32, "\x00", STR_PAD_LEFT );
	$s = str_pad( ltrim( $s, "\x00" ), 32, "\x00", STR_PAD_LEFT );

	return $r . $s;
}

/**
 * Raw 64-byte r||s (JWS ES256 format) → DER-encoded ECDSA signature.
 *
 * Only used in the Apple-token-verification direction when a provider
 * signs with ES256 (Apple's ID tokens are actually RS256, so this exists
 * for completeness/symmetry and future-proofing against Apple key changes).
 *
 * @param string $raw Raw signature bytes (r||s, or already-DER RS256 bytes).
 * @return string DER-encoded signature, or the input unchanged if it already looks like DER.
 */
function dg_jwt_jws_to_der_signature( string $raw ): string {
	// RS256 signatures (what Apple's ID tokens actually use) are already in
	// the format openssl_verify() expects — pass through unchanged.
	// Only ES256's raw r||s needs DER-wrapping, detected by exact 64-byte length.
	if ( 64 !== strlen( $raw ) ) {
		return $raw;
	}

	$r = ltrim( substr( $raw, 0, 32 ), "\x00" );
	$s = ltrim( substr( $raw, 32, 32 ), "\x00" );

	// Re-add a leading zero byte if the high bit is set (DER INTEGER must be non-negative).
	if ( '' !== $r && ( ord( $r[0] ) & 0x80 ) ) {
		$r = "\x00" . $r;
	}
	if ( '' !== $s && ( ord( $s[0] ) & 0x80 ) ) {
		$s = "\x00" . $s;
	}

	$r_part = "\x02" . chr( strlen( $r ) ) . $r;
	$s_part = "\x02" . chr( strlen( $s ) ) . $s;
	$body   = $r_part . $s_part;

	return "\x30" . chr( strlen( $body ) ) . $body;
}

/**
 * Convert an RSA JWK (modulus `n` + exponent `e`, both base64url) into a
 * PEM public key usable by `openssl_verify()`.
 *
 * @param string $n Base64url-encoded modulus.
 * @param string $e Base64url-encoded exponent.
 * @return string|WP_Error PEM-formatted public key, or WP_Error on failure.
 */
function dg_jwk_rsa_to_pem( string $n, string $e ) {
	$modulus  = dg_jwt_base64url_decode( $n );
	$exponent = dg_jwt_base64url_decode( $e );

	if ( '' === $modulus || '' === $exponent ) {
		return new WP_Error( 'dg_jwk_invalid', __( 'Received an invalid public key from the identity provider.', 'dragon-glow' ) );
	}

	// Build an ASN.1 DER SubjectPublicKeyInfo wrapping the RSA key, the
	// format openssl_pkey_get_public()/openssl_verify() need.
	$modulus_der  = dg_jwt_asn1_integer( $modulus );
	$exponent_der = dg_jwt_asn1_integer( $exponent );
	$rsa_pubkey   = dg_jwt_asn1_sequence( $modulus_der . $exponent_der );

	// RSA algorithm identifier (OID 1.2.840.113549.1.1.1) + NULL params.
	$alg_identifier = dg_jwt_asn1_sequence(
		"\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01" . "\x05\x00"
	);

	$public_key_bitstring = "\x00" . $rsa_pubkey; // Leading 0x00 = "0 unused bits" for BIT STRING.
	$bit_string            = "\x03" . dg_jwt_asn1_length( strlen( $public_key_bitstring ) ) . $public_key_bitstring;

	$spki = dg_jwt_asn1_sequence( $alg_identifier . $bit_string );

	$pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split( base64_encode( $spki ), 64, "\n" ) . "-----END PUBLIC KEY-----\n";

	$key = openssl_pkey_get_public( $pem );
	if ( false === $key ) {
		return new WP_Error( 'dg_jwk_pem_invalid', __( 'Could not build a usable public key from the identity provider response.', 'dragon-glow' ) );
	}

	return $key;
}

/**
 * ASN.1 DER length encoding (short or long form).
 *
 * @param int $length Content length in bytes.
 * @return string
 */
function dg_jwt_asn1_length( int $length ): string {
	if ( $length < 0x80 ) {
		return chr( $length );
	}
	$bytes = '';
	while ( $length > 0 ) {
		$bytes  = chr( $length & 0xff ) . $bytes;
		$length >>= 8;
	}
	return chr( 0x80 | strlen( $bytes ) ) . $bytes;
}

/**
 * Wrap raw bytes in an ASN.1 DER SEQUENCE.
 *
 * @param string $content Raw DER content.
 * @return string
 */
function dg_jwt_asn1_sequence( string $content ): string {
	return "\x30" . dg_jwt_asn1_length( strlen( $content ) ) . $content;
}

/**
 * Wrap raw unsigned-integer bytes in an ASN.1 DER INTEGER, adding the
 * leading zero byte required when the high bit of the first byte is set
 * (otherwise DER would interpret the integer as negative).
 *
 * @param string $bytes Raw big-endian unsigned integer bytes.
 * @return string
 */
function dg_jwt_asn1_integer( string $bytes ): string {
	$bytes = ltrim( $bytes, "\x00" );
	if ( '' === $bytes ) {
		$bytes = "\x00";
	}
	if ( ord( $bytes[0] ) & 0x80 ) {
		$bytes = "\x00" . $bytes;
	}
	return "\x02" . dg_jwt_asn1_length( strlen( $bytes ) ) . $bytes;
}

