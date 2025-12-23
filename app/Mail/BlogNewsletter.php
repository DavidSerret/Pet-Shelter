<?php

namespace App\Mail;

use App\Models\BlogPost;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BlogNewsletter extends Mailable
{
    use Queueable, SerializesModels;

    public $post;
    public $unsubscribeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(BlogPost $post)
    {
        $this->post = $post;
        $this->unsubscribeUrl = url('/newsletter/unsubscribe'); // Placeholder
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->post->title . ' - Pet Shelter Blog',
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.blog-newsletter',
            with: [
                'post' => $this->post,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}