<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Pip;

class PipCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pip;
    public $recipientType;

    public function __construct(Pip $pip, $recipientType = 'hr')
    {
        $this->pip = $pip;
        $this->recipientType = $recipientType;
    }

    public function build()
    {
        $subject = 'New PIP: ' . $this->pip->user->name;
        if ($this->recipientType === 'manager') {
            $subject = 'Action required: PIP created for your report ' . $this->pip->user->name;
        } elseif ($this->recipientType === 'employee') {
            $subject = 'Your PIP has been created';
        }

        return $this->subject($subject)
            ->view('emails.pip_created')
            ->with([
                'pip' => $this->pip,
                'recipientType' => $this->recipientType,
                'manager' => $this->pip->user->lineManager ?? null
            ]);
    }
}
