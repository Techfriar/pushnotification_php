<?php

class PushNotification
{
    private $apiUrl;

    public function __construct($containerUrl)
    {
        $this->apiUrl = $containerUrl;
    }

    /**
     * Sends a push notification to specified FCM (Firebase Cloud Messaging) tokens.
     *
     * @param string $title - The title of the push notification.
     * @param string $body - The body content of the push notification.
     * @param array $fcmTokens - An array of FCM tokens to which the notification should be sent.
     *
     * @return mixed - Returns the data from the API response if successful, or `false` if the response indicates failure.
     *
     * @throws Exception - Throws an exception if there is an issue with the request or response.
     */
    public function sendNotification($title, $body, $fcmTokens)
    {
        $url = $this->apiUrl . '/send'; // Use the URL from the Config class

        $data = json_encode([
            'title' => $title,
            'body' => $body,
            'fcm_tokens' => $fcmTokens,
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
