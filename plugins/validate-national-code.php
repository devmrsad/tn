<?php
/**
 * Plugin Name:  National Code Regex Validation
 * Description:  Validates the National Code field with a strict 10-digit regex.
 * Version:      1.0
 * Author:       Mohammadreza
 * License:      GPL v2 or later
 */

defined( 'ABSPATH' ) or die( 'Access denied.' );

// ====== CONFIGURATION ======
define( 'GFNC_FORM_ID', 1 );
define( 'GFNC_FIELD_ID', 11 );
define( 'GFNC_REGEX_PATTERN', '/^\d{10}$/' ); // Regex pattern (10 digits only)
define( 'GFNC_ERROR_MESSAGE', 'کد ملی وارد شده نامعتبر است!' );
// ===========================

add_filter( 'gform_field_validation', 'gfnc_validate_national_code', 10, 4 );
function gfnc_validate_national_code( $result, $value, $form, $field ) {
    // Only target the configured form and field
    if ( $form['id'] != GFNC_FORM_ID || $field->id != GFNC_FIELD_ID ) {
        return $result;
    }

    // If the field is empty, let the "required" setting handle it (if enabled)
    if ( empty( $value ) ) {
        return $result;
    }

    if ( ! preg_match( GFNC_REGEX_PATTERN, $value ) ) {
        $result['is_valid'] = false;
        $result['message']  = GFNC_ERROR_MESSAGE;
    }

    return $result;
}