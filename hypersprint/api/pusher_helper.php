<?php
// Pusher Credentials for Hypersprint
define('PUSHER_APP_ID', '2159936');
define('PUSHER_KEY', '5285b141d0e1f0c0cae6');
define('PUSHER_SECRET', 'efaa8a2ad59cf7fa0fff');
define('PUSHER_CLUSTER', 'ap4');

/**
 * Lightweight Pusher Trigger for PHP 5.4+
 * Sends an event to a channel.
 */
function pusher_trigger($channel, $event, $data) {
    $path = "/apps/" . PUSHER_APP_ID . "/events";
    $host = "api-" . PUSHER_CLUSTER . ".pusher.com";
    
    $body = json_encode(array(
        'name' => $event,
        'channels' => array($channel),
        'data' => json_encode($data)
    ));

    $params = array(
        'auth_key' => PUSHER_KEY,
        'auth_timestamp' => time(),
        'auth_version' => '1.0',
        'body_md5' => md5($body)
    );

    ksort($params);
    $query = http_build_query($params);
    
    $string_to_sign = "POST\n" . $path . "\n" . $query;
    $params['auth_signature'] = hash_hmac('sha256', $string_to_sign, PUSHER_SECRET);
    
    $url = "https://" . $host . $path . "?" . http_build_query($params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    // Disable SSL verification if Mercury has outdated certificates (common on old servers)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}
