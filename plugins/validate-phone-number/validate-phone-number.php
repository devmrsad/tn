<?php
/**
 * Plugin Name:  Phone Number Regex Validation
 * Description:  Validates a phone number field using a regex pattern (default: 09 followed by 9 digits). Optional field – validates only if filled.
 * Version:      1.0
 * Author:       Mohammadreza
 * License:      GPL v2 or later
 */

defined( 'ABSPATH' ) or die( 'Access denied.' );

// ====== CONFIGURATION ======
define( 'GFPN_FORM_ID', 2 );
define( 'GFPN_FIELD_ID', 8 );
define( 'GFPN_REGEX_PATTERN', '/^09\d{9}$/' );
define( 'GFPN_ERROR_MESSAGE', 'شماره تلفن وارد شده نامعتبر است!' );
// ===========================

add_filter( 'gform_field_validation', 'gfpn_validate_phone_number', 10, 4 );
function gfpn_validate_phone_number( $result, $value, $form, $field ) {
    if ( $form['id'] != GFPN_FORM_ID || $field->id != GFPN_FIELD_ID ) {
        return $result;
    }

    // Field is optional – if empty, do nothing (no error)
    if ( empty( trim( $value ) ) ) {
        return $result;
    }

    if ( ! preg_match( GFPN_REGEX_PATTERN, $value ) ) {
        $result['is_valid'] = false;
        $result['message']  = GFPN_ERROR_MESSAGE;
    }

    return $result;
}