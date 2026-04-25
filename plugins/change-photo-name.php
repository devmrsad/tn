<?php
/**
 * Plugin Name:  Change Photo Name
 * Description:  chnages uploaded photo's name to the entered national code
 * Version:      1.0
 * Author:       Mohammadreza
 * License:      GPL v2 or later
 */

defined( 'ABSPATH' ) or die( 'Access denied.' );

define( 'GFRU_FORM_ID', 1 );
define( 'GFRU_NATIONAL_CODE_FIELD_ID', 11 );
define( 'GFRU_UPLOAD_FIELD_ID', 10 );
// =============================================================

add_filter( 'gform_upload_path', 'rename_upload_based_on_national_code', 10, 2 );
function rename_upload_based_on_national_code( $path_info, $form_id ) {
    // Only run on the configured form
    if ( $form_id != GFRU_FORM_ID ) {
        return $path_info;
    }

    // Only affect the specific upload field
    if ( $path_info['field_id'] != GFRU_UPLOAD_FIELD_ID ) {
        return $path_info;
    }

    // Get the National Code from the POST data
    $national_code = rgpost( 'input_' . GFRU_NATIONAL_CODE_FIELD_ID );

    if ( empty( $national_code ) ) {
        return $path_info;
    }

    // Sanitise: keep only alphanumeric and hyphens (adjust if your codes contain other chars)
    $safe_code = preg_replace( '/[^a-zA-Z0-9\-]/', '', $national_code );
    if ( empty( $safe_code ) ) {
        return $path_info;
    }

    // Get original file extension
    $original_name = $path_info['name'];
    $ext = pathinfo( $original_name, PATHINFO_EXTENSION );

    // Build new name: e.g., 1162242327.jpg
    $new_name = $safe_code . '.' . $ext;

    // Update the path info array
    $path_info['name'] = $new_name;
    $path_info['url']  = trailingslashit( $path_info['url'] ) . $new_name;

    return $path_info;
}