# PHP Client for Firebase Cloud Messaging (FCM)

Firebase Cloud Messaging (FCM) is a cross-platform messaging solution that lets you reliably send notifications at no cost. Using FCM, you can notify a client app that new email or other data is available to sync. You can send notification messages to drive user re-engagement and retention.

This PHP SDK provides a clean and simple way to send push notifications with Firebase Cloud Messaging from your PHP applications.

Please refer to Firebase Cloud Messaging documentation for further details on FCM features and options.

## Installation

```bash
composer require volk/php-firebase-cloud-messaging
```

## Basic Usage

```php
// Initialize the FCM client with your Firebase server key
$fcmClient = new FCMClient('YOUR_FIREBASE_SERVER_KEY');

// Create a new message
$message = new Message();

// Configure the notification (visible to users)
$message
    ->setNotification([
        'title' => 'Hello World',
        'body' => 'This is a test notification',
        'sound' => 'default',
        'badge' => '1'
    ])
    // Add message data (invisible payload)
    ->setData([
        'key1' => 'value1',
        'key2' => 'value2'
    ])
    // Target a specific device
    ->setToken('DEVICE_FCM_TOKEN')
    // Or target multiple devices
    // ->setTokens(['TOKEN1', 'TOKEN2', 'TOKEN3'])
    // Or target a topic
    // ->setTopic('news')
    // Add optional parameters
    ->setPriority(Priority::HIGH)
    ->setTimeToLive(86400); // 1 day in seconds

// Send the message
$response = $fcmClient->send($message);

// Check the response
echo $response->getStatusCode(); // 200 if successful
$result = $response->getResult();
```

## Targeting Options

FCM messages can be sent to three types of targets:

1. **Single Device**: Send to a specific FCM registration token
   ```php
   $message->setToken('DEVICE_FCM_TOKEN');
   ```

2. **Multiple Devices**: Send to multiple FCM registration tokens (up to 1000 tokens)
   ```php
   $message->setTokens(['TOKEN1', 'TOKEN2', 'TOKEN3']);
   ```

3. **Topic**: Send to devices subscribed to a topic
   ```php
   $message->setTopic('news');
   ```

## Message Types

### Notification Messages

Notification messages display an alert, badge, or sound to notify the user about an incoming message. FCM handles displaying the notification on the user's device.

```php
$message->setNotification([
    'title' => 'Notification Title',
    'body' => 'Notification Body',
    'image' => 'https://example.com/image.png', // Optional
    'sound' => 'default',
    'badge' => '1',
    'click_action' => 'OPEN_ACTIVITY' // Optional: action when notification is tapped
]);
```

### Data Messages

Data messages contain your custom key-value pairs that are invisible to the user. Your client app is responsible for processing data messages.

```php
$message->setData([
    'score' => '850',
    'time' => '2:45',
    'type' => 'sports',
    'custom_data' => '{"key": "value"}'
]);
```

## Advanced Options

### Message Priority

Set the priority of the message:

```php
// Available options: Priority::HIGH, Priority::NORMAL
$message->setPriority(Priority::HIGH);
```

### Time To Live (TTL)

Set how long (in seconds) the message should be kept if the device is offline:

```php
$message->setTimeToLive(259200); // 3 days in seconds
```

### Collapse Key

Group multiple messages with the same collapse key to ensure only the last message is delivered when the device comes online:

```php
$message->setCollapseKey('updates');
```

### Content Available

For iOS, enable background updates when data arrives:

```php
$message->setContentAvailable(true);
```

### Mutable Content

For iOS, allow notification service extensions to modify the notification:

```php
$message->setMutableContent(true);
```

## Error Handling

```php
try {
    $response = $fcmClient->send($message);
    
    if ($response->isSuccess()) {
        echo "Message sent successfully!";
        $result = $response->getResult();
        
        // Check for successful tokens
        if (isset($result['success'])) {
            echo "Successful messages: " . $result['success'];
        }
        
        // Check for failed tokens
        if (isset($result['failure'])) {
            echo "Failed messages: " . $result['failure'];
            
            // Get detailed error information
            foreach ($result['results'] as $index => $tokenResult) {
                if (isset($tokenResult['error'])) {
                    echo "Token at index $index failed: " . $tokenResult['error'];
                }
            }
        }
    } else {
        echo "Failed to send message. Status code: " . $response->getStatusCode();
        echo "Error message: " . $response->getErrorMessage();
    }
} catch (FCMException $e) {
    echo "FCM error: " . $e->getMessage();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Response Fields

| Field          | Type    | Description                                      |
| :---           |    :----:   |    ---: |
| multicast_id   | string      | Unique ID identifying the multicast message |
| success        | integer     | Number of messages that were successfully processed |
| failure        | integer     | Number of messages that could not be processed |
| canonical_ids  | integer     | Number of results with canonical registration token |
| results        | array       | Array of objects representing the status of each token |

## Development & Contribution

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the LICENSE file for details.
