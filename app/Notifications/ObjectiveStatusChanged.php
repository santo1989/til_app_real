<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Objective;

class ObjectiveStatusChanged extends Notification
{
    use Queueable;

    protected $objective;
    protected $status;
    protected $actor; // user who approved/rejected
    protected $reason;

    public function __construct(Objective $objective, string $status, $actor = null, ?string $reason = null)
    {
        $this->objective = $objective;
        $this->status = $status;
        $this->actor = $actor;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $subject = $this->status === 'approved' ? 'Objective Approved' : 'Objective Rejected';
        $line = $this->status === 'approved' ? 'Your objective has been approved.' : 'Your objective has been rejected.';
        $action = config('app.url') . '/appraisal/users/' . ($this->objective->user_id ?? '') . '/objectives';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . ($notifiable->name ?? ''))
            ->line($line)
            ->line('Description: ' . ($this->objective->description ?? ''))
            ->line('Financial Year: ' . ($this->objective->financial_year ?? ''))
            ->action('View Objectives', $action)
            ->line('This is an automated notification.');

        if ($this->actor) {
            $mail->line('Action by: ' . ($this->actor->name ?? $this->actor->id));
        }
        if ($this->reason) {
            $mail->line('Reason: ' . $this->reason);
        }

        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'objective_id' => $this->objective->id,
            'status' => $this->status,
            'actor_id' => $this->actor->id ?? null,
            'reason' => $this->reason,
        ];
    }
}
