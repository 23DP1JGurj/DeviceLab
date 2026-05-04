<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage)
    {
    }

    public function envelope(): Envelope
    {
        $replyTo = $this->contactMessage->email
            ? [new Address($this->contactMessage->email, $this->contactMessage->name)]
            : [];

        return new Envelope(
            replyTo: $replyTo,
            subject: 'Jauns ziņojums no DeviceLab kontaktformas',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-message');
    }
}
