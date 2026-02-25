<?php
/**
 * Plugin Name: GF Class Auto-Fill Dropdown
 * Description: Fills a specific dropdown field in one Gravity Form with the current user's entries from another Gravity Form.
 * Version:     1.0.0
 */

if (!defined('ABSPATH'))
    exit;

// ── CONFIGURATION ─────────────────────────────────────────────────────────────
define('GFCA_SOURCE_FORM_ID', 2); // ID of the Classes form
define('GFCA_SOURCE_FIELD_ID', 1); // Field ID inside the Classes form that holds the class name
define('GFCA_TARGET_FORM_ID', 1); // ID of the form that has the dropdown
define('GFCA_TARGET_FIELD_ID', 8); // Field ID of the dropdown to populate
// ─────────────────────────────────────────────────────────────────────────────

add_filter('gform_pre_render', 'gfca_fill_dropdown');
add_filter('gform_pre_validation', 'gfca_fill_dropdown');
add_filter('gform_pre_submission_filter', 'gfca_fill_dropdown');
add_filter('gform_admin_pre_render', 'gfca_fill_dropdown');

function gfca_fill_dropdown($form)
{
    if ((int) $form['id'] !== GFCA_TARGET_FORM_ID) {
        return $form;
    }

    $user_id = get_current_user_id();

    $entries = GFAPI::get_entries(
        GFCA_SOURCE_FORM_ID,
        [
            'status' => 'active',
            'field_filters' => [
                ['key' => 'created_by', 'value' => $user_id],
            ],
        ],
        ['key' => 'date_created', 'direction' => 'ASC'],
        ['offset' => 0, 'page_size' => 200]
    );

    $choices = [];

    if (!is_wp_error($entries) && !empty($entries)) {
        foreach ($entries as $entry) {
            $name = rgar($entry, (string) GFCA_SOURCE_FIELD_ID);
            if (empty($name))
                continue;

            $choices[] = [
                'text' => $name,
                'value' => $name,
                'isSelected' => false,
            ];
        }
    }

    // No classes found — show a non-submittable placeholder
    if (empty($choices)) {
        $choices = [
            [
                'text' => 'ابتدا کلاس اضافه کنید!',
                'value' => '',
                'isSelected' => true,
            ],
        ];
    }

    foreach ($form['fields'] as &$field) {
        if ((int) $field->id === GFCA_TARGET_FIELD_ID) {
            $field->choices = $choices;
            break;
        }
    }

    return $form;
}

// Block submission if the placeholder value slips through
add_filter('gform_validation', function ($result) {
    if ((int) $result['form']['id'] !== GFCA_TARGET_FORM_ID) {
        return $result;
    }

    if (rgpost('input_' . GFCA_TARGET_FIELD_ID) === '') {
        $result['is_valid'] = false;

        foreach ($result['form']['fields'] as &$field) {
            if ((int) $field->id === GFCA_TARGET_FIELD_ID) {
                $field->failed_validation = true;
                $field->validation_message = 'ابتدا کلاس اضافه کنید!';
                break;
            }
        }

        $result['form'] = $result['form'];
    }

    return $result;
});