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
        0 3px 8px rgba(0,0,0,.05) !important,
        0 8px 22px rgba(0,0,0,.06);
}

/* ---------- Inputs ---------- */

.sg-input,
.sg-input-wide{
    width:100%;
    padding:10px 12px;
    border-radius:8px;
    border:1px solid #d5d9df;
    font-size:12px;
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

.alert-span{
    color:#c0392b;
    font-weight:600;
}

.blue{
    color:#4169e1;
    font-weight:600;
}

a.blue{
    font-size:13px;
}

/* ---------- Frames Grid ---------- */

.sg-frames-grid{
    display:flex;
    flex-wrap:wrap;
    gap:18px;
}

.sg-frame-item{
    border:2px solid transparent;
    border-radius:14px;
    padding:12px;
    text-align:center;
    width:150px;
    cursor:pointer;
    background:white;
    transition:.25s ease;
    position:relative;
    box-shadow:0 2px 8px rgba(0,0,0,.05);
}

.sg-frame-item:hover{
    transform:translateY(-4px);
    box-shadow:0 10px 22px rgba(0,0,0,.12);
}

.sg-frame-item.selected{
    border-color:#4169e1;
    box-shadow:0 0 0 3px rgba(65,105,225,.18);
}

.sg-frame-item img{
    width:125px;
    border-radius:8px;
    margin-bottom:6px;
}

.sg-frame-item input{
    display:none;
}

/* Tooltip */

.sg-frame-item span.sg-tooltip{
    visibility:hidden;
    background:#2c2c2c;
    color:#fff;
    border-radius:6px;
    padding:6px 8px;
    position:absolute;
    bottom:115%;
    left:50%;
    transform:translateX(-50%);
    opacity:0;
    transition:.2s;
    width:160px;
    font-size:12px;
}

.sg-frame-item:hover span.sg-tooltip{
    visibility:visible;
    opacity:1;
}

/* ---------- Switch ---------- */

.sg-switch{
    position:relative;
    display:inline-block;
    width:52px;
    height:26px;
}

.sg-switch input{
    opacity:0;
    width:0;
    height:0;
}

.sg-slider{
    position:absolute;
    cursor:pointer;
    inset:0;
    background:#d0d4da;
    border-radius:30px;
    transition:.25s;
}

.sg-slider:before{
    content:"";
    position:absolute;
    height:20px;
    width:20px;
    left:3px;
    bottom:3px;
    background:white;
    border-radius:50%;
    transition:.25s;
    box-shadow:0 2px 5px rgba(0,0,0,.2);
}

.sg-switch input:checked + .sg-slider{
    background:linear-gradient(135deg,#4169e1,#3154c5);
}

.sg-switch input:checked + .sg-slider:before{
    transform:translateX(26px);
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
        justify-content:center;
    }

    .sg-frame-item{
        width:45%;
    }

    .button{
        width:100%;
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