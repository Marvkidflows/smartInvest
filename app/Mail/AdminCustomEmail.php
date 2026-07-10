<?php
// LOCATION: app/Mail/AdminCustomEmail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminCustomEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $emailSubject,
        public string $bodyHtml,
        public ?string $attachmentPath = null,
        public ?string $attachmentName = null,
    ) {}

    public function build()
    {
        $mail = $this->subject($this->emailSubject)
            ->view('emails.admin-custom')
            ->with([
                'subject'  => $this->emailSubject,
                'bodyHtml' => $this->bodyHtml,
            ]);

        if ($this->attachmentPath && file_exists($this->attachmentPath)) {
            $mail->attach($this->attachmentPath, [
                'as' => $this->attachmentName ?? basename($this->attachmentPath),
            ]);
        }

        return $mail;
    }
}