<?php
/*
Plugin Name: School Settings Panel
Description: تنظیمات مدرسه
Version: 2.0
Author: Mohammadreza
*/

if (!defined('ABSPATH'))
    exit;

/* ======================================================
   TABLE MANAGEMENT
====================================================== */

define('SG_SCHOOL_DB_VERSION', '1.0');

function sg_school_table_name()
{
    global $wpdb;
    return $wpdb->prefix . 'sg_school';
}

function sg_create_school_table()
{
    global $wpdb;
    $table = sg_school_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  school_name VARCHAR(255) NOT NULL DEFAULT '',
  school_type VARCHAR(20) NOT NULL DEFAULT 'primary',
  school_gender VARCHAR(10) NOT NULL DEFAULT 'boys',
  eitaa_channel VARCHAR(100) NOT NULL DEFAULT '',
  manager_eitaa VARCHAR(20) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_id (user_id)
) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    update_option('sg_school_db_version', SG_SCHOOL_DB_VERSION);
}

// Run on activation (standard path)
register_activation_hook(__FILE__, 'sg_create_school_table');

// Also run on plugins_loaded if table version is missing/outdated
add_action('plugins_loaded', function () {
    if (get_option('sg_school_db_version') !== SG_SCHOOL_DB_VERSION) {
        sg_create_school_table();
    }
});

/* ======================================================
   DATA HELPERS
====================================================== */

function sg_school_defaults()
{
    return [
        'school_name' => '',
        'school_type' => 'primary',
        'school_gender' => 'boys',
        'eitaa_channel' => '',
        'manager_eitaa' => '',
    ];
}

function sg_insert_default_school_row($user_id)
{
    global $wpdb;
    $d = sg_school_defaults();
    $wpdb->query(
        $wpdb->prepare(
            "INSERT IGNORE INTO " . sg_school_table_name() . "
             (user_id, school_name, school_type, school_gender, eitaa_channel, manager_eitaa)
             VALUES (%d, %s, %s, %s, %s, %s)",
            $user_id,
            $d['school_name'],
            $d['school_type'],
            $d['school_gender'],
            $d['eitaa_channel'],
            $d['manager_eitaa']
        )
    );
}
add_action('user_register', 'sg_insert_default_school_row');

function sg_get_school_settings($user_id)
{
    global $wpdb;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM " . sg_school_table_name() . " WHERE user_id = %d LIMIT 1",
            $user_id
        ),
        ARRAY_A
    );

    if ($row === null) {
        sg_insert_default_school_row($user_id);
        return sg_school_defaults();
    }

    $settings = [];
    foreach (sg_school_defaults() as $key => $default) {
        $settings[$key] = (isset($row[$key]) && $row[$key] !== '') ? $row[$key] : $default;
    }
    return $settings;
}

function sg_save_school_settings($user_id, $data)
{
    global $wpdb;

    $wpdb->query(
        $wpdb->prepare(
            "INSERT INTO " . sg_school_table_name() . "
             (user_id, school_name, school_type, school_gender, eitaa_channel, manager_eitaa)
             VALUES (%d, %s, %s, %s, %s, %s)
             ON DUPLICATE KEY UPDATE
                school_name   = VALUES(school_name),
                school_type   = VALUES(school_type),
                school_gender = VALUES(school_gender),
                eitaa_channel = VALUES(eitaa_channel),
                manager_eitaa = VALUES(manager_eitaa)",
            $user_id,
            $data['school_name'],
            $data['school_type'],
            $data['school_gender'],
            $data['eitaa_channel'],
            $data['manager_eitaa']
        )
    );
}

/* ======================================================
   CSS (UNCHANGED STYLE SYSTEM)
====================================================== */

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

.sg-input{
    height: 53px !important;
}

.updated {
    background: #40ff4040;
    padding: 1rem;
    border-radius: 5px;
    border: none;
    border-right: 5px solid #008700;
    margin-bottom: 2rem;
}

.updated p {
    margin: 0;
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

/* ======================================================
   ADMIN MENU
====================================================== */

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

function sg_render_school_settings_page()
{
    echo sg_render_school_settings_form();
}

/* ======================================================
   SHORTCODE + FORM
====================================================== */

add_shortcode('sg_school_settings', 'sg_render_school_settings_form');

function sg_render_school_settings_form()
{

    if (!is_user_logged_in())
        return '<p>لطفاً وارد شوید.</p>';

    $uid = get_current_user_id();
    $settings = sg_get_school_settings($uid);
    $html = '';

    if (isset($_POST['sg_save_school_settings'])) {

        $data = [
            'school_name' => sanitize_text_field($_POST['school_name']),
            'school_type' => sanitize_key($_POST['school_type']),
            'school_gender' => sanitize_key($_POST['school_gender']),
            'eitaa_channel' => sanitize_text_field($_POST['eitaa_channel']),
            'manager_eitaa' => sanitize_text_field($_POST['manager_eitaa']),
        ];

        sg_save_school_settings($uid, $data);

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
                    <option value="high" <?= ($settings['school_type'] == 'high') ? 'selected' : '' ?>>دبیرستان</option>
                    <option value="technical" <?= ($settings['school_type'] == 'technical') ? 'selected' : '' ?>>هنرستان</option>
                    <option value="institute" <?= ($settings['school_type'] == 'institute') ? 'selected' : '' ?>>آموزشگاه (خارج از آموزش و پرورش)</option>
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