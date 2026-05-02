<?php
/**
 * Plugin Name: GF Class Auto-Fill Dropdown
 * Description: Fills a specific dropdown field in one Gravity Form with the current user's entries from another Gravity Form.
 * Version:     1.1.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// ═══════════════════ CONFIGURATION ═══════════════════
define( 'GFCD_SOURCE_FORM_ID',   2 );   // ID of the Classes form
define( 'GFCD_SOURCE_NAME_FIELD', 1 );  // Field ID that holds the class name
define( 'GFCD_TARGET_FORM_ID',   1 );   // ID of the Students form
define( 'GFCD_TARGET_FIELD_ID',  8 );   // Dropdown field ID in Students form
// ══════════════════════════════════════════════════════

add_filter( 'gform_pre_render',            'gfcd_fill_dropdown' );
add_filter( 'gform_pre_validation',        'gfcd_fill_dropdown' );
add_filter( 'gform_pre_submission_filter', 'gfcd_fill_dropdown' );
add_filter( 'gform_admin_pre_render',      'gfcd_fill_dropdown' );

function gfcd_fill_dropdown( $form ) {
    if ( (int) $form['id'] !== GFCD_TARGET_FORM_ID ) return $form;

    $user_id = get_current_user_id();
    $entries = GFAPI::get_entries(
        GFCD_SOURCE_FORM_ID,
        [
            'status'        => 'active',
            'field_filters' => [
                [ 'key' => 'created_by', 'value' => $user_id ],
            ],
        ],
        [ 'key' => 'date_created', 'direction' => 'ASC' ],
        [ 'offset' => 0, 'page_size' => 200 ]
    );

    $choices = [];
    if ( ! is_wp_error( $entries ) && ! empty( $entries ) ) {
        foreach ( $entries as $entry ) {
            $name = rgar( $entry, (string) GFCD_SOURCE_NAME_FIELD );
            if ( empty( $name ) ) continue;
            $choices[] = [
                'text'       => $name . ' | (' . $entry['id'] . ')',  // label shown to user
                'value'      => $entry['id'],                         // stored value = entry ID
                'isSelected' => false,
            ];
        }
    }

    // Placeholder when no classes exist
    if ( empty( $choices ) ) {
        $choices = [
            [
                'text'       => 'ابتدا کلاس اضافه کنید!',
                'value'      => '__no_class__',
                'isSelected' => true,
            ],
        ];
    }

    foreach ( $form['fields'] as &$field ) {
        if ( (int) $field->id === GFCD_TARGET_FIELD_ID ) {
            $field->choices = $choices;
            break;
        }
    }

    return $form;
}

// Block submission if the placeholder value is still selected
add_filter( 'gform_validation', function( $result ) {
    if ( (int) $result['form']['id'] !== GFCD_TARGET_FORM_ID ) return $result;

    $submitted = rgpost( 'input_' . GFCD_TARGET_FIELD_ID );
    if ( $submitted === '' || $submitted === '__no_class__' ) {
        $result['is_valid'] = false;
        foreach ( $result['form']['fields'] as &$field ) {
            if ( (int) $field->id === GFCD_TARGET_FIELD_ID ) {
                $field->failed_validation  = true;
                $field->validation_message = 'ابتدا کلاس اضافه کنید!';
                break;
            }
        }
    }

    return $result;
} );