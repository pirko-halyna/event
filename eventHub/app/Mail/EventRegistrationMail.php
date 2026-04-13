<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventRegistrationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public object $data;
    public string $eventUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(object $data, Event $event)
    {
        $this->data = $data;
        $this->eventUrl = route('events.show', $event);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Event Registration Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.event.registration',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // if it is a paid event and there are tickets available
        if (isset($this->data->tickets)) {
            foreach ($this->data->tickets as $ticket) {
                $attachments[] = Attachment::fromPath(storage_path('app/' . $ticket->pdf_path))
                    ->as('ticket.pdf')
                    ->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}
