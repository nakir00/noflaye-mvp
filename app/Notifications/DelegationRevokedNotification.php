<?php

namespace App\Notifications;

use App\Models\PermissionDelegation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * DelegationRevokedNotification
 *
 * Notify user that a delegation has been revoked
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class DelegationRevokedNotification extends Notification implements ShouldQueue
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
            ->subject('Permission Delegation Revoked')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A permission delegation has been revoked:')
            ->line('**Permission:** '.$this->delegation->permission_slug)
            ->line('**Delegated By:** '.$this->delegation->delegator_name)
            ->line('**Revoked At:** '.$this->delegation->revoked_at->format('Y-m-d H:i'));

        if ($this->delegation->scope) {
            $message->line('**Scope:** '.$this->delegation->scope->getDisplayName());
        }

        if ($this->delegation->revocation_reason) {
            $message->line('**Reason:** '.$this->delegation->revocation_reason);
        }

        $message->line('You no longer have access to this delegated permission.')
            ->action('View My Delegations', url('/my-delegations'))
            ->line('Thank you!');

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
            'revoked_at' => $this->delegation->revoked_at->toDateTimeString(),
            'revoked_by' => $this->delegation->revoked_by,
            'revocation_reason' => $this->delegation->revocation_reason,
            'scope_id' => $this->delegation->scope_id,
            'scope_name' => $this->delegation->scope?->getDisplayName(),
            'message' => 'Your delegation for "'.$this->delegation->permission_slug.'" has been revoked.',
        ];
    }
}
