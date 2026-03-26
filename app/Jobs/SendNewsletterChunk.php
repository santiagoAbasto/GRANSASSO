<?php

namespace App\Jobs;

use App\Mail\NewsletterCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewsletterChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public array $emails,
        public string $subject,
        public string $body,
    ) {}

    public function handle(): void
    {
        foreach ($this->emails as $email) {
            Mail::to($email)->send(new NewsletterCampaign($this->subject, $this->body));
        }
    }
}
