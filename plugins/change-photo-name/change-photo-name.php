<?php
/**
 * Plugin Name:  Change Photo Name (After Submission)
 * Description:  Renames the uploaded photo to the entered National Code after form submission.
 * Version:      1.0
 * Author:       Mohammadreza
 * License:      GPL v2 or later
 */

defined( 'ABSPATH' ) or die( 'Access denied.' );

// ====== CONFIGURATION ======
define( 'GFRU_FORM_ID', 1 );                   // Your form ID
define( 'GFRU_NATIONAL_CODE_FIELD_ID', 11 );   // National Code field ID
define( 'GFRU_UPLOAD_FIELD_ID', 10 );          // File Upload field ID
// ===========================

add_action( 'gform_after_submission', 'gfru_rename_file_after_submit', 10, 2 );
function gfru_rename_file_after_submit( $entry, $form ) {
    if ( $form['id'] != GFRU_FORM_ID ) {
        return;
    }

    // 1. Get National Code and uploaded file URL
    $national_code = rgar( $entry, GFRU_NATIONAL_CODE_FIELD_ID );
    $file_url      = rgar( $entry, GFRU_UPLOAD_FIELD_ID );

    if ( empty( $national_code ) || empty( $file_url ) ) {
        return; // nothing to rename
    }

    // 2. Sanitise for safe filename
    $safe_code = preg_replace( '/[^a-zA-Z0-9\-]/', '', $national_code );
    if ( empty( $safe_code ) ) {
        return;
    }

    // 3. Convert URL to server path
    $upload_dir = wp_get_upload_dir();
    $file_path  = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file_url );

    if ( ! file_exists( $file_path ) ) {
        return;
    }

    // 4. Build new filename with original extension
    $ext      = pathinfo( $file_path, PATHINFO_EXTENSION );
    $new_name = $safe_code . '.' . $ext;
    $new_path = trailingslashit( dirname( $file_path ) ) . $new_name;

    // 5. Rename the file
    if ( rename( $file_path, $new_path ) ) {
        // 6. Update the entry with the new URL
        $new_url = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $new_path );
        GFAPI::update_entry_field( $entry['id'], GFRU_UPLOAD_FIELD_ID, $new_url );
    }
}