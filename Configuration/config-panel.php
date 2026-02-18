<?php
/*
Plugin Name: GF User Class Filter & پیام‌های کاربری
Description: پر کردن کلاس دانش‌آموز + تنظیمات پیام
Version: 1.5
Author: Mohammadreza
*/

/* -------- CSS for frames grid + RTL + switches + tooltips -------- */
add_action('admin_head', 'sg_custom_css');
add_action('wp_head', 'sg_custom_css');

function sg_custom_css(){
echo '<style>

/* ---------- Base Layout ---------- */

body {
    direction: rtl !important;
    background:#f5f7fa !important;
    font-family: system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif !important;
}

.sg-settings-panel{
    max-width:900px !important;
    margin:auto !important;
}

/* ---------- Typography ---------- */

label{
    font-size:1.05rem !important;
    font-weight:600 !important;
    color:#2c2c2c !important;
}

h3{
    font-size:1.15rem !important;
    font-weight:700 !important;
}

/* ---------- Buttons ---------- */

.button{
    background:linear-gradient(135deg,#4169e1,#3154c5) !important;
    padding:14px !important;
    border:0 !important;
    color:white !important;
    width:260px !important;
    font-size:1.05rem !important;
    border-radius:8px !important;
    transition:.2s ease !important;
    box-shadow:0 4px 14px rgba(0,0,0,.15) !important;
}

.button:hover{
    transform:translateY(-2px) !important;
    box-shadow:0 8px 18px rgba(0,0,0,.2) !important;
}

/* ---------- Cards ---------- */

.sg-settings-section{
    padding:22px !important;
    border-radius:14px !important;
    margin-bottom:22px !important;
    background:white !important;
    border:none !important;
    box-shadow:
        0 3px 8px rgba(0,0,0,.05) !important,
        0 8px 22px rgba(0,0,0,.06) !important;
}

/* ---------- Inputs ---------- */

.sg-input,
.sg-input-wide{
    width:100% !important;
    padding:10px 12px !important;
    border-radius:8px !important;
    border:1px solid #d5d9df !important;
    font-size:12px !important;
    transition:.2s !important;
    background:#fbfcfe !important;
}

.sg-input:focus,
.sg-input-wide:focus{
    outline:none !important;
    border-color:#4169e1 !important;
    box-shadow:0 0 0 3px rgba(65,105,225,.15) !important;
}

/* ---------- Descriptions ---------- */

.sg-field-desc{
    color:#6a6f76 !important;
    font-size:13px !important;
    margin-top:6px !important;
    display:block !important;
    line-height:1.6 !important;
}

.alert-span{
    color:#c0392b !important;
    font-weight:600 !important;
}

.blue{
    color:#4169e1 !important;
    font-weight:600 !important;
}

a.blue{
    font-size:13px !important;
}

/* ---------- Frames Grid ---------- */

.sg-frames-grid{
    display:flex !important;
    flex-wrap:wrap !important;
    gap:18px !important;
}

.sg-frame-item{
    border:2px solid transparent !important;
    border-radius:14px !important;
    padding:12px !important;
    text-align:center !important;
    width:150px !important;
    cursor:pointer !important;
    background:white !important;
    transition:.25s ease !important;
    position:relative !important;
    box-shadow:0 2px 8px rgba(0,0,0,.05) !important;
}

.sg-frame-item:hover{
    transform:translateY(-4px) !important;
    box-shadow:0 10px 22px rgba(0,0,0,.12) !important;
}

.sg-frame-item.selected{
    border-color:#4169e1 !important;
    box-shadow:0 0 0 3px rgba(65,105,225,.18) !important;
}

.sg-frame-item img{
    width:125px !important;
    border-radius:8px !important;
    margin-bottom:6px !important;
}

.sg-frame-item input{
    display:none !important;
}

/* Tooltip */

.sg-frame-item span.sg-tooltip{
    visibility:hidden !important;
    background:#2c2c2c !important;
    color:#fff !important;
    border-radius:6px !important;
    padding:6px 8px !important;
    position:absolute !important;
    bottom:115% !important;
    left:50% !important;
    transform:translateX(-50%) !important;
    opacity:0 !important;
    transition:.2s !important;
    width:160px !important;
    font-size:12px !important;
}

.sg-frame-item:hover span.sg-tooltip{
    visibility:visible !important;
    opacity:1 !important;
}

/* ---------- Switch ---------- */

.sg-switch{
    position:relative !important;
    display:inline-block !important;
    width:52px !important;
    height:26px !important;
}

.sg-switch input{
    opacity:0 !important;
    width:0 !important;
    height:0 !important;
}

.sg-slider{
    position:absolute !important;
    cursor:pointer !important;
    inset:0 !important;
    background:#d0d4da !important;
    border-radius:30px !important;
    transition:.25s !important;
}

.sg-slider:before{
    content:"" !important;
    position:absolute !important;
    height:20px !important;
    width:20px !important;
    left:3px !important;
    bottom:3px !important;
    background:white !important;
    border-radius:50% !important;
    transition:.25s !important;
    box-shadow:0 2px 5px rgba(0,0,0,.2) !important;
}

.sg-switch input:checked + .sg-slider{
    background:linear-gradient(135deg,#4169e1,#3154c5) !important;
}

.sg-switch input:checked + .sg-slider:before{
    transform:translateX(26px) !important;
}


.mihanpanelpanel form input[type=checkbox]:after{
    display: none !important;
}

.mihanpanelpanel form input[type=radio]:after {
    display: none !important;
}

.mihanpanelpanel form input[type=checkbox] {
    visibility: hidden !important;
}
/* ---------- Responsive ---------- */

@media(max-width:700px){

    .sg-frames-grid{
        justify-content:center !important;
    }

    .sg-frame-item{
        width:45% !important;
    }

    .button{
        width:100% !important;
    }
}

</style>';

}

/* -------- Prefix Options Map -------- */
// Keys are stable ASCII identifiers stored in DB.
// Labels are the Persian display strings shown in the UI.
function sg_get_prefix_options() {
    return [
        'mr'       => 'جناب آقای',
        'ms'       => 'سرکار خانم',
        'student1' => 'دانش آموز عزیز،',
        'student2' => 'دانش آموز گرامی،',
        'child'    => 'فرزند عزیز،',
        'uni1'     => 'دانشجوی عزیز',
        'uni2'     => 'دانشجوی گرامی',
        'none'     => 'بدون پیشوند',
    ];
}

/* -------- Defaults Engine -------- */
function sg_get_user_settings($user_id){
    $defaults = [
        'selected_frames' => [1,2,3,4,5,6],
        'send_hour_congrats' => 10,
        'alert_hour_teacher' => 18,
        'eitaa_channel_id' => '',
        'message_footer' => '',
        'name_prefix' => 'mr',   // default is now a stable key, not a Persian string
        'include_poem' => 1,
        'trial_reminder_enabled' => 1
    ];

    $settings = [];
    foreach($defaults as $key => $value){
        $stored = get_user_meta($user_id,$key,true);
        $settings[$key] = ($stored === '') ? $value : $stored;
    }

    return $settings;
}

/* -------- Admin Menu -------- */
add_action('admin_menu', function(){
    add_menu_page(
        'تنظیمات پیام دانش‌آموز',
        'تنظیمات پیام',
        'read',
        'sg-message-settings',
        'sg_render_settings_page',
        'dashicons-admin-generic',
        26
    );
});

/* -------- Admin Page Render -------- */
function sg_render_settings_page(){
    echo sg_render_settings_form_for_shortcode();
}

/* -------- Shortcode Render Function -------- */
function sg_render_settings_form_for_shortcode() {
    if(!is_user_logged_in()) return '<p>لطفاً وارد شوید.</p>';
    $uid = get_current_user_id();
    $settings = sg_get_user_settings($uid);
    $html = '';

    // Save handler
    if(isset($_POST['sg_save_settings'])){
        update_user_meta($uid,'selected_frames', array_map('intval', $_POST['selected_frames'] ?? []));
        update_user_meta($uid,'send_hour_congrats', intval($_POST['send_hour_congrats']));
        update_user_meta($uid,'alert_hour_teacher', intval($_POST['alert_hour_teacher']));
        update_user_meta($uid,'eitaa_channel_id', sanitize_text_field($_POST['eitaa_channel_id']));
        update_user_meta($uid,'message_footer', sanitize_text_field($_POST['message_footer']));

        // Use sanitize_key() — safe for our ASCII keys, no Unicode corruption possible
        $valid_prefix_keys = array_keys(sg_get_prefix_options());
        $submitted_prefix  = sanitize_key($_POST['name_prefix'] ?? '');
        update_user_meta($uid,'name_prefix', in_array($submitted_prefix, $valid_prefix_keys) ? $submitted_prefix : 'mr');

        update_user_meta($uid,'include_poem', isset($_POST['include_poem']) ? 1 : 0);
        update_user_meta($uid,'trial_reminder_enabled', isset($_POST['trial_reminder_enabled']) ? 1 : 0);
        $html .= '<div class="updated"><p>تنظیمات ذخیره شد</p></div>';
        $settings = sg_get_user_settings($uid); // refresh
    }

    ob_start();
    ?>
    <div class="sg-settings-panel">
        <form method="post">
            <!-- Frames -->
            <div class="sg-settings-section"><h3>قالب‌ها</h3>
                <div class="sg-frames-grid">
                <?php
                $base = "https://your-image-server.com/frame-preview/";
                foreach([1,2,3,4,5,6] as $f):
                    $checked = in_array($f,$settings['selected_frames']) ? 'checked':'';
                    $selected_class = $checked ? 'selected':'';
                ?>
                    <label class="sg-frame-item <?= $selected_class ?>">
                        <img src="<?= $base.$f ?>.jpg" alt="قالب <?= $f ?>">
                        <input type="checkbox" name="selected_frames[]" value="<?= $f ?>" <?= $checked ?>>
                        قالب <?= $f ?>
                        <span class="sg-tooltip">نمایش پیش‌نمایش قالب شماره <?= $f ?></span>
                    </label>
                <?php endforeach; ?>
                </div>
            </div>

            <!-- Message Timing -->
            <div class="sg-settings-section"><h3>زمان‌بندی پیام‌ها</h3>
                <label>ساعت ارسال تبریک: </label>
                <select name="send_hour_congrats" class="sg-input">
                    <?php foreach([8,10,12] as $h): ?>
                        <option value="<?= $h ?>" <?= ($settings['send_hour_congrats']==$h)?'selected':'' ?>><?= $h ?>:00</option>
                    <?php endforeach; ?>
                </select>
                <span class="sg-field-desc">زمان ارسال پیام تبریک در کانال یا گروه</span><br>
                
                <label>ساعت هشدار معلم: </label>
                <select name="alert_hour_teacher" class="sg-input">
                    <?php foreach(range(16,21) as $h): ?>
                        <option value="<?= $h ?>" <?= ($settings['alert_hour_teacher']==$h)?'selected':'' ?>><?= $h ?>:00</option>
                    <?php endforeach; ?>
                </select>
                <span class="sg-field-desc">ساعت ارسال هشدار به معلم در روز قبل از تولد بوسیله SMS - <span class="alert-span">نیازمند خرید اشتراک سالانه پیامک</span></span>
            </div>

            <!-- Channel & Footer -->
            <div class="sg-settings-section"><h3>اطلاعات پیام</h3>
                <label>آیدی کانال یا گروه ایتا: </label>
                <input type="text" placeholder="لطفا بدون @ وارد کنید." name="eitaa_channel_id" value="<?= esc_attr($settings['eitaa_channel_id']) ?>" class="sg-input">
                <span class="sg-field-desc">آیدی کانال یا گروهی که پیام‌ها ارسال می‌شوند <span class="alert-span">اگر خالی بگذارید یا اشتباه وارد کنید، پیامی ارسال نخواهد شد!</span></span></span>
                    <a href="https://tahanic.ir/get-eitaa-id" class="blue">اگر نمیدانید چگونه آیدی کانال یا گروه را پیدا کنید، کلیک کنید.</a><br><br>

                <label>پاورقی پیام: </label>
                <input type="text" placeholder="مثلا: با آرزوی بهترین ها، مدیریت دبستان آزادگان" name="message_footer" value="<?= esc_attr($settings['message_footer']) ?>" class="sg-input-wide">
                <span class="sg-field-desc">متنی که در انتهای پیام‌ها اضافه می‌شود</span>
            </div>

            <!-- Prefix -->
            <div class="sg-settings-section"><h3>پیشوند نام</h3>
                <?php foreach(sg_get_prefix_options() as $key => $label): ?>
                    <label>
                        <input type="radio"
                               name="name_prefix"
                               value="<?= esc_attr($key) ?>"
                               <?= ($settings['name_prefix'] === $key) ? 'checked' : '' ?>>
                        <?= esc_html($label) ?>
                    </label><br>
                <?php endforeach; ?>
                <span class="sg-field-desc">انتخاب پیشوند برای نام دانش‌آموز در پیام</span>
            </div>

            <!-- Switches -->
            <div class="sg-settings-section"><h3>گزینه‌ها</h3>
                <label>ارسال شعر: </label>
                <label class="sg-switch"><input type="checkbox" name="include_poem" <?= ($settings['include_poem']? 'checked':'') ?>><span class="sg-slider"></span></label>
                <span class="sg-field-desc">آیا شعر زیبایی همراه پیام ارسال شود؟</span><br><br>

                <label>هشدار پایان پکیج سالانه: </label>
                <label class="sg-switch"><input type="checkbox" name="trial_reminder_enabled" <?= ($settings['trial_reminder_enabled']? 'checked':'') ?>><span class="sg-slider"></span></label>
                <span class="sg-field-desc">ارسال پیامک هشدار خرید اشتراک جدید در صورتی که خرید نکرده باشید و 10 روز به پایان مانده باشد <span class="blue">(رایگان)</span></span>
            </div>

            <button class="button button-primary" name="sg_save_settings">ذخیره تنظیمات</button>
        </form>
    </div>
    <?php
    return $html . ob_get_clean();
}

/* -------- Shortcode -------- */
add_shortcode('sg_user_settings', 'sg_render_settings_form_for_shortcode');

/* -------- JS for clickable frames -------- */
add_action('wp_footer', function() { ?>
<script>
document.querySelectorAll('.sg-frame-item').forEach(item => {
    item.addEventListener('click', e => {
        const checkbox = item.querySelector('input[type=checkbox]');
        checkbox.checked = !checkbox.checked;
        item.classList.toggle('selected', checkbox.checked);
    });
});
</script>
<?php });