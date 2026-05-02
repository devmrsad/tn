<?php
/**
 * Plugin Name:  Prevent Class Deletion
 * Description:  Prevents deleting a Class entry when linked Student entries exist.
 * Version:      2.0
 * Author:       Mohammadreza
 */
defined( 'ABSPATH' ) or die();

// ═══════════════════ CONFIGURATION ═══════════════════
define( 'BCD_CLASS_FORM_ID',     2 );   // Classes form ID
define( 'BCD_STUDENT_FORM_ID',   1 );   // Students form ID
define( 'BCD_STUDENT_CLASS_FIELD',8 );  // Student field that references the class
// ══════════════════════════════════════════════════════

// ─── 1. Block deletion on `init` ─────────────────────
add_action( 'init', 'bcd_block_class_delete', 1 );
function bcd_block_class_delete() {
    if ( empty( $_GET['action'] ) || $_GET['action'] !== 'delete' || empty( $_GET['entry_id'] ) ) {
        return;
    }

    $entry_id = absint( $_GET['entry_id'] );
    $entry = GFAPI::get_entry( $entry_id );

    if ( is_wp_error( $entry ) || empty( $entry ) || (int) $entry['form_id'] !== BCD_CLASS_FORM_ID ) {
        return;
    }

    if ( bcd_has_students( $entry_id ) ) {
        // Get the class name (field ID 1)
        $class_name = rgar( $entry, '1' );

        // Build redirect URL
        $redirect = remove_query_arg( array( 'action', 'delete', 'entry_id', 'gvid', 'view_id', 'delete', '_wpnonce', 'status' ) );
        $redirect = add_query_arg( 'bcd_error', 'has_students', $redirect );

        // Store the class name in a transient (for the alert)
        set_transient( 'bcd_block_error_' . get_current_user_id(), $class_name, 30 );

        wp_safe_redirect( $redirect );
        exit;
    }
}

// ─── 2. Show JavaScript alert with class name ─────────
add_action( 'wp_loaded', function() {
    if ( ! is_user_logged_in() ) return;
    $user_id = get_current_user_id();
    $class_name = get_transient( 'bcd_block_error_' . $user_id );

    if ( ! empty( $class_name ) ) {
        delete_transient( 'bcd_block_error_' . $user_id );
        add_action( 'wp_footer', function() use ( $class_name ) {
            $safe_name = esc_js( $class_name );
            echo '<script>alert("کلاس ' . $safe_name . ' دارای دانش‌آموز است. ابتدا باید دانش‌آموزان آن حذف شوند.");</script>';
        });
    }
});

// ─── 3. Hide delete link in GravityView (PHP filter) ─
add_filter( 'gravityview/delete/allow', function( $allow, $entry, $view ) {
    if ( (int) $entry['form_id'] !== BCD_CLASS_FORM_ID ) {
        return $allow;
    }
    if ( bcd_has_students( $entry['id'] ) ) {
        return false;
    }
    return $allow;
}, 10, 3 );

// ─── HELPER: Check if any active student exists for this class ─
function bcd_has_students( $class_entry_id ) {
    $class_entry = GFAPI::get_entry( $class_entry_id );
    $class_name = rgar( $class_entry, '1' );

    $filters = array(
        'mode' => 'any',
        array(
            'key'   => BCD_STUDENT_CLASS_FIELD,
            'value' => $class_entry_id,
        ),
        array(
            'key'   => BCD_STUDENT_CLASS_FIELD,
            'value' => $class_name,
        ),
    );

    $search_criteria = array(
        'status'        => 'active',
        'field_filters' => $filters,
    );

    $students = GFAPI::get_entries(
        BCD_STUDENT_FORM_ID,
        $search_criteria,
        null,
        array( 'page_size' => 1 )
    );

    return ( ! is_wp_error( $students ) && count( $students ) > 0 );
}