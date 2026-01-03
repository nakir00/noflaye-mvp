<?php

namespace App\Notifications;

use App\Models\PermissionDelegation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PermissionDelegatedNotification
 *
 * Notify user that a permission has been delegated to them
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class PermissionDelegatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance
     */
    public function __construct(
        public PermissionDelegation $delegation
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
        $message = (new MailMessage)
            ->subject('Permission Delegated to You')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A permission has been delegated to you:')
            ->line('**Permission:** '.$this->delegation->permission_slug)
            ->line('**Delegated By:** '.$this->delegation->delegator_name)
            ->line('**Valid Until:** '.$this->delegation->valid_until->format('Y-m-d H:i'));

        if ($this->delegation->scope) {
            $message->line('**Scope:** '.$this->delegation->scope->getDisplayName());
        }

        if ($this->delegation->reason) {
            $message->line('**Reason:** '.$this->delegation->reason);
        }

        if ($this->delegation->can_redelegate) {
            $message->line('**Note:** You can re-delegate this permission to others.')
                ->action('Manage Delegations', url('/my-delegations'));
        } else {
            $message->action('View My Delegations', url('/my-delegations'));
        }

        $message->line('Thank you!');

        return $message;
    }

    /**
     * Get the array representation of the notification
     */
    public function toArray(object $notifiable): array
    {
        return [
            'delegation_id' => $this->delegation->id,
            'permission_slug' => $this->delegation->permission_slug,
            'delegator_id' => $this->delegation->delegator_id,
            'delegator_name' => $this->delegation->delegator_name,
            'valid_until' => $this->delegation->valid_until->toDateTimeString(),
            'can_redelegate' => $this->delegation->can_redelegate,
            'reason' => $this->delegation->reason,
            'scope_id' => $this->delegation->scope_id,
            'scope_name' => $this->delegation->scope?->getDisplayName(),
            'message' => $this->delegation->delegator_name.' delegated "'.$this->delegation->permission_slug.'" to you until '.$this->delegation->valid_until->format('Y-m-d'),
        ];
    }
}
