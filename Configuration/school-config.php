<?php
/*
Plugin Name: School Settings Panel
Description: تنظیمات مدرسه
Version: 2.0
Author: Mohammadreza
*/

if (!defined('ABSPATH'))
    exit;

/* -------- CSS (UNCHANGED STYLE SYSTEM) -------- */
add_action('admin_head', 'sg_custom_css');
add_action('wp_head', 'sg_custom_css');

function sg_custom_css()
{
    echo '<style>

/* ---------- Base Layout ---------- */

body {
    direction: rtl;
    background:#f5f7fa;
    font-family: system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
}

.sg-settings-panel{
    max-width:900px;
    margin:auto;
}

/* ---------- Typography ---------- */

label{
    font-size:1.05rem;
    font-weight:600;
    color:#2c2c2c;
}

h3{
    font-size:1.15rem;
    font-weight:700;
}

/* ---------- Buttons ---------- */

.button{
    background:linear-gradient(135deg,#4169e1,#3154c5);
    padding:14px;
    border:0;
    color:white;
    width:260px;
    font-size:1.05rem;
    border-radius:8px;
    transition:.2s ease;
    box-shadow:0 4px 14px rgba(0,0,0,.15);
}

.button:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 18px rgba(0,0,0,.2);
}

/* ---------- Cards ---------- */

.sg-settings-section{
    padding:22px;
    border-radius:14px;
    margin-bottom:22px;
    background:white;
    border:none;
    box-shadow:
        0 3px 8px rgba(0,0,0,.05),
        0 8px 22px rgba(0,0,0,.06);
}

/* ---------- Inputs ---------- */

.sg-input,
.sg-input-wide{
    width:100%;
    padding:10px 12px;
    border-radius:8px;
    border:1px solid #d5d9df;
    font-size:14px;
    transition:.2s;
    background:#fbfcfe;
}

.sg-input:focus,
.sg-input-wide:focus{
    outline:none;
    border-color:#4169e1;
    box-shadow:0 0 0 3px rgba(65,105,225,.15);
}

/* ---------- Descriptions ---------- */

.sg-field-desc{
    color:#6a6f76;
    font-size:13px;
    margin-top:6px;
    display:block;
    line-height:1.6;
}

/* ---------- Responsive ---------- */

@media(max-width:700px){
    .button{
        width:100%;
    }
}

</style>';
}

/* -------- Defaults -------- */
function sg_school_defaults()
{
    return [
        'school_name' => '',
        'school_type' => 'primary',
        'school_gender' => 'boys',
        'eitaa_channel' => '',
        'manager_eitaa' => ''
    ];
}

function sg_get_school_settings($user_id)
{
    $defaults = sg_school_defaults();
    $settings = [];

    foreach ($defaults as $key => $value) {
        $stored = get_user_meta($user_id, $key, true);
        $settings[$key] = ($stored === '') ? $value : $stored;
    }

    return $settings;
}

/* -------- Admin Menu -------- */
add_action('admin_menu', function () {
    add_menu_page(
        'تنظیمات مدرسه',
        'تنظیمات مدرسه',
        'read',
        'sg-school-settings',
        'sg_render_school_settings_page',
        'dashicons-building',
        26
    );
});

/* -------- Render Page -------- */
function sg_render_school_settings_page()
{
    echo sg_render_school_settings_form();
}

/* -------- Shortcode -------- */
add_shortcode('sg_school_settings', 'sg_render_school_settings_form');

function sg_render_school_settings_form()
{

    if (!is_user_logged_in())
        return '<p>لطفاً وارد شوید.</p>';

    $uid = get_current_user_id();
    $settings = sg_get_school_settings($uid);
    $html = '';

    if (isset($_POST['sg_save_school_settings'])) {

        update_user_meta($uid, 'school_name', sanitize_text_field($_POST['school_name']));
        update_user_meta($uid, 'school_type', sanitize_key($_POST['school_type']));
        update_user_meta($uid, 'school_gender', sanitize_key($_POST['school_gender']));
        update_user_meta($uid, 'eitaa_channel', sanitize_text_field($_POST['eitaa_channel']));
        update_user_meta($uid, 'manager_eitaa', sanitize_text_field($_POST['manager_eitaa']));

        $html .= '<div class="updated"><p>تنظیمات ذخیره شد</p></div>';
        $settings = sg_get_school_settings($uid);
    }

    ob_start();
    ?>

    <div class="sg-settings-panel">
        <form method="post">

            <!-- School Info -->
            <div class="sg-settings-section">
                <h3>اطلاعات مدرسه</h3>

                <label>نام مدرسه</label>
                <input type="text" name="school_name" value="<?= esc_attr($settings['school_name']) ?>" class="sg-input">

                <br><br>

                <label>نوع مدرسه</label>
                <select name="school_type" class="sg-input">
                    <option value="primary" <?= ($settings['school_type'] == 'primary') ? 'selected' : '' ?>>ابتدایی</option>
                    <option value="middle" <?= ($settings['school_type'] == 'middle') ? 'selected' : '' ?>>متوسطه اول</option>
                    <option value="high" <?= ($settings['school_type'] == 'high') ? 'selected' : '' ?>>متوسطه دوم</option>
                </select>

                <br><br>

                <label>جنسیت</label>
                <select name="school_gender" class="sg-input">
                    <option value="boys" <?= ($settings['school_gender'] == 'boys') ? 'selected' : '' ?>>پسرانه</option>
                    <option value="girls" <?= ($settings['school_gender'] == 'girls') ? 'selected' : '' ?>>دخترانه</option>
                </select>
            </div>

            <!-- Eitaa Info -->
            <div class="sg-settings-section">
                <h3>اطلاعات ایتا</h3>

                <label>آدرس کانال ایتا</label>
                <input type="text" placeholder="مثال: myschoolchannel" name="eitaa_channel"
                    value="<?= esc_attr($settings['eitaa_channel']) ?>" class="sg-input">

                <span class="sg-field-desc">
                    بدون @ وارد شود.
                </span>

                <br><br>

                <label>شماره ایتا مدیر</label>
                <input type="text" placeholder="مثال: 09123456789" name="manager_eitaa"
                    value="<?= esc_attr($settings['manager_eitaa']) ?>" class="sg-input">

                <span class="sg-field-desc">
                    شماره با صفر وارد شود.
                </span>
            </div>

            <button class="button button-primary" name="sg_save_school_settings">
                ذخیره تنظیمات
            </button>

        </form>
    </div>

    <?php
    return $html . ob_get_clean();
}