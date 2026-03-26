<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterCampaign extends Mailable
{
    use Queueable, SerializesModels;

    public string $campaignSubject;
    public string $campaignBody;

    public function __construct(string $subject, string $body)
    {
        $this->campaignSubject = $subject;
        $this->campaignBody    = $body;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campaignSubject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.newsletter-campaign');
    }
}
