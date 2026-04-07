<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function createNotification(
        User $user,
        string $type,
        string $title,
        ?string $message = null,
        ?string $referenceType = null,
        ?string $referenceId = null
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }

    public function notifyFollowers(User $author, string $type, string $title, ?string $message = null, ?string $referenceType = null, ?string $referenceId = null): void
    {
        $followers = $author->followers;
        
        foreach ($followers as $follower) {
            $this->createNotification(
                $follower,
                $type,
                $title,
                $message,
                $referenceType,
                $referenceId
            );
        }
    }

    public function notifyUser(User $user, string $type, string $title, ?string $message = null, ?string $referenceType = null, ?string $referenceId = null): Notification
    {
        return $this->createNotification($user, $type, $title, $message, $referenceType, $referenceId);
    }
}
