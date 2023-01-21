<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Mail;

use Config;

class ReceiptEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private string $msgHtml;
    private string $msgSubject;

    /**
     * Create a new message instance.
     *
     * @param object {subject : string, message : string}
     * @return void
     */
    public function __construct(object $params)
    {
        $this->msgHtml = $params->message;
        $this->msgSubject = $params->subject;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: new Address(Config::get('mail.from.address'), Config::get('mail.from.name')),
            subject: Config::get('app.name') . ' ' . $this->msgSubject,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
           // view: 'mails.receipt',
           htmlString : $this->msgHtml
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
