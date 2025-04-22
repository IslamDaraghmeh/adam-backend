<?php

namespace App\Services;

use App\Models\Notfication;
use Log;
use Http;
use App\Models\User_device;
use Google\Client;
use Exception;

class FCMNotificationService
{
    protected string $projectUrl = 'https://fcm.googleapis.com/v1/projects/adamgriup-3b108/messages:send';
    public function sendNotification(array $userIds, string $title, string $message): array
    {
        $tokens = User_device::whereIn('user_id', $userIds)->pluck('device_token')->toArray();
        $devices = User_device::whereIn('user_id', $userIds)
        ->get(['user_id', 'device_token']);
        if (empty($tokens)) {
            return ['status' => false, 'message' => 'No device tokens found for the provided user IDs.'];
        }

        $accessToken = $this->getAccessToken();
        $responses = [];
        $processedUsers = []; // Keeps track of processed user IDs
        foreach ($devices as $device) {
            $userId =  (int) $device->user_id;
            $token = $device->device_token;
            $notificationData = $this->buildNotificationPayload($token, $title, $message);

            try {
                $response = Http::withToken($accessToken)->post($this->projectUrl, $notificationData);
                $logData = [
                    'userId' => $userId,
                    'token' => $token,
                    'status' => $response->successful(),
                    'response' => $response->json(),
                ];
                // Store notification history **only once per user**
                if (!array_key_exists($userId, $processedUsers)) {
                    Log::info("fcm entered if statment ..!");
                    $this->storeNotificationsHistory($userId, $title, $message, $response->json());
                    $processedUsers[$userId] = true; // Mark as processed
                }
                if ($response->successful()) {
                    Log::channel('fcm')->info('FCM Notification Sent', $logData);
                } else {
                    Log::channel('fcm')->error('FCM Notification Failed', $logData);
                }
                $responses[] = [
                    'token' => $token,
                    'response' => $response->json(),
                    'status' => $response->successful(),
                ];
            } catch (Exception $e) {
                Log::error("FCM Notification Failed: " . $e->getMessage());

                $responses[] = [
                    'token' => $token,
                    'error' => $e->getMessage(),
                    'status' => false
                ];
            }
        }

        return [
            'status' => true,
            'message' => 'Notifications processed.',
            'results' => $responses
        ];
    }
    private function storeNotificationsHistory($userId, $title, $message, $response) {
        Log::info("fcm is storing now ..!");
        // Store notification in DB
        $notification = Notfication::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $message,
            // 'data' => $response,
        ]);
        Log::channel('fcm')->info('FCM Notification History Stored', ['notificationId' => $notification->id]);
    }
    private function buildNotificationPayload(string $token, string $title, string $message): array
    {
        return [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body" => $message
                ],
                "android" => [
                    "notification" => [
                        "sound" => "default"
                    ]
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "sound" => "default"
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Retrieve OAuth2 Access Token for Firebase.
     *
     * @return string
     */
    private function getAccessToken(): string
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('fcm-service-account.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        return $client->fetchAccessTokenWithAssertion()['access_token'];
    }




}