<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/pusher_helper.php';

// Anyone can trigger a test ping
$res = pusher_trigger('test-channel', 'test-event', array(
    'message' => 'Hello from Mercury!',
    'timestamp' => date('H:i:s')
));

send_json(array(
    'success' => true,
    'message' => 'Pusher event triggered',
    'raw_response' => $res
));
