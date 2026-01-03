<?php

namespace App\Notifications;

use App\Models\Permission;
use App\Models\Scope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PermissionExpiredNotification
 *
 * Notify user that a permission has expired
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class PermissionExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance
     */
    public function __construct(
        public Permission $permission,
        public ?Scope $scope = null
    ) {}

    /**
     * Get the notification's delivery channels
     *
     * @return array<int, string>
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
        $message = (new MailMessage)
            ->subject('Permission Expired')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your permission has expired:')
            ->line('**Permission:** '.$this->permission->name)
            ->line('**Slug:** '.$this->permission->slug);

        if ($this->scope) {
            $message->line('**Scope:** '.$this->scope->getDisplayName());
        }

        $message->line('If you need to renew this permission, please contact your administrator.')
            ->action('View My Permissions', url('/my-permissions'))
            ->line('Thank you!');

        return $message;
    }

    /**
     * Get the array representation of the notification
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'permission_id' => $this->permission->id,
            'permission_slug' => $this->permission->slug,
            'permission_name' => $this->permission->name,
            'scope_id' => $this->scope?->id,
            'scope_name' => $this->scope?->getDisplayName(),
            'message' => 'Your permission "'.$this->permission->name.'" has expired.',
        ];
    }
}
