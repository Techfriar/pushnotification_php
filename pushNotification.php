<?php
require_once './config.php';
class PushNotification
{
    private $config;

    public function __construct()
    {
        $this->config = new Config();
    }

    /**
     * Sends a push notification to specified FCM (Firebase Cloud Messaging) tokens.
     *
     * This method sends a notification with a given title and body to a list of FCM tokens.
     * It makes an HTTP POST request to the API endpoint specified in the environment variables.
     * If the request is successful and the response status indicates success, the method returns
     * the data received from the API. If the status indicates failure, the method returns `false`.
     * In case of an error during the request, an error is thrown.
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
        $url = $this->config->apiUrl . '/send'; // Use the URL from the Config class

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
                throw new Exception('cURL Error: ' . curl_error($ch));
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $responseData = json_decode($response, true);

            if ($httpCode === 200 && isset($responseData['status']) && $responseData['status']) {
                return $responseData['data'];
            }

            return false;
        } catch (Exception $e) {
            // Log or handle the exception as needed
            throw new Exception('Failed to send notification: ' . $e->getMessage());
        }
    }
}
