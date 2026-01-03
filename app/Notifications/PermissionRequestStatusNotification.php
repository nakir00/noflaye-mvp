<?php

namespace App\Notifications;

use App\Models\PermissionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PermissionRequestStatusNotification
 *
 * Notify user about permission request status change
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class PermissionRequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance
     */
    public function __construct(
        public PermissionRequest $request,
        public string $status
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
        $subject = match ($this->status) {
            'approved' => 'Permission Request Approved',
            'rejected' => 'Permission Request Rejected',
            default => 'Permission Request Updated',
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello '.$notifiable->name.',');

        if ($this->status === 'approved') {
            $message->line('Good news! Your permission request has been approved.')
                ->line('**Permission:** '.$this->request->permission->name)
                ->line('**Reviewed By:** '.$this->request->reviewer->name)
                ->line('**Reviewed At:** '.$this->request->reviewed_at->format('Y-m-d H:i'));

            if ($this->request->scope) {
                $message->line('**Scope:** '.$this->request->scope->getDisplayName());
            }

            if ($this->request->review_comment) {
                $message->line('**Comment:** '.$this->request->review_comment);
            }

            $message->line('You can now use this permission.')
                ->action('View My Permissions', url('/my-permissions'));
        } elseif ($this->status === 'rejected') {
            $message->line('Your permission request has been rejected.')
                ->line('**Permission:** '.$this->request->permission->name)
                ->line('**Reviewed By:** '.$this->request->reviewer->name)
                ->line('**Reviewed At:** '.$this->request->reviewed_at->format('Y-m-d H:i'));

            if ($this->request->review_comment) {
                $message->line('**Reason:** '.$this->request->review_comment);
            }

            $message->line('You can submit a new request if needed.')
                ->action('Submit New Request', url('/permission-requests/create'));
        } else {
            $message->line('Your permission request status has been updated.')
                ->line('**Permission:** '.$this->request->permission->name)
                ->line('**Status:** '.ucfirst($this->status))
                ->action('View Request', url('/permission-requests/'.$this->request->id));
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
            'request_id' => $this->request->id,
            'permission_id' => $this->request->permission_id,
            'permission_slug' => $this->request->permission->slug,
            'permission_name' => $this->request->permission->name,
            'status' => $this->status,
            'reviewed_by' => $this->request->reviewed_by,
            'reviewer_name' => $this->request->reviewer?->name,
            'reviewed_at' => $this->request->reviewed_at?->toDateTimeString(),
            'review_comment' => $this->request->review_comment,
            'scope_id' => $this->request->scope_id,
            'scope_name' => $this->request->scope?->getDisplayName(),
            'message' => 'Your permission request for "'.$this->request->permission->name.'" has been '.$this->status.'.',
        ];
    }
}
