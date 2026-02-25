<?php
/*
Plugin Name: Settings Configuration
Description: تنظیمات پیکربندی پیام
Version: 2.0
Author: Mohammadreza
*/

if (!defined('ABSPATH'))
    exit;

/* ======================================================
   TABLE MANAGEMENT
====================================================== */

function sg_table_name()
{
    global $wpdb;
    return $wpdb->prefix . 'sg_config';
}

define('SG_CONFIG_DB_VERSION', '1.0');

function sg_create_table()
{
    global $wpdb;
    $table = sg_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  send_message TINYINT(1) NOT NULL DEFAULT 1,
  send_hour_channel TINYINT(2) NOT NULL DEFAULT 10,
  send_teacher TINYINT(1) NOT NULL DEFAULT 1,
  send_hour_teacher TINYINT(2) NOT NULL DEFAULT 18,
  name_prefix VARCHAR(20) NOT NULL DEFAULT 'mr',
  message_footer VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_id (user_id)
) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    update_option('sg_config_db_version', SG_CONFIG_DB_VERSION);
}

register_activation_hook(__FILE__, 'sg_create_table');

add_action('plugins_loaded', function () {
    if (get_option('sg_config_db_version') !== SG_CONFIG_DB_VERSION) {
        sg_create_table();
    }
});

function sg_insert_default_row($user_id)
{
    global $wpdb;
    $defaults = sg_config_defaults();
    $wpdb->query(
        $wpdb->prepare(
            "INSERT IGNORE INTO " . sg_table_name() . "
             (user_id, send_message, send_hour_channel, send_teacher, send_hour_teacher, name_prefix, message_footer)
             VALUES (%d, %d, %d, %d, %d, %s, %s)",
            $user_id,
            $defaults['send_message'],
            $defaults['send_hour_channel'],
            $defaults['send_teacher'],
            $defaults['send_hour_teacher'],
            $defaults['name_prefix'],
            $defaults['message_footer']
        )
    );
}
add_action('user_register', 'sg_insert_default_row');

/* ======================================================
   DATA HELPERS
====================================================== */

function sg_config_defaults()
{
    return [
        'send_message' => 1,
        'send_hour_channel' => 10,
        'send_teacher' => 1,
        'send_hour_teacher' => 18,
        'name_prefix' => 'mr',
        'message_footer' => '',
    ];
}

function sg_get_config_settings($user_id)
{
    global $wpdb;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM " . sg_table_name() . " WHERE user_id = %d LIMIT 1",
            $user_id
        ),
        ARRAY_A
    );

    if ($row === null) {
        sg_insert_default_row($user_id);
        return sg_config_defaults();
    }

    $defaults = sg_config_defaults();
    $settings = [];
    foreach ($defaults as $key => $default) {
        $settings[$key] = isset($row[$key]) && $row[$key] !== '' ? $row[$key] : $default;
    }
    return $settings;
}

function sg_save_config_settings($user_id, $data)
{
    global $wpdb;

    $wpdb->query(
        $wpdb->prepare(
            "INSERT INTO " . sg_table_name() . "
             (user_id, send_message, send_hour_channel, send_teacher, send_hour_teacher, name_prefix, message_footer)
             VALUES (%d, %d, %d, %d, %d, %s, %s)
             ON DUPLICATE KEY UPDATE
                send_message      = VALUES(send_message),
                send_hour_channel = VALUES(send_hour_channel),
                send_teacher      = VALUES(send_teacher),
                send_hour_teacher = VALUES(send_hour_teacher),
                name_prefix       = VALUES(name_prefix),
                message_footer    = VALUES(message_footer)",
            $user_id,
            $data['send_message'],
            $data['send_hour_channel'],
            $data['send_teacher'],
            $data['send_hour_teacher'],
            $data['name_prefix'],
            $data['message_footer']
        )
    );
}

/* ======================================================
   CSS
====================================================== */

add_action('admin_head', 'sg_config_css');
add_action('wp_head', 'sg_config_css');

function sg_config_css()
{
    echo '<style>

body {
    direction: rtl;
    background:#f5f7fa;
    font-family: system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
}

.sg-settings-panel{
    max-width:900px;
    margin:auto;
}

label{
    font-size:1.05rem;
    font-weight:600;
    color:#2c2c2c;
}

h3{
    font-size:1.15rem;
    font-weight:700;
}

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
    text-align:center;
    display:inline-block;
    text-decoration:none;
}

.button:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 18px rgba(0,0,0,.2);
}

.sg-settings-section{
    padding:22px;
    border-radius:14px;
    margin-bottom:22px;
    background:white;
    box-shadow:
        0 3px 8px rgba(0,0,0,.05),
        0 8px 22px rgba(0,0,0,.06);
}

.sg-input{
    width:100%;
    padding:10px 12px;
    border-radius:8px;
    border:1px solid #d5d9df;
    font-size:14px;
    background:#fbfcfe;
    height: 53px !important;
}

.sg-input:focus{
    outline:none;
    border-color:#4169e1;
    box-shadow:0 0 0 3px rgba(65,105,225,.15);
}

.sg-field-desc{
    color:#6a6f76;
    font-size:13px;
    margin-top:6px;
    display:block;
}

.mihanpanelpanel form input[type=radio]:after{
    display: none !important;
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
        'Settings Configuration',
        'Settings Config',
        'read',
        'sg-settings-config',
        'sg_render_config_page',
        'dashicons-admin-tools',
        27
    );
});

function sg_render_config_page()
{
    echo sg_render_config_form();
}

/* ======================================================
   SHORTCODE + FORM
====================================================== */

add_shortcode('sg_settings_config', 'sg_render_config_form');

function sg_render_config_form()
{
    if (!is_user_logged_in())
        return '<p>لطفاً وارد شوید.</p>';

    $uid = get_current_user_id();
    $settings = sg_get_config_settings($uid);
    $html = '';

    if (isset($_POST['sg_save_config'])) {

        $data = [
            'send_message' => isset($_POST['send_message']) ? intval($_POST['send_message']) : 0,
            'send_hour_channel' => intval($_POST['send_hour_channel']),
            'send_teacher' => isset($_POST['send_teacher']) ? intval($_POST['send_teacher']) : 0,
            'send_hour_teacher' => intval($_POST['send_hour_teacher']),
            'name_prefix' => sanitize_text_field($_POST['name_prefix']),
            'message_footer' => sanitize_text_field($_POST['message_footer']),
        ];

        sg_save_config_settings($uid, $data);

        $html .= '<div class="updated"><p>تنظیمات ذخیره شد</p></div>';
        $settings = sg_get_config_settings($uid);
    }

    ob_start();
    ?>

    <div class="sg-settings-panel">
        <form method="post">

            <!-- قالب -->
            <div class="sg-settings-section">
                <h3>قالب</h3>
                <a href="#" class="button">انتخاب قالب</a>
            </div>

            <!-- ارسال پیام -->
            <div class="sg-settings-section">
                <h3>ارسال پیام در کانال ایتا</h3>

                <label style="
    display: inline-flex !important;
    justify-content: center !important;
    align-items: center !important;
">
                    <input type="radio" name="send_message" value="1" <?= ($settings['send_message'] == 1) ? 'checked' : '' ?>>
                    فعال
                </label>

                <label style="
    display: inline-flex !important;
    justify-content: center !important;
    align-items: center !important;
">
                    <input type="radio" name="send_message" value="0" <?= ($settings['send_message'] == 0) ? 'checked' : '' ?>>
                    غیرفعال
                </label>

                <br><br>

                <label>ساعت ارسال در کانال</label>
                <select name="send_hour_channel" class="sg-input">
                    <?php foreach ([7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20] as $h): ?>
                        <option value="<?= $h ?>" <?= ($settings['send_hour_channel'] == $h) ? 'selected' : '' ?>>
                            <?= $h ?>:00
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- ارسال به معلم -->
            <div class="sg-settings-section">
                <h3>ارسال پیام به ایتای معلم (روز قبل از تولد)</h3>

                <label style="
    display: inline-flex !important;
    justify-content: center !important;
    align-items: center !important;
">
                    <input type="radio" name="send_teacher" value="1" <?= ($settings['send_teacher'] == 1) ? 'checked' : '' ?>>
                    فعال
                </label>

                <label style="
    display: inline-flex !important;
    justify-content: center !important;
    align-items: center !important;
">
                    <input type="radio" name="send_teacher" value="0" <?= ($settings['send_teacher'] == 0) ? 'checked' : '' ?>>
                    غیرفعال
                </label>

                <br><br>

                <label>ساعت ارسال به معلم</label>
                <select name="send_hour_teacher" class="sg-input">
                    <?php foreach ([13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23] as $h): ?>
                        <option value="<?= $h ?>" <?= ($settings['send_hour_teacher'] == $h) ? 'selected' : '' ?>>
                            <?= $h ?>:00
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- پیشوند -->
            <div class="sg-settings-section">
                <h3>پیشوند نام</h3>
                <input type="text" name="name_prefix" value="<?= esc_attr($settings['name_prefix']) ?>" class="sg-input">

                <span class="sg-field-desc">
                    مثلا: دانش آموز عزیز، دانشجوی عزیز، جناب آقای، سرکار خانم و یا ...
                </span>
            </div>

            <!-- پاورقی -->
            <div class="sg-settings-section">
                <h3>پاورقی پیام</h3>

                <input type="text" name="message_footer" value="<?= esc_attr($settings['message_footer']) ?>"
                    class="sg-input">

                <span class="sg-field-desc">
                    مثال: مدیریت دبستان شاهد
                </span>
            </div>

            <button class="button button-primary" name="sg_save_config">
                ذخیره تنظیمات
            </button>

        </form>
    </div>

    <?php
    return $html . ob_get_clean();
}