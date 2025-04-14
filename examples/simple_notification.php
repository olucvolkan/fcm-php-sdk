<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\CloudMessaging\Client\FCMClient;
use Firebase\CloudMessaging\Models\Priority;
use Firebase\CloudMessaging\RequestResponse\Message;

// Your Firebase Cloud Messaging server key
$serverKey = 'YOUR_FCM_SERVER_KEY';

// Initialize the FCM client
$fcmClient = new FCMClient($serverKey);

// Create a new message
$message = new Message();

// Example 1: Send notification to a single device
$message
    ->setNotification([
        'title' => 'Hello World',
        'body' => 'This is a test notification',
        'sound' => 'default',
        'badge' => '1'
    ])
    ->setData([
        'key1' => 'value1',
        'key2' => 'value2'
    ])
    ->setToken('DEVICE_FCM_TOKEN')  // Replace with an actual FCM token
    ->setPriority(Priority::HIGH);

try {
    // Send the message
    $response = $fcmClient->send($message);
    
    // Check the response
    if ($response->isSuccess()) {
        echo "Success! Message sent.\n";
        $result = $response->getResult();
        echo "Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Failed to send message. Status code: " . $response->getStatusCode() . "\n";
        echo "Error message: " . $response->getErrorMessage() . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Example 2: Send notification to multiple devices
$message = new Message();
$message
    ->setNotification([
        'title' => 'Bulk Message',
        'body' => 'This message was sent to multiple devices',
    ])
    ->setTokens([
        'TOKEN1',  // Replace with actual FCM tokens
        'TOKEN2',
        'TOKEN3'
    ]);

try {
    $response = $fcmClient->send($message);
    if ($response->isSuccess()) {
        $result = $response->getResult();
        echo "\nMulti-device message sent!\n";
        echo "Success count: " . $result['success'] . "\n";
        echo "Failure count: " . $result['failure'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Example 3: Send notification to a topic
$message = new Message();
$message
    ->setNotification([
        'title' => 'Topic Message',
        'body' => 'This message was sent to a topic',
    ])
    ->setTopic('news')  // Users must be subscribed to this topic
    ->setTimeToLive(86400);  // 1 day in seconds

try {
    $response = $fcmClient->send($message);
    if ($response->isSuccess()) {
        echo "\nTopic message sent!\n";
        echo "Response: " . json_encode($response->getResult(), JSON_PRETTY_PRINT) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
