<?php

namespace App\Notifications;

use App\Models\Permission;
use App\Models\Scope;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PermissionExpiringNotification
 *
 * Notify user that a permission will expire soon
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance
     */
    public function __construct(
        public Permission $permission,
        public Carbon $expiresAt,
        public ?Scope $scope = null
    ) {}

    /**
     * Get the notification's delivery channels
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification
     */
    public function toMail(object $notifiable): MailMessage
    {
        $daysRemaining = now()->diffInDays($this->expiresAt);

        $message = (new MailMessage)
            ->subject('Permission Expiring Soon')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your permission will expire in ' . $daysRemaining . ' day(s):')
            ->line('**Permission:** ' . $this->permission->name)
            ->line('**Slug:** ' . $this->permission->slug)
            ->line('**Expires At:** ' . $this->expiresAt->format('Y-m-d H:i'));

        if ($this->scope) {
            $message->line('**Scope:** ' . $this->scope->getDisplayName());
        }

        $message->line('Please contact your administrator if you need to extend this permission.')
            ->action('Request Extension', url('/permission-requests/create'))
            ->line('Thank you!');

        return $message;
    }

    /**
     * Get the array representation of the notification
     */
    public function toArray(object $notifiable): array
    {
        return [
            'permission_id' => $this->permission->id,
            'permission_slug' => $this->permission->slug,
            'permission_name' => $this->permission->name,
            'expires_at' => $this->expiresAt->toDateTimeString(),
            'days_remaining' => now()->diffInDays($this->expiresAt),
            'scope_id' => $this->scope?->id,
            'scope_name' => $this->scope?->getDisplayName(),
            'message' => 'Your permission "' . $this->permission->name . '" expires in ' . now()->diffInDays($this->expiresAt) . ' day(s).',
        ];
    }
}
