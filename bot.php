<?php
// ============================================
// CONFIGURATION - EDIT THESE VALUES
// ============================================
define('BOT_TOKEN', '8524655144:AAGD-9O0vdmi_v6Ph-W7C2VPvDr5xI52Bgk'); // Get from @BotFather
define('TARGET_ID', '313107992'); // Your personal/group chat ID
define('CHANNEL_USERNAME', '@drmesbah'); // Your channel username with @
define('SESSION_FILE', 'sessions.txt'); // File to store user sessions

// ============================================
// AVAILABLE SERVICES (Updated List)
// ============================================
$services = [
    'لابیوپلاستی' => '🌸 لابیوپلاستی',
    'تزریق چربی' => '💉 تزریق چربی',
    'تزریق فیلر' => '💋 تزریق فیلر',
    'ترانسفر چربی' => '🔄 ترانسفر چربی',
    'ابدومینوپلاستی' => '🩹 ابدومینوپلاستی',
    'پیکرتراشی' => '✂️ جراحی پیکرتراشی',
    'زیباسازی پوست' => '✨ زیباسازی پوست',
    'لیفت صورت' => '👩 لیفت صورت',
    'بوتاکس' => '😊 بوتاکس',
    'لیزر موهای زائد' => '🚫 لیزر موهای زائد',
    'میکرونیدلینگ' => '🪡 میکرونیدلینگ',
    // Cosmetic Gynecology Services (Newly Added)
    'تنگی واژن با لیزر' => '⚡ تنگی واژن با لیزر',
    'تقویت نقطه جی' => '🎯 تقویت نقطه جی (G-spot)',
    'رفع تیرگی واژن' => '🔆 رفع تیرگی واژن',
    'تزریق چربی به واژن' => '💉 تزریق چربی به واژن',
    'لابیاپلاستی' => '🌸 لابیاپلاستی (زیبایی لابیا)',
    'تزریق چربی به لابیا ماژور' => '💖 تزریق چربی به لابیا ماژور',
    'جراحی لابیاپلاستی همزمان با تزریق چربی' => '🔄 لابیاپلاستی + تزریق چربی',
    'لیفت واژن' => '⬆️ لیفت واژن',
    'تنگ کردن واژن با نخ' => '🧵 تنگ کردن واژن با نخ',
    'عمل پرینورافی' => '✨ عمل پرینورافی (تنگ کردن واژن و پرینه)',
    'درمان واژینیسموس' => '🩺 درمان واژینیسموس (دخول دردناک)',
    'عمل کورتاژ تشخیصی' => '🔬 عمل کورتاژ تشخیصی'
];

// ============================================
// CORE BOT LOGIC
// ============================================
$content = file_get_contents('php://input');
$update = json_decode($content, true);

$chat_id = $update['message']['chat']['id'] ?? $update['callback_query']['message']['chat']['id'] ?? null;
$user_id = $update['message']['from']['id'] ?? $update['callback_query']['from']['id'] ?? null;
$text = $update['message']['text'] ?? $update['callback_query']['data'] ?? '';
$message_id = $update['callback_query']['message']['message_id'] ?? null;
$voice = $update['message']['voice'] ?? null;

// Answer callback queries immediately
if (isset($update['callback_query']['id'])) {
    file_get_contents("https://api.telegram.org/bot" . BOT_TOKEN . "/answerCallbackQuery?callback_query_id=" . $update['callback_query']['id']);
}

if (!$chat_id || !$user_id) exit;

// Load user session
$sessions = file_exists(SESSION_FILE) ? json_decode(file_get_contents(SESSION_FILE), true) : [];
$session_key = $user_id;
$session = $sessions[$session_key] ?? ['step' => 0];

// Channel membership check
function checkMembership($user_id) {
    global $services; // Keep scope for services
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getChatMember?chat_id=" . CHANNEL_USERNAME . "&user_id=$user_id";
    $response = json_decode(file_get_contents($url), true);
    $status = $response['result']['status'] ?? 'left';
    return in_array($status, ['member', 'administrator', 'creator']);
}

// Handle commands and conversation flow
if ($text == '/start') {
    if (!checkMembership($user_id)) {
        $join_link = "https://t.me/" . ltrim(CHANNEL_USERNAME, '@');
        sendMessage($chat_id, "🌟 سلام عزیزم! 👩‍⚕️\nبرای دسترسی به خدمات کلینیک زیبایی دکتر سمیرا مصباح، لطفاً اول در کانال ما عضو بشید.\n\nلینک عضویت: $join_link\n\nبعد از عضویت، دوباره /start رو بزنید.");
        exit;
    }
    $session = ['step' => 1, 'user_id' => $user_id];
    sendMessage($chat_id, "🌟 خوش آمدید به کلینیک زیبایی دکتر سمیرا مصباح! 👩‍⚕️\nشما عضو کانال ما هستید، عالیه! حالا فرم مشاوره رو براتون پر میکنیم. 💄\n\n**نام کاملتون رو وارد کنید:**");
    
} elseif ($session['step'] == 1) {
    $session['name'] = trim($text);
    $session['step'] = 2;
    sendMessage($chat_id, "ممنون {$session['name']} جان! 🌸\n\n**شماره تلفن همراهتون رو وارد کنید** (مثل ۰۹۱۲۳۴۵۶۷۸۹):");
    
} elseif ($session['step'] == 2) {
    if (preg_match('/^09[0-9]{9}$/', $text)) {
        $session['phone'] = $text;
        $session['step'] = 3;
        sendMessage($chat_id, "عالی! 💖\n\n**آدرستون رو وارد کنید** (شهر، خیابان، etc.):");
    } else {
        sendMessage($chat_id, "❌ شماره تماس معتبر نیست. لطفاً دوباره وارد کنید (مثال: ۰۹۱۲۳۴۵۶۷۸۹):");
    }
    
} elseif ($session['step'] == 3) {
    $session['address'] = trim($text);
    $session['step'] = 4;
    sendMessage($chat_id, "**سنتون رو وارد کنید** (عدد):");
    
} elseif ($session['step'] == 4) {
    if (is_numeric($text) && $text > 0 && $text < 120) {
        $session['age'] = $text;
        $session['step'] = 5;
        sendMessage($chat_id, "**سابقه پزشکی یا آلرژی خاصی دارید؟**\n(اگر نه، بنویس 'هیچ' یا '-'):");
    } else {
        sendMessage($chat_id, "❌ سن وارد شده معتبر نیست. لطفاً عدد صحیح وارد کنید:");
    }
    
} elseif ($session['step'] == 5) {
    $session['medical_history'] = trim($text);
    $session['step'] = 6;
    $keyboard = buildServicesKeyboard();
    sendMessage($chat_id, "**کدام خدمات زیبایی مد نظرتون هست؟**\n(میتونید چندتا انتخاب کنید) 👗\n\n_برای تکمیل انتخاب، دکمه '✅ انتخاب‌ها کامل شد' رو بزنید._", $keyboard);
    
} elseif ($session['step'] == 6 && strpos($text, 'service_') === 0) {
    $service = str_replace('service_', '', $text);
    if (!isset($session['services'])) $session['services'] = [];
    
    // Toggle selection
    if (in_array($service, $session['services'])) {
        $key = array_search($service, $session['services']);
        unset($session['services'][$key]);
    } else {
        $session['services'][] = $service;
    }
    
    // Update inline keyboard
    $keyboard = buildServicesKeyboard($session['services']);
    editMessage($chat_id, $message_id, "**کدام خدمات زیبایی مد نظرتون هست؟**\n(میتونید چندتا انتخاب کنید) 👗\n\n_انتخاب شده: " . count($session['services']) . " مورد_\n_برای تکمیل، دکمه پایین رو بزنید._", $keyboard);
    
} elseif ($session['step'] == 6 && $text == 'done_services') {
    if (empty($session['services'])) {
        sendMessage($chat_id, "❌ لطفاً حداقل **یک سرویس** انتخاب کنید! 🌹");
    } else {
        $session['step'] = 7;
        // Display selected services for confirmation
        $selected_services = [];
        foreach ($session['services'] as $service_key) {
            if (isset($services[$service_key])) {
                $selected_services[] = $services[$service_key];
            }
        }
        sendMessage($chat_id, "✅ خدمات انتخابی شما:\n" . implode("\n", $selected_services) . "\n\n**توضیحات اضافی یا سوالی دارید؟**\n(اگر نه، بنویس 'هیچ' یا '-'):");
    }
    
} elseif ($session['step'] == 7) {
    $session['notes'] = trim($text);
    
    // Format and send the final form to admin
    $selected_services_names = [];
    foreach ($session['services'] as $service_key) {
        if (isset($services[$service_key])) {
            $selected_services_names[] = $services[$service_key];
        }
    }
    
    $form_data = "📋 **فرم مشاوره جدید - کلینیک دکتر سمیرا مصباح**\n" .
                 "────────────────────\n" .
                 "👤 نام: {$session['name']}\n" .
                 "📞 تلفن: {$session['phone']}\n" .
                 "📍 آدرس: {$session['address']}\n" .
                 "🎂 سن: {$session['age']}\n" .
                 "🩺 سابقه پزشکی: {$session['medical_history']}\n" .
                 "💅 خدمات درخواستی:\n   • " . implode("\n   • ", $selected_services_names) . "\n" .
                 "📝 توضیحات: {$session['notes']}\n" .
                 "────────────────────\n" .
                 "🆔 کد کاربر: {$session['user_id']}\n" .
                 "🕒 تاریخ: " . date('Y/m/d H:i');
    
    sendMessage(TARGET_ID, $form_data);
    
    // Notify user and enter free chat mode
    sendMessage($chat_id, "✅ **فرم شما با موفقیت ثبت شد!** 🌟\n\nاز این به بعد می‌تونید به صورت آزادانه با دکتر گفتگو کنید. هر پیام متنی یا ویسی که بفرستید، مستقیم برای ایشان ارسال میشه. 💬\n\nبرای پایان گفتگو، از دستور /end استفاده کنید.");
    $session['step'] = 8; // Enter free chat mode
    
} elseif ($session['step'] == 8) {
    if ($text == '/end') {
        sendMessage($chat_id, "👋 گفتگو به پایان رسید. ممنون از اعتماد شما به کلینیک زیبایی دکتر سمیرا مصباح! 💅\n\nبرای شروع مجدد، /start رو بزنید.");
        unset($sessions[$session_key]); // Clear session
    } else {
        // Forward user's message (text, voice, etc.) to admin
        $message_id_to_forward = $update['message']['message_id'];
        forwardMessage(TARGET_ID, $chat_id, $message_id_to_forward);
        sendMessage($chat_id, "📤 پیام شما ارسال شد. 🌹\nادامه بدید یا /end بزنید.");
    }
}

// Save session
$sessions[$session_key] = $session;
file_put_contents(SESSION_FILE, json_encode($sessions));

// ============================================
// HELPER FUNCTIONS
// ============================================
function sendMessage($chat_id, $text, $reply_markup = null) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage?chat_id=$chat_id&parse_mode=Markdown&text=" . urlencode($text);
    if ($reply_markup) $url .= "&reply_markup=" . urlencode(json_encode($reply_markup));
    file_get_contents($url);
}

function editMessage($chat_id, $message_id, $text, $reply_markup = null) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/editMessageText?chat_id=$chat_id&message_id=$message_id&parse_mode=Markdown&text=" . urlencode($text);
    if ($reply_markup) $url .= "&reply_markup=" . urlencode(json_encode($reply_markup));
    file_get_contents($url);
}

function forwardMessage($to_chat_id, $from_chat_id, $message_id) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/forwardMessage?chat_id=$to_chat_id&from_chat_id=$from_chat_id&message_id=$message_id";
    file_get_contents($url);
}

function buildServicesKeyboard($selected = []) {
    global $services;
    $buttons = [];
    
    // Create 2-column grid of service buttons
    $service_keys = array_keys($services);
    for ($i = 0; $i < count($service_keys); $i += 2) {
        $row = [];
        for ($j = 0; $j < 2; $j++) {
            if ($i + $j < count($service_keys)) {
                $key = $service_keys[$i + $j];
                $label = in_array($key, $selected) ? "✅ " . $services[$key] : $services[$key];
                $row[] = ['text' => $label, 'callback_data' => "service_$key"];
            }
        }
        if (!empty($row)) {
            $buttons[] = $row;
        }
    }
    
    // Add "Done" button
    $buttons[] = [['text' => '✅ انتخاب‌ها کامل شد', 'callback_data' => 'done_services']];
    
    return ['inline_keyboard' => $buttons];
}
?>