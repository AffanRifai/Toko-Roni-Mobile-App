<?php

namespace App\Traits;

trait InAppNotificationTrait
{
    /**
     * Standardize the database notification payload.
     */
    public function getInAppPayload(string $type, string $message, string $icon = 'fas fa-bell', string $color = 'blue', ?string $url = null, array $extra = [])
    {
        return array_merge([
            'type' => $type,
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
            'url' => $url,
            'user_name' => auth()->user()->name ?? 'System',
            'user_role' => auth()->user()->role ?? 'system',
        ], $extra);
    }
}
