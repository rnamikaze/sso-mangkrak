<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendSlipGaji extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $subject;
    public $attachmentData;
    public $attachmentName;

    /**
     * Create a new message instance.
     */
    public function __construct($data, $subject, $attachmentData, $attachmentName)
    {
        //
        $this->data = $data;
        $this->subject = $subject;
        $this->attachmentData = $attachmentData;
        $this->attachmentName = $attachmentName;
    }

    /**
     * Get the message envelope.
     */
    // public function envelope(): Envelope
    // {
    //     // return new Envelope(
    //     //     subject: 'Send Slip Gaji',
    //     // );
    //     return new Envelope(
    //         subject: 'Order Shipped',
    //     );
    // }

    public function build()
    {
        return $this->subject($this->subject)
            ->view('mails.slip-gaji', ["subject" => $this->subject]);
        // ->attachData($this->attachmentData, $this->attachmentName);
    }

    /**
     * Get the message content definition.
     */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    // public function attachments(): array
    // {
    //     return [];
    // }
}
