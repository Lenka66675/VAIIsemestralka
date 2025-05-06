<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MessageReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $replyMessage;
    public $originalMessage;
    public $name;
    public $senderName;
    public $senderEmail;

    public function __construct($name, $originalMessage, $replyMessage, $senderName, $senderEmail)
    {
        $this->name = $name;
        $this->originalMessage = $originalMessage;
        $this->replyMessage = $replyMessage;
        $this->senderName = $senderName;
        $this->senderEmail = $senderEmail;
    }

    public function build()
    {


        return $this->from($this->senderEmail, $this->senderName)
            ->subject('Reply to your message')
            ->view('products.reply');
    }






    /**
     * Create a new message instance.
     */

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Message Reply Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'products.reply',
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
