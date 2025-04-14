<?php

/**
 * This file demonstrates how to integrate the FCM PHP SDK with Symfony
 * 
 * Installation:
 * 1. Install the package through composer
 *    composer require volk/php-firebase-cloud-messaging
 * 
 * 2. Create a service
 * 3. Add configuration
 * 4. Use the service in your controllers or services
 */

/**
 * Example FCM Service (src/Service/FCMService.php)
 */

namespace App\Service;

use Firebase\CloudMessaging\Client\FCMClient;
use Firebase\CloudMessaging\Models\Priority;
use Firebase\CloudMessaging\RequestResponse\Message;

class FCMService
{
    private $fcmClient;

    public function __construct(string $serverKey)
    {
        $this->fcmClient = new FCMClient($serverKey);
    }

    /**
     * Send notification to a single device
     */
    public function sendToDevice(
        string $token, 
        string $title, 
        string $body, 
        array $data = [], 
        bool $highPriority = true
    ): bool {
        $message = new Message();
        $message
            ->setNotification([
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ])
            ->setData($data)
            ->setToken($token);
            
        if ($highPriority) {
            $message->setPriority(Priority::HIGH);
        }
        
        $response = $this->fcmClient->send($message);
        
        return $response->isSuccess();
    }

    /**
     * Send notification to multiple devices
     */
    public function sendToDevices(
        array $tokens, 
        string $title, 
        string $body, 
        array $data = []
    ): array {
        $message = new Message();
        $message
            ->setNotification([
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ])
            ->setData($data)
            ->setTokens($tokens);
            
        $response = $this->fcmClient->send($message);
        
        if ($response->isSuccess()) {
            $result = $response->getResult();
            return [
                'success' => $result['success'] ?? 0,
                'failure' => $result['failure'] ?? 0,
                'results' => $result['results'] ?? []
            ];
        }
        
        return [
            'success' => 0,
            'failure' => count($tokens),
            'error' => $response->getErrorMessage()
        ];
    }

    /**
     * Send notification to a topic
     */
    public function sendToTopic(
        string $topic,
        string $title,
        string $body,
        array $data = [],
        int $timeToLive = 86400
    ): bool {
        $message = new Message();
        $message
            ->setNotification([
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ])
            ->setData($data)
            ->setTopic($topic)
            ->setTimeToLive($timeToLive);
            
        $response = $this->fcmClient->send($message);
        
        return $response->isSuccess();
    }
}

/**
 * Symfony service configuration (config/services.yaml)
 */
/*
services:
    # Default configuration for services in this file
    _defaults:
        autowire: true
        autoconfigure: true

    # Custom services
    App\Service\FCMService:
        arguments:
            $serverKey: '%env(FCM_SERVER_KEY)%'
*/

/**
 * Environment configuration (.env)
 */
/*
# Firebase Cloud Messaging
FCM_SERVER_KEY=your_firebase_server_key
*/

/**
 * Example controller usage (src/Controller/NotificationController.php)
 */

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\FCMService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    private $fcmService;
    private $userRepository;
    
    public function __construct(FCMService $fcmService, UserRepository $userRepository)
    {
        $this->fcmService = $fcmService;
        $this->userRepository = $userRepository;
    }
    
    /**
     * @Route("/api/notifications/user/{id}", name="send_notification_to_user", methods={"POST"})
     */
    public function sendToUser(Request $request, int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        
        if (empty($user->getFcmToken())) {
            return $this->json(['error' => 'User does not have a registered device'], 400);
        }
        
        $data = json_decode($request->getContent(), true);
        $title = $data['title'] ?? 'Notification';
        $body = $data['body'] ?? 'You have a new notification';
        $additionalData = $data['data'] ?? [];
        
        $success = $this->fcmService->sendToDevice(
            $user->getFcmToken(),
            $title,
            $body,
            $additionalData
        );
        
        if ($success) {
            return $this->json(['message' => 'Notification sent successfully']);
        }
        
        return $this->json(['error' => 'Failed to send notification'], 500);
    }
    
    /**
     * @Route("/api/notifications/topic/{topic}", name="send_notification_to_topic", methods={"POST"})
     */
    public function sendToTopic(Request $request, string $topic): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $title = $data['title'] ?? 'Notification';
        $body = $data['body'] ?? 'You have a new notification';
        $additionalData = $data['data'] ?? [];
        
        $success = $this->fcmService->sendToTopic(
            $topic,
            $title,
            $body,
            $additionalData
        );
        
        if ($success) {
            return $this->json(['message' => 'Topic notification sent successfully']);
        }
        
        return $this->json(['error' => 'Failed to send topic notification'], 500);
    }
    
    /**
     * @Route("/api/notifications/all", name="send_notification_to_all", methods={"POST"})
     */
    public function sendToAll(Request $request): JsonResponse
    {
        $users = $this->userRepository->findAll();
        $tokens = [];
        
        foreach ($users as $user) {
            if (!empty($user->getFcmToken())) {
                $tokens[] = $user->getFcmToken();
            }
        }
        
        if (empty($tokens)) {
            return $this->json(['error' => 'No users with registered devices found'], 400);
        }
        
        $data = json_decode($request->getContent(), true);
        $title = $data['title'] ?? 'Notification';
        $body = $data['body'] ?? 'You have a new notification';
        $additionalData = $data['data'] ?? [];
        
        $result = $this->fcmService->sendToDevices(
            $tokens,
            $title,
            $body,
            $additionalData
        );
        
        return $this->json($result);
    }
}

/**
 * Example User entity with FCM token (src/Entity/User.php)
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fcmToken;
    
    // Other properties and methods...
    
    public function getFcmToken(): ?string
    {
        return $this->fcmToken;
    }
    
    public function setFcmToken(?string $fcmToken): self
    {
        $this->fcmToken = $fcmToken;
        
        return $this;
    }
}
