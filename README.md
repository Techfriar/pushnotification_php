# techfriar/pushnotification_php

`techfriar/pushnotification_php` is a PHP package for sending push notifications.
This package provides a simple interface to send notifications to a list of devices.

## Installation

To use this package:

Add repositories in `composer.json`

```json
 "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Techfriar/pushnotification_php.git"
        }
    ],
```

Add package name in `composer.json`

```json
 "techfriar/pushnotification_php": "dev-master" //master branch
```

## Usage

1. **Import the package:**

```php
use PushNotification;
```

2. **Create an instance of `PushNotification`:**

```php
$pushNotification = new PushNotification('<Your Docker Container URL>/api'); // Docker Container URL
```

3. **Send a notification:**

```php
 // Define the notification title and body
$title = "Notification Title";
$body = "Notification Body";

// List of FCM tokens to send the notification to
$fcmTokens = [];

// Send the notification
$sendNotification = $pushNotification->sendNotification($title, $body, $fcmTokens);
```

- `title`: The title of the push notification.
- `body`: The body content of the push notification.
- `fcmTokens`: An array of FCM tokens to which the notification should be sent.

The `sendNotification` method returns the data from the API response if the notification was sent successfully, or `false` if there was a failure.
