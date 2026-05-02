<?php
/**
 * Plugin Name:  Class Field Display
 * Description:  Displays the class name and ID in Views
 * Version:      1.0
 * Author:       Mohammadreza
 */
defined( 'ABSPATH' ) or die();

// ═══════════════════ CONFIGURATION ═══════════════════
define( 'GFSC_SOURCE_FORM_ID',   2 );   // Classes form ID
define( 'GFSC_SOURCE_NAME_FIELD', 1 );  // Field ID in Classes form that holds the class name
define( 'GFSC_TARGET_FORM_ID',   1 );   // Students form ID
define( 'GFSC_TARGET_FIELD_ID',  8 );   // Student dropdown field that stores the class entry ID
// ══════════════════════════════════════════════════════

/**
 * Replace the raw class entry ID with "Class Name | (ID)" whenever the field value is displayed.
 */
add_filter( 'gform_entry_field_value', function( $value, $field, $entry, $form ) {
    // Only target our specific form and field
    if ( (int) $form['id'] !== GFSC_TARGET_FORM_ID || (int) $field->id !== GFSC_TARGET_FIELD_ID ) {
        return $value;
    }

    // If the value is empty or not a valid numeric ID, leave it unchanged
    if ( empty( $value ) || ! is_numeric( $value ) ) {
        return $value;
    }

    // Fetch the class entry by ID
    $class_entry = GFAPI::get_entry( (int) $value );
    if ( is_wp_error( $class_entry ) || empty( $class_entry ) ) {
        return $value; // fallback to raw ID if class no longer exists
    }

    $class_name = rgar( $class_entry, (string) GFSC_SOURCE_NAME_FIELD );
    if ( empty( $class_name ) ) {
        return $value;
    }

    // Build the display string: 
    return $class_name . ' | (' . $value . ')';

}, 10, 4 );