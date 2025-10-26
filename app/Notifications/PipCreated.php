<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Pip;

class PipCreated extends Notification
{
    use Queueable;

    protected $pip;
    protected $type;

    public function __construct(Pip $pip, string $type = 'hr')
    {
        $this->pip = $pip;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = config('app.url') . '/appraisal/pips/' . ($this->pip->id ?? '');

        return (new MailMessage)
            ->subject('Performance Improvement Plan (PIP) Created')
            ->greeting('Hello,')
            ->line("A Performance Improvement Plan has been created for: {$this->pip->user->name}.")
            ->line('Reason: ' . ($this->pip->reason ?? 'Not specified'))
            ->line('Start: ' . ($this->pip->start_date ?? 'N/A') . ' — End: ' . ($this->pip->end_date ?? 'N/A'))
            ->action('View PIP', $url)
            ->line('This is an automated notification.');
    }
}
