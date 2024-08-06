<?php

class PushNotification
{
    private $apiUrl;

    /**
     * Constructs a new instance of the PushNotification class.
     *
     * This constructor initializes the PushNotification class with specified protocol,
     * host, and port, which are used to construct the base URL of the API to send push notifications.
     * Defaults are provided for protocol and port.
     *
     * @param string $host - The host of the API (e.g., "localhost" or "api.example.com").
     * @param string $protocol - The protocol of the API (e.g., "http" or "https"). Defaults to "http".
     * @param int $port - The port of the API (e.g., 80, 443, 3000). Defaults to 5000.
     * @throws Exception - Throws an exception if the URL is invalid.
     */
    public function __construct($host, $protocol = 'http', $port = 5000)
    {
        if (empty($host)) {
            throw new Exception('Host is required and cannot be empty.');
        }
        if (empty($protocol)) {
            throw new Exception('Protocol is required and cannot be empty.');
        }
        if (empty($port)) {
            throw new Exception('Port is required and cannot be empty.');
        }

        $this->apiUrl = sprintf('%s://%s:%d', $protocol, $host, $port);

        // Validate the constructed URL
        if (!filter_var($this->apiUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL constructed from provided protocol, host, and port.');
        }
    }

    /**
     * Sends a push notification to specified FCM (Firebase Cloud Messaging) tokens.
     *
     * @param string $title - The title of the push notification.
     * @param string $body - The body content of the push notification.
     * @param array $fcmTokens - An array of FCM tokens to which the notification should be sent.
     * @param Object [data={}] - An optional data object to include in the notification.
     *
     * @return mixed - Returns the data from the API response if successful, or `false` if the response indicates failure.
     *
     * @throws Exception - Throws an exception if there is an issue with the request or response.
     */
    public function sendNotification($title, $body, $fcmTokens, $data = [])
    {

        // Validate title
        if (!is_string($title) || trim($title) === '') {
            throw new Exception('Title must be a non-empty string.');
        }

        // Validate body
        if (!is_string($body) || trim($body) === '') {
            throw new Exception('Body must be a non-empty string.');
        }

        // Validate fcmTokens
        if (!is_array($fcmTokens) || empty($fcmTokens) || !array_reduce($fcmTokens, function ($carry, $token) {
            return $carry && is_string($token) && trim($token) !== '';
        }, true)) {
            throw new Exception('FCM tokens must be an array of non-empty strings.');
        }

        $url = $this->apiUrl . '/api/send'; // Use the URL from the Config class

        $data = json_encode([
            'title' => $title,
            'body' => $body,
            'fcm_tokens' => $fcmTokens,
            'data' => $data,
        ]);

        try {
            $ch = curl_init($url);
            if ($ch === false) {
                throw new Exception('Failed to initialize cURL session.');
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data),
            ]);

            $response = curl_exec($ch);
            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new Exception('cURL Error: ' . $error);
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $responseData = json_decode($response, true);

            if ($httpCode === 200 && isset($responseData['status']) && $responseData['status']) {
                return $responseData['data'];
            } else {
                return false;
            }
        } catch (Exception $e) {
            // Log or handle the exception as needed
            throw new Exception('Failed to send notification: ' . $e->getMessage());
        }
    }
}
